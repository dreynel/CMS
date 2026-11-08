<?php
/**
 * ISAT U Coffee Shop POS & Online Reservation System
 * Native PHP Database Configuration & Connection (PDO)
 * 
 * Compatible with Localhost (XAMPP/WAMP) & Hostinger Shared Hosting
 */

// Ensure Philippine Standard Time (+08:00) across both Localhost & Hostinger UTC servers
date_default_timezone_set('Asia/Manila');

// Optional saved Hostinger credentials file (created via 1-Click Web Setup if needed)
if (file_exists(__DIR__ . '/db_credentials.php')) {
    require_once __DIR__ . '/db_credentials.php';
}

// Handle 1-Click Hostinger DB Credentials Save from Setup Screen
if (
    php_sapi_name() !== 'cli'
    && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && isset($_POST['__hostinger_db_setup'])
) {
    $hHost = trim($_POST['db_host'] ?? 'localhost') ?: '194.59.164.63';
    $hPort = trim($_POST['db_port'] ?? '3306') ?: '3306';
    $hName = trim($_POST['db_name'] ?? 'u534933225_cms');
    $hUser = trim($_POST['db_user'] ?? 'u534933225_cms');
    $hPass = (string)($_POST['db_pass'] ?? 'Qx51>Ww?1g');

    if ($hName !== '' && $hUser !== '') {
        $credContent = "<?php\n"
            . "if (!defined('DB_HOST')) define('DB_HOST', " . var_export($hHost, true) . ");\n"
            . "if (!defined('DB_PORT')) define('DB_PORT', " . var_export($hPort, true) . ");\n"
            . "if (!defined('DB_NAME')) define('DB_NAME', " . var_export($hName, true) . ");\n"
            . "if (!defined('DB_USER')) define('DB_USER', " . var_export($hUser, true) . ");\n"
            . "if (!defined('DB_PASS')) define('DB_PASS', " . var_export($hPass, true) . ");\n";
        @file_put_contents(__DIR__ . '/db_credentials.php', $credContent);
        header('Location: ' . ($_SERVER['REQUEST_URI'] ?? './'));
        exit;
    }
}

// Hostinger / Production Configuration
// If on Hostinger, replace with your Hostinger MySQL details from cPanel/hPanel (or use the web prompt):
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '194.59.164.63');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_DATABASE') ?: 'u534933225_cms');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USERNAME') ?: 'u534933225_cms');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'Qx51>Ww?1g');

/**
 * Initialize session timezone, SQL mode, and auto-import schema if database is empty.
 */
function initDatabaseSession(PDO $pdo): void {
    try {
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("SET collation_connection = 'utf8mb4_unicode_ci'");
        $pdo->exec("SET time_zone = '+08:00'");
        $pdo->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
    } catch (Exception $e) {
        // Ignore if server restricts session variables
    }

    // Auto-import schema if the connected database has no `users` table yet
    try {
        $hasUsers = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
        if (!$hasUsers) {
            $sqlFile = file_exists(__DIR__ . '/../database_hostinger.sql')
                ? (__DIR__ . '/../database_hostinger.sql')
                : (__DIR__ . '/../database.sql');
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                if ($sql) {
                    $pdo->exec($sql);
                }
            }
        }
    } catch (Exception $e) {
        // Ignore auto-import errors
    }
}

/**
 * Get the PDO Database Connection instance.
 * Automatically tries primary password, then empty password (standard XAMPP), and localhost/127.0.0.1.
 *
 * @return PDO
 */
function getDB() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $hostsToTry = array_unique([DB_HOST, 'localhost', '127.0.0.1']);
    $usersToTry = array_unique([DB_USER, 'root']);
    $passwordsToTry = array_unique([DB_PASS, '']); // Try configured password first, then blank password for standard XAMPP

    foreach ($hostsToTry as $host) {
        foreach ($usersToTry as $user) {
            foreach ($passwordsToTry as $pass) {
                try {
                    $dsn = "mysql:host=" . $host . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_TIMEOUT => 3, // Prevent hanging on unreachable remote IP
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    ];
                    $pdo = new PDO($dsn, $user, $pass, $options);
                    initDatabaseSession($pdo);
                    return $pdo;
                } catch (PDOException $e) {
                    // If the database doesn't exist yet, attempt to create it (local environments)
                    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                        try {
                            $rootDsn = "mysql:host=" . $host . ";port=" . DB_PORT . ";charset=utf8mb4";
                            $rootPdo = new PDO($rootDsn, $user, $pass, [PDO::ATTR_TIMEOUT => 3]);
                            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                            
                            // Reconnect to newly created database
                            $pdo = new PDO($dsn, $user, $pass, $options);
                            initDatabaseSession($pdo);
                            return $pdo;
                        } catch (Exception $createEx) {
                            // Fall through
                        }
                    }
                    // If credentials failed, try next combination
                    continue;
                }
            }
        }
    }

    // If still failed, display friendly troubleshooting message
    $errorMsg = isset($e) ? $e->getMessage() : 'Database connection failed';
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, "Database connection error: $errorMsg\n");
        exit(1);
    }

    // Check if this is an API call
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $errorMsg,
            'setup_required' => true,
        ]);
        exit;
    }

    // Render friendly HTML setup prompt
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Setup Required - Raeden’s Resto, Bakery, Cafe</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light py-5">
        <div class="container" style="max-width: 600px;">
            <div class="card shadow-sm border-0 rounded-4 p-4 text-center">
                <div class="mb-3 text-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-database-exclamation" viewBox="0 0 16 16">
                        <path d="M8 1c-1.573 0-3.022.289-4.096.777C2.823 2.26 2 2.973 2 3.8s.823 1.54 1.904 2.023C4.978 6.31 6.427 6.6 8 6.6s3.022-.289 4.096-.777C13.177 5.34 14 4.627 14 3.8s-.823-1.54-1.904-2.023C11.022 1.29 9.573 1 8 1"/>
                        <path d="M2 5.5v2.8c0 .827.823 1.54 1.904 2.023C4.978 10.81 6.427 11.1 8 11.1s3.022-.289 4.096-.777C13.177 9.84 14 9.127 14 8.3V5.5c-.71.554-1.636.936-2.704 1.173A14 14 0 0 1 8 6.9c-1.127 0-2.203-.08-3.296-.227C3.636 6.436 2.71 6.054 2 5.5"/>
                        <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1.5a.5.5 0 0 0 1 0V11a.5.5 0 0 0-.5-.5m0 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                    </svg>
                </div>
                <h3 class="fw-bold text-dark">Database Connection Needed</h3>
                <p class="text-muted">The system could not connect to database <code><?= htmlspecialchars(DB_NAME) ?></code> on <code><?= htmlspecialchars(DB_HOST) ?></code>.</p>
                <div class="alert alert-danger text-start small">
                    <strong>Error:</strong> <?= htmlspecialchars($errorMsg) ?>
                </div>
                <form method="POST" class="text-start border rounded-4 p-3 bg-light mb-3">
                    <input type="hidden" name="__hostinger_db_setup" value="1">
                    <div class="fw-bold text-dark mb-2 small text-uppercase">1-Click Hostinger / MySQL Connection Setup</div>
                    <div class="row g-2 mb-2">
                        <div class="col-8">
                            <label class="form-label small fw-semibold mb-1">MySQL Host</label>
                            <input type="text" name="db_host" class="form-control form-control-sm" value="localhost" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold mb-1">Port</label>
                            <input type="text" name="db_port" class="form-control form-control-sm" value="3306" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Database Name (e.g. u123456789_cms)</label>
                        <input type="text" name="db_name" class="form-control form-control-sm" placeholder="u123456789_cms" value="<?= htmlspecialchars(DB_NAME) ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1">Database Username (e.g. u123456789_admin)</label>
                        <input type="text" name="db_user" class="form-control form-control-sm" placeholder="u123456789_admin" value="<?= htmlspecialchars(DB_USER) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">Database Password</label>
                        <input type="password" name="db_pass" class="form-control form-control-sm" placeholder="Enter MySQL password">
                    </div>
                    <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">
                        Save Credentials &amp; Auto-Install Database
                    </button>
                </form>
                <div class="d-grid gap-2">
                    <a href="javascript:location.reload()" class="btn btn-outline-secondary btn-sm">Retry Connection</a>
                </div>
                <hr class="my-3">
                <small class="text-muted">Or edit <code>config/database.php</code> directly with your Hostinger MySQL Database Name, Username, and Password.</small>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Send a JSON HTTP response and terminate script.
 *
 * @param mixed $data
 * @param int $status
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Sanitize user input string.
 *
 * @param string|null $input
 * @return string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}
