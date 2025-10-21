-- Database Backup
-- Database: `pharmacy`
-- Backup Date: 2025-08-28 08:07:37



CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` VALUES('1', 'medicines', 'Medicines', 'assets/images/medicines.png');
INSERT INTO `categories` VALUES('12', 'non-pharms', 'Non-pharms', 'assets/images/nonpharms.png');
INSERT INTO `categories` VALUES('32', 'haemostatics', 'Haemostatic Products', 'assets/images/haemostatics.png');
INSERT INTO `categories` VALUES('39', 'radiologicals', 'Radiological Supplies', 'assets/images/radiological.png');
INSERT INTO `categories` VALUES('40', 'laboratory', 'Laboratory Supplies', 'assets/images/laboratory.png');
INSERT INTO `categories` VALUES('44', 'foods', 'Milk products', 'assets/images/milkproducts.png');
INSERT INTO `categories` VALUES('45', 'Vaccines', 'Vaccines', '68a57984a1c7c.png');
INSERT INTO `categories` VALUES('46', 'Analgesics', 'Pain relief medications', NULL);



CREATE TABLE IF NOT EXISTS `credit_balances` (
  `id` int NOT NULL AUTO_INCREMENT,
  `receipt_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `balance_amount` decimal(10,2) DEFAULT NULL,
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` double DEFAULT NULL,
  `tendered_amount` double DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE IF NOT EXISTS `current_status` (
  `status_id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `current_status` VALUES('1', 'Active', '2025-06-19 00:00:00');
INSERT INTO `current_status` VALUES('2', 'Resigned', '2025-06-19 00:00:00');
INSERT INTO `current_status` VALUES('3', 'Terminated', '2025-06-19 00:00:00');
INSERT INTO `current_status` VALUES('4', 'On Leave', '2025-06-19 00:00:00');
INSERT INTO `current_status` VALUES('1', 'Active', '2025-06-19 00:00:00');
INSERT INTO `current_status` VALUES('2', 'Resigned', '2025-06-19 00:00:00');
INSERT INTO `current_status` VALUES('3', 'Terminated', '2025-06-19 00:00:00');
INSERT INTO `current_status` VALUES('4', 'On Leave', '2025-06-19 00:00:00');



CREATE TABLE IF NOT EXISTS `defaulters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `receipt_id` varchar(255) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `balance_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `tendered_amount` decimal(10,2) DEFAULT NULL,
  `transDate` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `moved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `defaulters` VALUES('1', 'ORD202508273557', 'Muongo Kasongo', '0111666666', '0.00', '30000.00', '30000.00', '2025-08-27 12:33:37', 'Paid', 'pharmtech', '2025-08-27 12:47:07');
INSERT INTO `defaulters` VALUES('2', 'ORD202508274395', 'Bestie', '0333445566', '0.00', '360.00', '360.00', '2025-08-27 12:59:40', 'Paid', 'pharmtech', '2025-08-27 13:24:39');



CREATE TABLE IF NOT EXISTS `loans` (
  `loan_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `request_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'processed',
  `approved_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `reason` text COLLATE utf8mb4_general_ci,
  `resubmitted` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`loan_id`),
  CONSTRAINT `loans_chk_1` CHECK ((`status` in (_utf8mb4'active',_utf8mb4'processed',_utf8mb4'paid')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `productname` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `brandname` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `packsize` int DEFAULT NULL,
  `pack_price` double DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT '0.00',
  `price` double DEFAULT '0',
  `reorder_level` int DEFAULT '0',
  `currentstatus` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brandname` (`brandname`)
) ENGINE=InnoDB AUTO_INCREMENT=19513 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` VALUES('19505', '1', 'Mpox Vaccine', 'MPOX-C', '200', '300', '1.50', '4', '10', 'Active', '2025-08-20 09:13:41');
INSERT INTO `products` VALUES('19507', '1', 'Ibuprofen', 'Advil', '10', '150', '15.00', '20', '50', 'Active', '2025-08-21 08:06:14');
INSERT INTO `products` VALUES('19508', '1', 'Paracetamol', 'Panadol', '10', '100', '10.00', '15', '50', 'Active', '2025-08-21 08:12:51');
INSERT INTO `products` VALUES('19509', '1', 'Ibuprofen', 'Ibufil-400', '10', '150', '15.00', '20', '50', 'Active', '2025-08-21 11:11:06');
INSERT INTO `products` VALUES('19510', '1', 'Paracetamol Tablets 500 mg', 'Panadol Extra', '20', '150', '7.50', '10', '200', 'Active', '2025-08-21 11:56:04');



CREATE TABLE IF NOT EXISTS `purchase_order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `total_amount` double DEFAULT NULL,
  `status` enum('Pending','Approved','Completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE IF NOT EXISTS `salary_payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Paid',
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sales_id` int DEFAULT NULL,
  `brandname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `discount` double DEFAULT '0',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `tax_amount` double DEFAULT NULL,
  `grand_total` double DEFAULT NULL,
  `transBy` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sale_items` VALUES('21', '80', 'Advil', '100', '20.00', '6', '2000.00', '30', '1880', 'admin');
INSERT INTO `sale_items` VALUES('22', '80', 'Ibufil-400', '20', '20.00', '10', '400.00', '6', '360', 'admin');
INSERT INTO `sale_items` VALUES('23', '81', 'Ibufil-400', '40', '20.00', '0', '800.00', '12', '800', 'admin');
INSERT INTO `sale_items` VALUES('24', '82', 'Ibufil-400', '200', '20.00', '5', '4000.00', '60', '3800', 'admin');
INSERT INTO `sale_items` VALUES('25', '83', 'Ibufil-400', '4', '20.00', '0', '80.00', '1.2', '80', 'admin');
INSERT INTO `sale_items` VALUES('26', '84', 'Panadol Extra', '200', '10.00', '0', '2000.00', '30', '2000', 'admin');
INSERT INTO `sale_items` VALUES('27', '85', 'Panadol', '10', '15.00', '5', '150.00', '2.25', '142.5', 'admin');
INSERT INTO `sale_items` VALUES('28', '86', 'Ibufil-400', '5', '20.00', '0', '100.00', '1.5', '100', 'admin');
INSERT INTO `sale_items` VALUES('29', '87', 'Advil', '18', '20.00', '0', '360.00', '5.3999999999999995', '360', 'admin');
INSERT INTO `sale_items` VALUES('32', '95', 'Kodak 223', '9', '60.00', '0', '540.00', '8.1', '540', 'pharmtech');
INSERT INTO `sale_items` VALUES('46', '113', 'Advil', '2', '20.00', '0', '40.00', '0.6', '40', 'pharmtech');
INSERT INTO `sale_items` VALUES('75', '146', 'Panadol Extra', '10', '10.00', '0', '100.00', '1.5', '100', 'pharmtech');
INSERT INTO `sale_items` VALUES('76', '147', 'Advil', '400', '20.00', '0', '8000.00', '120', '8000', 'pharmtech');
INSERT INTO `sale_items` VALUES('77', '148', 'Ibufil-400', '100', '20.00', '0', '2000.00', '30', '2000', 'pharmtech');
INSERT INTO `sale_items` VALUES('78', '148', 'Panadol Extra', '100', '10.00', '0', '1000.00', '15', '1000', 'pharmtech');
INSERT INTO `sale_items` VALUES('79', '149', 'Panadol Extra', '41', '10.00', '0', '410.00', '6.1499999999999995', '410', 'pharmtech');
INSERT INTO `sale_items` VALUES('80', '150', 'Panadol Extra', '8', '10.00', '0', '80.00', '1.2', '80', 'pharmtech');
INSERT INTO `sale_items` VALUES('81', '165', 'Panadol Extra', '9', '10.00', '0', '90.00', '1.3499999999999999', '90', 'pharmtech');
INSERT INTO `sale_items` VALUES('82', '166', 'Panadol Extra', '3000', '10.00', '0', '30000.00', '450', '30000', 'pharmtech');
INSERT INTO `sale_items` VALUES('83', '167', 'Ibufil-400', '18', '20.00', '0', '360.00', '5.3999999999999995', '360', 'pharmtech');
INSERT INTO `sale_items` VALUES('84', '168', 'Panadol Extra', '100', '10.00', '0', '1000.00', '15', '1000', 'pharmtech');
INSERT INTO `sale_items` VALUES('85', '169', 'Panadol Extra', '1', '10.00', '0', '10.00', '0.15', '10', 'pharmtech');
INSERT INTO `sale_items` VALUES('86', '170', 'Gauzeeee 500 mg', '15', '25.00', '0', '375.00', '5.625', '375', 'pharmtech');
INSERT INTO `sale_items` VALUES('87', '171', 'Panadol Extra', '1', '10.00', '0', '10.00', '0.15', '10', 'pharmtech');
INSERT INTO `sale_items` VALUES('88', '172', 'Panadol', '100', '15.00', '0', '1500.00', '22.5', '1500', 'pharmtech');
INSERT INTO `sale_items` VALUES('89', '173', 'MPOX-C', '3', '4.00', '0', '12.00', '0.18', '12', 'pharmtech');
INSERT INTO `sale_items` VALUES('90', '174', 'MPOX-C', '1', '4.00', '0', '4.00', '0.06', '4', 'pharmtech');
INSERT INTO `sale_items` VALUES('91', '175', 'MPOX-C', '51', '4.00', '0', '204.00', '3.06', '204', 'pharmtech');
INSERT INTO `sale_items` VALUES('92', '176', 'Panadol Extra', '2211', '10.00', '0', '22110.00', '331.65', '22110', 'pharmtech');
INSERT INTO `sale_items` VALUES('93', '177', 'Panadol Extra', '120', '10.00', '1', '1200.00', '18', '1188', 'pharmtech');
INSERT INTO `sale_items` VALUES('94', '178', 'Panadol Extra', '41', '10.00', '0', '410.00', '6.1499999999999995', '410', 'pharmtech');
INSERT INTO `sale_items` VALUES('95', '178', 'Panadol', '11', '15.00', '0', '165.00', '2.475', '165', 'pharmtech');
INSERT INTO `sale_items` VALUES('96', '179', 'Panadol Extra', '1030', '10.00', '0', '10300.00', '154.5', '10300', 'pharmtech');
INSERT INTO `sale_items` VALUES('97', '180', 'Panadol Extra', '200', '10.00', '0', '2000.00', '30', '2000', 'pharmtech');
INSERT INTO `sale_items` VALUES('98', '181', 'Panadol Extra', '200', '10.00', '0', '2000.00', '30', '2000', 'pharmtech');
INSERT INTO `sale_items` VALUES('99', '182', 'Panadol Extra', '300', '10.00', '0', '3000.00', '45', '3000', 'pharmtech');



CREATE TABLE IF NOT EXISTS `sales` (
  `sales_id` int NOT NULL AUTO_INCREMENT,
  `receipt_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `discount` double DEFAULT '0',
  `grand_total` decimal(10,2) DEFAULT NULL,
  `tendered_amount` double DEFAULT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Draft',
  `transBy` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sales_id`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `sales` VALUES('66', 'ORD202508244115', NULL, '20.00', '0.30', '0', '20.00', '40', 'Cash', 'Paid', 'admin', '2025-08-24 21:17:01');
INSERT INTO `sales` VALUES('80', 'ORD202508246323', NULL, '2400.00', '36.00', '160', '2240.00', '3000', 'Cash', 'Paid', 'admin', '2025-08-25 07:47:15');
INSERT INTO `sales` VALUES('81', 'ORD202508257908', NULL, '800.00', '12.00', '0', '800.00', '600', 'Cash', 'Credit', 'admin', '2025-08-25 07:59:28');
INSERT INTO `sales` VALUES('82', 'ORD202508258725', NULL, '4000.00', '60.00', '200', '3800.00', '4000', 'Cash', 'Paid', 'admin', '2025-08-25 09:06:47');
INSERT INTO `sales` VALUES('83', 'ORD202508253136', NULL, '80.00', '1.20', '0', '80.00', '100', 'Cash', 'Paid', 'admin', '2025-08-25 10:55:26');
INSERT INTO `sales` VALUES('84', 'ORD202508254536', NULL, '2000.00', '30.00', '0', '2000.00', '2400', 'Cash', 'Paid', 'admin', '2025-08-25 11:40:38');
INSERT INTO `sales` VALUES('85', 'ORD202508255421', NULL, '150.00', '2.25', '7.5', '142.50', '300', 'Cash', 'Paid', 'admin', '2025-08-25 12:01:42');
INSERT INTO `sales` VALUES('86', 'ORD202508258700', NULL, '100.00', '1.50', '0', '100.00', '80', 'Cash', 'Credit', 'admin', '2025-08-25 12:10:11');
INSERT INTO `sales` VALUES('87', 'ORD202508259611', NULL, '360.00', '5.40', '0', '360.00', '400', 'Cash', 'Paid', 'admin', '2025-08-25 12:33:21');
INSERT INTO `sales` VALUES('88', 'ORD202508259093', NULL, '44.00', '0.66', '0', '44.00', '60', 'Cash', 'Paid', 'admin', '2025-08-25 12:46:15');
INSERT INTO `sales` VALUES('89', 'ORD202508252047', NULL, '8.00', '0.12', '0', '8.00', '6', 'Cash', 'Credit', 'admin', '2025-08-25 12:47:30');
INSERT INTO `sales` VALUES('90', 'ORD202508257386', NULL, '80000.00', '1200.00', '4000', '76000.00', '77000', 'Cash', 'Paid', 'admin', '2025-08-25 13:02:47');
INSERT INTO `sales` VALUES('91', 'ORD202508254439', NULL, '6000.00', '90.00', '0', '6000.00', '4000', 'Cash', 'Credit', 'admin', '2025-08-25 13:07:46');
INSERT INTO `sales` VALUES('92', 'ORD202508254652', NULL, '460.00', '6.90', '0', '460.00', '500', 'Cash', 'Paid', 'pharmtech', '2025-08-25 13:31:57');
INSERT INTO `sales` VALUES('95', 'ORD202508251970', NULL, '540.00', '8.10', '0', '540.00', '600', 'Cash', 'Paid', 'pharmtech', '2025-08-25 15:57:17');
INSERT INTO `sales` VALUES('96', 'ORD202508251970', NULL, '540.00', '8.10', '0', '540.00', '600', 'Cash', 'Paid', 'pharmtech', '2025-08-25 15:57:40');
INSERT INTO `sales` VALUES('113', 'ORD202508259846', NULL, '40.00', '0.60', '0', '40.00', '80', 'Cash', 'Paid', 'pharmtech', '2025-08-25 17:58:45');
INSERT INTO `sales` VALUES('114', 'ORD202508252206', NULL, '120.00', '1.80', '0', '120.00', '140', 'Cash', 'Paid', 'pharmtech', '2025-08-25 18:13:31');
INSERT INTO `sales` VALUES('132', 'ORD202508265190', NULL, '120.00', '1.80', '0', '120.00', '200', 'Cash', 'Paid', 'pharmtech', '2025-08-26 13:35:12');
INSERT INTO `sales` VALUES('133', 'ORD202508261002', NULL, '200.00', '3.00', '0', '200.00', '220', 'Cash', 'Paid', 'pharmtech', '2025-08-26 13:37:11');
INSERT INTO `sales` VALUES('134', 'ORD202508262777', NULL, '60.00', '0.90', '0', '60.00', '80', 'Cash', 'Paid', 'pharmtech', '2025-08-26 13:39:10');
INSERT INTO `sales` VALUES('146', 'ORD202508267500', '[{\"brandname\":\"Panadol Extra\",\"quantity\":10,\"price\":10,\"discount\":0,\"total_amount\":100,\"tax_amount\":1.5,\"grand_total\":100}]', '100.00', '1.50', '0', '100.00', '120', 'Cash', 'Paid', 'pharmtech', '2025-08-26 14:42:06');
INSERT INTO `sales` VALUES('147', 'ORD202508268175', '[{\"brandname\":\"Advil\",\"quantity\":400,\"price\":20,\"discount\":0,\"total_amount\":8000,\"tax_amount\":120,\"grand_total\":8000}]', '8000.00', '120.00', '0', '8000.00', '8400', 'Cash', 'Paid', 'pharmtech', '2025-08-26 14:43:10');
INSERT INTO `sales` VALUES('148', 'ORD202508264112', '[{\"brandname\":\"Ibufil-400\",\"quantity\":100,\"price\":20,\"discount\":0,\"total_amount\":2000,\"tax_amount\":30,\"grand_total\":2000},{\"brandname\":\"Panadol Extra\",\"quantity\":100,\"price\":10,\"discount\":0,\"total_amount\":1000,\"tax_amount\":15,\"grand_total\":1000}]', '3000.00', '45.00', '0', '3000.00', '3000', 'Cash', 'Paid', 'pharmtech', '2025-08-26 14:47:19');
INSERT INTO `sales` VALUES('149', 'ORD202508266880', '[{\"brandname\":\"Panadol Extra\",\"quantity\":41,\"price\":10,\"discount\":0,\"total_amount\":410,\"tax_amount\":6.1499999999999995,\"grand_total\":410}]', '410.00', '6.15', '0', '410.00', '500', 'Cash', 'Paid', 'pharmtech', '2025-08-26 14:48:47');
INSERT INTO `sales` VALUES('150', 'ORD202508272707', '[{\"brandname\":\"Panadol Extra\",\"quantity\":8,\"price\":10,\"discount\":0,\"total_amount\":80,\"tax_amount\":1.2,\"grand_total\":80}]', '80.00', '1.20', '0', '80.00', '100', 'Cash', 'Paid', 'pharmtech', '2025-08-27 06:12:52');
INSERT INTO `sales` VALUES('165', 'ORD202508271853', '[{\"brandname\":\"Panadol Extra\",\"quantity\":9,\"price\":10,\"discount\":0,\"total_amount\":90,\"tax_amount\":1.3499999999999999,\"grand_total\":90}]', '90.00', '1.35', '0', '90.00', '100', 'Cash', 'Paid', 'pharmtech', '2025-08-27 07:13:39');
INSERT INTO `sales` VALUES('166', 'ORD202508273557', '[{\"brandname\":\"Panadol Extra\",\"quantity\":3000,\"price\":10,\"discount\":0,\"total_amount\":30000,\"tax_amount\":450,\"grand_total\":30000}]', '30000.00', '450.00', '0', '30000.00', '26000', 'Mpesa', 'Credit', 'pharmtech', '2025-08-27 07:16:00');
INSERT INTO `sales` VALUES('167', 'ORD202508274395', '[{\"brandname\":\"Ibufil-400\",\"quantity\":18,\"price\":20,\"discount\":0,\"total_amount\":360,\"tax_amount\":5.3999999999999995,\"grand_total\":360}]', '360.00', '5.40', '0', '360.00', '0', 'Cash', 'Credit', 'pharmtech', '2025-08-27 09:59:40');
INSERT INTO `sales` VALUES('168', 'ORD202508275939', '[{\"brandname\":\"Panadol Extra\",\"quantity\":100,\"price\":10,\"discount\":0,\"total_amount\":1000,\"tax_amount\":15,\"grand_total\":1000}]', '1000.00', '15.00', '0', '1000.00', '1000', 'Cash', 'Paid', 'pharmtech', '2025-08-27 10:17:51');
INSERT INTO `sales` VALUES('169', 'ORD202508289821', '[{\"brandname\":\"Panadol Extra\",\"quantity\":1,\"price\":10,\"discount\":0,\"total_amount\":10,\"tax_amount\":0.15,\"grand_total\":10}]', '10.00', '0.15', '0', '10.00', '15', 'Cash', 'Paid', 'pharmtech', '2025-08-28 05:54:35');
INSERT INTO `sales` VALUES('170', 'ORD202508271635', '[{\"brandname\":\"Gauzeeee 500 mg\",\"quantity\":15,\"price\":25,\"discount\":0,\"total_amount\":375,\"tax_amount\":5.625,\"grand_total\":375}]', '375.00', '5.63', '0', '375.00', '375', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:23:13');
INSERT INTO `sales` VALUES('171', 'ORD202508273479', '[{\"brandname\":\"Panadol Extra\",\"quantity\":1,\"price\":10,\"discount\":0,\"total_amount\":10,\"tax_amount\":0.15,\"grand_total\":10}]', '10.00', '0.15', '0', '10.00', '10', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:25:06');
INSERT INTO `sales` VALUES('172', 'ORD202508289974', '[{\"brandname\":\"Panadol\",\"quantity\":100,\"price\":15,\"discount\":0,\"total_amount\":1500,\"tax_amount\":22.5,\"grand_total\":1500}]', '1500.00', '22.50', '0', '1500.00', '1500', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:28:28');
INSERT INTO `sales` VALUES('173', 'ORD202508286038', '[{\"brandname\":\"MPOX-C\",\"quantity\":3,\"price\":4,\"discount\":0,\"total_amount\":12,\"tax_amount\":0.18,\"grand_total\":12}]', '12.00', '0.18', '0', '12.00', '12', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:31:03');
INSERT INTO `sales` VALUES('174', 'ORD202508288690', '[{\"brandname\":\"MPOX-C\",\"quantity\":1,\"price\":4,\"discount\":0,\"total_amount\":4,\"tax_amount\":0.06,\"grand_total\":4}]', '4.00', '0.06', '0', '4.00', '4', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:31:55');
INSERT INTO `sales` VALUES('175', 'ORD202508281638', '[{\"brandname\":\"MPOX-C\",\"quantity\":51,\"price\":4,\"discount\":0,\"total_amount\":204,\"tax_amount\":3.06,\"grand_total\":204}]', '204.00', '3.06', '0', '204.00', '210', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:33:48');
INSERT INTO `sales` VALUES('176', 'ORD202508280838', '[{\"brandname\":\"Panadol Extra\",\"quantity\":2211,\"price\":10,\"discount\":0,\"total_amount\":22110,\"tax_amount\":331.65,\"grand_total\":22110}]', '22110.00', '331.65', '0', '22110.00', '23000', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:45:20');
INSERT INTO `sales` VALUES('177', 'ORD202508281168', '[{\"brandname\":\"Panadol Extra\",\"quantity\":120,\"price\":10,\"discount\":1,\"total_amount\":1200,\"tax_amount\":18,\"grand_total\":1188}]', '1200.00', '18.00', '12', '1188.00', '2000', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:14:02');
INSERT INTO `sales` VALUES('178', 'ORD202508280440', '[{\"brandname\":\"Panadol Extra\",\"quantity\":41,\"price\":10,\"discount\":0,\"total_amount\":410,\"tax_amount\":6.1499999999999995,\"grand_total\":410},{\"brandname\":\"Panadol\",\"quantity\":11,\"price\":15,\"discount\":0,\"total_amount\":165,\"tax_amount\":2.475,\"grand_total\":165}]', '575.00', '8.63', '0', '575.00', '600', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:19:48');
INSERT INTO `sales` VALUES('179', 'ORD202508286109', '[{\"brandname\":\"Panadol Extra\",\"quantity\":1030,\"price\":10,\"discount\":0,\"total_amount\":10300,\"tax_amount\":154.5,\"grand_total\":10300}]', '10300.00', '154.50', '0', '10300.00', '11000', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:40:34');
INSERT INTO `sales` VALUES('180', 'ORD202508280337', '[{\"brandname\":\"Panadol Extra\",\"quantity\":200,\"price\":10,\"discount\":0,\"total_amount\":2000,\"tax_amount\":30,\"grand_total\":2000}]', '2000.00', '30.00', '0', '2000.00', '2000', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:45:02');
INSERT INTO `sales` VALUES('181', 'ORD202508284913', '[{\"brandname\":\"Panadol Extra\",\"quantity\":200,\"price\":10,\"discount\":0,\"total_amount\":2000,\"tax_amount\":30,\"grand_total\":2000}]', '2000.00', '30.00', '0', '2000.00', '2000', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:52:57');
INSERT INTO `sales` VALUES('182', 'ORD202508280263', '[{\"brandname\":\"Panadol Extra\",\"quantity\":300,\"price\":10,\"discount\":0,\"total_amount\":3000,\"tax_amount\":45,\"grand_total\":3000}]', '3000.00', '45.00', '0', '3000.00', '3000', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:58:40');



CREATE TABLE IF NOT EXISTS `sales_drafts` (
  `draft_id` int NOT NULL AUTO_INCREMENT,
  `receipt_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brandname` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` decimal(10,0) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `tendered_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `transBy` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`draft_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` int NOT NULL AUTO_INCREMENT,
  `date_of_joining` date NOT NULL,
  `job_title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nick_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sex` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `marital_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `religion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_number` int DEFAULT NULL,
  `phone` int DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `current_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `staff_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `photo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `staff` VALUES('1', '2025-06-21', 'Admin', 'Admin', 'User', 'Admin', 'Male', 'admin@gmail.com', '1980-01-01', 'Single', 'Xtian', '20202020', '700112233', 'Nairobi', 'Active', 'BSP00001', 'BSP00001.PNG', '2025-06-21 18:03:24');
INSERT INTO `staff` VALUES('4', '2025-08-14', 'Pharmaceutical Technologist', 'Pharm', 'Tech', '', 'Male', 'pharmtech@gmail.com', '2010-06-08', 'Single', 'Buddhist', '11336655', '333223344', 'pharma', 'Active', 'BSP00002', 'BSP00002.PNG', '2025-08-14 10:19:04');
INSERT INTO `staff` VALUES('5', '2025-08-14', 'Cashier', 'Cashier', 'User', 'Kashia', 'Female', 'cashier@gmail.com', '2000-08-01', 'Married', 'Others', '66664444', '444665544', 'cashier', 'Active', 'BSP00003', 'BSP00003.PNG', '2025-08-14 11:18:49');



CREATE TABLE IF NOT EXISTS `stock_movements` (
  `transID` int NOT NULL AUTO_INCREMENT,
  `id` int NOT NULL,
  `transactionType` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `productname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brandname` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `openingBalance` int DEFAULT '0',
  `quantityIn` double NOT NULL,
  `quantityOut` int DEFAULT NULL,
  `receivedFrom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `batch` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiryDate` date NOT NULL,
  `transBy` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stockBalance` double DEFAULT '0',
  `status` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'active',
  `reasons` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transID`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stock_movements` VALUES('55', '19509', 'Purchase', 'Ibuprofen', 'Ibufil-400', '0', '2000', NULL, 'TransPharm', '123456', '2025-08-30', 'Admin User', '2000', 'active', NULL, '2025-08-24 22:43:53');
INSERT INTO `stock_movements` VALUES('56', '19506', 'Purchase', 'Gauze Roll', 'Gauzeeee 500 mg', '0', '20', NULL, 'TransPharm', '145687', '2026-05-19', 'Admin User', '20', 'active', NULL, '2025-08-24 22:44:23');
INSERT INTO `stock_movements` VALUES('57', '19508', 'Purchase', 'Paracetamol', 'Panadol', '0', '5000', NULL, 'SittiPharm', '1234', '2026-02-26', 'Admin User', '5000', 'active', NULL, '2025-08-24 22:44:58');
INSERT INTO `stock_movements` VALUES('58', '19510', 'Purchase', 'Paracetamol Tablets 500 mg', 'Panadol Extra', '0', '5000', NULL, 'SittiPharm', '456321', '2025-10-10', 'Admin User', '5000', 'active', NULL, '2025-08-24 22:45:22');
INSERT INTO `stock_movements` VALUES('59', '19507', 'Purchase', 'Ibuprofen', 'Advil', '0', '4000', NULL, 'TransPharm', '4500', '2025-10-31', 'Admin User', '4000', 'active', NULL, '2025-08-24 22:45:51');
INSERT INTO `stock_movements` VALUES('60', '19507', 'Purchase', 'Ibuprofen', 'Advil', '4000', '2500', NULL, 'TransPharm', '4000', '2026-01-30', 'Admin User', '6500', 'active', NULL, '2025-08-24 22:46:33');
INSERT INTO `stock_movements` VALUES('61', '19507', 'Sales', 'Advil', 'Advil', '6500', '0', '100', 'none', NULL, '2025-12-31', 'admin', '6400', 'Completed', NULL, '2025-08-25 07:47:15');
INSERT INTO `stock_movements` VALUES('62', '19509', 'Sales', 'Ibufil-400', 'Ibufil-400', '2000', '0', '20', 'none', NULL, '2025-12-31', 'admin', '1980', 'Completed', NULL, '2025-08-25 07:47:15');
INSERT INTO `stock_movements` VALUES('63', '19509', 'Sales', 'Ibufil-400', 'Ibufil-400', '1980', '0', '40', 'none', NULL, '2025-12-31', 'admin', '1940', 'Completed', NULL, '2025-08-25 07:59:28');
INSERT INTO `stock_movements` VALUES('64', '19509', 'Sales', 'Ibufil-400', 'Ibufil-400', '1940', '0', '200', 'none', NULL, '2025-12-31', 'admin', '1740', 'Completed', NULL, '2025-08-25 09:06:47');
INSERT INTO `stock_movements` VALUES('65', '19509', 'Sales', 'Ibufil-400', 'Ibufil-400', '1740', '0', '4', 'none', NULL, '2025-12-31', 'admin', '1736', 'Completed', NULL, '2025-08-25 10:55:26');
INSERT INTO `stock_movements` VALUES('66', '19510', 'Sales', 'Panadol Extra', 'Panadol Extra', '5000', '0', '200', 'none', NULL, '2025-12-31', 'admin', '4800', 'Completed', NULL, '2025-08-25 11:40:38');
INSERT INTO `stock_movements` VALUES('67', '19508', 'Sales', 'Panadol', 'Panadol', '5000', '0', '10', 'none', NULL, '2025-12-31', 'admin', '4990', 'Completed', NULL, '2025-08-25 12:01:42');
INSERT INTO `stock_movements` VALUES('68', '19509', 'Sales', 'Ibufil-400', 'Ibufil-400', '1736', '0', '5', 'none', NULL, '2025-12-31', 'admin', '1731', 'Completed', NULL, '2025-08-25 12:10:11');
INSERT INTO `stock_movements` VALUES('69', '19507', 'Sales', 'Advil', 'Advil', '6400', '0', '18', 'none', NULL, '2025-12-31', 'admin', '6382', 'Completed', NULL, '2025-08-25 12:33:21');
INSERT INTO `stock_movements` VALUES('70', '19505', 'Purchase', 'Mpox Vaccine', 'MPOX-C', '0', '100', NULL, 'TransPharm', 'WANGA', '2025-12-25', 'Admin User', '100', 'active', NULL, '2025-08-25 12:45:30');
INSERT INTO `stock_movements` VALUES('71', '19507', 'Purchase', 'Ibuprofen', 'Advil', '6382', '3569', NULL, 'TransPharm', 'TEST', '2025-10-30', 'Admin User', '9951', 'active', NULL, '2025-08-25 12:59:15');
INSERT INTO `stock_movements` VALUES('72', '19507', 'Returns', 'Ibuprofen', 'Advil', '9951', '1500', NULL, 'TransPharm', 'TEST', '2025-10-23', 'Admin User', '11451', 'active', NULL, '2025-08-25 13:00:39');
INSERT INTO `stock_movements` VALUES('73', '19511', 'Purchase', 'X-Ray Film', 'Kodak 223', '0', '40', NULL, 'TransPharm', '123456K', '2026-04-15', 'Pharm Tech', '40', 'active', NULL, '2025-08-25 14:19:38');
INSERT INTO `stock_movements` VALUES('74', '19511', 'Sales', 'Kodak 223', 'Kodak 223', '40', '0', '9', 'none', NULL, '2025-12-31', 'pharmtech', '31', 'Completed', NULL, '2025-08-25 15:57:17');



CREATE TABLE IF NOT EXISTS `stocks` (
  `stockID` int NOT NULL AUTO_INCREMENT,
  `id` int NOT NULL,
  `transactionType` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `productname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brandname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reorderLevel` int DEFAULT NULL,
  `openingBalance` double DEFAULT '0',
  `quantityIn` int DEFAULT NULL,
  `batch` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `receivedFrom` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantityOut` int DEFAULT '0',
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `stockBalance` int DEFAULT NULL,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transBy` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`stockID`),
  KEY `idx_stocks_brandname_transDate` (`brandname`,`transDate` DESC)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stocks` VALUES('50', '19509', 'Sold', 'Ibuprofen', 'Ibufil-400', '10', '0', '2000', '123456', '2025-08-30 00:00:00', 'TransPharm', '269', '2025-08-26 17:47:19', '1631', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('51', '19506', 'Purchase', 'Gauze Roll', 'Gauzeeee 500 mg', '10', '0', '20', '145687', '2026-05-19 00:00:00', 'TransPharm', '0', '2025-08-24 22:44:23', '20', 'active', 'Admin User');
INSERT INTO `stocks` VALUES('52', '19508', 'Sold', 'Paracetamol', 'Panadol', '10', '0', '5000', '1234', '2026-02-26 00:00:00', 'SittiPharm', '10', '2025-08-28 08:04:32', '1990', 'active', 'admin');
INSERT INTO `stocks` VALUES('53', '19510', 'Sold', 'Paracetamol Tablets 500 mg', 'Panadol Extra', '10', '0', '5000', '456321', '2025-10-10 00:00:00', 'SittiPharm', '200', '2025-08-27 09:12:52', '4641', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('54', '19507', 'Purchase', 'Ibuprofen', 'Advil', '10', '0', '4000', '4500', '2025-10-31 00:00:00', 'TransPharm', '0', '2025-08-26 17:43:10', '11051', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('55', '19507', 'Sold', 'Ibuprofen', 'Advil', '10', '4000', '2500', '4000', '2026-01-30 00:00:00', 'TransPharm', '118', '2025-08-26 17:43:10', '11051', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('56', '19505', 'Purchase', 'Mpox Vaccine', 'MPOX-C', '10', '0', '100', 'WANGA', '2025-12-25 00:00:00', 'TransPharm', '0', '2025-08-25 12:45:30', '100', 'active', 'Admin User');
INSERT INTO `stocks` VALUES('57', '19507', 'Purchase', 'Ibuprofen', 'Advil', '10', '6382', '3569', 'TEST', '2025-10-30 00:00:00', 'TransPharm', '0', '2025-08-26 17:43:10', '11051', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('58', '19507', 'Returns', 'Ibuprofen', 'Advil', '10', '9951', '1500', 'TEST', '2025-10-23 00:00:00', 'TransPharm', '0', '2025-08-26 17:43:10', '11051', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('59', '19511', 'Sold', 'X-Ray Film', 'Kodak 223', '100', '0', '40', '123456K', '2026-04-15 00:00:00', 'TransPharm', '9', '2025-08-25 15:57:17', '31', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('60', '19510', 'Sold', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '4641', '0', '456321', '2025-10-10 00:00:00', 'None', '9', '2025-08-27 07:13:39', '4632', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('61', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '4641', '0', '456321', '2025-10-10 00:00:00', 'None', '3000', '2025-08-27 07:16:00', '1641', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('62', '19509', 'Sales', 'Ibuprofen', 'Ibufil-400', NULL, '1631', '0', '123456', '2025-08-30 00:00:00', 'None', '18', '2025-08-27 09:59:40', '1613', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('63', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '4641', '0', '456321', '2025-10-10 00:00:00', 'None', '100', '2025-08-28 08:04:32', '8541', 'active', 'pharmtech');
INSERT INTO `stocks` VALUES('64', '19508', 'Expired', 'Paracetamol', 'Panadol', NULL, '4990', '0', '1234', '2026-02-26 00:00:00', 'None', '3000', '2025-08-28 08:04:32', '1990', 'Completed', ' ');
INSERT INTO `stocks` VALUES('65', '19510', 'Positive Adjustment', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '4541', '4000', '456321', '2025-10-10 00:00:00', 'None', '0', '2025-08-28 08:04:32', '8541', 'Completed', ' ');
INSERT INTO `stocks` VALUES('66', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '1', '2025-08-28 05:54:35', '8540', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('67', '19506', 'Sales', 'Gauze Roll', 'Gauzeeee 500 mg', NULL, '20', '0', '145687', '2026-05-19 00:00:00', 'None', '15', '2025-08-28 06:23:13', '5', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('68', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '1', '2025-08-28 06:25:06', '8540', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('69', '19508', 'Sales', 'Paracetamol', 'Panadol', NULL, '1990', '0', '1234', '2026-02-26 00:00:00', 'None', '100', '2025-08-28 06:28:28', '1890', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('70', '19505', 'Sales', 'Mpox Vaccine', 'MPOX-C', NULL, '100', '0', '0', '2025-12-25 00:00:00', 'None', '3', '2025-08-28 06:31:03', '97', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('71', '19505', 'Sales', 'Mpox Vaccine', 'MPOX-C', NULL, '97', '0', '0', '2025-12-25 00:00:00', 'None', '1', '2025-08-28 06:31:55', '96', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('72', '19505', 'Sales', 'Mpox Vaccine', 'MPOX-C', NULL, '96', '0', '0', '2025-12-25 00:00:00', 'None', '51', '2025-08-28 06:33:48', '45', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('73', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '2211', '2025-08-28 06:45:20', '6330', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('74', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '120', '2025-08-28 07:14:02', '8421', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('75', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '41', '2025-08-28 07:19:48', '8500', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('76', '19508', 'Sales', 'Paracetamol', 'Panadol', NULL, '1990', '0', '1234', '2026-02-26 00:00:00', 'None', '11', '2025-08-28 07:19:48', '1979', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('77', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '1030', '2025-08-28 07:40:34', '7511', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('78', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '200', '2025-08-28 07:45:02', '8341', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('79', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '200', '2025-08-28 07:52:57', '8341', 'Active', 'pharmtech');
INSERT INTO `stocks` VALUES('80', '19510', 'Sales', 'Paracetamol Tablets 500 mg', 'Panadol Extra', NULL, '8541', '0', '456321', '2025-10-10 00:00:00', 'None', '300', '2025-08-28 07:58:40', '8241', 'Active', 'pharmtech');



CREATE TABLE IF NOT EXISTS `suppliers` (
  `supplier_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contact_person` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` int DEFAULT NULL,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`supplier_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `suppliers` VALUES('2', 'TransPharm', 'Pharma', '711112233', 'TransPharma@gmail.com', 'TransPharma231', '2025-08-08 20:13:06');
INSERT INTO `suppliers` VALUES('3', 'SittiPharm', 'Sitti', '222334455', 'sittipharm@gmail.com', 'sittipharma', '2025-08-18 13:05:08');



CREATE TABLE IF NOT EXISTS `tblsex` (
  `sex_id` int NOT NULL,
  `sex_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tblsex` VALUES('1', 'Female');
INSERT INTO `tblsex` VALUES('2', 'Male');



CREATE TABLE IF NOT EXISTS `transaction_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transactionType` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `transaction_types` VALUES('1', 'Purchase');
INSERT INTO `transaction_types` VALUES('2', 'Promotion');
INSERT INTO `transaction_types` VALUES('3', 'Donation');
INSERT INTO `transaction_types` VALUES('4', 'Returns');
INSERT INTO `transaction_types` VALUES('5', 'Exchange');



CREATE TABLE IF NOT EXISTS `userroles` (
  `id` int NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `userroles` VALUES('1', 'Admin', '2025-03-27 00:00:00');
INSERT INTO `userroles` VALUES('2', 'Cashier', '2025-03-27 00:00:00');
INSERT INTO `userroles` VALUES('3', 'Cleaner', '2025-03-27 00:00:00');
INSERT INTO `userroles` VALUES('4', 'Manager', '2025-06-22 00:00:00');
INSERT INTO `userroles` VALUES('6', 'Pharmacist', '2025-03-27 00:00:00');
INSERT INTO `userroles` VALUES('5', 'Pharmtech', '2025-06-22 00:00:00');
INSERT INTO `userroles` VALUES('7', 'Supervisor', '2025-03-27 00:00:00');



CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sex` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` int NOT NULL,
  `userrole` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `staff_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `mobile` (`mobile`),
  UNIQUE KEY `staff_number` (`staff_number`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES('1', 'Admin', 'User', 'admin@gmail.com', '$2y$10$Cz.mwpRZ7INr3VqlmyMAeOsk3Lpl9zDRv0nNiHSlgRD0KSoWCP8lu', 'admin', 'Male', '722427721', 'Admin', 'BSP00001', '2025-04-01 16:14:14');
INSERT INTO `users` VALUES('5', 'Cashier', 'User', 'cashier@gmail.com', '$2y$10$ysNeXOOOSvm8.RrEu/u.2eLjbs1pAzrg0gH4TVJNcz0Wp7NOUq5B6', 'cashier', 'Female', '666554488', 'Cashier', 'BSP00003', '2025-08-14 11:21:10');
INSERT INTO `users` VALUES('6', 'Pharm', 'Tech', 'pharmtech@gmail.com', '$2y$10$IFSCsTErrdx9ECU45lb.2eScv0eTnC6ijDADoKBhhGbwT4l8dubDi', 'pharmtech', 'Female', '111336699', 'Admin', 'BSP00002', '2025-08-14 11:22:04');

