-- MOTORSHOP POS - SAMPLE DATA (updated: include service_id in sale_items + expiry_date + void request/logs)
-- Run this after migrations (e.g., after `php artisan migrate` or `php artisan migrate:fresh`)

-- Password for all users: "password"

-- 1. USERS
INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
('Admin User', 'admin@motorshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW()),
('John Cashier', 'cashier@motorshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', NOW(), NOW()),
('Mike Mechanic', 'mechanic@motorshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', NOW(), NOW());

-- 2. CATEGORIES
INSERT INTO `categories` (`name`, `created_at`, `updated_at`) VALUES
('Motor Parts', NOW(), NOW()),
('Oils & Lubricants', NOW(), NOW()),
('Tires', NOW(), NOW()),
('Accessories', NOW(), NOW()),
('Tools', NOW(), NOW());

-- 3. CUSTOMERS
INSERT INTO `customers` (`first_name`, `last_name`, `phone`, `email`, `address`, `vehicle_info`, `created_at`, `updated_at`) VALUES
('Juan', 'Dela Cruz', '09171234567', 'juan@email.com', '123 Rizal St, QC', 'Honda Wave 125 - ABC-1234', NOW(), NOW()),
('Maria', 'Santos', '09181234567', 'maria@email.com', '456 Bonifacio Ave', 'Yamaha Mio - XYZ-5678', NOW(), NOW()),
('Pedro', 'Reyes', '09191234567', NULL, '789 Luna St, Makati', 'Suzuki Raider 150', NOW(), NOW()),
('Ana', 'Garcia', '09201234567', 'ana@email.com', '321 Aguinaldo Rd', 'Honda Click 150', NOW(), NOW()),
('Jose', 'Mendoza', '09211234567', NULL, '654 Mabini St', 'Kawasaki Fury', NOW(), NOW());

-- 4. SUPPLIERS
INSERT INTO `suppliers` (`name`, `contact_person`, `phone`, `email`, `address`, `payment_terms`, `created_at`, `updated_at`) VALUES
('Honda Parts Manila', 'Roberto Santos', '02-1234-5678', 'sales@honda.com', '100 EDSA, QC', '30 Days Credit', NOW(), NOW()),
('Yamaha Genuine Parts', 'Linda Garcia', '02-2345-6789', 'orders@yamaha.ph', '200 C5 Road', '45 Days Credit', NOW(), NOW()),
('Shell Lubricants PH', 'Diana Cruz', '02-4567-8901', 'sales@shell.ph', '400 Makati Ave', '15 Days Credit', NOW(), NOW());

-- 5. PRODUCTS (with expiry_date where appropriate)
INSERT INTO `products` (`sku`, `name`, `category_id`, `brand`, `cost_price`, `selling_price`, `stock`, `reorder_level`, `unit`, `description`, `expiry_date`, `created_at`, `updated_at`) VALUES
('MP-001', 'Spark Plug NGK', 1, 'NGK', 150.00, 250.00, 48, 10, 'piece', 'Standard spark plug', NULL, NOW(), NOW()),
('MP-002', 'Air Filter Honda', 1, 'Honda', 200.00, 350.00, 30, 10, 'piece', 'Genuine air filter', NULL, NOW(), NOW()),
('MP-003', 'Brake Pads Front', 1, 'TDR', 300.00, 500.00, 24, 8, 'set', 'High quality brake pads', NULL, NOW(), NOW()),
('MP-004', 'Chain Set 428', 1, 'RK', 800.00, 1200.00, 13, 5, 'set', 'Chain and sprocket', NULL, NOW(), NOW()),
('MP-005', 'Clutch Plate Set', 1, 'Daytona', 450.00, 750.00, 20, 5, 'set', 'Complete clutch set', NULL, NOW(), NOW()),
('OIL-001', 'Engine Oil 10W-40', 2, 'Shell', 250.00, 400.00, 96, 20, 'liter', 'Semi-synthetic oil', '2026-07-31', NOW(), NOW()),
('OIL-002', 'Engine Oil 20W-50', 2, 'Motul', 300.00, 480.00, 79, 15, 'liter', 'Mineral oil', '2026-10-31', NOW(), NOW()),
('OIL-003', 'Gear Oil 80W-90', 2, 'Shell', 180.00, 300.00, 59, 15, 'liter', 'Gear oil', '2027-01-31', NOW(), NOW()),
('OIL-004', 'Chain Lube Spray', 2, 'Motul', 200.00, 350.00, 44, 10, 'piece', 'Chain lubricant', '2027-02-28', NOW(), NOW()),
('TIRE-001', 'Tire 70/90-17 Front', 3, 'IRC', 800.00, 1200.00, 19, 5, 'piece', 'Front tire', NULL, NOW(), NOW()),
('TIRE-002', 'Tire 80/90-17 Rear', 3, 'IRC', 900.00, 1350.00, 17, 5, 'piece', 'Rear tire', NULL, NOW(), NOW()),
('TIRE-003', 'Tire 90/90-14 Scooter', 3, 'Dunlop', 950.00, 1400.00, 13, 5, 'piece', 'Scooter tire', NULL, NOW(), NOW()),
('ACC-001', 'Side Mirror Set', 4, 'Takasago', 250.00, 450.00, 34, 10, 'set', 'Universal mirrors', NULL, NOW(), NOW()),
('ACC-002', 'LED Headlight', 4, 'Racing Boy', 400.00, 650.00, 24, 8, 'piece', 'LED upgrade', NULL, NOW(), NOW()),
('ACC-003', 'Phone Holder', 4, 'Generic', 200.00, 380.00, 30, 8, 'piece', 'Universal holder', NULL, NOW(), NOW()),
('ACC-004', 'Helmet Full Face', 4, 'Bilmola', 1500.00, 2500.00, 11, 3, 'piece', 'DOT certified', NULL, NOW(), NOW()),
('TOOL-001', 'Socket Wrench Set', 5, 'Tactix', 800.00, 1200.00, 10, 3, 'set', 'Complete socket set', NULL, NOW(), NOW());

-- 6. SERVICES
INSERT INTO `services` (`name`, `code`, `category`, `description`, `labor_fee`, `estimated_duration`, `created_at`, `updated_at`) VALUES
('Change Oil', 'SVC-001', 'Maintenance', 'Engine oil change', 150.00, '30 minutes', NOW(), NOW()),
('Tune-up Basic', 'SVC-002', 'Maintenance', 'Basic tune-up', 350.00, '1 hour', NOW(), NOW()),
('Tune-up Complete', 'SVC-003', 'Maintenance', 'Complete tune-up', 600.00, '2 hours', NOW(), NOW()),
('Brake Service', 'SVC-004', 'Maintenance', 'Brake pad replacement', 200.00, '45 minutes', NOW(), NOW()),
('Tire Installation', 'SVC-005', 'Installation', 'Tire mounting', 150.00, '30 minutes', NOW(), NOW()),
('Chain Replacement', 'SVC-006', 'Repair', 'Chain and sprocket', 300.00, '1.5 hours', NOW(), NOW());

-- 7. SALES (dates set to 2025 and include transaction_year)
INSERT INTO `sales` (`customer_id`, `user_id`, `total_amount`, `discount`, `amount_paid`, `change_due`, `payment_method`, `transaction_year`, `created_at`, `updated_at`) VALUES
(1, 2, 1050.00, 0, 1100.00, 50.00, 'cash', 2025, '2025-12-10 09:30:00', '2025-12-10 09:30:00'),
(2, 2, 800.00, 0, 800.00, 0, 'gcash', 2025, '2025-12-10 11:15:00', '2025-12-10 11:15:00'),
(NULL, 2, 450.00, 0, 500.00, 50.00, 'cash', 2025, '2025-12-10 14:20:00', '2025-12-10 14:20:00'),
(3, 2, 2550.00, 100, 2450.00, 0, 'cash', 2025, '2025-12-11 10:00:00', '2025-12-11 10:00:00'),
(4, 2, 1200.00, 0, 1200.00, 0, 'card', 2025, '2025-12-11 15:30:00', '2025-12-11 15:30:00'),
(5, 3, 150.00, 0, 150.00, 0, 'cash', 2025, '2025-12-12 09:00:00', '2025-12-12 09:00:00');

-- 8. SALE ITEMS (including service_id column)
INSERT INTO `sale_items` (`sale_id`, `product_id`, `service_id`, `quantity`, `price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 2, 250.00, 500.00, '2025-12-10 09:30:00', '2025-12-10 09:30:00'),
(1, 6, NULL, 1, 400.00, 400.00, '2025-12-10 09:30:00', '2025-12-10 09:30:00'),
(1, 9, NULL, 1, 350.00, 350.00, '2025-12-10 09:30:00', '2025-12-10 09:30:00'),
(2, 3, NULL, 1, 500.00, 500.00, '2025-12-10 11:15:00', '2025-12-10 11:15:00'),
(2, 8, NULL, 1, 300.00, 300.00, '2025-12-10 11:15:00', '2025-12-10 11:15:00'),
(3, 13, NULL, 1, 450.00, 450.00, '2025-12-10 14:20:00', '2025-12-10 14:20:00'),
(4, 10, NULL, 1, 1200.00, 1200.00, '2025-12-11 10:00:00', '2025-12-11 10:00:00'),
(4, 11, NULL, 1, 1350.00, 1350.00, '2025-12-11 10:00:00', '2025-12-11 10:00:00'),
(5, NULL, 1, 1, 150.00, 150.00, '2025-12-12 09:00:00', '2025-12-12 09:00:00');

-- 9. SERVICE JOBS (to 2025)
INSERT INTO `service_jobs` (`customer_id`, `mechanic_id`, `motorcycle_details`, `status`, `total_cost`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 'Honda Wave 125 - ABC-1234', 'completed', 550.00, 'Regular maintenance', '2025-12-08 09:00:00', '2025-12-08 16:00:00'),
(2, 3, 'Yamaha Mio - XYZ-5678', 'completed', 900.00, 'Brake service', '2025-12-09 10:00:00', '2025-12-09 15:00:00'),
(3, 3, 'Suzuki Raider 150', 'ongoing', 1800.00, 'Chain replacement', '2025-12-11 08:00:00', '2025-12-11 08:00:00'),
(4, 3, 'Honda Click 150', 'pending', 0, 'Waiting for parts', '2025-12-11 14:00:00', '2025-12-11 14:00:00');

-- 10. SERVICE JOB ITEMS (to 2025)
INSERT INTO `service_job_items` (`service_job_id`, `product_id`, `quantity`, `price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 400.00, 400.00, '2025-12-08 09:00:00', '2025-12-08 09:00:00'),
(2, 3, 1, 500.00, 500.00, '2025-12-09 10:00:00', '2025-12-09 10:00:00'),
(3, 4, 1, 1200.00, 1200.00, '2025-12-11 08:00:00', '2025-12-11 08:00:00');

-- 11. SERVICE JOB SERVICES (to 2025)
INSERT INTO `service_job_services` (`service_job_id`, `service_id`, `labor_fee`, `created_at`, `updated_at`) VALUES
(1, 1, 150.00, '2025-12-08 09:00:00', '2025-12-08 09:00:00'),
(2, 4, 200.00, '2025-12-09 10:00:00', '2025-12-09 10:00:00'),
(2, 1, 150.00, '2025-12-09 10:00:00', '2025-12-09 10:00:00'),
(3, 6, 300.00, '2025-12-11 08:00:00', '2025-12-11 08:00:00');

-- 12. Void request example (cashier requests a void, manager approves)
-- A request: sale_id=2, requested_by John (id=2)
INSERT INTO `void_requests` (`sale_id`, `requested_by`, `requested_at`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(2, 2, '2025-12-11 13:00:00', 'Customer requested refund; duplicate purchase', 'pending', NOW(), NOW());

-- Manager approves it and a log entry added (use application or run the following statements):
UPDATE `void_requests` SET `status` = 'approved', `approved_by` = 1, `approved_at` = '2025-12-11 14:00:00' WHERE `sale_id` = 2;

INSERT INTO `void_logs` (`sale_id`, `action`, `performed_by`, `performed_at`, `note`, `created_at`, `updated_at`) VALUES
(2, 'requested', 2, '2025-12-11 13:00:00', 'Cashier created void request', NOW(), NOW()),
(2, 'approved', 1, '2025-12-11 14:00:00', 'Manager approved void', NOW(), NOW());

-- Update the 'sales' record to mark it voided (use app logic or run this after approving):
UPDATE `sales` SET `is_void` = 1, `voided_by` = 1, `voided_at` = '2025-12-11 14:00:00', `void_reason` = 'Manager approved void: duplicate purchase' WHERE `id` = 2;

-- Populate transaction_year for inserted sample sales
UPDATE `sales` SET `transaction_year` = YEAR(`created_at`) WHERE `transaction_year` IS NULL;

-- Done. You can now run `php artisan migrate:fresh` then run this SQL to populate sample data and voids.
