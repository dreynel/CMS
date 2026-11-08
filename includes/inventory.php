<?php
/**
 * Raeden’s Bakery and Café - Inventory Engine (Perpetual Ledger)
 *
 * Single source of truth for every stock movement. All modules
 * (Stock In, Stock Out, Item Conversion, Brew Production, POS Sale,
 * Reservation Pre-order) MUST post through inv_post_movement() so that:
 *   - the item row is locked (SELECT ... FOR UPDATE) to prevent race conditions,
 *   - outgoing quantities are validated against the running balance,
 *   - an immutable, accounting-style entry (IN / OUT / BALANCE) is written
 *     to `inventory_ledger`, separate from the transaction tables.
 *
 * NOTE: inv_post_movement() must be called inside an open PDO transaction.
 */

if (!function_exists('inv_ensure_schema')) {

/**
 * Create inventory tables on first use (idempotent). DDL causes an implicit
 * commit in MySQL, so call this BEFORE beginTransaction().
 */
function inv_ensure_schema(PDO $pdo): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    $exists = $pdo->query("SHOW TABLES LIKE 'inventory_ledger'")->fetchColumn();
    if (!$exists) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `inventory_items` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `item_code` VARCHAR(50) NOT NULL,
        `name` VARCHAR(191) NOT NULL,
        `item_type` ENUM('raw','finished') NOT NULL DEFAULT 'raw' COMMENT 'raw = ingredient / raw material, finished = sellable product',
        `unit` VARCHAR(30) NOT NULL DEFAULT 'pc',
        `image_url` VARCHAR(255) NULL DEFAULT NULL,
        `product_id` BIGINT UNSIGNED NULL COMMENT 'Linked POS product (finished items only)',
        `unit_cost` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
        `reorder_level` DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
        `current_stock` DECIMAL(14,4) NOT NULL DEFAULT 0.0000 COMMENT 'Cached running balance (mirrors ledger)',
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_item_code` (`item_code`),
        UNIQUE KEY `uq_item_product` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `product_recipes` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `finished_item_id` BIGINT UNSIGNED NOT NULL,
        `ingredient_item_id` BIGINT UNSIGNED NOT NULL,
        `qty_per_unit` DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_recipe_line` (`finished_item_id`, `ingredient_item_id`),
        CONSTRAINT `fk_recipe_finished` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_recipe_ingredient` FOREIGN KEY (`ingredient_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `stock_transactions` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `txn_no` VARCHAR(50) NOT NULL,
        `txn_type` ENUM('IN','OUT') NOT NULL,
        `txn_date` DATE NOT NULL,
        `reference_no` VARCHAR(100) NULL COMMENT 'DR / Invoice / Memo No.',
        `party` VARCHAR(191) NULL COMMENT 'Supplier (IN) or Reason (OUT)',
        `remarks` TEXT NULL,
        `status` ENUM('Draft','Posted','Cancelled') NOT NULL DEFAULT 'Draft',
        `created_by` VARCHAR(100) NULL,
        `posted_by` VARCHAR(100) NULL,
        `posted_at` DATETIME NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_txn_no` (`txn_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `stock_transaction_lines` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `txn_id` BIGINT UNSIGNED NOT NULL,
        `item_id` BIGINT UNSIGNED NOT NULL,
        `quantity` DECIMAL(14,4) NOT NULL,
        `unit_cost` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
        `remarks` VARCHAR(255) NULL,
        PRIMARY KEY (`id`),
        KEY `idx_stl_txn` (`txn_id`),
        CONSTRAINT `fk_stl_txn` FOREIGN KEY (`txn_id`) REFERENCES `stock_transactions` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_stl_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `item_conversions` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `conv_no` VARCHAR(50) NOT NULL,
        `conv_date` DATE NOT NULL,
        `source_item_id` BIGINT UNSIGNED NOT NULL,
        `source_qty` DECIMAL(14,4) NOT NULL,
        `dest_item_id` BIGINT UNSIGNED NOT NULL,
        `factor` DECIMAL(14,4) NOT NULL COMMENT '1 source unit = factor destination units',
        `dest_qty` DECIMAL(14,4) NOT NULL,
        `remarks` TEXT NULL,
        `status` ENUM('Draft','Posted','Cancelled') NOT NULL DEFAULT 'Draft',
        `created_by` VARCHAR(100) NULL,
        `posted_by` VARCHAR(100) NULL,
        `posted_at` DATETIME NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_conv_no` (`conv_no`),
        CONSTRAINT `fk_conv_src` FOREIGN KEY (`source_item_id`) REFERENCES `inventory_items` (`id`),
        CONSTRAINT `fk_conv_dst` FOREIGN KEY (`dest_item_id`) REFERENCES `inventory_items` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `brew_batches` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `batch_no` VARCHAR(50) NOT NULL,
        `batch_date` DATE NOT NULL,
        `finished_item_id` BIGINT UNSIGNED NOT NULL,
        `quantity` DECIMAL(14,4) NOT NULL,
        `remarks` TEXT NULL,
        `status` ENUM('Draft','Posted','Cancelled') NOT NULL DEFAULT 'Draft',
        `created_by` VARCHAR(100) NULL,
        `posted_by` VARCHAR(100) NULL,
        `posted_at` DATETIME NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_batch_no` (`batch_no`),
        CONSTRAINT `fk_brew_item` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `inventory_ledger` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `entry_date` DATETIME NOT NULL,
        `item_id` BIGINT UNSIGNED NOT NULL,
        `txn_type` ENUM('STOCK_IN','STOCK_OUT','POS_SALE','RES_SALE','CONV_OUT','CONV_IN','BREW_OUT','BREW_IN') NOT NULL,
        `ref_table` VARCHAR(50) NOT NULL,
        `ref_id` BIGINT UNSIGNED NOT NULL,
        `ref_no` VARCHAR(50) NOT NULL,
        `particulars` VARCHAR(255) NULL,
        `qty_in` DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
        `qty_out` DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
        `balance_after` DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
        `unit_cost` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
        `posted_by` VARCHAR(100) NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_ledger_item` (`item_id`, `id`),
        KEY `idx_ledger_ref` (`ref_table`, `ref_id`),
        KEY `idx_ledger_date` (`entry_date`),
        CONSTRAINT `fk_ledger_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        inv_sync_products_to_master($pdo);
        inv_seed_demo_master($pdo);
    }

    $addonsTblExists = $pdo->query("SHOW TABLES LIKE 'product_item_addons'")->fetchColumn();
    if (!$addonsTblExists) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `product_item_addons` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `finished_item_id` BIGINT UNSIGNED NOT NULL,
            `addon_name` VARCHAR(100) NOT NULL,
            `ingredient_item_id` BIGINT UNSIGNED NOT NULL,
            `qty_per_unit` DECIMAL(14,4) NOT NULL DEFAULT 1.0000,
            `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_pia_line` (`finished_item_id`, `addon_name`),
            KEY `idx_pia_finished` (`finished_item_id`),
            CONSTRAINT `fk_pia_finished` FOREIGN KEY (`finished_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pia_ingredient` FOREIGN KEY (`ingredient_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $hasBrewAddonsCol = $pdo->query("SHOW COLUMNS FROM `brew_batches` LIKE 'addons_json'")->fetchColumn();
        if (!$hasBrewAddonsCol) {
            $pdo->exec("ALTER TABLE `brew_batches` ADD COLUMN `addons_json` TEXT NULL AFTER `quantity`");
        }
    }
}

/**
 * Every POS product must exist in the master list as a finished item.
 * New finished items always start at ZERO stock; products.stock_quantity is
 * reset to mirror the ledger (0) so legacy stock figures are not trusted.
 */
function inv_sync_products_to_master(PDO $pdo): void
{
    $rows = $pdo->query("SELECT p.id, p.product_code, p.name, p.unit, p.cost_price, p.min_stock_alert
        FROM products p
        LEFT JOIN inventory_items i ON i.product_id = p.id
        WHERE i.id IS NULL")->fetchAll();

    $ins = $pdo->prepare("INSERT INTO inventory_items (item_code, name, item_type, unit, product_id, unit_cost, reorder_level, current_stock)
        VALUES (?, ?, 'finished', ?, ?, ?, ?, 0)");
    $reset = $pdo->prepare("UPDATE products SET stock_quantity = 0 WHERE id = ?");

    foreach ($rows as $r) {
        $unit = strtolower((string)$r['unit']);
        $unit = $unit === 'cups' ? 'cup' : ($unit === 'pieces' ? 'pc' : ($unit === 'slices' ? 'slice' : ($unit ?: 'pc')));
        $ins->execute([$r['product_code'], $r['name'], $unit, $r['id'], (float)$r['cost_price'], (float)$r['min_stock_alert']]);
        $reset->execute([$r['id']]);
    }
}

/**
 * Seed a starter raw-material master list + recipes (all at ZERO stock)
 * only when no raw materials exist yet.
 */
function inv_seed_demo_master(PDO $pdo): void
{
    $hasRaw = (int)$pdo->query("SELECT COUNT(*) FROM inventory_items WHERE item_type = 'raw'")->fetchColumn();
    if ($hasRaw > 0) return;

    $raw = [
        // code, name, unit, unit_cost, reorder
        ['RM-PATTY', 'Patty', 'pc', 35.00, 10],
        ['RM-FRIES', 'Fries', 'pc', 20.00, 10],
    ];
    $ins = $pdo->prepare("INSERT IGNORE INTO inventory_items (item_code, name, item_type, unit, unit_cost, reorder_level, current_stock)
        VALUES (?, ?, 'raw', ?, ?, ?, 0)");
    foreach ($raw as $r) $ins->execute($r);

    $id = function (string $code) use ($pdo) {
        $s = $pdo->prepare("SELECT id FROM inventory_items WHERE item_code = ?");
        $s->execute([$code]);
        return (int)$s->fetchColumn();
    };

    $recipes = [
        'SKU-BF-001' => ['RM-PATTY' => 1, 'RM-FRIES' => 1],
    ];
    $rIns = $pdo->prepare("INSERT IGNORE INTO product_recipes (finished_item_id, ingredient_item_id, qty_per_unit) VALUES (?, ?, ?)");
    foreach ($recipes as $fgCode => $lines) {
        $fgId = $id($fgCode);
        if (!$fgId) continue;
        foreach ($lines as $code => $qty) {
            $ingId = $id($code);
            if ($ingId) $rIns->execute([$fgId, $ingId, $qty]);
        }
    }
}

/** Current operator name for audit columns. */
function inv_user(): string
{
    return $_SESSION['user']['name'] ?? 'System';
}

/** Generate the next document number, e.g. SI-20261024-0001. */
function inv_next_no(PDO $pdo, string $prefix, string $table, string $column): string
{
    $base = $prefix . '-' . date('Ymd') . '-';
    $stmt = $pdo->prepare("SELECT $column FROM $table WHERE $column LIKE ? ORDER BY $column DESC LIMIT 1");
    $stmt->execute([$base . '%']);
    $last = $stmt->fetchColumn();
    $seq = $last ? ((int)substr($last, -4)) + 1 : 1;
    return $base . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
}

/** Format a decimal quantity without trailing zeros. */
function inv_fmt($qty): string
{
    $s = number_format((float)$qty, 4, '.', ',');
    return rtrim(rtrim($s, '0'), '.');
}

/**
 * Post one movement into the ledger (MUST be inside a transaction).
 *
 * @throws Exception when the outgoing quantity exceeds the available balance.
 * @return float the new running balance
 */
function inv_post_movement(PDO $pdo, int $itemId, float $qtyIn, float $qtyOut, string $txnType,
                           string $refTable, int $refId, string $refNo, string $particulars = '',
                           ?float $unitCost = null): float
{
    if ($qtyIn < 0 || $qtyOut < 0 || ($qtyIn == 0 && $qtyOut == 0)) {
        throw new Exception('Invalid movement quantity.');
    }

    $lock = $pdo->prepare("SELECT id, name, unit, product_id, current_stock, unit_cost, is_active FROM inventory_items WHERE id = ? FOR UPDATE");
    $lock->execute([$itemId]);
    $item = $lock->fetch();
    if (!$item) {
        throw new Exception("Inventory item #$itemId not found in the master list.");
    }

    $current = round((float)$item['current_stock'], 4);
    $newBal  = round($current + $qtyIn - $qtyOut, 4);

    if ($qtyOut > 0 && $newBal < 0) {
        throw new Exception(sprintf(
            "Insufficient stock for '%s': available %s %s, required %s %s.",
            $item['name'], inv_fmt($current), $item['unit'], inv_fmt($qtyOut), $item['unit']
        ));
    }

    $cost = $unitCost ?? (float)$item['unit_cost'];

    $pdo->prepare("INSERT INTO inventory_ledger
        (entry_date, item_id, txn_type, ref_table, ref_id, ref_no, particulars, qty_in, qty_out, balance_after, unit_cost, posted_by)
        VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$itemId, $txnType, $refTable, $refId, $refNo, mb_substr($particulars, 0, 255), $qtyIn, $qtyOut, $newBal, $cost, inv_user()]);

    $pdo->prepare("UPDATE inventory_items SET current_stock = ? WHERE id = ?")->execute([$newBal, $itemId]);

    // Keep POS catalog availability in sync for finished goods
    if (!empty($item['product_id'])) {
        $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?")
            ->execute([(int)floor($newBal), $item['product_id']]);
    }

    return $newBal;
}

/** Fetch the finished inventory item linked to a POS product (or null). */
function inv_finished_item_for_product(PDO $pdo, int $productId, string $productName = ''): ?array
{
    if ($productId > 0) {
        $s = $pdo->prepare("SELECT * FROM inventory_items WHERE product_id = ? AND item_type = 'finished' LIMIT 1");
        $s->execute([$productId]);
        $row = $s->fetch();
        if ($row) return $row;
    }
    if ($productName !== '') {
        $s2 = $pdo->prepare("SELECT * FROM inventory_items WHERE LOWER(name) = LOWER(?) AND item_type = 'finished' LIMIT 1");
        $s2->execute([trim($productName)]);
        $row2 = $s2->fetch();
        if ($row2) return $row2;
    }
    return null;
}

/**
 * Return live stock map for order-time consumables (Cups, Straws, Takeout Boxes)
 * that exist as active items in `inventory_items`.
 * Keyed by item_code (`RM-CUP12-PC`, `RM-CUP16-PC`, `RM-STRAW-PC`, `RM-TOBOX-PC`).
 */
function inv_get_packaging_stock_map(PDO $pdo): array
{
    $codes = ['RM-CUP12-PC', 'RM-CUP16-PC', 'RM-STRAW-PC', 'RM-TOBOX-PC'];
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $stmt = $pdo->prepare("SELECT id, item_code, name, unit, current_stock, reorder_level, unit_cost
        FROM inventory_items
        WHERE item_code IN ($placeholders) AND is_active = 1");
    $stmt->execute($codes);
    $rows = $stmt->fetchAll();

    $map = [];
    foreach ($rows as $r) {
        $map[$r['item_code']] = [
            'id' => (int)$r['id'],
            'item_code' => $r['item_code'],
            'name' => $r['name'],
            'unit' => $r['unit'],
            'current_stock' => round((float)$r['current_stock'], 4),
            'reorder_level' => round((float)$r['reorder_level'], 4),
            'unit_cost' => round((float)$r['unit_cost'], 4),
        ];
    }
    return $map;
}

/**
 * Calculate required Cups (`RM-CUP12-PC`, `RM-CUP16-PC`), Straws (`RM-STRAW-PC`),
 * and Takeout Boxes (`RM-TOBOX-PC`) for a given list of order items and order type.
 *
 * Rules:
 * - Beverages (`category_id` 1, 2, 3):
 *   - Cup: 1 × `RM-CUP16-PC` if size contains `16oz`/`22oz` or Cold beverage (`category_id` 2 or 3);
 *          otherwise 1 × `RM-CUP12-PC` (`12oz` Hot Regular).
 *   - Straw: 1 × `RM-STRAW-PC` per beverage if Cold (`category_id` 2 or 3) OR `order_type === 'Takeout'`.
 * - Takeout Orders (`order_type === 'Takeout'`):
 *   - Takeout Box (`RM-TOBOX-PC`): 1 per Pastry item (`category_id == 4`), or 1 per Takeout order if no pastries.
 *
 * @param PDO $pdo
 * @param array $items Each item may have `product_id`, `product_name`/`name`, `quantity`/`qty`, `size_name`
 * @param string $orderType 'Dine-in' | 'Takeout'
 * @return array Keyed by item_code => ['id','item_code','name','unit','required','available_stock','sufficient','sources']
 */
function inv_calculate_order_packaging(PDO $pdo, array $items, string $orderType = 'Dine-in'): array
{
    $stockMap = inv_get_packaging_stock_map($pdo);
    $isTakeout = (strtolower(trim($orderType)) === 'takeout');

    $prodStmt = $pdo->prepare("SELECT id, name, category_id FROM products WHERE id = ? LIMIT 1");
    $prodByNameStmt = $pdo->prepare("SELECT id, name, category_id FROM products WHERE LOWER(name) = LOWER(?) LIMIT 1");

    $requiredByCode = [];
    $addReq = function (string $code, float $qty, string $source) use (&$requiredByCode, $stockMap) {
        if ($qty <= 0 || !isset($stockMap[$code])) return;
        if (!isset($requiredByCode[$code])) {
            $meta = $stockMap[$code];
            $requiredByCode[$code] = [
                'ingredient_item_id' => (int)$meta['id'],
                'item_code' => $code,
                'name' => $meta['name'],
                'unit' => $meta['unit'],
                'required' => 0.0,
                'available_stock' => (float)$meta['current_stock'],
                'sufficient' => true,
                'sources' => [],
            ];
        }
        $requiredByCode[$code]['required'] = round($requiredByCode[$code]['required'] + $qty, 4);
        $requiredByCode[$code]['sources'][] = $source;
    };

    $totalItemsCount = 0;
    $pastryCount = 0;

    foreach ($items as $it) {
        $pid = (int)($it['product_id'] ?? $it['id'] ?? 0);
        $pname = trim((string)($it['product_name'] ?? $it['name'] ?? ''));
        $qty = max(1.0, (float)($it['quantity'] ?? $it['qty'] ?? 1));
        $sizeName = (string)($it['size_name'] ?? '');

        $prod = null;
        if ($pid > 0) {
            $prodStmt->execute([$pid]);
            $prod = $prodStmt->fetch();
        }
        if (!$prod && $pname !== '') {
            $prodByNameStmt->execute([$pname]);
            $prod = $prodByNameStmt->fetch();
        }

        $catId = $prod ? (int)$prod['category_id'] : 1;
        $displayName = $prod ? $prod['name'] : ($pname ?: 'Item');
        $totalItemsCount += $qty;

        if (in_array($catId, [1, 2, 3], true)) {
            // Beverage: determine 12oz vs 16oz cup
            $is16oz = (stripos($sizeName, '16oz') !== false)
                || (stripos($sizeName, '22oz') !== false)
                || (stripos($sizeName, 'large') !== false)
                || in_array($catId, [2, 3], true);

            $cupCode = $is16oz ? 'RM-CUP16-PC' : 'RM-CUP12-PC';
            $addReq($cupCode, $qty, "{$qty}x {$displayName}");

            // Cold drinks or Takeout drinks require a drinking straw
            if (in_array($catId, [2, 3], true) || $isTakeout) {
                $addReq('RM-STRAW-PC', $qty, "{$qty}x {$displayName}");
            }
        } else {
            // Pastry / Food item
            $pastryCount += $qty;
            if ($isTakeout) {
                $addReq('RM-TOBOX-PC', $qty, "{$qty}x {$displayName} (Takeout)");
            }
        }
    }

    // Every Takeout order requires at least 1 Takeout Box even if it only contains beverages
    if ($isTakeout && $totalItemsCount > 0 && $pastryCount <= 0) {
        $addReq('RM-TOBOX-PC', 1.0, "Takeout Order Carrier Box");
    }

    foreach ($requiredByCode as $code => &$row) {
        $row['sufficient'] = ($row['available_stock'] + 1e-9 >= $row['required']);
    }
    unset($row);

    return $requiredByCode;
}

/**
 * Validate that all required Cups, Straws, and Takeout Boxes have sufficient stock
 * BEFORE an order is accepted in the POS Terminal.
 * Note: This only validates stock; actual deduction happens in the Kitchen upon Brew & Serve (`Completed`).
 */
function inv_validate_order_packaging_stock(PDO $pdo, array $items, string $orderType = 'Dine-in'): array
{
    $packaging = inv_calculate_order_packaging($pdo, $items, $orderType);
    $shortages = [];
    foreach ($packaging as $code => $pkg) {
        if (!$pkg['sufficient']) {
            $shortages[] = sprintf(
                "%s [%s] (Available: %s %s, Required: %s %s)",
                $pkg['name'],
                $code,
                inv_fmt($pkg['available_stock']),
                $pkg['unit'],
                inv_fmt($pkg['required']),
                $pkg['unit']
            );
        }
    }

    if (!empty($shortages)) {
        throw new Exception(
            "Cannot place order — Insufficient Cup / Takeout Supply stock in Inventory: " .
            implode('; ', $shortages) . ". Please Stock In or Convert Packs to Pieces first."
        );
    }

    return $packaging;
}

/**
 * Deduct raw material ingredients (base recipe + ordered add-ons + cups, straws & takeout boxes)
 * in the Kitchen once an order is brewed and served (`Completed`).
 * Finished products are not stocked units — only raw ingredients & packaging are deducted (`BREW_OUT -`).
 *
 * @return array Summary of deducted raw ingredients & packaging
 */
function inv_deduct_kitchen_order_ingredients(PDO $pdo, int $orderId, string $orderNumber): array
{
    // Idempotency guard: do not deduct twice for the same order
    $chk = $pdo->prepare("SELECT COUNT(*) FROM inventory_ledger WHERE ref_table = 'orders' AND ref_id = ? AND txn_type = 'BREW_OUT'");
    $chk->execute([$orderId]);
    if ((int)$chk->fetchColumn() > 0) {
        return [];
    }

    $ordStmt = $pdo->prepare("SELECT order_type FROM orders WHERE id = ? LIMIT 1");
    $ordStmt->execute([$orderId]);
    $orderRow = $ordStmt->fetch();
    $orderType = $orderRow ? (string)$orderRow['order_type'] : 'Dine-in';

    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$orderId]);
    $orderItems = $itemStmt->fetchAll();
    if (empty($orderItems)) {
        return [];
    }

    $recStmt = $pdo->prepare("SELECT r.ingredient_item_id, r.qty_per_unit, i.item_code, i.name, i.unit, i.current_stock, i.unit_cost
        FROM product_recipes r
        JOIN inventory_items i ON i.id = r.ingredient_item_id
        WHERE r.finished_item_id = ?");

    $addonStmt = $pdo->prepare("SELECT a.addon_name, a.ingredient_item_id, a.qty_per_unit, i.item_code, i.name, i.unit, i.current_stock, i.unit_cost
        FROM product_item_addons a
        JOIN inventory_items i ON i.id = a.ingredient_item_id
        WHERE a.finished_item_id = ? AND LOWER(a.addon_name) = LOWER(?)
        LIMIT 1");

    // Packaging codes are calculated dynamically via inv_calculate_order_packaging() so cup sizes & takeout supplies are exact
    $packagingCodes = ['RM-CUP12-PC', 'RM-CUP16-PC', 'RM-STRAW-PC', 'RM-TOBOX-PC'];

    $requiredByIng = []; // ingredient_item_id => ['item_code','name','unit','required','sources'=>[]]

    foreach ($orderItems as $oi) {
        $pid = (int)($oi['product_id'] ?? 0);
        $pname = (string)($oi['product_name'] ?? '');
        $qty = max(1, (float)($oi['quantity'] ?? 1));

        $fg = inv_finished_item_for_product($pdo, $pid, $pname);
        if (!$fg) {
            continue;
        }
        $fgId = (int)$fg['id'];

        // 1. Base Recipe Ingredients (excluding packaging codes which are calculated below by size & order_type)
        $recStmt->execute([$fgId]);
        $baseLines = $recStmt->fetchAll();
        foreach ($baseLines as $bl) {
            if (in_array($bl['item_code'], $packagingCodes, true)) {
                continue;
            }
            $ingId = (int)$bl['ingredient_item_id'];
            $need = round((float)$bl['qty_per_unit'] * $qty, 4);
            if ($need <= 0) continue;

            if (!isset($requiredByIng[$ingId])) {
                $requiredByIng[$ingId] = [
                    'ingredient_item_id' => $ingId,
                    'item_code' => $bl['item_code'],
                    'name' => $bl['name'],
                    'unit' => $bl['unit'],
                    'required' => 0.0,
                    'sources' => [],
                ];
            }
            $requiredByIng[$ingId]['required'] = round($requiredByIng[$ingId]['required'] + $need, 4);
            $requiredByIng[$ingId]['sources'][] = "{$qty}x {$fg['name']}";
        }

        // 2. Ordered Add-on Ingredients
        $addons = [];
        if (!empty($oi['addons_json'])) {
            $decoded = json_decode($oi['addons_json'], true);
            if (is_array($decoded)) {
                $addons = $decoded;
            }
        }
        foreach ($addons as $addonLabel) {
            $addonClean = trim((string)$addonLabel);
            if ($addonClean === '') continue;

            $addonStmt->execute([$fgId, $addonClean]);
            $adRow = $addonStmt->fetch();
            if ($adRow) {
                $ingId = (int)$adRow['ingredient_item_id'];
                $need = round((float)$adRow['qty_per_unit'] * $qty, 4);
                if ($need <= 0) continue;

                if (!isset($requiredByIng[$ingId])) {
                    $requiredByIng[$ingId] = [
                        'ingredient_item_id' => $ingId,
                        'item_code' => $adRow['item_code'],
                        'name' => $adRow['name'],
                        'unit' => $adRow['unit'],
                        'required' => 0.0,
                        'sources' => [],
                    ];
                }
                $requiredByIng[$ingId]['required'] = round($requiredByIng[$ingId]['required'] + $need, 4);
                $requiredByIng[$ingId]['sources'][] = "+{$adRow['addon_name']} ({$qty}x)";
            }
        }
    }

    // 3. Cups, Straws, and Takeout Packaging (by drink size & Dine-in/Takeout order_type)
    $packagingReqs = inv_calculate_order_packaging($pdo, $orderItems, $orderType);
    foreach ($packagingReqs as $pkgCode => $pkg) {
        $ingId = (int)$pkg['ingredient_item_id'];
        if ($ingId <= 0 || $pkg['required'] <= 0) continue;

        if (!isset($requiredByIng[$ingId])) {
            $requiredByIng[$ingId] = [
                'ingredient_item_id' => $ingId,
                'item_code' => $pkg['item_code'],
                'name' => $pkg['name'],
                'unit' => $pkg['unit'],
                'required' => 0.0,
                'sources' => [],
            ];
        }
        $requiredByIng[$ingId]['required'] = round($requiredByIng[$ingId]['required'] + $pkg['required'], 4);
        foreach ($pkg['sources'] as $src) {
            $requiredByIng[$ingId]['sources'][] = $src;
        }
    }

    if (empty($requiredByIng)) {
        return [];
    }

    // Validate all required raw materials & packaging before deducting
    $shortages = [];
    $lockStmt = $pdo->prepare("SELECT id, name, unit, current_stock FROM inventory_items WHERE id = ? FOR UPDATE");
    foreach ($requiredByIng as $ingId => $reqInfo) {
        $lockStmt->execute([$ingId]);
        $liveIng = $lockStmt->fetch();
        $avail = $liveIng ? round((float)$liveIng['current_stock'], 4) : 0.0;
        if ($avail + 1e-9 < $reqInfo['required']) {
            $shortages[] = sprintf(
                "%s (Available: %s %s, Required: %s %s)",
                $reqInfo['name'],
                inv_fmt($avail),
                $reqInfo['unit'],
                inv_fmt($reqInfo['required']),
                $reqInfo['unit']
            );
        }
    }

    if (!empty($shortages)) {
        throw new Exception(
            "Cannot complete brew & serve for {$orderNumber} — Insufficient raw ingredient or cup/packaging stock in Kitchen: " .
            implode('; ', $shortages) . ". Please stock in or convert raw materials/cups first."
        );
    }

    // Deduct each raw ingredient & cup/packaging item and post BREW_OUT (-) to inventory_ledger
    $deducted = [];
    foreach ($requiredByIng as $ingId => $reqInfo) {
        $srcText = implode(', ', array_unique($reqInfo['sources']));
        $particulars = "Kitchen Brew & Serve {$orderNumber} [{$orderType}] ({$srcText})";
        inv_post_movement(
            $pdo,
            (int)$ingId,
            0,
            (float)$reqInfo['required'],
            'BREW_OUT',
            'orders',
            $orderId,
            $orderNumber,
            $particulars
        );
        $deducted[] = $reqInfo;
    }

    return $deducted;
}

/**
 * Parse a reservation's date and time_slot to calculate:
 * - Guest arrival timestamp & formatted time (e.g. "01:00 PM")
 * - Scheduled Kitchen Prep timestamp & formatted time (15 minutes before arrival, e.g. "12:45 PM")
 * - Minutes until arrival and minutes until T-15m scheduled prep
 */
function inv_parse_reservation_schedule(array $res): array {
    $dateStr = !empty($res['reservation_date']) ? substr((string)$res['reservation_date'], 0, 10) : date('Y-m-d');
    $slotStr = trim((string)($res['time_slot'] ?? ''));
    $parts = explode('-', $slotStr);
    $startStr = trim($parts[0] ?? '');
    if ($startStr === '') {
        $startStr = '12:00 PM';
    }

    $arrivalTs = strtotime($dateStr . ' ' . $startStr);
    if ($arrivalTs === false) {
        $arrivalTs = strtotime($dateStr . ' 12:00:00');
    }

    // Scheduled Order Prep is 15 minutes (900 seconds) before booking arrival time
    $prepTs = $arrivalTs - (15 * 60);
    $nowTs  = time();

    $minutesUntilArrival = (int)round(($arrivalTs - $nowTs) / 60);
    $minutesUntilPrep    = (int)round(($prepTs - $nowTs) / 60);

    // Due for T-15m prep when current time is within 15 minutes before arrival (up to 3 hours after start)
    $isToday = ($dateStr === date('Y-m-d'));
    $isDueForPrep = $isToday && ($minutesUntilArrival <= 15 && $minutesUntilArrival >= -180);

    return [
        'reservation_date'      => $dateStr,
        'time_slot'             => $slotStr,
        'arrival_ts'            => $arrivalTs,
        'arrival_formatted'     => date('h:i A', $arrivalTs),
        'prep_ts'               => $prepTs,
        'prep_formatted'        => date('h:i A', $prepTs),
        'minutes_until_arrival' => $minutesUntilArrival,
        'minutes_until_prep'    => $minutesUntilPrep,
        'is_today'              => $isToday,
        'is_due_for_prep'       => $isDueForPrep,
        'is_upcoming_today'     => $isToday && ($minutesUntilArrival > 15),
    ];
}

/**
 * Create a Kitchen KDS Order (`orders` + `order_items`) from a reservation's pre-ordered items.
 * Idempotent: if an order already exists for this reservation_id, returns the existing order.
 */
function inv_create_order_from_reservation(PDO $pdo, int $reservationId, string $triggerSource = 'T-15m Scheduled Pre-Order'): ?array {
    if ($reservationId <= 0) {
        return null;
    }

    // Check if an active order already exists for this reservation
    $chkStmt = $pdo->prepare("
        SELECT id, order_number, status, order_type, subtotal, discount_type, discount_amount, vat_amount, net_total, net_total AS total_amount, created_at
        FROM orders
        WHERE reservation_id = ? AND status != 'Cancelled'
        ORDER BY id DESC
        LIMIT 1
    ");
    $chkStmt->execute([$reservationId]);
    $existingOrder = $chkStmt->fetch();
    if ($existingOrder) {
        $existingOrder['already_exists'] = true;
        return $existingOrder;
    }

    // Load reservation + dining_tables info
    $resStmt = $pdo->prepare("
        SELECT r.*, dt.table_code, dt.name AS table_name, dt.zone
        FROM reservations r
        LEFT JOIN dining_tables dt ON r.table_id = dt.id
        WHERE r.id = ?
        LIMIT 1
    ");
    $resStmt->execute([$reservationId]);
    $res = $resStmt->fetch();
    if (!$res || ($res['status'] ?? '') === 'Cancelled') {
        return null;
    }

    // Load pre-ordered reservation_items
    $itemStmt = $pdo->prepare("SELECT * FROM reservation_items WHERE reservation_id = ? ORDER BY id ASC");
    $itemStmt->execute([$reservationId]);
    $resItems = $itemStmt->fetchAll();
    if (empty($resItems)) {
        return null;
    }

    $subtotal = 0.0;
    foreach ($resItems as $ri) {
        $lineTotal = (float)($ri['line_total'] ?? ((float)$ri['unit_price'] * (int)$ri['quantity']));
        $subtotal += $lineTotal;
    }

    $custType = trim((string)($res['customer_type'] ?? 'Regular'));
    $isExempt = ($custType === 'Senior/PWD' || stripos($custType, 'Senior') !== false || stripos($custType, 'PWD') !== false);
    if ($isExempt) {
        $vatExemptSales = $subtotal / 1.12;
        $discountType   = 'Senior/PWD (20%)';
        $discountAmount = round($vatExemptSales * 0.20, 2);
        $vatAmount      = 0.00;
        $netTotal       = round($vatExemptSales - $discountAmount, 2);
    } elseif ($custType === 'Student' || stripos($custType, 'Student') !== false) {
        $discountType   = 'Student (10%)';
        $discountAmount = round($subtotal * 0.10, 2);
        $discounted     = $subtotal - $discountAmount;
        $vatAmount      = round($discounted - ($discounted / 1.12), 2);
        $netTotal       = round($discounted, 2);
    } else {
        $discountType   = 'None';
        $discountAmount = 0.00;
        $vatAmount      = round($subtotal - ($subtotal / 1.12), 2);
        $netTotal       = round($subtotal, 2);
    }

    $sched = inv_parse_reservation_schedule($res);
    $orderNum = 'ORD-PRE-' . strtoupper(substr(uniqid(), -5));
    $tableLabel = !empty($res['table_code'])
        ? ('Table #' . $res['table_id'] . ' (' . $res['table_code'] . ')')
        : ('Table #' . ($res['table_id'] ?? '1'));
    $payRef = sprintf(
        "%s | %s | Arrival %s (Prep %s)",
        $res['reservation_number'] ?? ('RES-' . $reservationId),
        $triggerSource,
        $sched['arrival_formatted'],
        $sched['prep_formatted']
    );

    $ownTx = !$pdo->inTransaction();
    if ($ownTx) {
        $pdo->beginTransaction();
    }

    try {
        // Re-verify inside transaction to prevent race condition duplicates
        $chkStmt->execute([$reservationId]);
        $existingInside = $chkStmt->fetch();
        if ($existingInside) {
            if ($ownTx) {
                $pdo->commit();
            }
            $existingInside['already_exists'] = true;
            return $existingInside;
        }

        // Check packaging (cups/straws/takeout boxes) warning or note — Scheduled orders enter Pending so Barista can prep at T-15m
        $ordStmt = $pdo->prepare("
            INSERT INTO orders (
                order_number, reservation_id, order_type, table_number, customer_name,
                subtotal, discount_type, discount_amount, vat_amount, net_total,
                payment_method, payment_reference, status
            ) VALUES (?, ?, 'Scheduled Pre-order', ?, ?, ?, ?, ?, ?, ?, 'Pay at Counter', ?, 'Pending')
        ");
        $ordStmt->execute([
            $orderNum,
            $reservationId,
            $tableLabel,
            $res['customer_name'],
            $subtotal,
            $discountType,
            $discountAmount,
            $vatAmount,
            $netTotal,
            substr($payRef, 0, 100),
        ]);
        $orderId = (int)$pdo->lastInsertId();

        $oiStmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_name, size_name, sugar_level, ice_level, addons_json, special_notes, quantity, unit_price, line_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $noteForItem = "Scheduled Pre-Order (Arrival {$sched['arrival_formatted']} / Prep {$sched['prep_formatted']})";
        if (!empty($res['special_notes'])) {
            $noteForItem .= ' - ' . $res['special_notes'];
        }
        $noteForItem = substr($noteForItem, 0, 250);

        foreach ($resItems as $ri) {
            $qty = max(1, (int)$ri['quantity']);
            $uPrice = (float)$ri['unit_price'];
            $lineTotal = (float)($ri['line_total'] ?? ($uPrice * $qty));
            $addonsJson = !empty($ri['addons_json']) ? $ri['addons_json'] : '[]';
            $oiStmt->execute([
                $orderId,
                !empty($ri['product_id']) ? (int)$ri['product_id'] : null,
                $ri['product_name'],
                $ri['size_name'] ?: 'Regular (12oz)',
                $ri['sugar_level'] ?: '50%',
                $ri['ice_level'] ?: 'Regular Ice',
                $addonsJson,
                $noteForItem,
                $qty,
                $uPrice,
                $lineTotal,
            ]);
        }

        if ($ownTx) {
            $pdo->commit();
        }

        return [
            'id'                => $orderId,
            'order_number'      => $orderNum,
            'status'            => 'Pending',
            'order_type'        => 'Scheduled Pre-order',
            'subtotal'          => $subtotal,
            'discount_type'     => $discountType,
            'discount_amount'   => $discountAmount,
            'vat_amount'        => $vatAmount,
            'net_total'         => $netTotal,
            'total_amount'      => $netTotal,
            'created_at'        => date('Y-m-d H:i:s'),
            'already_exists'    => false,
            'arrival_formatted' => $sched['arrival_formatted'],
            'prep_formatted'    => $sched['prep_formatted'],
        ];
    } catch (Throwable $e) {
        if ($ownTx && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * Automatically scan today's Confirmed / Checked-In reservations with pre-ordered items.
 * - Any reservation within 15 minutes of arrival (T-15m) or already Checked-In is automatically
 *   dispatched to the Kitchen KDS (`orders` table) if not yet created.
 * - Also returns upcoming scheduled pre-orders (> 15 minutes away today) so Kitchen KDS and
 *   Staff Concierge can see the scheduled queue in advance.
 */
function inv_process_scheduled_reservation_orders(PDO $pdo): array {
    $dispatched = [];
    $upcoming   = [];

    try {
        $stmt = $pdo->query("
            SELECT r.*, dt.table_code, dt.name AS table_name, dt.zone,
                   (SELECT o.id FROM orders o WHERE o.reservation_id = r.id AND o.status != 'Cancelled' ORDER BY o.id DESC LIMIT 1) AS existing_order_id,
                   (SELECT o.order_number FROM orders o WHERE o.reservation_id = r.id AND o.status != 'Cancelled' ORDER BY o.id DESC LIMIT 1) AS existing_order_number,
                   (SELECT o.status FROM orders o WHERE o.reservation_id = r.id AND o.status != 'Cancelled' ORDER BY o.id DESC LIMIT 1) AS existing_order_status
            FROM reservations r
            LEFT JOIN dining_tables dt ON r.table_id = dt.id
            WHERE DATE(r.reservation_date) = CURDATE()
              AND r.status IN ('Confirmed', 'Checked-In')
            ORDER BY r.id ASC
        ");
        $rows = $stmt->fetchAll();

        $itemStmt = $pdo->prepare("SELECT id, product_id, product_name, size_name, sugar_level, ice_level, addons_json, quantity, unit_price, line_total FROM reservation_items WHERE reservation_id = ? ORDER BY id ASC");

        foreach ($rows as $r) {
            $resId = (int)$r['id'];
            $itemStmt->execute([$resId]);
            $items = $itemStmt->fetchAll();
            if (empty($items)) {
                continue;
            }

            $sched = inv_parse_reservation_schedule($r);
            $hasOrder = !empty($r['existing_order_id']);
            $tableLabel = !empty($r['table_code'])
                ? ('Table #' . $r['table_id'] . ' (' . $r['table_code'] . ')')
                : ('Table #' . $r['table_id']);

            if (!$hasOrder && ($sched['is_due_for_prep'] || $r['status'] === 'Checked-In')) {
                $trigger = ($r['status'] === 'Checked-In')
                    ? 'Guest Checked-In'
                    : 'Auto T-15m Before Arrival';
                $created = inv_create_order_from_reservation($pdo, $resId, $trigger);
                if ($created && empty($created['already_exists'])) {
                    $dispatched[] = [
                        'reservation_id'     => $resId,
                        'reservation_number' => $r['reservation_number'],
                        'qr_token'           => $r['qr_token'],
                        'customer_name'      => $r['customer_name'],
                        'table_number'       => $tableLabel,
                        'order_id'           => $created['id'],
                        'order_number'       => $created['order_number'],
                        'arrival_formatted'  => $sched['arrival_formatted'],
                        'prep_formatted'     => $sched['prep_formatted'],
                    ];
                }
            } elseif (!$hasOrder && $sched['is_upcoming_today']) {
                $upcoming[] = [
                    'reservation_id'        => $resId,
                    'reservation_number'    => $r['reservation_number'],
                    'qr_token'              => $r['qr_token'],
                    'customer_name'         => $r['customer_name'],
                    'phone'                 => $r['phone'],
                    'table_id'              => (int)$r['table_id'],
                    'table_code'            => $r['table_code'],
                    'table_name'            => $r['table_name'],
                    'table_number'          => $tableLabel,
                    'zone'                  => $r['zone'],
                    'guest_count'           => (int)$r['guest_count'],
                    'time_slot'             => $r['time_slot'],
                    'total_amount'          => (float)$r['total_amount'],
                    'arrival_formatted'     => $sched['arrival_formatted'],
                    'prep_formatted'        => $sched['prep_formatted'],
                    'minutes_until_arrival' => $sched['minutes_until_arrival'],
                    'minutes_until_prep'    => $sched['minutes_until_prep'],
                    'items'                 => $items,
                ];
            }
        }

        usort($upcoming, function ($a, $b) {
            return ($a['minutes_until_arrival'] <=> $b['minutes_until_arrival']);
        });
    } catch (Throwable $e) {
        // Non-fatal in background auto-dispatch
    }

    return [
        'dispatched' => $dispatched,
        'upcoming'   => $upcoming,
    ];
}

} // function_exists guard


