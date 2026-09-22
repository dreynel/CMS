-- ==========================================================
-- ISAT U Coffee Shop POS System - MySQL Sample Seed Data
-- ==========================================================

-- 1. SEED USERS
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `affiliation`, `id_number`, `phone`) VALUES
(1, 'Admin Manager', 'admin@isatu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'faculty', 'ISATU-ADMIN-001', '0917-111-2222'),
(2, 'Staff Bea Ramos', 'cashier.bea@isatu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', 'student', 'ISATU-STU-2022-049', '0918-333-4444'),
(3, 'Barista Jason Cole', 'barista.jason@isatu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'barista', 'student', 'ISATU-STU-2021-118', '0919-555-6666'),
(4, 'Engr. Mark Villanueva', 'mark.villanueva@isatu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'faculty', 'ISATU-FAC-2018-091', '0917-123-4567');

-- 2. SEED CATEGORIES
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`) VALUES
(1, 'ISAT U Signatures', 'signature', 'sparkles', 1, 1),
(2, 'Espresso & Classics', 'espresso', 'cup-soda', 2, 1),
(3, 'Cold Brew & Iced', 'coldbrew', 'snowflake', 3, 1),
(4, 'Milk Teas & Non-Coffee', 'noncoffee', 'heart', 4, 1),
(5, 'Pastries & Bakes', 'pastries', 'cake', 5, 1),
(6, 'Snacks & Light Meals', 'meals', 'utensils', 6, 1);

-- 3. SEED PRODUCTS
INSERT INTO `products` (`id`, `category_id`, `product_code`, `name`, `slug`, `description`, `image_url`, `price`, `cost_price`, `stock_quantity`, `min_stock_alert`, `badge`, `has_customizations`, `is_available`) VALUES
(1, 1, 'PROD-001', 'ISAT U Golden Tamaraw Latte', 'isatu-golden-tamaraw-latte', 'Espresso layered with golden honey honeycomb, fresh milk, and subtle caramel drizzle. Inspired by the university pride.', 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=600&auto=format&fit=crop&q=80', 135.00, 65.00, 45, 10, 'Bestseller', 1, 1),
(2, 1, 'PROD-002', 'Blue & Gold Velvet Frappe', 'blue-gold-velvet-frappe', 'Rich vanilla butterfly pea flower blend with golden salted caramel crust and whipped cream.', 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=600&auto=format&fit=crop&q=80', 150.00, 70.00, 38, 10, 'Signature', 1, 1),
(3, 2, 'PROD-003', 'Classic Spanish Latte', 'classic-spanish-latte', 'Double shot rich espresso with sweetened condensed milk and silky textured steamed milk.', 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=600&auto=format&fit=crop&q=80', 120.00, 55.00, 60, 15, 'Staff Pick', 1, 1),
(4, 2, 'PROD-004', 'Campustech Dark Mocha', 'campustech-dark-mocha', 'Davao dark chocolate combined with bold espresso roast and steamed whole milk.', 'https://images.unsplash.com/photo-1534778101976-62847782c213?w=600&auto=format&fit=crop&q=80', 130.00, 60.00, 52, 10, '', 1, 1),
(5, 3, 'PROD-005', 'Nitro Sweet Cream Cold Brew', 'nitro-sweet-cream-cold-brew', '18-hour slow-steeped Arabica blend topped with house-crafted vanilla sweet cream foam.', 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=600&auto=format&fit=crop&q=80', 140.00, 50.00, 25, 10, 'Premium', 1, 1),
(6, 4, 'PROD-006', 'Oat Milk Brown Sugar Boba', 'oat-milk-brown-sugar-boba', 'Rich brown sugar boba pearls slow-cooked, paired with smooth creamy oat milk.', 'https://images.unsplash.com/photo-1558857563-b371033873b8?w=600&auto=format&fit=crop&q=80', 125.00, 50.00, 40, 10, '', 1, 1),
(7, 5, 'PROD-007', 'Butter Croissant with Honey Butter', 'butter-croissant-honey-butter', 'Flaky, buttery French croissant baked daily at 6:00 AM, served with Iloilo farm honey butter.', 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&auto=format&fit=crop&q=80', 85.00, 35.00, 22, 5, 'Freshly Baked', 0, 1),
(8, 5, 'PROD-008', 'Ube Halaya Basque Cheesecake Slice', 'ube-halaya-basque-cheesecake-slice', 'Silky burnt cheesecake infused with authentic Filipino purple yam halaya.', 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=600&auto=format&fit=crop&q=80', 110.00, 45.00, 14, 5, 'Campus Favorite', 0, 1),
(9, 6, 'PROD-009', 'Clubhouse Panini with Fries', 'clubhouse-panini-with-fries', 'Smoked ham, grilled chicken, egg, cheddar, and crisp lettuce on toasted sourdough panini.', 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=600&auto=format&fit=crop&q=80', 145.00, 65.00, 18, 5, '', 0, 1),
(10, 6, 'PROD-010', 'Creamy Pesto Pasta with Garlic Toast', 'creamy-pesto-pasta-garlic-toast', 'Al dente penne in fresh basil parmesan cream sauce with grilled herb chicken.', 'https://images.unsplash.com/photo-1621996346565-e3d5d6281691?w=600&auto=format&fit=crop&q=80', 160.00, 70.00, 12, 5, '', 0, 1);

-- 4. SEED PRODUCT SIZES
INSERT INTO `product_sizes` (`product_id`, `size_name`, `price_additional`) VALUES
(1, 'Regular (12oz)', 0.00),
(1, 'Large (16oz)', 25.00),
(1, 'Jumbo (22oz)', 45.00),
(2, 'Regular (16oz)', 0.00),
(2, 'Large (22oz)', 30.00),
(3, 'Regular (12oz)', 0.00),
(3, 'Large (16oz)', 20.00),
(4, 'Regular (12oz)', 0.00),
(4, 'Large (16oz)', 25.00),
(5, 'Large (16oz)', 0.00),
(5, 'Jumbo (22oz)', 30.00),
(6, 'Regular (16oz)', 0.00),
(6, 'Large (22oz)', 25.00);

-- 5. SEED DINING TABLES
INSERT INTO `dining_tables` (`id`, `table_code`, `name`, `zone`, `capacity`, `status`, `description`) VALUES
(1, 'T-01', 'Table 1 (Lounge)', 'Lounge Zone', 2, 'available', 'Cozy leather seating with low coffee table.'),
(2, 'T-02', 'Table 2 (Lounge)', 'Lounge Zone', 4, 'available', 'Spacious plush sofa booth near window view.'),
(3, 'T-03', 'Study Pod A1', 'Study Hub', 1, 'reserved', 'Individual quiet pod with USB-C/power outlet & desk lamp.'),
(4, 'T-04', 'Study Pod A2', 'Study Hub', 1, 'available', 'Individual quiet pod with high-speed campus Wi-Fi.'),
(5, 'T-05', 'Study Pod B1 (Duo)', 'Study Hub', 2, 'available', 'Dual workstation with whiteboard divider.'),
(6, 'T-06', 'Table 6 (Main Café)', 'Main Hall', 4, 'available', 'Central café wooden dining table.'),
(7, 'T-07', 'Table 7 (Main Café)', 'Main Hall', 6, 'occupied', 'Large group table for research teams & org meetings.'),
(8, 'T-08', 'Terrace T1 (Al Fresco)', 'Outdoor Terrace', 2, 'available', 'Open-air garden terrace with fresh breeze.'),
(9, 'T-09', 'Terrace T2 (Al Fresco)', 'Outdoor Terrace', 4, 'available', 'Shaded terrace seating overlooking university grounds.');

-- 6. SEED SAMPLE RESERVATION
INSERT INTO `reservations` (`id`, `reservation_number`, `qr_token`, `user_id`, `table_id`, `customer_name`, `customer_type`, `id_number`, `phone`, `email`, `reservation_date`, `time_slot`, `guest_count`, `special_notes`, `total_amount`, `payment_status`, `status`) VALUES
(1, 'RES-20260830-001', 'ISATU-RES-20260830-001-A9F2', 4, 3, 'Engr. Mark Villanueva', 'Faculty', 'ISATU-FAC-2018-091', '0917-123-4567', 'mark.villanueva@isatu.edu.ph', '2026-08-30', '10:00 AM - 12:00 PM', 1, 'Quiet study session and research review', 245.00, 'Paid (GCash)', 'Confirmed');

-- 7. SEED RESERVATION ITEMS
INSERT INTO `reservation_items` (`reservation_id`, `product_id`, `product_name`, `size_name`, `sugar_level`, `ice_level`, `quantity`, `unit_price`, `line_total`) VALUES
(1, 1, 'ISAT U Golden Tamaraw Latte', 'Large (16oz)', '50%', 'Normal Ice', 1, 160.00, 160.00),
(1, 7, 'Butter Croissant with Honey Butter', 'Standard', '-', '-', 1, 85.00, 85.00);
