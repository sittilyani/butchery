-- Database Backup
-- Database: `pharmacy`
-- Backup Date: 2025-12-15 08:41:11



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
  `customer_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `balance_amount` decimal(10,2) DEFAULT NULL,
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` double DEFAULT NULL,
  `tendered_amount` double DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `credit_balances` VALUES('7', 'ORD202510176749', 'Lyani', '0722427721', '0.00', '2025-10-17 15:17:22', '140', '140', 'Paid', 'admin');
INSERT INTO `credit_balances` VALUES('8', 'ORD202512086033', 'Name', '0722427721', '800.00', '2025-12-10 19:14:46', '3500', '2700', 'Partially Paid', 'Admin User');
INSERT INTO `credit_balances` VALUES('9', 'ORD202512133740', 'My name', '0745587871', '160.00', '2025-12-13 14:20:37', '760', '600', 'Pending', 'Admin User');
INSERT INTO `credit_balances` VALUES('10', 'ORD202512134941', 'test', '0721212121', '300.00', '2025-12-13 14:29:13', '850', '550', 'Pending', 'Admin User');
INSERT INTO `credit_balances` VALUES('11', 'ORD202512137884', 'Martin', '0722427721', '180.00', '2025-12-13 15:14:41', '300', '120', 'Pending', 'Admin User');



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
  `receipt_id` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `balance_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `tendered_amount` decimal(10,2) DEFAULT NULL,
  `transDate` datetime DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `moved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `defaulters` VALUES('1', 'ORD202508273557', 'Muongo Kasongo', '0111666666', '0.00', '30000.00', '30000.00', '2025-08-27 12:33:37', 'Paid', 'pharmtech', '2025-08-27 12:47:07');
INSERT INTO `defaulters` VALUES('2', 'ORD202508274395', 'Bestie', '0333445566', '0.00', '360.00', '360.00', '2025-08-27 12:59:40', 'Paid', 'pharmtech', '2025-08-27 13:24:39');



CREATE TABLE IF NOT EXISTS `loans` (
  `loan_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `request_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'processed',
  `approved_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `resubmitted` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`loan_id`),
  CONSTRAINT `loans_chk_1` CHECK ((`status` in (_utf8mb4'active',_utf8mb4'processed',_utf8mb4'paid')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `productname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brandname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `packsize` int DEFAULT NULL,
  `pack_price` double DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT '0.00',
  `price` double DEFAULT '0',
  `reorder_level` int DEFAULT '0',
  `currentstatus` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brandname` (`brandname`)
) ENGINE=InnoDB AUTO_INCREMENT=350 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` VALUES('1', '1', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', '100', '125', '1.25', '3', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('2', '1', 'Paracetamol Tablet BP 500mg', 'Cetamol 500mg', '100', '60', '0.60', '2', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('3', '1', 'Piroxicam Capsules USP', 'Roxicam 20mg', '100', '65', '0.65', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('4', '1', 'Meloxicam Tablet 7.5mg BP', 'Melostar 7.5mg', '100', '161', '1.61', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('5', '1', 'Soluble Paracetamol BP 1000mg', 'PARA NOVA ', '8', '185', '23.13', '40', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('6', '1', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', '10', '99', '9.90', '20', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('7', '1', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', '10', '260', '26.00', '35', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('8', '1', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'Rilif - MR Tablets', '20', '495', '24.75', '35', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('9', '1', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 500mg', 'Zyrtal - MR Tablets', '20', '685', '34.25', '50', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('10', '1', 'Diclofenac 50mg/Paracetamol 500mg/Chlorzoxazone 250mg', 'DOLOACT - MR Tablets', '100', '1807', '18.07', '25', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('11', '1', 'Paracetamol Tablet BP 500mg/Caffeine 65mg', 'Panadol Extra Tablets (Pairs)', '50', '396', '7.92', '20', '17', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('12', '1', 'Paracetamol Tablet BP 500mg', 'Panadol Advance Tablets (Pairs)', '50', '396', '7.92', '15', '17', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('13', '1', 'Etoricoxib Tablet 60mg', 'Tory 60 Tablet', '30', '830', '27.67', '40', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('14', '1', 'Etoricoxib Tablet 90mg', 'Tory 90 Tablet', '30', '877', '29.23', '45', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('15', '1', 'Etoricoxib Tablet 120mg', 'Tory 120 Tablet', '30', '1066', '35.53', '50', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('16', '1', 'Paracetamol Tablet BP 500mg/Pseudoephedrine Hydrochloride 30mg/Chlorpheniramine Maleate 2mg', 'Panadol Cold&Flu Tablets', '24', '320', '13.33', '20', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('17', '1', 'Paracetamol Tablet BP 300mg/Pseudoephedrine Hydrochloride 30mg/Chlorpheniramine Maleate 2mg/Caffeine 30mg', 'FLU-GONE Capsules', '1', '115', '115.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('18', '1', 'Paracetamol Tablet BP 650mg/Phenylephrine Hydrochloride 5mg/Chlorpheniramine Maleate 2mg', 'CONTUS-650 Tablet', '1', '99', '99.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('19', '1', 'Cetirizine Hydrochloride Tablet 10mg', 'CACHCET Tablet', '100', '78', '0.78', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('20', '1', 'Montelukast 10mg/Levocetirizine 5mg', 'Montallerg Tablets', '20', '1000', '50.00', '70', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('21', '1', 'Montelukast 10mg', 'Montana 10mg', '14', '740', '52.86', '75', '5', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('22', '1', 'Montelukast 10mg/Levocetirizine 5mg', 'Motechest', '30', '409', '13.63', '40', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('23', '1', 'Ephedrine 12mg/Theophyline 120mg', 'F-Tab (Franol) Tablet', '100', '179', '1.79', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('24', '1', 'Salbutamol Inhaler 100mcg', 'Medisalant 100mcg', '1', '165', '165.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('25', '1', 'Betamethasone 0.25mg/Dexchlorpheniramine Maleate BP 2mg', 'Celestinac Tablet', '30', '149', '4.97', '15', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('26', '1', 'Paracetamol 500mg/Chlorzoxazone 250mg', 'Myospaz Tablet', '100', '1595', '15.95', '25', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('27', '1', 'Diclofenac 100mg', 'ZOFENAC 100', '100', '75', '0.75', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('28', '1', 'Predinsolone 5mg', 'Olsolone Tablets', '100', '68', '0.68', '3', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('29', '1', 'Paracetamol 500mg/Hyoscine Butylbromide 10mg', 'Duxscospan Plus (Buscopan Plus Generic)', '20', '338', '16.90', '25', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('30', '1', 'Doxylamine Succ. 10mg/Pyridoxine 10mg ', 'NOSIC ', '20', '385', '19.25', '25', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('31', '1', 'Metoclopramide 10mg', 'Emeton 10mg Tablet', '100', '70', '0.70', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('32', '1', 'Meloxicam EP Tablet 7.5mg', 'Melcam 7.5mg Tablet', '100', '331.5', '3.32', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('33', '1', 'Meloxicam EP Tablet 15mg', 'Melcam 15mg Tablet', '50', '306', '6.12', '10', '17', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('34', '1', 'Metoclopramide 10mg', 'Melasil - 10 Tablet', '100', '70', '0.70', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('35', '1', 'Promethazine 25mg', 'Promethazine Tablet', '100', '55', '0.55', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('36', '1', 'Paracetamol 120mg/5ml', 'Curamol Suspension 60mL', '1', '26', '26.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('37', '1', 'Paracetamol 120mg/5ml', 'Curamol Suspension 100mL', '1', '47', '47.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('38', '1', 'Paracetamol 120mg/5ml', 'Calpol Suspension 60mL', '1', '220', '220.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('39', '1', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '21', '21.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('40', '1', 'Ibuprofen 100mg/5ml', 'Triofen 100mL', '1', '30', '30.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('41', '1', 'Paracetamol 120mg/5ml', 'Panadol Baby&Infant 100mL', '1', '343', '343.00', '450', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('42', '1', 'Ibuprofen 100mg/Paracetamol 125mg BP', 'Brustan Suspension', '1', '235', '235.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('43', '1', 'Soluble Paracetamol BP 1000mg', 'Parafast ET 1000 Tablet', '8', '380', '47.50', '50', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('44', '1', 'Loratidine USP 10mg', 'Loratin Fast', '100', '850', '8.50', '15', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('45', '1', 'Hyoscine - N- Butylbromide 10mg', 'HYCIN 10 Tablets (Buscopan Generic)', '100', '240', '2.40', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('46', '1', 'Paracetamol 500mg/Hyoscine Butylbromide 10mg', 'Hismopan Plus Tablets (Buscopan Plus Generic)', '100', '600', '6.00', '15', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('47', '1', 'Metronidazole 400mg', 'Tricozole - 400mg Tablet', '100', '120', '1.20', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('48', '1', 'Tetracycline Eye Ointment USP', 'Metacycline', '1', '33', '33.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('49', '1', 'Betamethasone 0.1% w/v/Neomycin 0.5 %w/v', 'Probeta-N', '1', '105', '105.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('50', '1', 'Ciprofloxacin/Dexamethasone Eye/Ear Drops', 'Ciploglax-D Eye Drops', '1', '149', '149.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('51', '1', 'Ciprofloxacin 0.3% USP', 'Ciproken', '1', '102', '102.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('52', '1', 'Dextran 70 USP 1mg/Hypromellose USP 3mg', 'Lubtear', '1', '255', '255.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('53', '1', 'Ciprofloxacin 0.3%/Beclomethasone 0.025%/Clotrimazole 1%/Lignocaine 2% Ear Drops', 'Otobiotic', '1', '228', '228.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('54', '1', 'fluticasone propionate ', 'Flonaspray', '1', '680', '680.00', '950', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('55', '1', 'Esomeprazole', 'Protas 40 Tablets', '100', '1190', '11.90', '30', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('56', '1', 'Esomeprazole', 'Nexium 20', '14', '550', '39.29', '70', '5', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('57', '1', 'Omeprazole Satchets', 'Risek insta', '10', '331', '33.10', '50', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('58', '1', 'Omeprazole ', 'Omecos 20 Capsules', '100', '105', '1.05', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('59', '1', 'Pantoprazole Delayed Release', 'Pantakind 40', '60', '1440', '24.00', '35', '20', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('60', '1', 'Clotrimazole Vaginal Tablets 200mg', 'Canazol Vaginal Tablet, 3s', '1', '46', '46.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('61', '1', 'Ketocozole 200mg  BP', 'Hitoral 200mg Tablet', '100', '340', '3.40', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('62', '1', 'Amoxicllin 125mg/5mL', 'ELYMOX Suspension 100mL', '1', '43', '43.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('63', '1', 'Co-trimoxazole 240mg/5mL', 'BIOTRIM 100mL', '1', '45', '45.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('64', '1', 'Co-trimoxazole 240mg/5mL', 'BIOTRIM 50mL', '1', '30', '30.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('65', '1', 'Ampicillin/Cloxacillin 250mg/5mL', 'Ampiclo-Dawa Suspension', '1', '68', '68.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('66', '1', 'Ampicillin/Cloxacillin 250mg/250mg', 'Ampiclo-Dawa 500 Capsule', '100', '400', '4.00', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('67', '1', 'Flucloxacillin 125mg/5mL', 'ELYFLOX 100mL Suspension', '1', '76', '76.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('68', '1', 'Azithromycin Oral Suspension 200mg', 'IzziThree 15mg', '1', '39', '39.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('69', '1', 'Vitamin B Complex', 'Neuro-Forte', '20', '270', '13.50', '20', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('70', '1', 'Levocetirizine 5mg', 'VIVACET L 5', '10', '55', '5.50', '20', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('71', '1', 'Maternity Pads', 'Medimax', '1', '97', '97.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('72', '1', 'Maternity Pads', 'Medicott', '1', '90', '90.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('73', '1', 'Absorbent Cotton Wool 400mg', 'Velvex', '1', '250', '250.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('74', '1', 'Adult Diapers XL', 'MY A+ XLARGE', '10', '100', '10.00', '15', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('75', '1', 'Adult Diapers L', 'MY A+ LARGE', '10', '100', '10.00', '15', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('76', '1', 'Adult Pants L', 'ADFIT  PANTS LARGE', '30', '100', '3.33', '5', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('77', '1', 'Sulphadoxine 500mg/Pyrimethamine 25mg', 'FANLAR Tablets', '25', '30', '1.20', '50', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('78', '1', 'Terbinafine 250mg', 'Terbinaforce 250 Tablets', '30', '953', '31.77', '50', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('79', '1', 'Fluconazole 150mg', 'NOCANZ 150 Tablet', '1', '15', '15.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('80', '1', 'Griseofulvin 250mg', 'Grisolab-250 Tablet', '100', '520', '5.20', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('81', '1', 'Griseofulvin 125mg', 'Biofulvin 125 Tablet', '100', '390', '3.90', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('82', '1', 'Artemether 20mg/Lumefantrine 120mg', 'LONART Tablet, 24s', '1', '55', '55.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('83', '1', 'Artemether 20mg/Lumefantrine 120mg', 'PANAART 20/120, 24s', '1', '0', '0.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('84', '1', 'Povidone-Iodine USP 1%', 'Peardine Mouth Wash, 100mL', '1', '90', '90.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('85', '1', 'Povidone-Iodine USP 1%', 'Rexe-Dine Mouth Wash', '1', '135', '135.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('86', '1', 'Amoxicillin 500mg', 'AMOXIMED 500 Capsule', '100', '320', '3.20', '6.6', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('87', '1', 'Cefuroxime 500mg USP', 'Theoroxime 500 Tablets', '1', '165', '165.00', '400', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('88', '1', 'Cefixime 400mg USP', 'Theofix - 400 Tablets', '10', '170', '17.00', '350', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('89', '1', 'Azithromycin 500mg USP', 'AGYCIN-500 Tablet, 3s', '1', '55', '55.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('90', '1', 'Nitrofurantoin 100mg', 'NIFURAN', '100', '200', '2.00', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('91', '1', 'Tinidazole 500mg', 'Tinizol 500 Tablets,4s', '1', '11', '11.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('92', '1', 'Amoxicillin 250mg', 'SPASMOX 250 Capsule', '100', '195', '1.95', '3.3', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('93', '1', 'Doxycycline 100mg', 'XYCYCLINE 100 Capsule', '100', '149', '1.49', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('94', '1', 'Secnidazole 1g', 'Secnida Forte Tablets, 2s', '1', '25', '25.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('95', '1', 'Levonogestrel 0.15mg/Ethinylestradiol 0.03mg', 'Microgynon Fe Tablets', '1', '225', '225.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('96', '1', 'Sildenafil 50mg ', 'MTM-50 Tablet', '4', '36', '9.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('97', '1', 'Sildenafil 100mg ', 'MTM-100 Tablet', '4', '46', '11.50', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('98', '1', 'Sildenafil 100mg ', 'Nelgra-100 Tablet', '4', '43', '10.75', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('99', '1', 'Sildenafil 100mg ', 'VEGA-100 Tablet', '4', '41', '10.25', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('100', '1', 'Levonogestrel 0.75mg', 'Postinor-2 Tablets, 2s', '1', '143', '143.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('101', '1', 'levonogestrel 0.75mg', 'Safe-72 Tablets,2s', '1', '20', '20.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('102', '1', 'Betamethasone Sodium Phosphate 2mg/ml/Betamethasone Dipropionate 5mg/ml Suspension for Inj,2ml', 'KRIDOFOS Injection', '1', '675', '675.00', '900', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('103', '1', 'Medroxyprogesterone Injection 150mg/mL', 'Lydia Contraceptive Injection', '1', '80', '80.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('104', '1', 'Levonogestrel 0.15mg/Ethinylestradiol 0.03mg', 'Femiplan Tablets', '1', '85', '85.00', '120', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('105', '1', 'Diclofenac 75mg Injection', 'CAREFENAC Injection', '10', '45', '4.50', '20', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('106', '1', 'Carbamazepine 200mg BP', 'Carbamazepine 200mg Tablets', '100', '369', '3.69', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('107', '1', 'Amitriptyline 25mg', 'Amitiptyline Tablets, Cosmos', '100', '252', '2.52', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('108', '1', 'Phenobarbital 30mg', 'Phenobarbital Tablets, Cosmos', '100', '235', '2.35', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('109', '1', 'Diazepam 5mg', 'Cozepam Tablets', '100', '302', '3.02', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('110', '1', 'Maternity Pants', 'Dafi Maternity Pants, M-L', '6', '750', '125.00', '200', '2', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('111', '1', 'Maternity Pants', 'Dafi Maternity Pants, S-L', '6', '750', '125.00', '200', '2', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('112', '1', 'Amoxicillin/Clavulanate 457mg/5mL', 'Augmentin 457 Suspension', '1', '990', '990.00', '1200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('113', '1', 'Amoxicillin/Clavulanate 228.5mg/5mL', 'Augmentin 228 Suspension', '1', '635', '635.00', '850', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('114', '1', 'Tamsulosin 0.4mg', 'Tamsolin Capsules', '10', '770', '77.00', '100', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('115', '1', 'Amoxicillin/Clavulanate 625mg', 'Labclav 625 Tablets', '1', '115', '115.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('116', '1', 'Amoxicillin/Clavulanate 1000mg', 'Acinet 1000 Tablets', '1', '275', '275.00', '400', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('117', '1', 'Amoxicillin/Clavulanate 375mg', 'Acinet 375 Tablets', '1', '171', '171.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('118', '1', 'Ciprofloxacin 500mg USP', 'Ciproglax 500mg Tablets', '100', '240', '2.40', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('119', '1', 'Finasteride 5mg/Tamsulosin 400mcg', 'FINOSIN Tablets', '30', '1750', '58.33', '75', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('120', '1', 'Flucloxacillin Injection 500mg Vial', 'Flupene Injection', '1', '38', '38.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('121', '1', 'HydrocortisoneInjection 100mg', 'OCORTIN 100 Injection', '1', '30', '30.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('122', '1', 'Lidocaine Injection 20mg/mL, 30mL', 'LIDOCAINE 2% Injection', '1', '36', '36.00', '60', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('123', '1', 'Cefuroxime 125mg/5mL', 'Evorox, 50mL Suspension', '1', '220', '220.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('124', '1', 'Cefalexin 125mh/5mL', 'Leocef, 100mL Suspension', '1', '80', '80.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('125', '1', 'Amoxicillin/Clavulanate 228.5mg/5mL', 'Labclav 228 Suspension', '1', '102', '102.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('126', '1', 'Amoxicillin/Clavulanate 457mg/5mL', 'ACINET DRY SYRUP 457', '1', '260', '260.00', '380', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('127', '1', 'Ampicillin 60mg/cloxacillin 30mg/0.6mL', 'Neonatal Ampiclox Drops', '1', '345', '345.00', '500', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('128', '1', 'Ampicillin 60mg/cloxacillin 30mg/0.6mL', 'Ampliclo-Dawa (Neonata Amplicox Gen)', '1', '50', '50.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('129', '1', 'Amlodipine 5mg/Losartan 50mg/Hydrochlorothiazide 12.5mg', 'Amlozaar-H Tablet', '30', '1400', '46.67', '70', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('130', '1', 'Flucloxacillin 250mg/Amoxicillin 250mg', 'MoxaForte 500 Capsules, 20s', '1', '354', '354.00', '500', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('131', '1', 'Ceftriaxone 1g', '\'GALAXY\'S SEFIN Injection', '1', '32', '32.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('132', '1', 'Urine Bag', 'Urine Collection Bag, 2000mL', '1', '48', '48.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('133', '1', 'Zinc Sulfate DT 20mg', 'Junior Zinc Tablets', '100', '89', '0.89', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('134', '1', 'Normal Saline Nasal Drops', 'Nosfree Saline Drops', '1', '17', '17.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('135', '1', 'Hyoscine - N- Butylbromide 5mg/5mL', 'Hycin Syrup, 60mL', '1', '62', '62.00', '120', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('136', '1', 'Orals Rehydration Salts', 'ORASOL, ORS', '1', '8', '8.00', '20', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('137', '1', 'Ondasetron 2mg/5mL', 'EMITOSS Oral Solution, 30mL', '1', '270', '270.00', '450', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('138', '1', 'Zinc Sulfate 20mg Syrup', 'TOTO-ZincOD Syrup', '1', '80', '80.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('139', '1', 'Nystatin 100,000 units', 'NYSTAL Suspension', '1', '62', '62.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('140', '1', 'Glucosamine/Chondroitin/Cod Liver/Omega 3', 'Cartil Omega Softgel Capsules', '30', '1758', '58.60', '75', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('141', '1', 'Calcium/Magnesium/Vitamin D', 'Osteocare Tablets', '30', '620', '20.67', '30', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('142', '1', 'Calcium 320mg/Phosphorus 137.5mg', 'Purecal Chewable Tablets', '30', '750', '25.00', '35', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('143', '1', 'Calcium/Magnesium/Vitamin D', 'Zedcal Oral Suspension, 200mL', '1', '549', '549.00', '750', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('144', '1', 'Lansoprazole/Tinidazole/Clarithromycin', 'Sure Kit, H. Pylori Kit', '1', '698', '698.00', '1000', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('145', '1', 'Amoxicillin,clarithromycin,Esomeprazole', 'Esofag kit,H.Pylori kit', '1', '950', '950.00', '1300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('146', '1', 'lansoprazole/Amoxicilin/Clarithromycin', 'Pylotrip,H.Pylori Kit', '1', '890', '890.00', '1300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('147', '1', 'syringe 10cc', 'Syringe 10cc', '1', '450', '450.00', '10', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('148', '1', 'Syringe 5cc', 'Syringe 5cc', '1', '270', '270.00', '10', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('149', '1', 'Lactulose solution', 'Osmolax Suspension', '1', '340', '340.00', '450', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('150', '1', 'magaldrate and simethicone 200ml', 'Maganta Suspension', '1', '361.25', '361.25', '500', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('151', '1', 'Sodium alginate 500mg/sodium bicarbonate/calcium carbonate', ' Asynta Max 200mL', '1', '467.5', '467.50', '700', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('152', '1', 'Aluminium Hydroxide 365mg/magnesium hydroxyde/simethicone', 'Relcer Gel 180mL', '1', '325', '325.00', '450', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('153', '1', 'Aluminium Hydroxide 365mg/magnesium hydroxyde/simethicone', 'Relcer Gel 100mL', '1', '247', '247.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('154', '1', 'Aluminium Hydroxide 120mg/Magnesium Trisilicate 250mg', 'Alugel Suspension, 100mL', '1', '81', '81.00', '120', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('155', '1', 'Aluminium oxide 200mg/Magnesium hydroxyde 400mg/simethicone 30mg', 'Nilacid 200mL Suspension', '1', '255', '255.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('156', '1', 'Aluminium oxide 200mg/Magnesium hydroxyde 400mg/simethicone 30mg', 'Nilacid 100mL Suspension', '1', '135', '135.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('157', '1', 'Aluminium Hydroxide 120mg/Magnesium Trisilicate 250mg', 'Gocid 100mL Suspension', '1', '35', '35.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('158', '1', 'Alginic Acid Aluminium Hydroxide 365mg/magnesium hydroxyde/simethicone ', 'ULGICID Suspension 200mL', '1', '285', '285.00', '400', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('159', '1', 'Sucralfate/Oxetacaine', 'Sucrafil O Gel Suspension, 100mL', '1', '280', '280.00', '400', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('160', '1', 'Aluminium Hydroxide 300mg/magnesium hydroxyde 150mg/simethicone 100mg', 'Benagas Gel, 100mL', '1', '40', '40.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('161', '1', 'Aluminium Hydroxide 150mg/Magnesium Trisilicate 250mg', 'Tryactin Suspension, 100mL', '1', '40', '40.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('162', '1', 'Magaldrate 480mg and simethicone 20mg', 'Magnacid Gel 100mL', '1', '55', '55.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('163', '1', 'Ferric Ammonium Citrate 200mg/Folic 1.5mg/Cyanocobalamin 50mcg/Ethanol', 'Ranferon 12 Suspension, 200mL', '1', '325', '325.00', '450', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('164', '1', 'Dried Ferrous Sulphate 200mg/Folic Acid 0.4mg', 'Ferrolic-LF Tablets, IFAS', '100', '133', '1.33', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('165', '1', 'Ferrous Fumarate 305mg/Folic Acid 0.75mg/Cyanocobalamin 5mcg/Ascorbic Acid 75mg/Zinc Sulphate 5mg', 'Ranferon Capsules', '30', '286', '9.53', '20', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('166', '1', 'Iron 50mg/Manganese 1.33mg/Copper 0.70mg', '\'Tot\'Hema Ampoules\'', '20', '860', '43.00', '80', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('167', '1', 'Diloxanide Furoate 250mg/Metronidazole 200mg/Dicyclomine 10mg/5mL', 'Entamaxin Oral Suspension, 100mL', '1', '90', '90.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('168', '1', 'Metronidazole 200mg/5mL', 'Amizole Oral Suspension', '1', '45', '45.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('169', '1', 'Metronidazole Benzoate 200mg/5mL', 'Tricozole - 200mg Suspension', '1', '45', '45.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('170', '1', 'Multivitamin Syrup', 'Filwel Kids, 100mL', '1', '204', '204.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('171', '1', 'Secnidazole 750mg/15mL', 'Secnida for Children, 15mL', '1', '98', '98.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('172', '1', 'Cyproheptadine 4mg/ B Vitamins/Minerals', 'Becoactin Tablets', '30', '360', '12.00', '15', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('173', '1', 'Cyproheptadine 4mg/ B Vitamins/Minerals', 'Becoactin Syrup, 200mL', '30', '320', '10.67', '450', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('174', '1', 'Cyproheptadine 2mg/ B Vitamins/Minerals', 'Cypro B Plus Syrup, 200mL', '1', '235', '235.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('175', '1', 'Cyproheptadine 2mg/ Tricholine Citrate 275mg', 'Cypon Syrup,100mL', '1', '210', '210.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('176', '1', 'Ambroxol Hydrochloride 15mg/5mL', 'Ambroxol Expectorant Syrup, 100mL', '1', '178.4', '178.40', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('177', '1', 'Adovas Syrup', 'Adovas Syrup, 100mL', '1', '204', '204.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('178', '1', 'Sulbutamol 1mg/Bromhexine 2mg/Guaifenesin 50mg', 'Cadistin Plus, 100mL', '1', '175', '175.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('179', '1', 'Chlorpheniramine 2mg/Sodium Citrate 44mg/Guaifenesin 80mg/Ammonium Chloride 100mg/Levomenthol 0.8mg', 'Cadiphen Syrup, 100mL', '1', '140', '140.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('180', '1', 'Dextromethorphan 10mg/Chlorpheniramine 2mg/Pseudoephedrine 30mg', 'Flu-Gone DM, 60mL', '1', '165', '165.00', '230', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('181', '1', 'Dextromethorphan 10mg/Chlorpheniramine 2mg/Pseudoephedrine 30mg/Paracetamol', 'Flu-Gone P+, 60mL', '1', '165', '165.00', '230', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('182', '1', 'Promethazine 2.5mg/Diphenhydramine 5mg/Ammonium Cl 90mg/Sodium Citrate 45mg/Ephedrine Hcl 7.5mg', 'Tridex Cough Mixture,100mL', '1', '58', '58.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('183', '1', 'Dextromethorphan 10mg/Cetirizine 5mg/Phenylephrine Hcl 5mg', 'Zefcolin Dry Cough Formula Syrup, 100mL', '1', '229', '229.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('184', '1', 'SalbutamoL Sulfate 2mg/Bromhexine Hcl 4mg/Guaifenesin 100mg/ Menthol 1mg', 'Ascoril Expectorant 100mL', '1', '214.4', '214.40', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('185', '1', 'SalbutamoL Sulfate 2mg/Bromhexine Hcl 4mg/Guaifenesin 100mg/ Menthol 1mg', 'Ascoril Expectorant 200mL', '1', '375.4', '375.40', '500', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('186', '1', 'Chlorpheniramine 2mg/Sodium Citrate 44mg/Guaifenesin 80mg/Ammonium Chloride 100mg/Levomenthol 0.8mg', 'Cadistin Expectorant, 100mL', '1', '98', '98.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('187', '1', 'Chlorpheniramine 2mg/Pseudoephedrine 10mg/Paracetamol 120mg', 'Coldcap Syrup, 100mL', '1', '90', '90.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('188', '1', 'Diphenhydramine 10mg/Promethazine 5mg/Ammonium Chloride 180mg/Sodium Citrate 90mg', 'Benahist Syrup, 60mL', '1', '36', '36.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('189', '1', 'Diphenhydramine 10mg/Promethazine 5mg/Ammonium Chloride 180mg/Sodium Citrate 90mg', 'Benahist Syrup, 100mL', '1', '53', '53.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('190', '1', 'Chlorpheniramine 2mg/Pseudoephedrine 30mg/Guaifenesin 100mg', 'Trimex Diabetic', '1', '215', '215.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('191', '1', 'Ambroxol Hydrochloride 15mg/5mL', 'Mucosolvan Syrup,100mL', '1', '590', '590.00', '850', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('192', '1', 'Chlorpheniramine 2mg/Pseudoephedrine 30mg/Sodium Citrate 44mg/Dextromethorphan 10mg/Menthol 1mg', 'Coscof-DM Linctus,100mL', '1', '155', '155.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('193', '1', 'Cod Liver Oil, Vitamin A&D, Calcium', 'Scott\'s Emulsion, Original, 100mL, Syrup', '1', '410', '410.00', '600', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('194', '1', 'Cod Liver Oil, Vitamin A&D, Calcium', 'Scott\'s Emulsion, Orange Flavour, 100mL, Syrup', '1', '410', '410.00', '600', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('195', '1', 'Bonnisan', 'Bonnisan, 120mL, Syrup', '1', '385', '385.00', '450', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('196', '1', 'Sodium Bicarbonate 50mg/Terpeneless Dillseed Oil 2.15mg', 'Gripe Water', '1', '92', '92.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('197', '1', 'Levosalbutamol 1mg/5mg', 'Levostar, 100mL Syrup', '1', '216', '216.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('198', '1', 'Levocetirizine 2.5mg', 'ALERFREE Syrup,60mL', '1', '123', '123.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('199', '1', 'Desloratidine 2.5mg/5mL', 'DESOSTAR Syrup, 60mL', '1', '266', '266.00', '380', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('200', '1', 'Loratidine USP 5mg/5mL', 'Lorhistina Syrup,  60mL', '1', '179', '179.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('201', '1', 'Predinsolone 5mg/5mL', 'Olsolone Syrup, 50mL', '1', '100', '100.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('202', '1', 'Promethazine Hcl, 5mg/5mL', 'Largan, Syrup', '1', '19', '19.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('203', '1', 'Chlorpheniramine Maleate 4mg', 'Dawa-CPM Tablets, 4mg', '100', '31', '0.31', '2', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('204', '1', 'Chlorpheniramine Maleate 2mg/5mL', 'Dawa-CPM Syrup, 60mL', '1', '22', '22.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('205', '1', 'Cetirizine HCL 5mg/5mL', 'CetriPlain, 60mL Syrup', '1', '20', '20.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('206', '1', 'Sodium Lactate IV Infusion', 'Ringer - Lactate, 500mL,IV Infusion', '1', '80', '80.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('207', '1', 'Sodium Chloride, 0.9%', 'VIDASAL, 500mL, IV Infusion (Normal Saline)', '1', '80', '80.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('208', '1', 'Erythromycin 125mg/5mL', 'Erythrox 100mL', '1', '78', '78.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('209', '1', 'Clarithromycin 500mg', 'Aziclar-500 Tablets', '1', '229', '229.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('210', '1', 'Diloxanide Furoate 500mg/Metronidazole 400mg', 'Diracip-MDS, Tablets,15s', '1', '225', '225.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('211', '1', 'Azithromycin 1g/Fluconazole 150mg/Secnidazole 1g', 'AZFLOSEC KIT', '1', '325', '325.00', '500', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('212', '1', 'Ciprofloxacin 500mg/Tinidazole 600mg', 'CIPRO-T, Tablets', '1', '150', '150.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('213', '1', 'Cefalexin 500mg', 'Felaxin 500 Capsules', '1', '6.8', '6.80', '15', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('214', '1', 'Tramadol 50mg', 'Metadol Capsules', '100', '350', '3.50', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('215', '1', 'Cefadroxil 500mg', 'DROX 500', '1', '183.6', '183.60', '400', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('216', '1', 'Pregnancy Test Strip', 'Pregnancy Test Strip', '1', '11', '11.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('217', '1', 'Metformin HCl 500mg', 'Glucophage 500 Tablets', '90', '395', '4.39', '8', '30', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('218', '1', 'Metformin HCl 850mg', 'Glucophage 850 Tablets', '60', '564', '9.40', '15', '20', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('219', '1', 'Norethisterone 5mg', 'Primolut N Tablets', '30', '940', '31.33', '45', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('220', '1', 'Clomifene 50mg', 'Clophene 50mg, Tablets', '30', '379', '12.63', '50', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('221', '1', 'Fluconazole 150mg/Azithromycin 1g/Secnidazole 1g', 'VDM KIT, Tablets', '1', '645', '645.00', '850', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('222', '1', 'Glibenclamide 5mg', 'Nogluc 5mg, 28s Tablets', '28', '145', '5.18', '7', '9', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('223', '1', 'Metformin 500mg', 'Sukarmin 500mg Tablets', '100', '85', '0.85', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('224', '1', 'Amlodipine 5mg', 'Varinil 5 Tablets', '28', '155', '5.54', '10', '9', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('225', '1', 'Hydrochlorothiazide 50mg', 'HYMET Tablets', '100', '75', '0.75', '4', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('226', '1', 'Acetylsalicylic Acid 75mg', 'Ascard 75 Tablets', '30', '105', '3.50', '150', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('227', '1', 'Metformin HCl 850mg', 'Glucomet 850 Tablets', '56', '420', '7.50', '15', '19', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('228', '1', 'Glibenclamide 5mg', 'Nogluc 5mg, 112s Tablets', '112', '495', '4.42', '7', '37', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('229', '1', 'Anusol', 'Anusol Suppositories', '12', '700', '58.33', '80', '4', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('230', '1', 'Atenolol 50mg', 'Cardinol 50 Tablets, 28s', '28', '125', '4.46', '8', '9', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('231', '1', 'Losartan 50mg', 'Amlozaar 50 Tablets', '30', '950', '31.67', '45', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('232', '1', 'Carvedilol 12.5mg', 'Vidol 12.5 Tablets', '28', '410', '14.64', '20', '9', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('233', '1', 'Carvedilol 6.25mg', 'Vidol 6.25 Tablets', '28', '285', '10.18', '15', '9', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('234', '1', 'Nifedipine 20mg', 'Nicardin-SR Tablets', '100', '80', '0.80', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('235', '1', 'Losartan 50mg', 'Angilock 50 Tablets', '30', '131.75', '4.39', '8', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('236', '1', 'Losartan 50mg/Hydrochlorothiazide 12.5mg', 'Angilock-Plus 50/12.5 Tablets', '30', '170', '5.67', '10', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('237', '1', 'Atenolol 50mg', 'Lonet 50 Tablets', '100', '337', '3.37', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('238', '1', 'Atorvastatin 20mg ', 'Avastatin 20 Tablets', '28', '304', '10.86', '20', '9', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('239', '1', 'Atorvastatin 20mg ', 'Atsta*20 Tablets', '30', '170', '5.67', '15', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('240', '1', 'Enalapril 10mg', 'Dawapril 10 Tablet', '100', '113', '1.13', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('241', '1', 'Enalapril 5mg', 'Dawapril 5 Tablet', '100', '123', '1.23', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('242', '1', 'Co-trimoxazole 480mg Tablets', 'CO-TRI 480 Tablets', '100', '149', '1.49', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('243', '1', 'Co-trimoxazole 960mg Tablets', 'Co-trimoxazole 960 Tablets', '100', '485', '4.85', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('244', '1', 'Blood Lancets', 'Blood Lancets Pieces', '100', '174', '1.74', '300', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('245', '1', 'Benzyl Benzoate Application 25%', 'Scabees Application, 100mL', '1', '210', '210.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('246', '1', 'Toothpaste', 'ELEDENT TOOTHPASTE, 75MG', '1', '120', '120.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('247', '1', 'Toothpaste', 'ELEDENT TOOTHPASTE, 150MG', '1', '220', '220.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('248', '1', 'Calcium Antiacids', 'ENO Tablets (Pairs)', '100', '595', '5.95', '20', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('249', '1', 'Touch ang Go', 'Touch and Go', '1', '220', '220.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('250', '1', 'Hydrocortisone Ointment 1% w/w', 'ELYCORT 15G OINTMENT', '1', '62', '62.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('251', '1', 'Hydrocortisone Cream 1% w/w', 'ELYCORT 15G CREAM', '1', '66', '66.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('252', '1', 'Neomycin 5mg/Bacitracin 2.5mg/Gramicidin 0.5mg Powder', 'GRABACIN POWDER 10GM', '1', '125', '125.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('253', '1', 'Neomycin 5mg/Bacitracin 250 IU', 'NEBANOL POWDER 5MG', '1', '59', '59.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('254', '1', 'Neomycin 3.5mg/Bacitracin 5000 units/Bacitracin 500 units', 'GRABACIN 3 Ointment', '1', '255', '255.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('255', '1', 'Hydrocortisone Ointment 1% w/w', 'HYDROTOPIC Ointment 15g Tube', '1', '30', '30.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('256', '1', 'Condoms', 'Kiss Classic Condoms, 3s', '24', '627', '26.13', '50', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('257', '1', 'Condoms', 'Kiss Strawberry Condoms, 3s', '24', '826', '34.42', '100', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('258', '1', 'Condoms', 'Kiss Studded Condoms, 3s', '24', '892', '37.17', '100', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('259', '1', 'Condoms', 'Kiss Chocolate Condoms, 3s', '24', '826', '34.42', '100', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('260', '1', 'Condoms', 'Durex Fetherlite Ultra, 3s', '1', '337', '337.00', '550', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('261', '1', 'Condoms', 'Durex Extra Safe, 3s', '1', '302', '302.00', '400', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('262', '1', 'Condoms', 'TRUST RIBBED, 3s', '24', '920', '38.33', '100', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('263', '1', 'Condoms', 'TRUST CLASSIC, 3s', '24', '600', '25.00', '50', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('264', '1', 'Condoms', 'TRUST STUDDED, 3s', '24', '900', '37.50', '100', '8', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('265', '1', 'Bandages', 'Crepe Bandage Spandex 5cm', '1', '18', '18.00', '30', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('266', '1', 'Bandages', 'Crepe Bandage Spandex 7.5cm', '1', '29', '29.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('267', '1', 'Bandages', 'Crepe Bandage Spandex 15cm', '1', '49', '49.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('268', '1', 'Bandages', 'Crepe Bandage Spandex 10cm', '1', '46', '46.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('269', '1', 'Diclofenac Gel 1%', 'DICLOGEN 1% GEL', '1', '19', '19.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('270', '1', 'Clotrimazole 1% w/w/Beclomethasone Dipropionate 0.025% w/w', 'Bulkot-B Cream', '1', '51', '51.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('271', '1', 'Betamethasone Dipropionate, Gentamicin & Clotrimazole  Cream', 'Xtraderm Cream', '1', '126', '126.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('272', '1', 'Clotrimazole 10.0% w/w/Betamethasone 0.5mg', 'Clozole-B Cream', '1', '72', '72.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('273', '1', 'Clotrimazole 1.0% w/w', 'Clozole Cream', '1', '28', '28.00', '70', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('274', '1', 'Hydrocortisone Cream 1% w/w', 'OLCORT 15G Cream', '1', '28', '28.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('275', '1', 'Beclomethasone 0.025%, Miconazole 2%, Neomycin Sulphate 0.5% Chlorocresol 0.25%', 'Beclomin Ointment 15g', '1', '150', '150.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('276', '1', 'Betamethasone Valerate 0.1% w/w', 'MEDIVEN Ointment 15g', '1', '76', '76.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('277', '1', 'Betamethasone Valerate 0.1% w/w', 'MEDIVEN Cream 15g', '1', '45', '45.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('278', '1', 'Betamethasone Valerate 0.1% w/w/Salicylic acid 3% w/w', 'MEDIVEN-S Ointment 15g', '1', '180', '180.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('279', '1', 'Silver Sulfadiazine 1% w/w', 'Dermazine Cream 15g', '1', '40', '40.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('280', '1', 'Calamine Lotion', 'COVIGEN Calamine Lotion, 100mL', '1', '29', '29.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('281', '1', 'Surgical Spirit 70% v/v', 'COVIGEN Surgical Spirit, 50mL', '1', '26', '26.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('282', '1', 'Povidone-Iodine USP 10% w/v', 'FAHOLO POVIDONE IODINE, 50mL', '1', '50', '50.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('283', '1', 'Silver Sulfadiazine 1% w/w', 'Dermazine Cream 1% w/w, Dawa, 100g', '1', '169', '169.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('284', '1', 'Silver Sulfadiazine 1% w/w', 'Dermazine Cream 1% w/w, Dawa, 250g', '1', '308', '308.00', '500', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('285', '1', 'Chlorine 3.5% w/v', 'FAHOLO Sodium Hypochlorite 3.5% w/v', '1', '300', '300.00', '450', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('286', '1', 'Sterile Paraffin Dressing ', 'Sterifin Dressing Gauze, 10x10', '10', '287', '28.70', '50', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('287', '1', 'Sodium Bicarbonate 300mg ', 'SodaMint Tablets', '100', '87', '0.87', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('288', '1', 'Indomethacin 25mg', 'Caredomet 25mg Capsules, Indocid', '100', '80', '0.80', '3', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('289', '1', 'Esomeprazole 20mg', 'ESOMAC 20', '28', '789', '28.18', '35', '9', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('290', '1', 'Esomeprazole 20mg', 'ESOMAC 40', '14', '745', '53.21', '70', '5', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('291', '1', 'Albendazole 400mg', 'Zentel 400 Tablet', '1', '190', '190.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('292', '1', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '1', '316', '316.00', '500', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('293', '1', 'Ivermectin BP 6mg', 'Iverkot-6', '10', '2100', '210.00', '250', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('294', '1', 'Lidocaine HCl/Cetylpyridinium', 'Dentinox 10g', '2', '646', '323.00', '900', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('295', '1', 'Trimetabol', 'Trimetabol Solution', '1', '750', '750.00', '1100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('296', '1', 'Diclofenac/Linseed/Methyl Salicylate/Racementhol/Benzyl Alcohol', 'VOLINI GEL, 100MG', '1', '218', '218.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('297', '1', 'Good Morning Lung Tonic', 'Good Morning, 60mL', '1', '76', '76.00', '130', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('298', '1', 'Kofgon Red', 'KOFGON Syr, 60mL', '1', '25', '25.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('299', '1', 'Tricohist ', 'Tricohist, 100mL', '1', '108', '108.00', '180', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('300', '1', 'Tricohist ', 'Tricohist, 60mL', '1', '95', '95.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('301', '1', 'Diphenhydramine 10mg/Promethazine 5mg/Ammonium Chloride 180mg', 'Tripozed, Expectorant, 100mL', '1', '41', '41.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('302', '1', 'Diclofenac Sodium Slow Release 100mg', 'Diclomol SR 100mg, Tablets', '100', '435', '4.35', '10', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('303', '1', 'Tretinoine 0.05% w/w', 'Acnesol Cream, 25g', '1', '153', '153.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('304', '1', 'Telmisartan 80mg/Amlodipine 5mg', 'AMTEL 80/5 Tabs', '30', '2000', '66.67', '80', '10', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('305', '1', 'Sulfadoxine/Pyrimethamine', 'Malodar Tbalets, 3s', '1', '40', '40.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('306', '1', 'Flucloxacillin/Amoxicillin 250mg/5mL', 'MoxaForte Suspension, 100mL', '1', '265', '265.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('307', '1', 'Sensodyne Toothpaste', 'Sensodyne Multicare, 40mL', '1', '241', '241.00', '350', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('308', '1', 'Sildenafil 100mg/5g', 'KAMAGRA ORAL JELLY 100MG', '1', '660', '660.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('309', '1', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', '10', '519', '51.90', '15', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('310', '1', 'Canullas G22 (Blue)', 'Canullas G22 (Blue)', '1', '13', '13.00', '30', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('311', '1', 'Canullas G24(Yellow)', 'Canullas G24(Yellow)', '1', '14', '14.00', '30', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('312', '1', 'FloraNorm Satchets', 'FloraNorm Satchets', '10', '870', '87.00', '130', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('313', '1', 'Amoxicillin 1g/Clavulanate 200mg', 'GAMOK Injection, Vial', '1', '125', '125.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('314', '1', 'Insulin Syringes, 0.5mL', 'Insulin Syringes, 0.5mL', '10', '205', '20.50', '30', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('315', '1', 'Insulin Syringes, 1mL', 'Insulin Syringes, 1mL', '10', '200', '20.00', '30', '3', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('316', '1', 'Diclofenac/Paracetamol/Chlorzoxazone', 'LOBAK Tablets, 100s', '100', '969', '9.69', '15', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('317', '1', 'Tranexamic Acid 500mg/5mL', 'MENOZIP INJ, 500mg/5mL', '5', '461', '92.20', '150', '2', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('318', '1', 'Carbocisteine 100mg', 'NASITHIOL INFANT, 60mL', '1', '56', '56.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('319', '1', 'Carbocisteine 100mg/Promethazine 2.5mg', 'NASITHIOL PROM, 100mL', '1', '67', '67.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('320', '1', 'Carbocisteine 100mg/Promethazine 2.5mg', 'NASITHIOL PROM, 60mL', '1', '56', '56.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('321', '1', 'Needle G21', 'Needle G21', '100', '128', '1.28', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('322', '1', 'Needle G23', 'Needle G23', '100', '128', '1.28', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('323', '1', 'Omeprazole Inj 40mg', 'Ompac 40mg', '1', '60', '60.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('324', '1', 'Ondasetron 4mg/2mL', 'ONDEX 4MG', '5', '431', '86.20', '150', '2', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('325', '1', 'Dihydroartemisinin 40mg/Piperaquine 320mg', 'P-ALAXIN 9S', '1', '175', '175.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('326', '1', 'Paracetamol/Codeine Phosphate/Doxylamine, Caffeine', 'TAMEPYN, 20S', '20', '215', '10.75', '15', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('327', '1', 'Lubricating Jelly', 'Veri-Lube, 42g', '1', '117', '117.00', '280', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('328', '1', 'Carbocisteine 100mg/Promethazine 2.5mg', 'Vithiol Syrup, 125mg', '1', '130', '130.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('329', '1', 'Timolol Eye Drops 0.5%', 'TIMOGLAX EYE DROPS, 5mL', '1', '67', '67.00', '150', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('330', '1', 'Herbal Cough Lozenges', 'ZECUF LOZENGES, LEMON', '20', '185', '9.25', '30', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('331', '1', 'Herbal Cough Lozenges', 'ZECUF LOZENGES, ORANGE', '20', '185', '9.25', '30', '7', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('332', '1', 'Miconazole Nitrate 2%w/w', 'MUCOBEN CREAM', '1', '30', '30.00', '100', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('333', '1', 'Medicated Soap', 'TETMOSOL Medicated Soap', '1', '95', '95.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('334', '1', 'Strepsils', 'Strepsils Soothing, Honey & Lemon', '100', '1300', '13.00', '50', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('335', '1', 'Strepsils', 'Strepsils Regular', '100', '1300', '13.00', '50', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('336', '1', 'Mupirocin Ointment 2%', 'Zupricin Ointment, 15g', '1', '505.5', '505.50', '750', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('337', '1', 'Mupirocin 2%/Betamethasone 0.5% Ointment', 'Zupricin B Ointment, 15g', '1', '625.5', '625.50', '850', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('338', '1', 'Anti-Rabies Vaccine', 'Anti-Rabies Vaccine, Vial', '3', '800', '266.67', '1200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('339', '1', 'Vitamin B Complex', 'Neurobion Forte Tablets', '1', '3', '3.00', '10', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('340', '1', 'Insulin 70/30', 'Mixtard 30, Vial', '1', '500', '500.00', '700', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('341', '1', 'Bisacodyl 5mg', 'Bicolex 5, Tablets', '100', '187', '1.87', '5', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('342', '1', 'Clindamycin 300mg', 'Clindacin-300 Capsules', '1', '135', '135.00', '250', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('343', '1', 'Diloxanide 250mg/Metronidazole 200mg/Dicyclomine 10mg', 'Entamaxin Capsules,', '1', '190', '190.00', '300', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('344', '1', 'Fluconazole 200mg ', 'Diconazol 200mg Tablets', '100', '1086', '10.86', '15', '33', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('345', '1', 'Crotamiton+Sulphur', 'Scabion Cream, 20g', '1', '135', '135.00', '200', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('346', '1', 'Surgical Masks', 'Surgical Mask', '50', '150', '3.00', '10', '17', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('347', '1', 'MENTHO PLUS BALM', 'EMAMI MENTHO PLUS', '1', '41', '41.00', '80', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('348', '1', 'Albendazole 400mg', 'NILWORM 400MG, TABLET', '1', '9', '9.00', '50', '1', 'active', '2025-09-04 14:52:46');
INSERT INTO `products` VALUES('349', '1', 'Albendazole 400mg/10mL', 'TANZOL SUSPENSION', '1', '18', '18.00', '50', '1', 'active', '2025-09-04 14:52:46');



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
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Paid',
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sales_id` int DEFAULT NULL,
  `brandname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `unit_price` double DEFAULT NULL,
  `buying_price_total` double DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `discount` double DEFAULT '0',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `grand_total` double DEFAULT NULL,
  `profit` double DEFAULT NULL,
  `sales_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `transBy` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sale_items` VALUES('100', '183', 'Brustan Tablets', '20', '9.9', '198', '20.00', '0', '400.00', '6.00', '400', '202', '2025-09-14 15:53:23', 'admin');
INSERT INTO `sale_items` VALUES('101', '184', 'Brustan Tablets', '11', '9.9', '108.9', '20.00', '0', '220.00', '3.30', '220', '111.1', '2025-09-14 15:53:23', 'admin');
INSERT INTO `sale_items` VALUES('102', '185', 'CONTUS-650 Tablet', '2', '99', '198', '150.00', '0', '300.00', '4.50', '300', '102', '2025-09-14 15:53:23', 'admin');
INSERT INTO `sale_items` VALUES('103', '186', 'Gesic 400 ADL Brufen', '111', '1.25', '138.75', '3.00', '0', '333.00', '5.00', '333', '194.25', '2025-09-14 15:53:23', 'admin');
INSERT INTO `sale_items` VALUES('104', '187', 'Hismopan Plus Tablets (Buscopan Plus Generic)', '15', '6', '90', '15.00', '0', '225.00', '3.38', '225', '135', '2025-09-14 15:57:38', 'admin');
INSERT INTO `sale_items` VALUES('105', '188', 'Ompac 40mg', '3', '60', '180', '150.00', '0', '450.00', '6.75', '450', '270', '2025-09-14 16:13:24', 'admin');
INSERT INTO `sale_items` VALUES('106', '189', 'GAMOK Injection, Vial', '4', '125', '500', '200.00', '0', '800.00', '12.00', '800', '300', '2025-09-14 16:23:17', 'admin');
INSERT INTO `sale_items` VALUES('107', '190', 'Acinet 1000 Tablets', '3', '275', '825', '400.00', '0', '1260.00', '18.90', '1260', '435', '2025-09-14 16:43:07', 'admin');
INSERT INTO `sale_items` VALUES('108', '191', 'Gesic 400 ADL Brufen', '451', '1.25', '563.75', '3.00', '0', '1353.00', '20.29', '1353', '789.25', '2025-09-17 09:53:26', 'admin');
INSERT INTO `sale_items` VALUES('109', '192', 'ACEPAR-MR Caplets', '4', '26', '104', '35.00', '0', '140.00', '2.10', '140', '36', '2025-10-17 15:17:22', 'admin');
INSERT INTO `sale_items` VALUES('110', '196', 'ACETAL MR Tablets', '12', '51.9', '622.8', '15.00', '0', '180.00', '2.70', '180', '-442.8', '2025-10-17 20:20:00', ' ');
INSERT INTO `sale_items` VALUES('111', '197', 'Hitoral  Shampoo, 100mL', '1', '316', '316', '500.00', '0', '500.00', '7.50', '500', '184', '2025-10-17 20:23:07', ' ');
INSERT INTO `sale_items` VALUES('112', '198', 'Hitoral  Shampoo, 100mL', '2', '316', '632', '500.00', '0', '1000.00', '15.00', '1000', '368', '2025-10-17 20:25:15', ' ');
INSERT INTO `sale_items` VALUES('113', '199', 'Hitoral  Shampoo, 100mL', '4', '316', '1264', '500.00', '0', '2000.00', '30.00', '2000', '736', '2025-10-17 20:30:57', ' ');
INSERT INTO `sale_items` VALUES('122', '208', 'Calpol Suspension 60mL', '5', '220', '1100', '300.00', '0', '1500.00', '22.50', '1500', '400', '2025-10-17 21:27:32', ' ');
INSERT INTO `sale_items` VALUES('123', '208', 'Cetamol 500mg', '1', '0.6', '0.6', '2.00', '0', '2.00', '0.03', '2', '1.4', '2025-10-17 21:27:32', ' ');
INSERT INTO `sale_items` VALUES('124', '208', 'Curamol Suspension 100mL', '4', '47', '188', '100.00', '0', '400.00', '6.00', '400', '212', '2025-10-17 21:27:32', ' ');
INSERT INTO `sale_items` VALUES('125', '209', 'Hitoral  Shampoo, 100mL', '4', '316', '1264', '500.00', '0', '2000.00', '30.00', '2000', '736', '2025-10-17 21:29:30', ' ');
INSERT INTO `sale_items` VALUES('126', '209', 'Ampliclo-Dawa (Neonata Amplicox Gen)', '1', '50', '50', '100.00', '0', '100.00', '1.50', '100', '50', '2025-10-17 21:29:30', ' ');
INSERT INTO `sale_items` VALUES('127', '210', 'Hitoral  Shampoo, 100mL', '1', '316', '316', '500.00', '0', '500.00', '7.50', '500', '184', '2025-10-17 21:34:43', ' ');
INSERT INTO `sale_items` VALUES('128', '211', 'Hitoral  Shampoo, 100mL', '2', '316', '632', '500.00', '0', '1000.00', '15.00', '1000', '368', '2025-10-17 21:50:30', ' ');
INSERT INTO `sale_items` VALUES('129', '211', 'Coldcap Syrup, 100mL', '2', '90', '180', '150.00', '0', '300.00', '4.50', '300', '120', '2025-10-17 21:50:30', ' ');
INSERT INTO `sale_items` VALUES('130', '212', 'Brustan Tablets', '1', '9.9', '9.9', '20.00', '0', '20.00', '0.30', '20', '10.1', '2025-10-17 21:56:03', ' ');
INSERT INTO `sale_items` VALUES('131', '212', 'Triofen 60mL', '5', '21', '105', '50.00', '0', '250.00', '3.75', '250', '145', '2025-10-17 21:56:03', ' ');
INSERT INTO `sale_items` VALUES('132', '213', 'ACEPAR-MR Caplets', '1', '26', '26', '35.00', '0', '35.00', '0.53', '35', '9', '2025-10-17 21:57:58', ' ');
INSERT INTO `sale_items` VALUES('133', '214', 'ACEPAR-MR Caplets', '1', '26', '26', '35.00', '0', '35.00', '0.53', '35', '9', '2025-10-17 21:58:06', ' ');
INSERT INTO `sale_items` VALUES('136', '217', 'Curamol Suspension 100mL', '10', '47', '470', '100.00', '0', '1000.00', '15.00', '1000', '530', '2025-10-17 22:03:21', ' ');
INSERT INTO `sale_items` VALUES('137', '218', 'Triofen 60mL', '5', '21', '105', '50.00', '0', '250.00', '3.75', '250', '145', '2025-10-17 22:05:20', ' ');
INSERT INTO `sale_items` VALUES('138', '218', 'Triofen 100mL', '4', '30', '120', '100.00', '0', '400.00', '6.00', '400', '280', '2025-10-17 22:05:20', ' ');
INSERT INTO `sale_items` VALUES('139', '219', 'Triofen 60mL', '11', '21', '231', '50.00', '0', '550.00', '8.25', '550', '319', '2025-10-17 22:13:32', ' ');
INSERT INTO `sale_items` VALUES('140', '220', 'Calpol Suspension 60mL', '1', '220', '220', '300.00', '0', '300.00', '4.50', '300', '80', '2025-10-17 22:15:15', ' ');
INSERT INTO `sale_items` VALUES('141', '220', 'ACETAL MR Tablets', '10', '51.9', '519', '15.00', '0', '150.00', '2.25', '150', '-369', '2025-10-17 22:15:16', ' ');
INSERT INTO `sale_items` VALUES('142', '220', 'Brustan Tablets', '1', '9.9', '9.9', '20.00', '0', '20.00', '0.30', '20', '10.1', '2025-10-17 22:15:16', ' ');
INSERT INTO `sale_items` VALUES('143', '220', 'Curamol Suspension 60mL', '4', '26', '104', '50.00', '0', '200.00', '3.00', '200', '96', '2025-10-17 22:15:16', ' ');
INSERT INTO `sale_items` VALUES('144', '221', 'Myospaz Tablet', '2', '15.95', '31.9', '25.00', '0', '50.00', '0.75', '50', '18.1', '2025-10-17 22:27:02', ' ');
INSERT INTO `sale_items` VALUES('145', '221', 'Flu-Gone P+, 60mL', '1', '165', '165', '230.00', '0', '230.00', '3.45', '230', '65', '2025-10-17 22:27:02', ' ');
INSERT INTO `sale_items` VALUES('146', '221', 'TAMEPYN, 20S', '8', '10.75', '86', '15.00', '0', '120.00', '1.80', '120', '34', '2025-10-17 22:27:02', ' ');
INSERT INTO `sale_items` VALUES('147', '222', 'ADFIT  PANTS LARGE', '15', '3.33', '49.95', '5.00', '0', '75.00', '1.13', '75', '25.05', '2025-10-17 22:32:36', ' ');
INSERT INTO `sale_items` VALUES('148', '222', 'Cartil Omega Softgel Capsules', '4', '58.6', '234.4', '75.00', '0', '300.00', '4.50', '300', '65.6', '2025-10-17 22:32:36', ' ');
INSERT INTO `sale_items` VALUES('149', '222', 'Crepe Bandage Spandex 15cm', '10', '49', '490', '100.00', '0', '1000.00', '15.00', '1000', '510', '2025-10-17 22:32:36', ' ');
INSERT INTO `sale_items` VALUES('150', '222', 'DICLOGEN 1% GEL', '3', '19', '57', '80.00', '0', '240.00', '3.60', '240', '183', '2025-10-17 22:32:36', ' ');
INSERT INTO `sale_items` VALUES('151', '222', 'Gesic 400 ADL Brufen', '45', '1.25', '56.25', '3.00', '0', '135.00', '2.03', '135', '78.75', '2025-10-17 22:32:36', ' ');
INSERT INTO `sale_items` VALUES('152', '222', 'Hismopan Plus Tablets (Buscopan Plus Generic)', '10', '6', '60', '15.00', '0', '150.00', '2.25', '150', '90', '2025-10-17 22:32:36', ' ');
INSERT INTO `sale_items` VALUES('153', '223', 'Velvex', '2', '250', '500', '350.00', '0', '700.00', '10.50', '700', '200', '2025-10-17 22:40:57', ' ');
INSERT INTO `sale_items` VALUES('154', '223', 'Anti-Rabies Vaccine, Vial', '1', '266.67', '266.67', '1200.00', '0', '1200.00', '18.00', '1200', '933.33', '2025-10-17 22:40:57', ' ');
INSERT INTO `sale_items` VALUES('155', '224', 'Panadol Extra Tablets (Pairs)', '20', '7.92', '158.4', '20.00', '0', '400.00', '6.00', '400', '241.6', '2025-10-17 22:51:49', ' ');
INSERT INTO `sale_items` VALUES('156', '225', 'Acnesol Cream, 25g', '3', '153', '459', '250.00', '0', '750.00', '11.25', '750', '291', '2025-10-17 22:59:29', ' ');
INSERT INTO `sale_items` VALUES('158', '227', 'Acnesol Cream, 25g', '1', '153', '153', '250.00', '0', '250.00', '3.75', '250', '97', '2025-10-17 23:00:20', ' ');
INSERT INTO `sale_items` VALUES('159', '228', 'Calpol Suspension 60mL', '1', '220', '220', '300.00', '0', '300.00', '4.50', '300', '80', '2025-10-17 23:04:56', ' ');
INSERT INTO `sale_items` VALUES('160', '229', 'Velvex', '1', '250', '250', '350.00', '0', '350.00', '5.25', '350', '100', '2025-10-17 23:12:16', ' ');
INSERT INTO `sale_items` VALUES('161', '230', 'VIVACET L 5', '12', '5.5', '66', '20.00', '0', '240.00', '3.60', '240', '174', '2025-10-17 23:18:52', ' ');
INSERT INTO `sale_items` VALUES('162', '231', 'Triofen 60mL', '11', '21', '231', '50.00', '0', '550.00', '8.25', '550', '319', '2025-10-18 12:38:26', ' ');
INSERT INTO `sale_items` VALUES('163', '232', 'Ompac 40mg', '1', '60', '60', '150.00', '0', '150.00', '2.25', '150', '90', '2025-10-18 13:56:52', ' ');
INSERT INTO `sale_items` VALUES('164', '233', 'Ampiclo-Dawa 500 Capsule', '1', '4', '4', '10.00', '0', '10.00', '0.15', '10', '6', '2025-10-18 14:03:40', ' ');
INSERT INTO `sale_items` VALUES('165', '234', 'ACINET DRY SYRUP 457', '2', '260', '520', '380.00', '0', '760.00', '11.40', '760', '240', '2025-10-18 21:36:35', ' ');
INSERT INTO `sale_items` VALUES('166', '235', 'Neonatal Ampiclox Drops', '1', '345', '345', '500.00', '0', '500.00', '7.50', '500', '155', '2025-10-18 21:37:12', ' ');
INSERT INTO `sale_items` VALUES('167', '236', 'Acinet 375 Tablets', '6', '171', '1026', '250.00', '0', '1500.00', '22.50', '1500', '474', '2025-10-18 21:41:43', ' ');
INSERT INTO `sale_items` VALUES('168', '237', 'Triofen 60mL', '3', '21', '63', '50.00', '0', '150.00', '2.25', '150', '87', '2025-10-19 19:18:59', ' ');
INSERT INTO `sale_items` VALUES('169', '238', 'Triofen 60mL', '2', '21', '42', '50.00', '0', '100.00', '1.50', '100', '58', '2025-10-19 19:20:30', ' ');
INSERT INTO `sale_items` VALUES('170', '239', 'Triofen 60mL', '1', '21', '21', '50.00', '0', '50.00', '0.75', '50', '29', '2025-10-19 19:25:02', ' ');
INSERT INTO `sale_items` VALUES('171', '240', 'Triofen 60mL', '4', '21', '84', '50.00', '0', '200.00', '3.00', '200', '116', '2025-10-19 20:08:52', ' ');
INSERT INTO `sale_items` VALUES('172', '241', 'Triofen 60mL', '1', '21', '21', '50.00', '0', '50.00', '0.75', '50', '29', '2025-10-19 20:45:21', 'Admin User');
INSERT INTO `sale_items` VALUES('173', '242', 'Alugel Suspension, 100mL', '4', '81', '324', '120.00', '0', '480.00', '7.20', '480', '156', '2025-10-19 21:33:08', 'Admin User');
INSERT INTO `sale_items` VALUES('174', '243', 'Gesic 400 ADL Brufen', '211', '1.25', '263.75', '3.00', '0', '633.00', '9.50', '633', '369.25', '2025-10-19 21:37:46', 'Admin User');
INSERT INTO `sale_items` VALUES('175', '244', 'Bulkot-B Cream', '2', '51', '102', '100.00', '0', '200.00', '3.00', '200', '98', '2025-10-20 13:26:52', 'Admin User');
INSERT INTO `sale_items` VALUES('176', '244', 'Filwel Kids, 100mL', '1', '204', '204', '300.00', '0', '300.00', '4.50', '300', '96', '2025-10-20 13:26:52', 'Admin User');
INSERT INTO `sale_items` VALUES('178', '246', '\'GALAXY\'S SEFIN Injection', '11', '32', '352', '80.00', '0', '880.00', '13.20', '880', '528', '2025-10-21 09:57:28', 'Admin User');
INSERT INTO `sale_items` VALUES('179', '247', 'Acinet 375 Tablets', '8', '171', '1368', '250.00', '0', '2000.00', '30.00', '2000', '632', '2025-10-21 10:03:36', 'Admin User');
INSERT INTO `sale_items` VALUES('180', '248', 'Tory 60 Tablet', '21', '27.67', '581.07', '40.00', '0', '840.00', '12.60', '840', '258.93', '2025-10-21 12:57:15', 'Admin User');
INSERT INTO `sale_items` VALUES('184', '252', 'Velvex', '6', '250', '1500', '350.00', '0', '2100.00', '31.50', '2100', '600', '2025-10-21 15:04:11', 'Admin User');
INSERT INTO `sale_items` VALUES('185', '252', 'MEDIVEN Cream 15g', '3', '45', '135', '80.00', '0', '240.00', '3.60', '240', '105', '2025-10-21 15:04:11', 'Admin User');
INSERT INTO `sale_items` VALUES('186', '252', 'Kiss Strawberry Condoms, 3s', '16', '34.42', '550.72', '100.00', '0', '1600.00', '24.00', '1600', '1049.28', '2025-10-21 15:04:11', 'Admin User');
INSERT INTO `sale_items` VALUES('187', '253', 'Alugel Suspension, 100mL', '2', '81', '162', '120.00', '0', '240.00', '3.60', '240', '78', '2025-10-21 15:50:45', 'Admin User');
INSERT INTO `sale_items` VALUES('188', '253', 'Angilock-Plus 50/12.5 Tablets', '10', '5.67', '56.7', '10.00', '0', '100.00', '1.50', '100', '43.3', '2025-10-21 15:50:45', 'Admin User');
INSERT INTO `sale_items` VALUES('189', '253', 'Clindacin-300 Capsules', '2', '135', '270', '250.00', '0', '500.00', '7.50', '500', '230', '2025-10-21 15:50:45', 'Admin User');
INSERT INTO `sale_items` VALUES('190', '254', 'Gesic 400 ADL Brufen', '3', '1.25', '3.75', '3.00', '0', '9.00', '0.14', '9', '5.25', '2025-11-07 12:12:34', 'Admin User');
INSERT INTO `sale_items` VALUES('191', '254', 'Triofen 60mL', '1', '21', '21', '50.00', '2', '50.00', '0.75', '49', '29', '2025-11-07 12:12:34', 'Admin User');
INSERT INTO `sale_items` VALUES('192', '255', 'Hitoral  Shampoo, 100mL', '3', '316', '948', '500.00', '0', '1500.00', '22.50', '1500', '552', '2025-11-07 12:13:30', 'Admin User');
INSERT INTO `sale_items` VALUES('193', '256', 'Augmentin 457 Suspension', '1', '990', '990', '1200.00', '0', '1200.00', '18.00', '1200', '210', '2025-12-10 15:19:41', 'Admin User');
INSERT INTO `sale_items` VALUES('194', '256', 'ACETAL MR Tablets', '1', '51.9', '51.9', '15.00', '0', '15.00', '0.22', '15', '-36.9', '2025-12-10 15:19:41', 'Admin User');
INSERT INTO `sale_items` VALUES('195', '261', 'Theofix - 400 Tablets', '10', '17', '170', '350.00', '0', '3500.00', '52.50', '3500', '3330', '2025-12-10 19:14:46', 'Admin User');
INSERT INTO `sale_items` VALUES('196', '262', 'ACETAL MR Tablets', '10', '51.9', '519', '15.00', '0', '150.00', '2.25', '150', '-369', '2025-12-10 19:15:05', 'Admin User');
INSERT INTO `sale_items` VALUES('197', '262', 'Curamol Suspension 60mL', '12', '26', '312', '50.00', '0', '600.00', '9.00', '600', '288', '2025-12-10 19:15:05', 'Admin User');
INSERT INTO `sale_items` VALUES('198', '263', 'ACINET DRY SYRUP 457', '2', '260', '520', '380.00', '0', '760.00', '11.40', '760', '240', '2025-12-13 14:20:37', 'Admin User');
INSERT INTO `sale_items` VALUES('199', '264', 'Augmentin 228 Suspension', '1', '635', '635', '850.00', '0', '850.00', '12.75', '850', '215', '2025-12-13 14:29:13', 'Admin User');
INSERT INTO `sale_items` VALUES('200', '265', 'Cadistin Expectorant, 100mL', '2', '98', '196', '150.00', '0', '300.00', '4.50', '300', '104', '2025-12-13 15:14:41', 'Admin User');
INSERT INTO `sale_items` VALUES('201', '266', 'Becoactin Syrup, 200mL', '3', '10.67', '32.01', '450.00', '0', '1350.00', '20.25', '1350', '1317.99', '2025-12-13 15:35:13', 'Admin User');
INSERT INTO `sale_items` VALUES('203', '268', 'Clindacin-300 Capsules', '2', '135', '270', '250.00', '0', '500.00', '7.50', '500', '230', '2025-12-13 15:36:14', 'Admin User');
INSERT INTO `sale_items` VALUES('204', '269', 'Ampiclo-Dawa Suspension', '11', '68', '748', '100.00', '0', '1100.00', '16.50', '1100', '352', '2025-12-13 15:36:54', 'Admin User');
INSERT INTO `sale_items` VALUES('205', '270', 'Ampiclo-Dawa Suspension', '7', '68', '476', '100.00', '0', '700.00', '10.50', '700', '224', '2025-12-13 17:36:47', 'Admin User');
INSERT INTO `sale_items` VALUES('207', '272', 'Augmentin 457 Suspension', '1', '990', '990', '1200.00', '0', '1200.00', '18.00', '1200', '210', '2025-12-13 20:54:35', 'Admin User');



CREATE TABLE IF NOT EXISTS `sales` (
  `sales_id` int NOT NULL AUTO_INCREMENT,
  `receipt_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tendered_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `transBy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sales_id`)
) ENGINE=InnoDB AUTO_INCREMENT=273 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sales` VALUES('66', 'ORD202508244115', NULL, '20.00', '0.30', '0.00', '20.00', '40.00', 'Cash', 'Paid', 'admin', '2025-08-24 21:17:01');
INSERT INTO `sales` VALUES('80', 'ORD202508246323', NULL, '2400.00', '36.00', '160.00', '2240.00', '3000.00', 'Cash', 'Paid', 'admin', '2025-08-25 07:47:15');
INSERT INTO `sales` VALUES('81', 'ORD202508257908', NULL, '800.00', '12.00', '0.00', '800.00', '600.00', 'Cash', 'Credit', 'admin', '2025-08-25 07:59:28');
INSERT INTO `sales` VALUES('82', 'ORD202508258725', NULL, '4000.00', '60.00', '200.00', '3800.00', '4000.00', 'Cash', 'Paid', 'admin', '2025-08-25 09:06:47');
INSERT INTO `sales` VALUES('83', 'ORD202508253136', NULL, '80.00', '1.20', '0.00', '80.00', '100.00', 'Cash', 'Paid', 'admin', '2025-08-25 10:55:26');
INSERT INTO `sales` VALUES('84', 'ORD202508254536', NULL, '2000.00', '30.00', '0.00', '2000.00', '2400.00', 'Cash', 'Paid', 'admin', '2025-08-25 11:40:38');
INSERT INTO `sales` VALUES('85', 'ORD202508255421', NULL, '150.00', '2.25', '7.50', '142.50', '300.00', 'Cash', 'Paid', 'admin', '2025-08-25 12:01:42');
INSERT INTO `sales` VALUES('86', 'ORD202508258700', NULL, '100.00', '1.50', '0.00', '100.00', '80.00', 'Cash', 'Credit', 'admin', '2025-08-25 12:10:11');
INSERT INTO `sales` VALUES('87', 'ORD202508259611', NULL, '360.00', '5.40', '0.00', '360.00', '400.00', 'Cash', 'Paid', 'admin', '2025-08-25 12:33:21');
INSERT INTO `sales` VALUES('88', 'ORD202508259093', NULL, '44.00', '0.66', '0.00', '44.00', '60.00', 'Cash', 'Paid', 'admin', '2025-08-25 12:46:15');
INSERT INTO `sales` VALUES('89', 'ORD202508252047', NULL, '8.00', '0.12', '0.00', '8.00', '6.00', 'Cash', 'Credit', 'admin', '2025-08-25 12:47:30');
INSERT INTO `sales` VALUES('90', 'ORD202508257386', NULL, '80000.00', '1200.00', '4000.00', '76000.00', '77000.00', 'Cash', 'Paid', 'admin', '2025-08-25 13:02:47');
INSERT INTO `sales` VALUES('91', 'ORD202508254439', NULL, '6000.00', '90.00', '0.00', '6000.00', '4000.00', 'Cash', 'Credit', 'admin', '2025-08-25 13:07:46');
INSERT INTO `sales` VALUES('92', 'ORD202508254652', NULL, '460.00', '6.90', '0.00', '460.00', '500.00', 'Cash', 'Paid', 'pharmtech', '2025-08-25 13:31:57');
INSERT INTO `sales` VALUES('95', 'ORD202508251970', NULL, '540.00', '8.10', '0.00', '540.00', '600.00', 'Cash', 'Paid', 'pharmtech', '2025-08-25 15:57:17');
INSERT INTO `sales` VALUES('96', 'ORD202508251970', NULL, '540.00', '8.10', '0.00', '540.00', '600.00', 'Cash', 'Paid', 'pharmtech', '2025-08-25 15:57:40');
INSERT INTO `sales` VALUES('113', 'ORD202508259846', NULL, '40.00', '0.60', '0.00', '40.00', '80.00', 'Cash', 'Paid', 'pharmtech', '2025-08-25 17:58:45');
INSERT INTO `sales` VALUES('114', 'ORD202508252206', NULL, '120.00', '1.80', '0.00', '120.00', '140.00', 'Cash', 'Paid', 'pharmtech', '2025-08-25 18:13:31');
INSERT INTO `sales` VALUES('132', 'ORD202508265190', NULL, '120.00', '1.80', '0.00', '120.00', '200.00', 'Cash', 'Paid', 'pharmtech', '2025-08-26 13:35:12');
INSERT INTO `sales` VALUES('133', 'ORD202508261002', NULL, '200.00', '3.00', '0.00', '200.00', '220.00', 'Cash', 'Paid', 'pharmtech', '2025-08-26 13:37:11');
INSERT INTO `sales` VALUES('134', 'ORD202508262777', NULL, '60.00', '0.90', '0.00', '60.00', '80.00', 'Cash', 'Paid', 'pharmtech', '2025-08-26 13:39:10');
INSERT INTO `sales` VALUES('146', 'ORD202508267500', '[{\"brandname\":\"Panadol Extra\",\"quantity\":10,\"price\":10,\"discount\":0,\"total_amount\":100,\"tax_amount\":1.5,\"grand_total\":100}]', '100.00', '1.50', '0.00', '100.00', '120.00', 'Cash', 'Paid', 'pharmtech', '2025-08-26 14:42:06');
INSERT INTO `sales` VALUES('147', 'ORD202508268175', '[{\"brandname\":\"Advil\",\"quantity\":400,\"price\":20,\"discount\":0,\"total_amount\":8000,\"tax_amount\":120,\"grand_total\":8000}]', '8000.00', '120.00', '0.00', '8000.00', '8400.00', 'Cash', 'Paid', 'pharmtech', '2025-08-26 14:43:10');
INSERT INTO `sales` VALUES('148', 'ORD202508264112', '[{\"brandname\":\"Ibufil-400\",\"quantity\":100,\"price\":20,\"discount\":0,\"total_amount\":2000,\"tax_amount\":30,\"grand_total\":2000},{\"brandname\":\"Panadol Extra\",\"quantity\":100,\"price\":10,\"discount\":0,\"total_amount\":1000,\"tax_amount\":15,\"grand_total\":1000}]', '3000.00', '45.00', '0.00', '3000.00', '3000.00', 'Cash', 'Paid', 'pharmtech', '2025-08-26 14:47:19');
INSERT INTO `sales` VALUES('149', 'ORD202508266880', '[{\"brandname\":\"Panadol Extra\",\"quantity\":41,\"price\":10,\"discount\":0,\"total_amount\":410,\"tax_amount\":6.1499999999999995,\"grand_total\":410}]', '410.00', '6.15', '0.00', '410.00', '500.00', 'Cash', 'Paid', 'pharmtech', '2025-08-26 14:48:47');
INSERT INTO `sales` VALUES('150', 'ORD202508272707', '[{\"brandname\":\"Panadol Extra\",\"quantity\":8,\"price\":10,\"discount\":0,\"total_amount\":80,\"tax_amount\":1.2,\"grand_total\":80}]', '80.00', '1.20', '0.00', '80.00', '100.00', 'Cash', 'Paid', 'pharmtech', '2025-08-27 06:12:52');
INSERT INTO `sales` VALUES('165', 'ORD202508271853', '[{\"brandname\":\"Panadol Extra\",\"quantity\":9,\"price\":10,\"discount\":0,\"total_amount\":90,\"tax_amount\":1.3499999999999999,\"grand_total\":90}]', '90.00', '1.35', '0.00', '90.00', '100.00', 'Cash', 'Paid', 'pharmtech', '2025-08-27 07:13:39');
INSERT INTO `sales` VALUES('166', 'ORD202508273557', '[{\"brandname\":\"Panadol Extra\",\"quantity\":3000,\"price\":10,\"discount\":0,\"total_amount\":30000,\"tax_amount\":450,\"grand_total\":30000}]', '30000.00', '450.00', '0.00', '30000.00', '26000.00', 'Mpesa', 'Credit', 'pharmtech', '2025-08-27 07:16:00');
INSERT INTO `sales` VALUES('167', 'ORD202508274395', '[{\"brandname\":\"Ibufil-400\",\"quantity\":18,\"price\":20,\"discount\":0,\"total_amount\":360,\"tax_amount\":5.3999999999999995,\"grand_total\":360}]', '360.00', '5.40', '0.00', '360.00', '0.00', 'Cash', 'Credit', 'pharmtech', '2025-08-27 09:59:40');
INSERT INTO `sales` VALUES('168', 'ORD202508275939', '[{\"brandname\":\"Panadol Extra\",\"quantity\":100,\"price\":10,\"discount\":0,\"total_amount\":1000,\"tax_amount\":15,\"grand_total\":1000}]', '1000.00', '15.00', '0.00', '1000.00', '1000.00', 'Cash', 'Paid', 'pharmtech', '2025-08-27 10:17:51');
INSERT INTO `sales` VALUES('169', 'ORD202508289821', '[{\"brandname\":\"Panadol Extra\",\"quantity\":1,\"price\":10,\"discount\":0,\"total_amount\":10,\"tax_amount\":0.15,\"grand_total\":10}]', '10.00', '0.15', '0.00', '10.00', '15.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 05:54:35');
INSERT INTO `sales` VALUES('170', 'ORD202508271635', '[{\"brandname\":\"Gauzeeee 500 mg\",\"quantity\":15,\"price\":25,\"discount\":0,\"total_amount\":375,\"tax_amount\":5.625,\"grand_total\":375}]', '375.00', '5.63', '0.00', '375.00', '375.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:23:13');
INSERT INTO `sales` VALUES('171', 'ORD202508273479', '[{\"brandname\":\"Panadol Extra\",\"quantity\":1,\"price\":10,\"discount\":0,\"total_amount\":10,\"tax_amount\":0.15,\"grand_total\":10}]', '10.00', '0.15', '0.00', '10.00', '10.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:25:06');
INSERT INTO `sales` VALUES('172', 'ORD202508289974', '[{\"brandname\":\"Panadol\",\"quantity\":100,\"price\":15,\"discount\":0,\"total_amount\":1500,\"tax_amount\":22.5,\"grand_total\":1500}]', '1500.00', '22.50', '0.00', '1500.00', '1500.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:28:28');
INSERT INTO `sales` VALUES('173', 'ORD202508286038', '[{\"brandname\":\"MPOX-C\",\"quantity\":3,\"price\":4,\"discount\":0,\"total_amount\":12,\"tax_amount\":0.18,\"grand_total\":12}]', '12.00', '0.18', '0.00', '12.00', '12.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:31:03');
INSERT INTO `sales` VALUES('174', 'ORD202508288690', '[{\"brandname\":\"MPOX-C\",\"quantity\":1,\"price\":4,\"discount\":0,\"total_amount\":4,\"tax_amount\":0.06,\"grand_total\":4}]', '4.00', '0.06', '0.00', '4.00', '4.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:31:55');
INSERT INTO `sales` VALUES('175', 'ORD202508281638', '[{\"brandname\":\"MPOX-C\",\"quantity\":51,\"price\":4,\"discount\":0,\"total_amount\":204,\"tax_amount\":3.06,\"grand_total\":204}]', '204.00', '3.06', '0.00', '204.00', '210.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:33:48');
INSERT INTO `sales` VALUES('176', 'ORD202508280838', '[{\"brandname\":\"Panadol Extra\",\"quantity\":2211,\"price\":10,\"discount\":0,\"total_amount\":22110,\"tax_amount\":331.65,\"grand_total\":22110}]', '22110.00', '331.65', '0.00', '22110.00', '23000.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 06:45:20');
INSERT INTO `sales` VALUES('177', 'ORD202508281168', '[{\"brandname\":\"Panadol Extra\",\"quantity\":120,\"price\":10,\"discount\":1,\"total_amount\":1200,\"tax_amount\":18,\"grand_total\":1188}]', '1200.00', '18.00', '12.00', '1188.00', '2000.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:14:02');
INSERT INTO `sales` VALUES('178', 'ORD202508280440', '[{\"brandname\":\"Panadol Extra\",\"quantity\":41,\"price\":10,\"discount\":0,\"total_amount\":410,\"tax_amount\":6.1499999999999995,\"grand_total\":410},{\"brandname\":\"Panadol\",\"quantity\":11,\"price\":15,\"discount\":0,\"total_amount\":165,\"tax_amount\":2.475,\"grand_total\":165}]', '575.00', '8.63', '0.00', '575.00', '600.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:19:48');
INSERT INTO `sales` VALUES('179', 'ORD202508286109', '[{\"brandname\":\"Panadol Extra\",\"quantity\":1030,\"price\":10,\"discount\":0,\"total_amount\":10300,\"tax_amount\":154.5,\"grand_total\":10300}]', '10300.00', '154.50', '0.00', '10300.00', '11000.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:40:34');
INSERT INTO `sales` VALUES('180', 'ORD202508280337', '[{\"brandname\":\"Panadol Extra\",\"quantity\":200,\"price\":10,\"discount\":0,\"total_amount\":2000,\"tax_amount\":30,\"grand_total\":2000}]', '2000.00', '30.00', '0.00', '2000.00', '2000.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:45:02');
INSERT INTO `sales` VALUES('181', 'ORD202508284913', '[{\"brandname\":\"Panadol Extra\",\"quantity\":200,\"price\":10,\"discount\":0,\"total_amount\":2000,\"tax_amount\":30,\"grand_total\":2000}]', '2000.00', '30.00', '0.00', '2000.00', '2000.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:52:57');
INSERT INTO `sales` VALUES('182', 'ORD202508280263', '[{\"brandname\":\"Panadol Extra\",\"quantity\":300,\"price\":10,\"discount\":0,\"total_amount\":3000,\"tax_amount\":45,\"grand_total\":3000}]', '3000.00', '45.00', '0.00', '3000.00', '3000.00', 'Cash', 'Paid', 'pharmtech', '2025-08-28 07:58:40');
INSERT INTO `sales` VALUES('183', 'ORD202509064556', '[{\"brandname\":\"Brustan Tablets\",\"quantity\":20,\"price\":20,\"discount\":0,\"total_amount\":400,\"tax_amount\":6,\"grand_total\":400}]', '400.00', '6.00', '0.00', '400.00', '400.00', 'Cash', 'Paid', 'admin', '2025-09-06 10:15:22');
INSERT INTO `sales` VALUES('184', 'ORD202509061129', '[{\"brandname\":\"Brustan Tablets\",\"quantity\":11,\"price\":20,\"discount\":0,\"total_amount\":220,\"tax_amount\":3.3,\"grand_total\":220}]', '220.00', '3.30', '0.00', '220.00', '220.00', 'Cash', 'Paid', 'admin', '2025-09-06 10:27:05');
INSERT INTO `sales` VALUES('185', 'ORD202509060851', '[{\"brandname\":\"CONTUS-650 Tablet\",\"quantity\":2,\"price\":150,\"discount\":0,\"total_amount\":300,\"tax_amount\":4.5,\"grand_total\":300}]', '300.00', '4.50', '0.00', '300.00', '300.00', 'Cash', 'Paid', 'admin', '2025-09-06 10:48:45');
INSERT INTO `sales` VALUES('186', 'ORD202509141024', '[{\"brandname\":\"Gesic 400 ADL Brufen\",\"quantity\":111,\"price\":3,\"discount\":0,\"total_amount\":333,\"tax_amount\":4.995,\"grand_total\":333}]', '333.00', '5.00', '0.00', '333.00', '400.00', 'Cash', 'Paid', 'admin', '2025-09-14 10:30:33');
INSERT INTO `sales` VALUES('187', 'ORD202509144704', '[{\"brandname\":\"Hismopan Plus Tablets (Buscopan Plus Generic)\",\"quantity\":15,\"price\":15,\"discount\":0,\"total_amount\":225,\"tax_amount\":3.375,\"grand_total\":225}]', '225.00', '3.38', '0.00', '225.00', '230.00', 'Cash', 'Paid', 'admin', '2025-09-14 12:57:38');
INSERT INTO `sales` VALUES('188', 'ORD202509149048', '[{\"brandname\":\"Ompac 40mg\",\"quantity\":3,\"price\":150,\"discount\":0,\"total_amount\":450,\"tax_amount\":6.75,\"grand_total\":450}]', '450.00', '6.75', '0.00', '450.00', '500.00', 'Cash', 'Paid', 'admin', '2025-09-14 13:13:24');
INSERT INTO `sales` VALUES('189', 'ORD202509142339', '[{\"brandname\":\"GAMOK Injection, Vial\",\"quantity\":4,\"price\":200,\"discount\":0,\"total_amount\":800,\"tax_amount\":12,\"grand_total\":800}]', '800.00', '12.00', '0.00', '800.00', '800.00', 'Cash', 'Paid', 'admin', '2025-09-14 13:23:17');
INSERT INTO `sales` VALUES('190', 'ORD202509148766', '[{\"brandname\":\"Acinet 1000 Tablets\",\"quantity\":3,\"price\":420,\"discount\":0,\"total_amount\":1260,\"tax_amount\":18.9,\"grand_total\":1260}]', '1260.00', '18.90', '0.00', '1260.00', '1300.00', 'Cash', 'Paid', 'admin', '2025-09-14 13:43:07');
INSERT INTO `sales` VALUES('191', 'ORD202509174728', '[{\"brandname\":\"Gesic 400 ADL Brufen\",\"quantity\":451,\"price\":3,\"discount\":0,\"total_amount\":1353,\"tax_amount\":20.294999999999998,\"grand_total\":1353}]', '1353.00', '20.29', '0.00', '1353.00', '1400.00', 'Cash', 'Paid', 'admin', '2025-09-17 06:53:26');
INSERT INTO `sales` VALUES('192', 'ORD202510176749', '[{\"brandname\":\"ACEPAR-MR Caplets\",\"quantity\":4,\"price\":35,\"discount\":0,\"total_amount\":140,\"tax_amount\":2.1,\"grand_total\":140}]', '140.00', '2.10', '0.00', '140.00', '35.00', 'Cash', 'Credit', 'admin', '2025-10-17 12:17:22');
INSERT INTO `sales` VALUES('196', 'ORD202510175841', 'ACETAL MR Tablets (12)', '180.00', '2.70', '0.00', '180.00', '0.00', 'cash', 'Pending', ' ', '2025-10-17 20:20:00');
INSERT INTO `sales` VALUES('197', 'ORD202510173619', 'Hitoral  Shampoo, 100mL (1)', '500.00', '7.50', '0.00', '500.00', '0.00', 'cash', 'Pending', ' ', '2025-10-17 20:23:07');
INSERT INTO `sales` VALUES('198', 'ORD202510173619', 'Hitoral  Shampoo, 100mL (2)', '1000.00', '15.00', '0.00', '1000.00', '0.00', 'cash', 'Pending', ' ', '2025-10-17 20:25:15');
INSERT INTO `sales` VALUES('199', 'ORD202510174621', 'Hitoral  Shampoo, 100mL (4)', '2000.00', '30.00', '0.00', '2000.00', '0.00', 'cash', 'Pending', ' ', '2025-10-17 20:30:57');
INSERT INTO `sales` VALUES('208', 'ORD202510171094', 'Calpol Suspension 60mL (5), Cetamol 500mg (1), Curamol Suspension 100mL (4)', '1902.00', '28.53', '0.00', '1902.00', '2000.00', 'cash', 'Paid', ' ', '2025-10-17 21:27:32');
INSERT INTO `sales` VALUES('209', 'ORD202510172531', 'Hitoral  Shampoo, 100mL (4), Ampliclo-Dawa (Neonata Amplicox Gen) (1)', '2100.00', '31.50', '0.00', '2100.00', '2200.00', 'cash', 'Paid', ' ', '2025-10-17 21:29:30');
INSERT INTO `sales` VALUES('210', 'ORD202510174310', 'Hitoral  Shampoo, 100mL (1)', '500.00', '7.50', '0.00', '500.00', '600.00', 'cash', 'Paid', ' ', '2025-10-17 21:34:43');
INSERT INTO `sales` VALUES('211', 'ORD202510174310', 'Hitoral  Shampoo, 100mL (2), Coldcap Syrup, 100mL (2)', '1300.00', '19.50', '0.00', '1300.00', '1300.00', 'cash', 'Paid', ' ', '2025-10-17 21:50:30');
INSERT INTO `sales` VALUES('212', 'ORD202510176082', 'Brustan Tablets (1), Triofen 60mL (5)', '270.00', '4.05', '0.00', '270.00', '300.00', 'cash', 'Paid', ' ', '2025-10-17 21:56:03');
INSERT INTO `sales` VALUES('213', 'ORD202510177214', 'ACEPAR-MR Caplets (1)', '35.00', '0.53', '0.00', '35.00', '35.00', 'cash', 'Paid', ' ', '2025-10-17 21:57:58');
INSERT INTO `sales` VALUES('214', 'ORD202510177214', 'ACEPAR-MR Caplets (1)', '35.00', '0.53', '0.00', '35.00', '35.00', 'cash', 'Paid', ' ', '2025-10-17 21:58:06');
INSERT INTO `sales` VALUES('217', 'ORD202510173229', 'Curamol Suspension 100mL (10)', '1000.00', '15.00', '0.00', '1000.00', '1010.00', 'cash', 'Paid', ' ', '2025-10-17 22:03:21');
INSERT INTO `sales` VALUES('218', 'ORD202510179378', 'Triofen 60mL (5), Triofen 100mL (4)', '650.00', '9.75', '0.00', '650.00', '700.00', 'cash', 'Paid', ' ', '2025-10-17 22:05:20');
INSERT INTO `sales` VALUES('219', 'ORD202510172195', 'Triofen 60mL (11)', '550.00', '8.25', '0.00', '550.00', '600.00', 'cash', 'Paid', ' ', '2025-10-17 22:13:32');
INSERT INTO `sales` VALUES('220', 'ORD202510177380', 'Calpol Suspension 60mL (1), ACETAL MR Tablets (10), Brustan Tablets (1), Curamol Suspension 60mL (4)', '670.00', '10.05', '0.00', '670.00', '700.00', 'cash', 'Paid', ' ', '2025-10-17 22:15:15');
INSERT INTO `sales` VALUES('221', 'ORD202510178953', 'Myospaz Tablet (2), Flu-Gone P+, 60mL (1), TAMEPYN, 20S (8)', '400.00', '6.00', '0.00', '400.00', '400.00', 'cash', 'Paid', ' ', '2025-10-17 22:27:02');
INSERT INTO `sales` VALUES('222', 'ORD202510171880', 'ADFIT  PANTS LARGE (15), Cartil Omega Softgel Capsules (4), Crepe Bandage Spandex 15cm (10), DICLOGEN 1% GEL (3), Gesic 400 ADL Brufen (45), Hismopan Plus Tablets (Buscopan Plus Generic) (10)', '1900.00', '28.50', '0.00', '1900.00', '2000.00', 'cash', 'Paid', ' ', '2025-10-17 22:32:36');
INSERT INTO `sales` VALUES('223', 'ORD202510179983', 'Velvex (2), Anti-Rabies Vaccine, Vial (1)', '1900.00', '28.50', '0.00', '1900.00', '2000.00', 'cash', 'Paid', ' ', '2025-10-17 22:40:57');
INSERT INTO `sales` VALUES('224', 'ORD202510170820', 'Panadol Extra Tablets (Pairs) (20)', '400.00', '6.00', '0.00', '400.00', '400.00', 'cash', 'Paid', ' ', '2025-10-17 22:51:49');
INSERT INTO `sales` VALUES('225', 'ORD202510173196', 'Acnesol Cream, 25g (3)', '750.00', '11.25', '0.00', '750.00', '750.00', 'cash', 'Paid', ' ', '2025-10-17 22:59:29');
INSERT INTO `sales` VALUES('227', 'ORD202510173196', 'Acnesol Cream, 25g (1)', '250.00', '3.75', '0.00', '250.00', '750.00', 'cash', 'Paid', ' ', '2025-10-17 23:00:20');
INSERT INTO `sales` VALUES('228', 'ORD202510179210', 'Calpol Suspension 60mL (1)', '300.00', '4.50', '0.00', '300.00', '300.00', 'cash', 'Paid', ' ', '2025-10-17 23:04:56');
INSERT INTO `sales` VALUES('229', 'ORD202510173694', 'Velvex (1)', '350.00', '5.25', '0.00', '350.00', '400.00', 'cash', 'Paid', ' ', '2025-10-17 23:12:16');
INSERT INTO `sales` VALUES('230', 'ORD202510172678', 'VIVACET L 5 (12)', '240.00', '3.60', '0.00', '240.00', '250.00', 'cash', 'Paid', ' ', '2025-10-17 23:18:52');
INSERT INTO `sales` VALUES('231', 'ORD202510186652', 'Triofen 60mL (11)', '550.00', '8.25', '0.00', '550.00', '550.00', 'cash', 'Paid', ' ', '2025-10-18 12:38:26');
INSERT INTO `sales` VALUES('232', 'ORD202510189713', 'Ompac 40mg (1)', '150.00', '2.25', '0.00', '150.00', '150.00', 'cash', 'Paid', ' ', '2025-10-18 13:56:52');
INSERT INTO `sales` VALUES('233', 'ORD202510189713', 'Ampiclo-Dawa 500 Capsule (1)', '10.00', '0.15', '0.00', '10.00', '100.00', 'cash', 'Paid', ' ', '2025-10-18 14:03:40');
INSERT INTO `sales` VALUES('234', 'ORD202510185213', 'ACINET DRY SYRUP 457 (2)', '760.00', '11.40', '0.00', '760.00', '760.00', 'cash', 'Paid', ' ', '2025-10-18 21:36:35');
INSERT INTO `sales` VALUES('235', 'ORD202510184974', 'Neonatal Ampiclox Drops (1)', '500.00', '7.50', '0.00', '500.00', '500.00', 'cash', 'Paid', ' ', '2025-10-18 21:37:12');
INSERT INTO `sales` VALUES('236', 'ORD202510183843', 'Acinet 375 Tablets (6)', '1500.00', '22.50', '0.00', '1500.00', '1500.00', 'cash', 'Paid', ' ', '2025-10-18 21:41:43');
INSERT INTO `sales` VALUES('237', 'ORD202510195798', 'Triofen 60mL (3)', '150.00', '2.25', '0.00', '0.00', '150.00', 'cash', 'Paid', ' ', '2025-10-19 19:18:59');
INSERT INTO `sales` VALUES('238', 'ORD202510197022', 'Triofen 60mL (2)', '100.00', '1.50', '0.00', '100.00', '100.00', 'cash', 'Paid', ' ', '2025-10-19 19:20:30');
INSERT INTO `sales` VALUES('239', 'ORD202510192279', 'Triofen 60mL (1)', '50.00', '0.75', '0.00', '50.00', '50.00', 'cash', 'Paid', ' ', '2025-10-19 19:25:02');
INSERT INTO `sales` VALUES('240', 'ORD202510197140', 'Triofen 60mL (4)', '200.00', '3.00', '0.00', '200.00', '220.00', 'cash', 'Paid', ' ', '2025-10-19 20:08:52');
INSERT INTO `sales` VALUES('241', 'ORD202510198591', 'Triofen 60mL (1)', '50.00', '0.75', '0.00', '50.00', '50.00', 'cash', 'Paid', 'Admin User', '2025-10-19 20:45:21');
INSERT INTO `sales` VALUES('242', 'ORD202510195020', 'Alugel Suspension, 100mL (4)', '480.00', '7.20', '0.00', '480.00', '480.00', 'cash', 'Paid', 'Admin User', '2025-10-19 21:33:08');
INSERT INTO `sales` VALUES('243', 'ORD202510198171', 'Gesic 400 ADL Brufen (211)', '633.00', '9.49', '0.00', '633.00', '633.00', 'cash', 'Paid', 'Admin User', '2025-10-19 21:37:46');
INSERT INTO `sales` VALUES('244', 'ORD202510209710', 'Bulkot-B Cream (2), Filwel Kids, 100mL (1)', '500.00', '7.50', '0.00', '500.00', '500.00', '0', 'Paid', 'Admin User', '2025-10-20 13:26:52');
INSERT INTO `sales` VALUES('246', 'ORD202510216099', '\'GALAXY\'S SEFIN Injection (11)', '880.00', '13.20', '0.00', '880.00', '1000.00', '0', 'Paid', 'Admin User', '2025-10-21 09:57:28');
INSERT INTO `sales` VALUES('247', 'ORD202510214436', 'Acinet 375 Tablets (8)', '2000.00', '30.00', '0.00', '2000.00', '2000.00', '0', 'Paid', 'Admin User', '2025-10-21 10:03:36');
INSERT INTO `sales` VALUES('248', 'ORD202510213536', 'Tory 60 Tablet (21)', '840.00', '12.60', '0.00', '840.00', '840.00', 'Cash', 'Paid', 'Admin User', '2025-10-21 12:57:15');
INSERT INTO `sales` VALUES('252', 'ORD202510212928', 'Velvex (6), MEDIVEN Cream 15g (3), Kiss Strawberry Condoms, 3s (16)', '3940.00', '59.10', '0.00', '3940.00', '4000.00', 'Cash', 'Paid', 'Admin User', '2025-10-21 15:04:11');
INSERT INTO `sales` VALUES('253', 'ORD202510216541', 'Alugel Suspension, 100mL (2), Angilock-Plus 50/12.5 Tablets (10), Clindacin-300 Capsules (2)', '840.00', '12.60', '0.00', '840.00', '840.00', 'Cash', 'Paid', 'Admin User', '2025-10-21 15:50:45');
INSERT INTO `sales` VALUES('254', 'ORD202511076401', 'Gesic 400 ADL Brufen (3), Triofen 60mL (1)', '59.00', '0.89', '0.00', '58.00', '58.00', 'Cash', 'Paid', 'Admin User', '2025-11-07 12:12:34');
INSERT INTO `sales` VALUES('255', 'ORD202511078473', 'Hitoral  Shampoo, 100mL (3)', '1500.00', '22.50', '0.00', '1500.00', '1500.00', 'Cash', 'Paid', 'Admin User', '2025-11-07 12:13:30');
INSERT INTO `sales` VALUES('256', 'ORD202511189477', 'Augmentin 457 Suspension (1), ACETAL MR Tablets (1)', '1215.00', '18.23', '0.00', '1215.00', '1216.00', 'Cash', 'Paid', 'Admin User', '2025-12-10 15:19:41');
INSERT INTO `sales` VALUES('261', 'ORD202512086033', 'Theofix - 400 Tablets (10)', '3500.00', '52.50', '0.00', '3500.00', '1500.00', 'Credit', 'Credit', 'Admin User', '2025-12-10 19:14:46');
INSERT INTO `sales` VALUES('262', 'ORD202512089704', 'ACETAL MR Tablets (10), Curamol Suspension 60mL (12)', '750.00', '11.25', '0.00', '750.00', '800.00', 'Cash', 'Paid', 'Admin User', '2025-12-10 19:15:05');
INSERT INTO `sales` VALUES('263', 'ORD202512133740', 'ACINET DRY SYRUP 457 (2)', '760.00', '11.40', '0.00', '760.00', '600.00', 'Credit', 'Credit', 'Admin User', '2025-12-13 14:20:37');
INSERT INTO `sales` VALUES('264', 'ORD202512134941', 'Augmentin 228 Suspension (1)', '850.00', '12.75', '0.00', '850.00', '550.00', 'Credit', 'Credit', 'Admin User', '2025-12-13 14:29:13');
INSERT INTO `sales` VALUES('265', 'ORD202512137884', 'Cadistin Expectorant, 100mL (2)', '300.00', '4.50', '0.00', '300.00', '120.00', 'Cash', 'Credit', 'Admin User', '2025-12-13 15:14:41');
INSERT INTO `sales` VALUES('266', 'ORD202512139787', 'Becoactin Syrup, 200mL (3)', '1350.00', '20.25', '0.00', '1350.00', '1350.00', 'Mpesa', 'Paid', 'Admin User', '2025-12-13 15:35:13');
INSERT INTO `sales` VALUES('268', 'ORD202512130163', 'Clindacin-300 Capsules (2)', '500.00', '7.50', '0.00', '500.00', '500.00', 'Mpesa', 'Paid', 'Admin User', '2025-12-13 15:36:14');
INSERT INTO `sales` VALUES('269', 'ORD202512133671', 'Ampiclo-Dawa Suspension (11)', '1100.00', '16.50', '0.00', '1100.00', '1100.00', 'Cash', 'Paid', 'Admin User', '2025-12-13 15:36:54');
INSERT INTO `sales` VALUES('270', 'ORD202512138788', 'Ampiclo-Dawa Suspension (7)', '700.00', '10.50', '0.00', '700.00', '700.00', 'Cash', 'Paid', 'Admin User', '2025-12-13 17:36:47');
INSERT INTO `sales` VALUES('272', 'ORD202512133017', 'Augmentin 457 Suspension (1)', '1200.00', '18.00', '0.00', '1200.00', '1200.00', 'Cash', 'Paid', 'Admin User', '2025-12-13 20:54:35');



CREATE TABLE IF NOT EXISTS `sales_drafts` (
  `draft_id` int NOT NULL AUTO_INCREMENT,
  `receipt_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brandname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




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
  `transactionType` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `productname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brandname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `openingBalance` int DEFAULT '0',
  `quantityIn` double NOT NULL,
  `quantityOut` int DEFAULT NULL,
  `receivedFrom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `batch` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiryDate` date NOT NULL,
  `transBy` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stockBalance` double DEFAULT '0',
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `reasons` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transID`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stock_movements` VALUES('1', '292', 'Purchase', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '5', '12', NULL, 'SittiPharm', 'sitti-001', '2026-04-24', 'Admin User', '17', 'active', NULL, '2025-10-17 20:28:19');
INSERT INTO `stock_movements` VALUES('2', '38', 'sales', 'Paracetamol 120mg/5ml', 'Calpol Suspension 60mL', '10', '0', '5', 'None', NULL, '2030-01-01', ' ', '5', 'active', NULL, '2025-10-17 21:27:32');
INSERT INTO `stock_movements` VALUES('3', '2', 'sales', 'Paracetamol Tablet BP 500mg', 'Cetamol 500mg', '1990', '0', '1', 'None', NULL, '2030-01-01', ' ', '1989', 'active', NULL, '2025-10-17 21:27:32');
INSERT INTO `stock_movements` VALUES('4', '37', 'sales', 'Paracetamol 120mg/5ml', 'Curamol Suspension 100mL', '30', '0', '4', 'None', NULL, '2030-01-01', ' ', '26', 'active', NULL, '2025-10-17 21:27:32');
INSERT INTO `stock_movements` VALUES('5', '292', 'sales', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '13', '0', '4', 'None', NULL, '2030-01-01', ' ', '9', 'active', NULL, '2025-10-17 21:29:30');
INSERT INTO `stock_movements` VALUES('6', '128', 'sales', 'Ampicillin 60mg/cloxacillin 30mg/0.6mL', 'Ampliclo-Dawa (Neonata Amplicox Gen)', '5', '0', '1', 'None', NULL, '2030-01-01', ' ', '4', 'active', NULL, '2025-10-17 21:29:30');
INSERT INTO `stock_movements` VALUES('7', '292', 'sales', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '9', '0', '1', 'None', NULL, '2030-01-01', ' ', '8', 'active', NULL, '2025-10-17 21:34:44');
INSERT INTO `stock_movements` VALUES('8', '292', 'sales', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '8', '0', '2', 'None', NULL, '2030-01-01', ' ', '6', 'active', NULL, '2025-10-17 21:50:30');
INSERT INTO `stock_movements` VALUES('9', '187', 'sales', 'Chlorpheniramine 2mg/Pseudoephedrine 10mg/Paracetamol 120mg', 'Coldcap Syrup, 100mL', '4', '0', '2', 'None', NULL, '2030-01-01', ' ', '2', 'active', NULL, '2025-10-17 21:50:30');
INSERT INTO `stock_movements` VALUES('10', '6', 'sales', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', '2', '0', '1', 'None', NULL, '2030-01-01', ' ', '1', 'active', NULL, '2025-10-17 21:56:03');
INSERT INTO `stock_movements` VALUES('11', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '49', '0', '5', 'None', NULL, '2030-01-01', ' ', '44', 'active', NULL, '2025-10-17 21:56:03');
INSERT INTO `stock_movements` VALUES('12', '7', 'sales', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', '2', '0', '1', 'None', NULL, '2030-01-01', ' ', '1', 'active', NULL, '2025-10-17 21:57:58');
INSERT INTO `stock_movements` VALUES('13', '7', 'sales', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', '1', '0', '1', 'None', NULL, '2030-01-01', ' ', '0', 'active', NULL, '2025-10-17 21:58:06');
INSERT INTO `stock_movements` VALUES('14', '37', 'sales', 'Paracetamol 120mg/5ml', 'Curamol Suspension 100mL', '26', '0', '10', 'None', NULL, '2030-01-01', ' ', '16', 'active', NULL, '2025-10-17 22:03:21');
INSERT INTO `stock_movements` VALUES('15', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '44', '0', '5', 'None', NULL, '2030-01-01', ' ', '39', 'active', NULL, '2025-10-17 22:05:20');
INSERT INTO `stock_movements` VALUES('16', '40', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 100mL', '10', '0', '4', 'None', NULL, '2030-01-01', ' ', '6', 'active', NULL, '2025-10-17 22:05:20');
INSERT INTO `stock_movements` VALUES('17', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '39', '0', '11', 'None', NULL, '2030-01-01', ' ', '28', 'active', NULL, '2025-10-17 22:13:32');
INSERT INTO `stock_movements` VALUES('18', '38', 'sales', 'Paracetamol 120mg/5ml', 'Calpol Suspension 60mL', '5', '0', '1', 'None', NULL, '2030-01-01', ' ', '4', 'active', NULL, '2025-10-17 22:15:16');
INSERT INTO `stock_movements` VALUES('19', '309', 'sales', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', '77', '0', '10', 'None', NULL, '2030-01-01', ' ', '67', 'active', NULL, '2025-10-17 22:15:16');
INSERT INTO `stock_movements` VALUES('20', '6', 'sales', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', '1', '0', '1', 'None', NULL, '2030-01-01', ' ', '0', 'active', NULL, '2025-10-17 22:15:16');
INSERT INTO `stock_movements` VALUES('21', '36', 'sales', 'Paracetamol 120mg/5ml', 'Curamol Suspension 60mL', '50', '0', '4', 'None', NULL, '2030-01-01', ' ', '46', 'active', NULL, '2025-10-17 22:15:16');
INSERT INTO `stock_movements` VALUES('22', '26', 'sales', 'Paracetamol 500mg/Chlorzoxazone 250mg', 'Myospaz Tablet', '100', '0', '2', 'None', NULL, '2030-01-01', ' ', '98', 'active', NULL, '2025-10-17 22:27:02');
INSERT INTO `stock_movements` VALUES('23', '181', 'sales', 'Dextromethorphan 10mg/Chlorpheniramine 2mg/Pseudoephedrine 30mg/Paracetamol', 'Flu-Gone P+, 60mL', '3', '0', '1', 'None', NULL, '2030-01-01', ' ', '2', 'active', NULL, '2025-10-17 22:27:02');
INSERT INTO `stock_movements` VALUES('24', '326', 'sales', 'Paracetamol/Codeine Phosphate/Doxylamine, Caffeine', 'TAMEPYN, 20S', '40', '0', '8', 'None', NULL, '2030-01-01', ' ', '32', 'active', NULL, '2025-10-17 22:27:02');
INSERT INTO `stock_movements` VALUES('25', '76', 'sales', 'Adult Pants L', 'ADFIT  PANTS LARGE', '30', '0', '15', 'None', NULL, '2030-01-01', ' ', '15', 'active', NULL, '2025-10-17 22:32:36');
INSERT INTO `stock_movements` VALUES('26', '140', 'sales', 'Glucosamine/Chondroitin/Cod Liver/Omega 3', 'Cartil Omega Softgel Capsules', '30', '0', '4', 'None', NULL, '2030-01-01', ' ', '26', 'active', NULL, '2025-10-17 22:32:36');
INSERT INTO `stock_movements` VALUES('27', '267', 'sales', 'Bandages', 'Crepe Bandage Spandex 15cm', '12', '0', '10', 'None', NULL, '2030-01-01', ' ', '2', 'active', NULL, '2025-10-17 22:32:36');
INSERT INTO `stock_movements` VALUES('28', '269', 'sales', 'Diclofenac Gel 1%', 'DICLOGEN 1% GEL', '28', '0', '3', 'None', NULL, '2030-01-01', ' ', '25', 'active', NULL, '2025-10-17 22:32:36');
INSERT INTO `stock_movements` VALUES('29', '1', 'sales', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', '1416', '0', '45', 'None', NULL, '2030-01-01', ' ', '1371', 'active', NULL, '2025-10-17 22:32:36');
INSERT INTO `stock_movements` VALUES('30', '46', 'sales', 'Paracetamol 500mg/Hyoscine Butylbromide 10mg', 'Hismopan Plus Tablets (Buscopan Plus Generic)', '185', '0', '10', 'None', NULL, '2030-01-01', ' ', '175', 'active', NULL, '2025-10-17 22:32:36');
INSERT INTO `stock_movements` VALUES('31', '73', 'sales', 'Absorbent Cotton Wool 400mg', 'Velvex', '10', '0', '2', 'None', NULL, '2030-01-01', ' ', '8', 'active', NULL, '2025-10-17 22:40:57');
INSERT INTO `stock_movements` VALUES('32', '338', 'sales', 'Anti-Rabies Vaccine', 'Anti-Rabies Vaccine, Vial', '3', '0', '1', 'None', NULL, '2030-01-01', ' ', '2', 'active', NULL, '2025-10-17 22:40:57');
INSERT INTO `stock_movements` VALUES('33', '11', 'sales', 'Paracetamol Tablet BP 500mg/Caffeine 65mg', 'Panadol Extra Tablets (Pairs)', '96', '0', '20', 'None', NULL, '2030-01-01', ' ', '76', 'active', NULL, '2025-10-17 22:51:49');
INSERT INTO `stock_movements` VALUES('34', '303', 'sales', 'Tretinoine 0.05% w/w', 'Acnesol Cream, 25g', '4', '0', '3', 'None', NULL, '2030-01-01', ' ', '1', 'active', NULL, '2025-10-17 22:59:29');
INSERT INTO `stock_movements` VALUES('35', '303', 'sales', 'Tretinoine 0.05% w/w', 'Acnesol Cream, 25g', '1', '0', '1', 'None', NULL, '2030-01-01', ' ', '0', 'active', NULL, '2025-10-17 23:00:20');
INSERT INTO `stock_movements` VALUES('36', '38', 'sales', 'Paracetamol 120mg/5ml', 'Calpol Suspension 60mL', '4', '0', '1', 'None', NULL, '2030-01-01', ' ', '3', 'active', NULL, '2025-10-17 23:04:56');
INSERT INTO `stock_movements` VALUES('37', '73', 'sales', 'Absorbent Cotton Wool 400mg', 'Velvex', '8', '0', '1', 'None', NULL, '2030-01-01', ' ', '7', 'active', NULL, '2025-10-17 23:12:16');
INSERT INTO `stock_movements` VALUES('38', '70', 'sales', 'Levocetirizine 5mg', 'VIVACET L 5', '100', '0', '12', 'None', NULL, '2030-01-01', ' ', '88', 'active', NULL, '2025-10-17 23:18:52');
INSERT INTO `stock_movements` VALUES('39', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '28', '0', '11', 'None', NULL, '2030-01-01', ' ', '17', 'active', NULL, '2025-10-18 12:38:26');
INSERT INTO `stock_movements` VALUES('40', '323', 'sales', 'Omeprazole Inj 40mg', 'Ompac 40mg', '1', '0', '1', 'None', NULL, '2030-01-01', ' ', '0', 'active', NULL, '2025-10-18 13:56:52');
INSERT INTO `stock_movements` VALUES('41', '66', 'sales', 'Ampicillin/Cloxacillin 250mg/250mg', 'Ampiclo-Dawa 500 Capsule', '480', '0', '1', 'None', NULL, '2030-01-01', ' ', '479', 'active', NULL, '2025-10-18 14:03:40');
INSERT INTO `stock_movements` VALUES('42', '126', 'sales', 'Amoxicillin/Clavulanate 457mg/5mL', 'ACINET DRY SYRUP 457', '5', '0', '2', 'None', NULL, '2030-01-01', ' ', '3', 'active', NULL, '2025-10-18 21:36:35');
INSERT INTO `stock_movements` VALUES('43', '127', 'sales', 'Ampicillin 60mg/cloxacillin 30mg/0.6mL', 'Neonatal Ampiclox Drops', '2', '0', '1', 'None', NULL, '2030-01-01', ' ', '1', 'active', NULL, '2025-10-18 21:37:12');
INSERT INTO `stock_movements` VALUES('44', '117', 'sales', 'Amoxicillin/Clavulanate 375mg', 'Acinet 375 Tablets', '100', '0', '6', 'None', NULL, '2030-01-01', ' ', '94', 'active', NULL, '2025-10-18 21:41:43');
INSERT INTO `stock_movements` VALUES('45', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '17', '0', '3', 'None', NULL, '2030-01-01', ' ', '14', 'active', NULL, '2025-10-19 19:18:59');
INSERT INTO `stock_movements` VALUES('46', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '14', '0', '2', 'None', NULL, '2030-01-01', ' ', '12', 'active', NULL, '2025-10-19 19:20:30');
INSERT INTO `stock_movements` VALUES('47', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '12', '0', '1', 'None', NULL, '2030-01-01', ' ', '11', 'active', NULL, '2025-10-19 19:25:02');
INSERT INTO `stock_movements` VALUES('48', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '11', '0', '4', 'None', NULL, '2030-01-01', ' ', '7', 'active', NULL, '2025-10-19 20:08:52');
INSERT INTO `stock_movements` VALUES('49', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '7', '0', '1', 'None', NULL, '2030-01-01', 'Admin User', '6', 'active', NULL, '2025-10-19 20:45:21');
INSERT INTO `stock_movements` VALUES('50', '154', 'sales', 'Aluminium Hydroxide 120mg/Magnesium Trisilicate 250mg', 'Alugel Suspension, 100mL', '10', '0', '4', 'None', NULL, '2030-01-01', 'Admin User', '6', 'active', NULL, '2025-10-19 21:33:08');
INSERT INTO `stock_movements` VALUES('51', '1', 'sales', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', '1371', '0', '211', 'None', NULL, '2030-01-01', 'Admin User', '1160', 'active', NULL, '2025-10-19 21:37:46');
INSERT INTO `stock_movements` VALUES('52', '270', 'sales', 'Clotrimazole 1% w/w/Beclomethasone Dipropionate 0.025% w/w', 'Bulkot-B Cream', '10', '0', '2', 'None', NULL, '2030-01-01', 'Admin User', '8', 'active', NULL, '2025-10-20 13:26:52');
INSERT INTO `stock_movements` VALUES('53', '170', 'sales', 'Multivitamin Syrup', 'Filwel Kids, 100mL', '5', '0', '1', 'None', NULL, '2030-01-01', 'Admin User', '4', 'active', NULL, '2025-10-20 13:26:52');
INSERT INTO `stock_movements` VALUES('54', '131', 'sales', 'Ceftriaxone 1g', '\'GALAXY\'S SEFIN Injection', '50', '0', '11', 'None', NULL, '2030-01-01', 'Admin User', '39', 'active', NULL, '2025-10-21 09:57:28');
INSERT INTO `stock_movements` VALUES('55', '117', 'sales', 'Amoxicillin/Clavulanate 375mg', 'Acinet 375 Tablets', '94', '0', '8', 'None', NULL, '2030-01-01', 'Admin User', '86', 'active', NULL, '2025-10-21 10:03:36');
INSERT INTO `stock_movements` VALUES('56', '13', 'sales', 'Etoricoxib Tablet 60mg', 'Tory 60 Tablet', '30', '0', '21', 'None', NULL, '2030-01-01', 'Admin User', '9', 'active', NULL, '2025-10-21 12:57:15');
INSERT INTO `stock_movements` VALUES('57', '73', 'sales', 'Absorbent Cotton Wool 400mg', 'Velvex', '7', '0', '6', 'None', NULL, '2030-01-01', 'Admin User', '1', 'active', NULL, '2025-10-21 15:04:11');
INSERT INTO `stock_movements` VALUES('58', '277', 'sales', 'Betamethasone Valerate 0.1% w/w', 'MEDIVEN Cream 15g', '10', '0', '3', 'None', NULL, '2030-01-01', 'Admin User', '7', 'active', NULL, '2025-10-21 15:04:11');
INSERT INTO `stock_movements` VALUES('59', '257', 'sales', 'Condoms', 'Kiss Strawberry Condoms, 3s', '23', '0', '16', 'None', NULL, '2030-01-01', 'Admin User', '7', 'active', NULL, '2025-10-21 15:04:11');
INSERT INTO `stock_movements` VALUES('60', '154', 'sales', 'Aluminium Hydroxide 120mg/Magnesium Trisilicate 250mg', 'Alugel Suspension, 100mL', '6', '0', '2', 'None', NULL, '2030-01-01', 'Admin User', '4', 'active', NULL, '2025-10-21 15:50:45');
INSERT INTO `stock_movements` VALUES('61', '236', 'sales', 'Losartan 50mg/Hydrochlorothiazide 12.5mg', 'Angilock-Plus 50/12.5 Tablets', '150', '0', '10', 'None', NULL, '2030-01-01', 'Admin User', '140', 'active', NULL, '2025-10-21 15:50:45');
INSERT INTO `stock_movements` VALUES('62', '342', 'sales', 'Clindamycin 300mg', 'Clindacin-300 Capsules', '5', '0', '2', 'None', NULL, '2030-01-01', 'Admin User', '3', 'active', NULL, '2025-10-21 15:50:45');
INSERT INTO `stock_movements` VALUES('63', '1', 'sales', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', '1160', '0', '3', 'None', NULL, '2030-01-01', 'Admin User', '1157', 'active', NULL, '2025-11-07 12:12:34');
INSERT INTO `stock_movements` VALUES('64', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '6', '0', '1', 'None', NULL, '2030-01-01', 'Admin User', '5', 'active', NULL, '2025-11-07 12:12:34');
INSERT INTO `stock_movements` VALUES('65', '292', 'sales', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '6', '0', '3', 'None', NULL, '2030-01-01', 'Admin User', '3', 'active', NULL, '2025-11-07 12:13:30');
INSERT INTO `stock_movements` VALUES('66', '112', 'sales', 'Amoxicillin/Clavulanate 457mg/5mL', 'Augmentin 457 Suspension', '2', '0', '1', 'None', NULL, '2030-01-01', 'Admin User', '1', 'active', NULL, '2025-12-10 15:19:41');
INSERT INTO `stock_movements` VALUES('67', '309', 'sales', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', '67', '0', '1', 'None', NULL, '2030-01-01', 'Admin User', '66', 'active', NULL, '2025-12-10 15:19:41');
INSERT INTO `stock_movements` VALUES('68', '88', 'sales', 'Cefixime 400mg USP', 'Theofix - 400 Tablets', '90', '0', '10', 'None', NULL, '2030-01-01', 'Admin User', '80', 'active', NULL, '2025-12-10 19:14:46');
INSERT INTO `stock_movements` VALUES('69', '309', 'sales', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', '66', '0', '10', 'None', NULL, '2030-01-01', 'Admin User', '56', 'active', NULL, '2025-12-10 19:15:05');
INSERT INTO `stock_movements` VALUES('70', '36', 'sales', 'Paracetamol 120mg/5ml', 'Curamol Suspension 60mL', '46', '0', '12', 'None', NULL, '2030-01-01', 'Admin User', '34', 'active', NULL, '2025-12-10 19:15:05');
INSERT INTO `stock_movements` VALUES('71', '126', 'sales', 'Amoxicillin/Clavulanate 457mg/5mL', 'ACINET DRY SYRUP 457', '3', '0', '2', 'None', NULL, '2030-01-01', 'Admin User', '1', 'active', NULL, '2025-12-13 14:20:37');
INSERT INTO `stock_movements` VALUES('72', '113', 'sales', 'Amoxicillin/Clavulanate 228.5mg/5mL', 'Augmentin 228 Suspension', '3', '0', '1', 'None', NULL, '2030-01-01', 'Admin User', '2', 'active', NULL, '2025-12-13 14:29:13');
INSERT INTO `stock_movements` VALUES('73', '186', 'sales', 'Chlorpheniramine 2mg/Sodium Citrate 44mg/Guaifenesin 80mg/Ammonium Chloride 100mg/Levomenthol 0.8mg', 'Cadistin Expectorant, 100mL', '9', '0', '2', 'None', NULL, '2030-01-01', 'Admin User', '7', 'active', NULL, '2025-12-13 15:14:41');
INSERT INTO `stock_movements` VALUES('74', '173', 'sales', 'Cyproheptadine 4mg/ B Vitamins/Minerals', 'Becoactin Syrup, 200mL', '8', '0', '3', 'None', NULL, '2030-01-01', 'Admin User', '5', 'active', NULL, '2025-12-13 15:35:13');
INSERT INTO `stock_movements` VALUES('75', '342', 'sales', 'Clindamycin 300mg', 'Clindacin-300 Capsules', '3', '0', '2', 'None', NULL, '2030-01-01', 'Admin User', '1', 'active', NULL, '2025-12-13 15:36:14');
INSERT INTO `stock_movements` VALUES('76', '65', 'sales', 'Ampicillin/Cloxacillin 250mg/5mL', 'Ampiclo-Dawa Suspension', '20', '0', '11', 'None', NULL, '2030-01-01', 'Admin User', '9', 'active', NULL, '2025-12-13 15:36:54');
INSERT INTO `stock_movements` VALUES('77', '65', 'sales', 'Ampicillin/Cloxacillin 250mg/5mL', 'Ampiclo-Dawa Suspension', '9', '0', '7', 'None', NULL, '2030-01-01', 'Admin User', '2', 'active', NULL, '2025-12-13 17:36:47');
INSERT INTO `stock_movements` VALUES('78', '112', 'sales', 'Amoxicillin/Clavulanate 457mg/5mL', 'Augmentin 457 Suspension', '1', '0', '1', 'None', NULL, '2030-01-01', 'Admin User', '0', 'active', NULL, '2025-12-13 20:54:35');



CREATE TABLE IF NOT EXISTS `stocks` (
  `stockID` int NOT NULL AUTO_INCREMENT,
  `id` int NOT NULL,
  `transactionType` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `productname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brandname` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reorderLevel` int DEFAULT NULL,
  `openingBalance` double DEFAULT '0',
  `quantityIn` int DEFAULT NULL,
  `batch` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `receivedFrom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantityOut` int DEFAULT '0',
  `transDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `stockBalance` int DEFAULT NULL,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transBy` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`stockID`),
  KEY `idx_stocks_brandname_transDate` (`brandname`,`transDate` DESC)
) ENGINE=InnoDB AUTO_INCREMENT=459 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stocks` VALUES('1', '1', 'Purchase', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', '33', '0', '1978', 'T4945', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1978', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('2', '2', 'Purchase', 'Paracetamol Tablet BP 500mg', 'Cetamol 500mg', '33', '0', '1990', '251090', '2029-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1990', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('3', '3', 'Purchase', 'Piroxicam Capsules USP', 'Roxicam 20mg', '33', '0', '497', '325049', '2028-02-29 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '497', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('4', '4', 'Purchase', 'Meloxicam Tablet 7.5mg BP', 'Melostar 7.5mg', '33', '0', '487', 'T24G048', '2027-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '487', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('5', '5', 'Purchase', 'Soluble Paracetamol BP 1000mg', 'PARA NOVA ', '3', '0', '38', 'ZH5002', '2028-02-29 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '38', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('6', '6', 'Purchase', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', '3', '0', '40', 'DFF5825A', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '40', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('7', '7', 'Purchase', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', '3', '0', '30', '2308255', '2026-07-30 00:00:00', 'Philmed ', '0', '2025-09-14 14:55:08', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('8', '8', 'Purchase', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'Rilif - MR Tablets', '7', '0', '75', '2501049', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '75', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('9', '9', 'Purchase', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 500mg', 'Zyrtal - MR Tablets', '7', '0', '17', 'ZR0092409', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '17', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('10', '10', 'Purchase', 'Diclofenac 50mg/Paracetamol 500mg/Chlorzoxazone 250mg', 'DOLOACT - MR Tablets', '33', '0', '92', 'K00224AP', '2031-08-27 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '92', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('11', '11', 'Purchase', 'Paracetamol Tablet BP 500mg/Caffeine 65mg', 'Panadol Extra Tablets (Pairs)', '17', '0', '96', 'Y092AE', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '96', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('12', '12', 'Purchase', 'Paracetamol Tablet BP 500mg', 'Panadol Advance Tablets (Pairs)', '17', '0', '100', 'Y039BB', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('13', '13', 'Purchase', 'Etoricoxib Tablet 60mg', 'Tory 60 Tablet', '10', '0', '30', '4H00017', '2026-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('14', '14', 'Purchase', 'Etoricoxib Tablet 90mg', 'Tory 90 Tablet', '10', '0', '30', '5A0001', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('15', '15', 'Purchase', 'Etoricoxib Tablet 120mg', 'Tory 120 Tablet', '10', '0', '30', '4M00008', '2026-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('16', '16', 'Purchase', 'Paracetamol Tablet BP 500mg/Pseudoephedrine Hydrochloride 30mg/Chlorpheniramine Maleate 2mg', 'Panadol Cold&Flu Tablets', '8', '0', '48', 'EC128', '2027-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '48', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('17', '17', 'Purchase', 'Paracetamol Tablet BP 300mg/Pseudoephedrine Hydrochloride 30mg/Chlorpheniramine Maleate 2mg/Caffeine', 'FLU-GONE Capsules', '1', '0', '8', 'R34AI', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '8', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('18', '18', 'Purchase', 'Paracetamol Tablet BP 650mg/Phenylephrine Hydrochloride 5mg/Chlorpheniramine Maleate 2mg', 'CONTUS-650 Tablet', '1', '0', '5', '25-XCFT-217', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('19', '19', 'Purchase', 'Cetirizine Hydrochloride Tablet 10mg', 'CACHCET Tablet', '33', '0', '473', 'CCT24097E', '2026-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '473', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('20', '20', 'Purchase', 'Montelukast 10mg/Levocetirizine 5mg', 'Montallerg Tablets', '7', '0', '40', '2410112', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '40', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('21', '21', 'Purchase', 'Montelukast 10mg', 'Montana 10mg', '5', '0', '26', '7M0012401', '2026-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '26', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('22', '22', 'Purchase', 'Montelukast 10mg/Levocetirizine 5mg', 'Motechest', '10', '0', '58', 'T25044', '2025-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '58', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('23', '23', 'Purchase', 'Ephedrine 12mg/Theophyline 120mg', 'F-Tab (Franol) Tablet', '33', '0', '200', 'BPL937A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '200', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('24', '24', 'Purchase', 'Salbutamol Inhaler 100mcg', 'Medisalant 100mcg', '1', '0', '3', '2424112', '2027-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('25', '25', 'Purchase', 'Betamethasone 0.25mg/Dexchlorpheniramine Maleate BP 2mg', 'Celestinac Tablet', '10', '0', '147', 'T24146', '2024-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '147', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('26', '26', 'Purchase', 'Paracetamol 500mg/Chlorzoxazone 250mg', 'Myospaz Tablet', '33', '0', '100', 'B03725', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('27', '27', 'Purchase', 'Diclofenac 100mg', 'ZOFENAC 100', '33', '0', '300', 'T25B030', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('28', '28', 'Purchase', 'Predinsolone 5mg', 'Olsolone Tablets', '33', '0', '760', 'BPL919A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '760', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('29', '29', 'Purchase', 'Paracetamol 500mg/Hyoscine Butylbromide 10mg', 'Duxscospan Plus (Buscopan Plus Generic)', '7', '0', '100', '5814182A', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('30', '30', 'Purchase', 'Doxylamine Succ. 10mg/Pyridoxine 10mg ', 'NOSIC ', '7', '0', '60', '24070528', '2026-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '60', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('31', '31', 'Purchase', 'Metoclopramide 10mg', 'Emeton 10mg Tablet', '33', '0', '300', '1024097', '2027-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('32', '32', 'Purchase', 'Meloxicam EP Tablet 7.5mg', 'Melcam 7.5mg Tablet', '33', '0', '100', '5D00013', '2027-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('33', '33', 'Purchase', 'Meloxicam EP Tablet 15mg', 'Melcam 15mg Tablet', '17', '0', '50', '5F00010', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '50', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('34', '34', 'Purchase', 'Metoclopramide 10mg', 'Melasil - 10 Tablet', '33', '0', '100', '89180', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('35', '35', 'Purchase', 'Promethazine 25mg', 'Promethazine Tablet', '33', '0', '98', 'O125', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '98', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('36', '36', 'Purchase', 'Paracetamol 120mg/5ml', 'Curamol Suspension 60mL', '1', '0', '50', '2506189', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '50', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('37', '37', 'Purchase', 'Paracetamol 120mg/5ml', 'Curamol Suspension 100mL', '1', '0', '30', '2411024', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('38', '38', 'Purchase', 'Paracetamol 120mg/5ml', 'Calpol Suspension 60mL', '1', '0', '10', 'Y015BE', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('39', '39', 'Purchase', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '0', '49', 'L25E007', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '49', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('40', '40', 'Purchase', 'Ibuprofen 100mg/5ml', 'Triofen 100mL', '1', '0', '30', 'L25G073', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-19 14:56:17', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('41', '41', 'Purchase', 'Paracetamol 120mg/5ml', 'Panadol Baby&Infant 100mL', '1', '0', '5', 'YC32BB', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('42', '42', 'Purchase', 'Ibuprofen 100mg/Paracetamol 125mg BP', 'Brustan Suspension', '1', '0', '9', 'ALB0201', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-06 12:53:37', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('43', '43', 'Purchase', 'Soluble Paracetamol BP 1000mg', 'Parafast ET 1000 Tablet', '3', '0', '8', 'GE4007', '2027-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '8', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('44', '44', 'Purchase', 'Loratidine USP 10mg', 'Loratin Fast', '33', '0', '100', '5A00015', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('45', '45', 'Purchase', 'Hyoscine - N- Butylbromide 10mg', 'HYCIN 10 Tablets (Buscopan Generic)', '33', '0', '200', '2503117', '2028-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '200', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('46', '46', 'Purchase', 'Paracetamol 500mg/Hyoscine Butylbromide 10mg', 'Hismopan Plus Tablets (Buscopan Plus Generic)', '33', '0', '200', 'O223', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '200', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('47', '47', 'Purchase', 'Metronidazole 400mg', 'Tricozole - 400mg Tablet', '33', '0', '485', '88554', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '485', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('48', '48', 'Purchase', 'Tetracycline Eye Ointment USP', 'Metacycline', '1', '0', '20', 'S-444', '2027-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('49', '49', 'Purchase', 'Betamethasone 0.1% w/v/Neomycin 0.5 %w/v', 'Probeta-N', '1', '0', '9', 'FW021L', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('50', '50', 'Purchase', 'Ciprofloxacin/Dexamethasone Eye/Ear Drops', 'Ciploglax-D Eye Drops', '1', '0', '3', 'PE 1565', '2026-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('51', '51', 'Purchase', 'Ciprofloxacin 0.3% USP', 'Ciproken', '1', '0', '10', '4J03923', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('52', '52', 'Purchase', 'Dextran 70 USP 1mg/Hypromellose USP 3mg', 'Lubtear', '1', '0', '3', '5A00022', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('53', '53', 'Purchase', 'Ciprofloxacin 0.3%/Beclomethasone 0.025%/Clotrimazole 1%/Lignocaine 2% Ear Drops', 'Otobiotic', '1', '0', '5', 'BPL977A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('54', '54', 'Purchase', 'fluticasone propionate ', 'Flonaspray', '1', '0', '2', '5B01774', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('55', '55', 'Purchase', 'Esomeprazole', 'Protas 40 Tablets', '33', '0', '100', '5C00002', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('56', '56', 'Purchase', 'Esomeprazole', 'Nexium 20', '5', '0', '28', 'SR428', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '28', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('57', '57', 'Purchase', 'Omeprazole Satchets', 'Risek insta', '3', '0', '10', '58547', '2026-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('58', '58', 'Purchase', 'Omeprazole ', 'Omecos 20 Capsules', '33', '0', '473', '240013', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '473', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('59', '59', 'Purchase', 'Pantoprazole Delayed Release', 'Pantakind 40', '20', '0', '60', 'ASFO001', '2028-02-27 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '60', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('60', '60', 'Purchase', 'Clotrimazole Vaginal Tablets 200mg', 'Canazol Vaginal Tablet, 3s', '1', '0', '9', 'O625086', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('61', '61', 'Purchase', 'Ketocozole 200mg  BP', 'Hitoral 200mg Tablet', '33', '0', '200', 'BPL968A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '200', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('62', '62', 'Purchase', 'Amoxicllin 125mg/5mL', 'ELYMOX Suspension 100mL', '1', '0', '49', 'SE20', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '49', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('63', '63', 'Purchase', 'Co-trimoxazole 240mg/5mL', 'BIOTRIM 100mL', '1', '0', '20', '0725030', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('64', '64', 'Purchase', 'Co-trimoxazole 240mg/5mL', 'BIOTRIM 50mL', '1', '0', '19', '0725123', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '19', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('65', '65', 'Purchase', 'Ampicillin/Cloxacillin 250mg/5mL', 'Ampiclo-Dawa Suspension', '1', '0', '20', '2505046', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('66', '66', 'Purchase', 'Ampicillin/Cloxacillin 250mg/250mg', 'Ampiclo-Dawa 500 Capsule', '33', '0', '480', '2505228', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '480', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('67', '67', 'Purchase', 'Flucloxacillin 125mg/5mL', 'ELYFLOX 100mL Suspension', '1', '0', '20', '5F66', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('68', '68', 'Purchase', 'Azithromycin Oral Suspension 200mg', 'IzziThree 15mg', '1', '0', '20', 'BPL995A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('69', '69', 'Purchase', 'Vitamin B Complex', 'Neuro-Forte', '7', '0', '30', 'NBT2503', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('70', '70', 'Purchase', 'Levocetirizine 5mg', 'VIVACET L 5', '3', '0', '100', '12340', '2026-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('71', '71', 'Purchase', 'Maternity Pads', 'Medimax', '1', '0', '14', '21456', '2029-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '14', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('72', '72', 'Purchase', 'Maternity Pads', 'Medicott', '1', '0', '14', '1406', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '14', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('73', '73', 'Purchase', 'Absorbent Cotton Wool 400mg', 'Velvex', '1', '0', '10', '162507', '2030-07-16 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('74', '74', 'Purchase', 'Adult Diapers XL', 'MY A+ XLARGE', '3', '0', '20', 'JA09AD06', '2028-01-02 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('75', '75', 'Purchase', 'Adult Diapers L', 'MY A+ LARGE', '3', '0', '10', 'JA09AD07', '2028-04-10 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('76', '76', 'Purchase', 'Adult Pants L', 'ADFIT  PANTS LARGE', '10', '0', '30', 'AFADP2503', '2028-03-23 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('77', '77', 'Purchase', 'Sulphadoxine 500mg/Pyrimethamine 25mg', 'FANLAR Tablets', '8', '0', '24', '2502064', '2030-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '24', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('78', '78', 'Purchase', 'Terbinafine 250mg', 'Terbinaforce 250 Tablets', '10', '0', '30', 'A0FOY001', '2031-12-27 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('79', '79', 'Purchase', 'Fluconazole 150mg', 'NOCANZ 150 Tablet', '1', '0', '18', 'ONOCE-004', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '18', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('80', '80', 'Purchase', 'Griseofulvin 250mg', 'Grisolab-250 Tablet', '33', '0', '300', '88646', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('81', '81', 'Purchase', 'Griseofulvin 125mg', 'Biofulvin 125 Tablet', '33', '0', '200', '0125046', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '200', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('82', '82', 'Purchase', 'Artemether 20mg/Lumefantrine 120mg', 'LONART Tablet, 24s', '1', '0', '10', 'T3ACG060', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('83', '83', 'Purchase', 'Artemether 20mg/Lumefantrine 120mg', 'PANAART 20/120, 24s', '1', '0', '2', 'T285501', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('84', '84', 'Purchase', 'Povidone-Iodine USP 1%', 'Peardine Mouth Wash, 100mL', '1', '0', '3', 'P1110', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('85', '85', 'Purchase', 'Povidone-Iodine USP 1%', 'Rexe-Dine Mouth Wash', '1', '0', '3', '5814247A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('86', '86', 'Purchase', 'Amoxicillin 500mg', 'AMOXIMED 500 Capsule', '33', '0', '985', '706241037', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '985', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('87', '87', 'Purchase', 'Cefuroxime 500mg USP', 'Theoroxime 500 Tablets', '1', '0', '100', 'ECT240180', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('88', '88', 'Purchase', 'Cefixime 400mg USP', 'Theofix - 400 Tablets', '3', '0', '90', 'ECT250099', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '90', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('89', '89', 'Purchase', 'Azithromycin 500mg USP', 'AGYCIN-500 Tablet, 3s', '1', '0', '26', 'T1ATC009', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '26', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('90', '90', 'Purchase', 'Nitrofurantoin 100mg', 'NIFURAN', '33', '0', '200', '86236', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '200', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('91', '91', 'Purchase', 'Tinidazole 500mg', 'Tinizol 500 Tablets,4s', '1', '0', '20', '5370248', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('92', '92', 'Purchase', 'Amoxicillin 250mg', 'SPASMOX 250 Capsule', '33', '0', '1455', 'C25220', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1455', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('93', '93', 'Purchase', 'Doxycycline 100mg', 'XYCYCLINE 100 Capsule', '33', '0', '300', '250101', '2027-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('94', '94', 'Purchase', 'Secnidazole 1g', 'Secnida Forte Tablets, 2s', '1', '0', '15', 'BPL754A', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '15', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('95', '95', 'Purchase', 'Levonogestrel 0.15mg/Ethinylestradiol 0.03mg', 'Microgynon Fe Tablets', '1', '0', '10', 'KT0LA13', '2026-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('96', '96', 'Purchase', 'Sildenafil 50mg ', 'MTM-50 Tablet', '1', '0', '20', '2035', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('97', '97', 'Purchase', 'Sildenafil 100mg ', 'MTM-100 Tablet', '1', '0', '39', 'M-189010', '2028-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '39', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('98', '98', 'Purchase', 'Sildenafil 100mg ', 'Nelgra-100 Tablet', '1', '0', '40', '5814428A', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '40', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('99', '99', 'Purchase', 'Sildenafil 100mg ', 'VEGA-100 Tablet', '1', '0', '40', 'T24L036', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '40', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('100', '100', 'Purchase', 'Levonogestrel 0.75mg', 'Postinor-2 Tablets, 2s', '1', '0', '18', 'T46250B', '2029-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '18', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('101', '101', 'Purchase', 'levonogestrel 0.75mg', 'Safe-72 Tablets,2s', '1', '0', '29', 'OHT-068', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '29', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('102', '102', 'Purchase', 'Betamethasone Sodium Phosphate 2mg/ml/Betamethasone Dipropionate 5mg/ml Suspension for Inj,2ml', 'KRIDOFOS Injection', '1', '0', '1', 'S872302', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('103', '103', 'Purchase', 'Medroxyprogesterone Injection 150mg/mL', 'Lydia Contraceptive Injection', '1', '0', '5', 'EVM24045', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('104', '104', 'Purchase', 'Levonogestrel 0.15mg/Ethinylestradiol 0.03mg', 'Femiplan Tablets', '1', '0', '14', '4003883', '2030-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '14', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('105', '105', 'Purchase', 'Diclofenac 75mg Injection', 'CAREFENAC Injection', '3', '0', '20', '241064', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('106', '106', 'Purchase', 'Carbamazepine 200mg BP', 'Carbamazepine 200mg Tablets', '33', '0', '300', 'CAR409', '2027-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('107', '107', 'Purchase', 'Amitriptyline 25mg', 'Amitiptyline Tablets, Cosmos', '33', '0', '100', '240820', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('108', '108', 'Purchase', 'Phenobarbital 30mg', 'Phenobarbital Tablets, Cosmos', '33', '0', '300', '250813', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('109', '109', 'Purchase', 'Diazepam 5mg', 'Cozepam Tablets', '33', '0', '100', '250268', '2028-02-29 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('110', '110', 'Purchase', 'Maternity Pants', 'Dafi Maternity Pants, M-L', '2', '0', '6', '250412', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '6', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('111', '111', 'Purchase', 'Maternity Pants', 'Dafi Maternity Pants, S-L', '2', '0', '6', '250112', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '6', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('112', '112', 'Purchase', 'Amoxicillin/Clavulanate 457mg/5mL', 'Augmentin 457 Suspension', '1', '0', '2', 'NY3S', '2026-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('113', '113', 'Purchase', 'Amoxicillin/Clavulanate 228.5mg/5mL', 'Augmentin 228 Suspension', '1', '0', '3', 'WT9J', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('114', '114', 'Purchase', 'Tamsulosin 0.4mg', 'Tamsolin Capsules', '3', '0', '10', '192C45', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('115', '115', 'Purchase', 'Amoxicillin/Clavulanate 625mg', 'Labclav 625 Tablets', '1', '0', '140', '89399', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '140', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('116', '116', 'Purchase', 'Amoxicillin/Clavulanate 1000mg', 'Acinet 1000 Tablets', '1', '0', '50', 'SEBPT-0379', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '50', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('117', '117', 'Purchase', 'Amoxicillin/Clavulanate 375mg', 'Acinet 375 Tablets', '1', '0', '100', 'SEBPT-0358', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('118', '118', 'Purchase', 'Ciprofloxacin 500mg USP', 'Ciproglax 500mg Tablets', '33', '0', '495', 'CH0079', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '495', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('119', '119', 'Purchase', 'Finasteride 5mg/Tamsulosin 400mcg', 'FINOSIN Tablets', '10', '0', '30', '82503', '2027-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('120', '120', 'Purchase', 'Flucloxacillin Injection 500mg Vial', 'Flupene Injection', '1', '0', '16', 'MB25031', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '16', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('121', '121', 'Purchase', 'HydrocortisoneInjection 100mg', 'OCORTIN 100 Injection', '1', '0', '30', 'IP24168', '2031-05-27 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('122', '122', 'Purchase', 'Lidocaine Injection 20mg/mL, 30mL', 'LIDOCAINE 2% Injection', '1', '0', '5', '752505', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('123', '123', 'Purchase', 'Cefuroxime 125mg/5mL', 'Evorox, 50mL Suspension', '1', '0', '5', '4L166', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('124', '124', 'Purchase', 'Cefalexin 125mh/5mL', 'Leocef, 100mL Suspension', '1', '0', '5', '88578', '2027-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('125', '125', 'Purchase', 'Amoxicillin/Clavulanate 228.5mg/5mL', 'Labclav 228 Suspension', '1', '0', '10', '88914', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('126', '126', 'Purchase', 'Amoxicillin/Clavulanate 457mg/5mL', 'ACINET DRY SYRUP 457', '1', '0', '5', 'SEBPD-0195', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('127', '127', 'Purchase', 'Ampicillin 60mg/cloxacillin 30mg/0.6mL', 'Neonatal Ampiclox Drops', '1', '0', '2', 'A31226', '2026-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('128', '128', 'Purchase', 'Ampicillin 60mg/cloxacillin 30mg/0.6mL', 'Ampliclo-Dawa (Neonata Amplicox Gen)', '1', '0', '5', '2505124', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('129', '129', 'Purchase', 'Amlodipine 5mg/Losartan 50mg/Hydrochlorothiazide 12.5mg', 'Amlozaar-H Tablet', '10', '0', '60', 'ZDTP0064', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '60', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('130', '130', 'Purchase', 'Flucloxacillin 250mg/Amoxicillin 250mg', 'MoxaForte 500 Capsules, 20s', '1', '0', '3', '2409258', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('131', '131', 'Purchase', 'Ceftriaxone 1g', '\'GALAXY\'S SEFIN Injection', '1', '0', '50', 'CI-25C29A', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '50', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('132', '132', 'Purchase', 'Urine Bag', 'Urine Collection Bag, 2000mL', '1', '0', '5', '231099', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('133', '133', 'Purchase', 'Zinc Sulfate DT 20mg', 'Junior Zinc Tablets', '33', '0', '100', '2502060', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('134', '134', 'Purchase', 'Normal Saline Nasal Drops', 'Nosfree Saline Drops', '1', '0', '20', '225012', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('135', '135', 'Purchase', 'Hyoscine - N- Butylbromide 5mg/5mL', 'Hycin Syrup, 60mL', '1', '0', '5', '2504248', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('136', '136', 'Purchase', 'Orals Rehydration Salts', 'ORASOL, ORS', '1', '0', '100', '0425084', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('137', '137', 'Purchase', 'Ondasetron 2mg/5mL', 'EMITOSS Oral Solution, 30mL', '1', '0', '2', 'GE10219', '2027-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('138', '138', 'Purchase', 'Zinc Sulfate 20mg Syrup', 'TOTO-ZincOD Syrup', '1', '0', '9', 'L24F055', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('139', '139', 'Purchase', 'Nystatin 100,000 units', 'NYSTAL Suspension', '1', '0', '20', 'BPL966A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('140', '140', 'Purchase', 'Glucosamine/Chondroitin/Cod Liver/Omega 3', 'Cartil Omega Softgel Capsules', '10', '0', '30', 'S2410332', '2026-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('141', '141', 'Purchase', 'Calcium/Magnesium/Vitamin D', 'Osteocare Tablets', '10', '0', '90', 'OT2405PA', '2028-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '90', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('142', '142', 'Purchase', 'Calcium 320mg/Phosphorus 137.5mg', 'Purecal Chewable Tablets', '10', '0', '30', 'PUT7001', '2027-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('143', '143', 'Purchase', 'Calcium/Magnesium/Vitamin D', 'Zedcal Oral Suspension, 200mL', '1', '0', '2', '271P2409X', '2027-11-03 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('144', '144', 'Purchase', 'Lansoprazole/Tinidazole/Clarithromycin', 'Sure Kit, H. Pylori Kit', '1', '0', '2', 'B25032', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('145', '145', 'Purchase', 'Amoxicillin,clarithromycin,Esomeprazole', 'Esofag kit,H.Pylori kit', '1', '0', '2', 'EFGH 24005', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('146', '146', 'Purchase', 'lansoprazole/Amoxicilin/Clarithromycin', 'Pylotrip,H.Pylori Kit', '1', '0', '1', '5A00618', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('147', '147', 'Purchase', 'syringe 10cc', 'Syringe 10cc', '1', '0', '100', '113224', '2029-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('148', '148', 'Purchase', 'Syringe 5cc', 'Syringe 5cc', '1', '0', '100', '60725', '2030-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('149', '149', 'Purchase', 'Lactulose solution', 'Osmolax Suspension', '1', '0', '3', '4J00081', '2026-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('150', '150', 'Purchase', 'magaldrate and simethicone 200ml', 'Maganta Suspension', '1', '0', '3', '4j02364', '2026-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('151', '151', 'Purchase', 'Sodium alginate 500mg/sodium bicarbonate/calcium carbonate', ' Asynta Max 200mL', '1', '0', '3', '4j03420', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('152', '152', 'Purchase', 'Aluminium Hydroxide 365mg/magnesium hydroxyde/simethicone', 'Relcer Gel 180mL', '1', '0', '3', '10241131', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('153', '153', 'Purchase', 'Aluminium Hydroxide 365mg/magnesium hydroxyde/simethicone', 'Relcer Gel 100mL', '1', '0', '6', '10241479', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '6', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('154', '154', 'Purchase', 'Aluminium Hydroxide 120mg/Magnesium Trisilicate 250mg', 'Alugel Suspension, 100mL', '1', '0', '10', '5050', '2027-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('155', '155', 'Purchase', 'Aluminium oxide 200mg/Magnesium hydroxyde 400mg/simethicone 30mg', 'Nilacid 200mL Suspension', '1', '0', '5', '4F02513', '2026-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('156', '156', 'Purchase', 'Aluminium oxide 200mg/Magnesium hydroxyde 400mg/simethicone 30mg', 'Nilacid 100mL Suspension', '1', '0', '8', '4K01354', '2026-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '8', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('157', '157', 'Purchase', 'Aluminium Hydroxide 120mg/Magnesium Trisilicate 250mg', 'Gocid 100mL Suspension', '1', '0', '5', 'BPL853A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('158', '158', 'Purchase', 'Alginic Acid Aluminium Hydroxide 365mg/magnesium hydroxyde/simethicone ', 'ULGICID Suspension 200mL', '1', '0', '2', 'UCS6004', '2027-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('159', '159', 'Purchase', 'Sucralfate/Oxetacaine', 'Sucrafil O Gel Suspension, 100mL', '1', '0', '4', 'M0220', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '4', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('160', '160', 'Purchase', 'Aluminium Hydroxide 300mg/magnesium hydroxyde 150mg/simethicone 100mg', 'Benagas Gel, 100mL', '1', '0', '5', '00525', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('161', '161', 'Purchase', 'Aluminium Hydroxide 150mg/Magnesium Trisilicate 250mg', 'Tryactin Suspension, 100mL', '1', '0', '5', '87406', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('162', '162', 'Purchase', 'Magaldrate 480mg and simethicone 20mg', 'Magnacid Gel 100mL', '1', '0', '5', '87914', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('163', '163', 'Purchase', 'Ferric Ammonium Citrate 200mg/Folic 1.5mg/Cyanocobalamin 50mcg/Ethanol', 'Ranferon 12 Suspension, 200mL', '1', '0', '5', 'ALB0212', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('164', '164', 'Purchase', 'Dried Ferrous Sulphate 200mg/Folic Acid 0.4mg', 'Ferrolic-LF Tablets, IFAS', '33', '0', '500', '89377', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '500', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('165', '165', 'Purchase', 'Ferrous Fumarate 305mg/Folic Acid 0.75mg/Cyanocobalamin 5mcg/Ascorbic Acid 75mg/Zinc Sulphate 5mg', 'Ranferon Capsules', '10', '0', '60', 'DFF062A', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '60', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('166', '166', 'Purchase', 'Iron 50mg/Manganese 1.33mg/Copper 0.70mg', '\'Tot\'Hema Ampoules', '7', '0', '20', '71520', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('167', '167', 'Purchase', 'Diloxanide Furoate 250mg/Metronidazole 200mg/Dicyclomine 10mg/5mL', 'Entamaxin Oral Suspension, 100mL', '1', '0', '9', '00468SEX', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('168', '168', 'Purchase', 'Metronidazole 200mg/5mL', 'Amizole Oral Suspension', '1', '0', '10', 'BPL731A', '2027-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('169', '169', 'Purchase', 'Metronidazole Benzoate 200mg/5mL', 'Tricozole - 200mg Suspension', '1', '0', '9', '86564', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('170', '170', 'Purchase', 'Multivitamin Syrup', 'Filwel Kids, 100mL', '1', '0', '5', '5A00918', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('171', '171', 'Purchase', 'Secnidazole 750mg/15mL', 'Secnida for Children, 15mL', '1', '0', '2', 'BPL468A', '2026-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('172', '172', 'Purchase', 'Cyproheptadine 4mg/ B Vitamins/Minerals', 'Becoactin Tablets', '10', '0', '60', '324P2470X', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '60', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('173', '173', 'Purchase', 'Cyproheptadine 4mg/ B Vitamins/Minerals', 'Becoactin Syrup, 200mL', '10', '0', '8', '018P2499X', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '8', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('174', '174', 'Purchase', 'Cyproheptadine 2mg/ B Vitamins/Minerals', 'Cypro B Plus Syrup, 200mL', '1', '0', '4', 'GE80246', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '4', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('175', '175', 'Purchase', 'Cyproheptadine 2mg/ Tricholine Citrate 275mg', 'Cypon Syrup,100mL', '1', '0', '5', 'GC044204', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('176', '176', 'Purchase', 'Ambroxol Hydrochloride 15mg/5mL', 'Ambroxol Expectorant Syrup, 100mL', '1', '0', '5', '4J01839', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('177', '177', 'Purchase', 'Adovas Syrup', 'Adovas Syrup, 100mL', '1', '0', '5', '4K02246', '2026-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('178', '178', 'Purchase', 'Sulbutamol 1mg/Bromhexine 2mg/Guaifenesin 50mg', 'Cadistin Plus, 100mL', '1', '0', '5', 'L24067', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('179', '179', 'Purchase', 'Chlorpheniramine 2mg/Sodium Citrate 44mg/Guaifenesin 80mg/Ammonium Chloride 100mg/Levomenthol 0.8mg', 'Cadiphen Syrup, 100mL', '1', '0', '10', 'N66E5009', '2029-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('180', '180', 'Purchase', 'Dextromethorphan 10mg/Chlorpheniramine 2mg/Pseudoephedrine 30mg', 'Flu-Gone DM, 60mL', '1', '0', '3', 'P35AB', '2027-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('181', '181', 'Purchase', 'Dextromethorphan 10mg/Chlorpheniramine 2mg/Pseudoephedrine 30mg/Paracetamol', 'Flu-Gone P+, 60mL', '1', '0', '3', 'R37AA', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('182', '182', 'Purchase', 'Promethazine 2.5mg/Diphenhydramine 5mg/Ammonium Cl 90mg/Sodium Citrate 45mg/Ephedrine Hcl 7.5mg', 'Tridex Cough Mixture,100mL', '1', '0', '19', '250722', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '19', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('183', '183', 'Purchase', 'Dextromethorphan 10mg/Cetirizine 5mg/Phenylephrine Hcl 5mg', 'Zefcolin Dry Cough Formula Syrup, 100mL', '1', '0', '9', 'ME25A028', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('184', '184', 'Purchase', 'SalbutamoL Sulfate 2mg/Bromhexine Hcl 4mg/Guaifenesin 100mg/ Menthol 1mg', 'Ascoril Expectorant 100mL', '1', '0', '10', '05241548A', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('185', '185', 'Purchase', 'SalbutamoL Sulfate 2mg/Bromhexine Hcl 4mg/Guaifenesin 100mg/ Menthol 1mg', 'Ascoril Expectorant 200mL', '1', '0', '5', '05240538', '2026-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('186', '186', 'Purchase', 'Chlorpheniramine 2mg/Sodium Citrate 44mg/Guaifenesin 80mg/Ammonium Chloride 100mg/Levomenthol 0.8mg', 'Cadistin Expectorant, 100mL', '1', '0', '9', 'L25041', '2029-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('187', '187', 'Purchase', 'Chlorpheniramine 2mg/Pseudoephedrine 10mg/Paracetamol 120mg', 'Coldcap Syrup, 100mL', '1', '0', '4', '242157', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '4', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('188', '188', 'Purchase', 'Diphenhydramine 10mg/Promethazine 5mg/Ammonium Chloride 180mg/Sodium Citrate 90mg', 'Benahist Syrup, 60mL', '1', '0', '9', '00825', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('189', '189', 'Purchase', 'Diphenhydramine 10mg/Promethazine 5mg/Ammonium Chloride 180mg/Sodium Citrate 90mg', 'Benahist Syrup, 100mL', '1', '0', '8', '00925', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '8', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('190', '190', 'Purchase', 'Chlorpheniramine 2mg/Pseudoephedrine 30mg/Guaifenesin 100mg', 'Trimex Diabetic', '1', '0', '4', '240466', '2027-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '4', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('191', '191', 'Purchase', 'Ambroxol Hydrochloride 15mg/5mL', 'Mucosolvan Syrup,100mL', '1', '0', '4', '235086', '2026-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '4', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('192', '192', 'Purchase', 'Chlorpheniramine 2mg/Pseudoephedrine 30mg/Sodium Citrate 44mg/Dextromethorphan 10mg/Menthol 1mg', 'Coscof-DM Linctus,100mL', '1', '0', '6', '251012', '2028-02-29 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '6', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('193', '193', 'Purchase', 'Cod Liver Oil, Vitamin A&D, Calcium', '\'Scott\'s Emulsion, Original, 100mL, Syrup', '1', '0', '1', 'Y069AE', '2026-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('194', '194', 'Purchase', 'Cod Liver Oil, Vitamin A&D, Calcium', '\'Scott\'s Emulsion, Orange Flavour, 100mL, Syrup', '1', '0', '3', 'Y014AH', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('195', '195', 'Purchase', 'Bonnisan', 'Bonnisan, 120mL, Syrup', '1', '0', '5', 'AUS-83', '2027-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('196', '196', 'Purchase', 'Sodium Bicarbonate 50mg/Terpeneless Dillseed Oil 2.15mg', 'Gripe Water', '1', '0', '5', '2408145', '2027-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('197', '197', 'Purchase', 'Levosalbutamol 1mg/5mg', 'Levostar, 100mL Syrup', '1', '0', '6', '4D02043', '2026-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '6', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('198', '198', 'Purchase', 'Levocetirizine 2.5mg', 'ALERFREE Syrup,60mL', '1', '0', '2', 'B24102', '2026-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('199', '199', 'Purchase', 'Desloratidine 2.5mg/5mL', 'DESOSTAR Syrup, 60mL', '1', '0', '2', 'LL032', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('200', '200', 'Purchase', 'Loratidine USP 5mg/5mL', 'Lorhistina Syrup,  60mL', '1', '0', '3', '240056', '2026-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('201', '201', 'Purchase', 'Predinsolone 5mg/5mL', 'Olsolone Syrup, 50mL', '1', '0', '10', 'BPL944A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('202', '202', 'Purchase', 'Promethazine Hcl, 5mg/5mL', 'Largan, Syrup', '1', '0', '29', 'L25F033', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '29', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('203', '203', 'Purchase', 'Chlorpheniramine Maleate 4mg', 'Dawa-CPM Tablets, 4mg', '33', '0', '200', '2504071', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '200', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('204', '204', 'Purchase', 'Chlorpheniramine Maleate 2mg/5mL', 'Dawa-CPM Syrup, 60mL', '1', '0', '49', '2506061', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '49', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('205', '205', 'Purchase', 'Cetirizine HCL 5mg/5mL', 'CetriPlain, 60mL Syrup', '1', '0', '47', '125214', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '47', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('206', '206', 'Purchase', 'Sodium Lactate IV Infusion', 'Ringer - Lactate, 500mL,IV Infusion', '1', '0', '5', '051058', '2027-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('207', '207', 'Purchase', 'Sodium Chloride, 0.9%', 'VIDASAL, 500mL, IV Infusion (Normal Saline)', '1', '0', '10', '2505014', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('208', '208', 'Purchase', 'Erythromycin 125mg/5mL', 'Erythrox 100mL', '1', '0', '10', '2504049', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('209', '209', 'Purchase', 'Clarithromycin 500mg', 'Aziclar-500 Tablets', '1', '0', '10', 'KD-300', '2027-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('210', '210', 'Purchase', 'Diloxanide Furoate 500mg/Metronidazole 400mg', 'Diracip-MDS, Tablets,15s', '1', '0', '10', '5EC0525', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('211', '211', 'Purchase', 'Azithromycin 1g/Fluconazole 150mg/Secnidazole 1g', 'AZFLOSEC KIT', '1', '0', '30', 'T25036', '2028-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('212', '212', 'Purchase', 'Ciprofloxacin 500mg/Tinidazole 600mg', 'CIPRO-T, Tablets', '1', '0', '10', '2015', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('213', '213', 'Purchase', 'Cefalexin 500mg', 'Felaxin 500 Capsules', '1', '0', '200', 'C3064006', '2027-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '200', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('214', '214', 'Purchase', 'Tramadol 50mg', 'Metadol Capsules', '33', '0', '300', 'C25D010', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('215', '215', 'Purchase', 'Cefadroxil 500mg', 'DROX 500', '1', '0', '10', 'ODXCB23004A', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('216', '216', 'Purchase', 'Pregnancy Test Strip', 'Pregnancy Test Strip', '1', '0', '43', 'HPS2502015', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '43', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('217', '217', 'Purchase', 'Metformin HCl 500mg', 'Glucophage 500 Tablets', '30', '0', '450', 'C24063', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '450', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('218', '218', 'Purchase', 'Metformin HCl 850mg', 'Glucophage 850 Tablets', '20', '0', '120', 'C24027', '2027-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '120', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('219', '219', 'Purchase', 'Norethisterone 5mg', 'Primolut N Tablets', '10', '0', '30', 'WEX92W', '2029-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('220', '220', 'Purchase', 'Clomifene 50mg', 'Clophene 50mg, Tablets', '10', '0', '26', 'BPL879A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '26', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('221', '221', 'Purchase', 'Fluconazole 150mg/Azithromycin 1g/Secnidazole 1g', 'VDM KIT, Tablets', '1', '0', '2', 'T08700124', '2027-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('222', '222', 'Purchase', 'Glibenclamide 5mg', 'Nogluc 5mg, 28s Tablets', '9', '0', '266', '250319', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '266', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('223', '223', 'Purchase', 'Metformin 500mg', 'Sukarmin 500mg Tablets', '33', '0', '1000', 'BPL865A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1000', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('224', '224', 'Purchase', 'Amlodipine 5mg', 'Varinil 5 Tablets', '9', '0', '140', '250241', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '140', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('225', '225', 'Purchase', 'Hydrochlorothiazide 50mg', 'HYMET Tablets', '33', '0', '500', '88345', '2028-02-29 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '500', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('226', '226', 'Purchase', 'Acetylsalicylic Acid 75mg', 'Ascard 75 Tablets', '10', '0', '300', 'AR038L', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('227', '227', 'Purchase', 'Metformin HCl 850mg', 'Glucomet 850 Tablets', '19', '0', '280', '240201', '2027-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '280', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('228', '228', 'Purchase', 'Glibenclamide 5mg', 'Nogluc 5mg, 112s Tablets', '37', '0', '224', '250323', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '224', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('229', '229', 'Purchase', 'Anusol', 'Anusol Suppositories', '4', '0', '20', '2309', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '20', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('230', '230', 'Purchase', 'Atenolol 50mg', 'Cardinol 50 Tablets, 28s', '9', '0', '280', '250801', '2028-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '280', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('231', '231', 'Purchase', 'Losartan 50mg', 'Amlozaar 50 Tablets', '10', '0', '300', 'ZATP0171', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '300', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('232', '232', 'Purchase', 'Carvedilol 12.5mg', 'Vidol 12.5 Tablets', '9', '0', '56', '241058', '2026-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '56', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('233', '233', 'Purchase', 'Carvedilol 6.25mg', 'Vidol 6.25 Tablets', '9', '0', '56', '241058', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '56', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('234', '234', 'Purchase', 'Nifedipine 20mg', 'Nicardin-SR Tablets', '33', '0', '1000', '88131', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1000', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('235', '235', 'Purchase', 'Losartan 50mg', 'Angilock 50 Tablets', '10', '0', '150', 'SE00015', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '150', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('236', '236', 'Purchase', 'Losartan 50mg/Hydrochlorothiazide 12.5mg', 'Angilock-Plus 50/12.5 Tablets', '10', '0', '150', 'SC00014', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '150', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('237', '237', 'Purchase', 'Atenolol 50mg', 'Lonet 50 Tablets', '33', '0', '80', '11509471', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '80', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('238', '238', 'Purchase', 'Atorvastatin 20mg ', 'Avastatin 20 Tablets', '9', '0', '28', '250230', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '28', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('239', '239', 'Purchase', 'Atorvastatin 20mg ', 'Atsta*20 Tablets', '10', '0', '120', 'AP12824', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '120', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('240', '240', 'Purchase', 'Enalapril 10mg', 'Dawapril 10 Tablet', '33', '0', '100', '2503121', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('241', '241', 'Purchase', 'Enalapril 5mg', 'Dawapril 5 Tablet', '33', '0', '100', '2505087', '2027-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('242', '242', 'Purchase', 'Co-trimoxazole 480mg Tablets', 'CO-TRI 480 Tablets', '33', '0', '100', '5B76', '2029-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('243', '243', 'Purchase', 'Co-trimoxazole 960mg Tablets', 'Co-trimoxazole 960 Tablets', '33', '0', '100', 'COO506', '2028-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('244', '244', 'Purchase', 'Blood Lancets', 'Blood Lancets Pieces', '33', '0', '100', '24321', '2029-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('245', '245', 'Purchase', 'Benzyl Benzoate Application 25%', 'Scabees Application, 100mL', '1', '0', '5', '240026', '2026-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('246', '246', 'Purchase', 'Toothpaste', 'ELEDENT TOOTHPASTE, 75MG', '1', '0', '3', 'ELE123', '2029-05-27 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('247', '247', 'Purchase', 'Toothpaste', 'ELEDENT TOOTHPASTE, 150MG', '1', '0', '6', 'ELE124', '2029-05-27 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '6', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('248', '248', 'Purchase', 'Calcium Antiacids', 'ENO Tablets (Pairs)', '33', '0', '50', 'Y052AF', '2029-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '50', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('249', '249', 'Purchase', 'Touch ang Go', 'Touch and Go', '1', '0', '5', 'BN 6052', '2029-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('250', '250', 'Purchase', 'Hydrocortisone Ointment 1% w/w', 'ELYCORT 15G OINTMENT', '1', '0', '5', '3J109', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('251', '251', 'Purchase', 'Hydrocortisone Cream 1% w/w', 'ELYCORT 15G CREAM', '1', '0', '5', '4C91', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('252', '252', 'Purchase', 'Neomycin 5mg/Bacitracin 2.5mg/Gramicidin 0.5mg Powder', 'GRABACIN POWDER 10GM', '1', '0', '3', 'MA018J', '2026-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('253', '253', 'Purchase', 'Neomycin 5mg/Bacitracin 250 IU', 'NEBANOL POWDER 5MG', '1', '0', '5', '4L00231', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('254', '254', 'Purchase', 'Neomycin 3.5mg/Bacitracin 5000 units/Bacitracin 500 units', 'GRABACIN 3 Ointment', '1', '0', '3', '24010', '2027-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('255', '255', 'Purchase', 'Hydrocortisone Ointment 1% w/w', 'HYDROTOPIC Ointment 15g Tube', '1', '0', '10', '1124101', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('256', '256', 'Purchase', 'Condoms', 'Kiss Classic Condoms, 3s', '8', '0', '23', 'L38241105', '2029-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '23', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('257', '257', 'Purchase', 'Condoms', 'Kiss Strawberry Condoms, 3s', '8', '0', '23', 'L34241102', '2029-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '23', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('258', '258', 'Purchase', 'Condoms', 'Kiss Studded Condoms, 3s', '8', '0', '24', 'L43250101', '2029-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '24', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('259', '259', 'Purchase', 'Condoms', 'Kiss Chocolate Condoms, 3s', '8', '0', '24', 'L32241101', '2029-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '24', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('260', '260', 'Purchase', 'Condoms', 'Durex Fetherlite Ultra, 3s', '1', '0', '1', '1002564779', '2029-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('261', '261', 'Purchase', 'Condoms', 'Durex Extra Safe, 3s', '1', '0', '1', '1002662224', '2029-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('262', '262', 'Purchase', 'Condoms', 'TRUST RIBBED, 3s', '8', '0', '22', 'BR45RG003', '2029-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '22', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('263', '263', 'Purchase', 'Condoms', 'TRUST CLASSIC, 3s', '8', '0', '23', 'B45RG042', '2029-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '23', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('264', '264', 'Purchase', 'Condoms', 'TRUST STUDDED, 3s', '8', '0', '22', 'BD45RG008', '2029-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '22', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('265', '265', 'Purchase', 'Bandages', 'Crepe Bandage Spandex 5cm', '1', '0', '10', 'CJ25173', '2030-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('266', '266', 'Purchase', 'Bandages', 'Crepe Bandage Spandex 7.5cm', '1', '0', '12', 'S250428', '2027-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '12', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('267', '267', 'Purchase', 'Bandages', 'Crepe Bandage Spandex 15cm', '1', '0', '12', 'CJ25099', '2030-03-09 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '12', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('268', '268', 'Purchase', 'Bandages', 'Crepe Bandage Spandex 10cm', '1', '0', '12', 'S250428', '2030-04-27 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '12', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('269', '269', 'Purchase', 'Diclofenac Gel 1%', 'DICLOGEN 1% GEL', '1', '0', '28', 'E25067', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '28', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('270', '270', 'Purchase', 'Clotrimazole 1% w/w/Beclomethasone Dipropionate 0.025% w/w', 'Bulkot-B Cream', '1', '0', '10', 'BPL829A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('271', '271', 'Purchase', 'Betamethasone Dipropionate, Gentamicin & Clotrimazole  Cream', 'Xtraderm Cream', '1', '0', '8', 'C534', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '8', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('272', '272', 'Purchase', 'Clotrimazole 10.0% w/w/Betamethasone 0.5mg', 'Clozole-B Cream', '1', '0', '8', '5371174', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '8', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('273', '273', 'Purchase', 'Clotrimazole 1.0% w/w', 'Clozole Cream', '1', '0', '18', '4130835', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '18', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('274', '274', 'Purchase', 'Hydrocortisone Cream 1% w/w', 'OLCORT 15G Cream', '1', '0', '10', 'BPL841A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('275', '275', 'Purchase', 'Beclomethasone 0.025%, Miconazole 2%, Neomycin Sulphate 0.5% Chlorocresol 0.25%', 'Beclomin Ointment 15g', '1', '0', '9', '2444', '2026-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('276', '276', 'Purchase', 'Betamethasone Valerate 0.1% w/w', 'MEDIVEN Ointment 15g', '1', '0', '2', '250761', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('277', '277', 'Purchase', 'Betamethasone Valerate 0.1% w/w', 'MEDIVEN Cream 15g', '1', '0', '10', '250459', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('278', '278', 'Purchase', 'Betamethasone Valerate 0.1% w/w/Salicylic acid 3% w/w', 'MEDIVEN-S Ointment 15g', '1', '0', '4', '250744', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '4', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('279', '279', 'Purchase', 'Silver Sulfadiazine 1% w/w', 'Dermazine Cream 15g', '1', '0', '6', '2505144', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '6', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('280', '280', 'Purchase', 'Calamine Lotion', 'COVIGEN Calamine Lotion, 100mL', '1', '0', '9', '1462CCL', '2028-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '9', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('281', '281', 'Purchase', 'Surgical Spirit 70% v/v', 'COVIGEN Surgical Spirit, 50mL', '1', '0', '29', '1472CSS', '2028-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '29', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('282', '282', 'Purchase', 'Povidone-Iodine USP 10% w/v', 'FAHOLO POVIDONE IODINE, 50mL', '1', '0', '5', '11125 FPI', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('283', '283', 'Purchase', 'Silver Sulfadiazine 1% w/w', 'Dermazine Cream 1% w/w, Dawa, 100g', '1', '0', '3', '2409312', '2028-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('284', '284', 'Purchase', 'Silver Sulfadiazine 1% w/w', 'Dermazine Cream 1% w/w, Dawa, 250g', '1', '0', '2', '2506015', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('285', '285', 'Purchase', 'Chlorine 3.5% w/v', 'FAHOLO Sodium Hypochlorite 3.5% w/v', '1', '0', '3', '22725 FSH', '2027-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('286', '286', 'Purchase', 'Sterile Paraffin Dressing ', 'Sterifin Dressing Gauze, 10x10', '3', '0', '10', 'CJ25083', '2030-03-09 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('287', '287', 'Purchase', 'Sodium Bicarbonate 300mg ', 'SodaMint Tablets', '33', '0', '100', '86974', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('288', '288', 'Purchase', 'Indomethacin 25mg', 'Caredomet 25mg Capsules, Indocid', '33', '0', '185', '241103', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '185', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('289', '289', 'Purchase', 'Esomeprazole 20mg', 'ESOMAC 20', '9', '0', '26', '4EC0596', '2026-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '26', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('290', '290', 'Purchase', 'Esomeprazole 20mg', 'ESOMAC 40', '5', '0', '26', '4EC0698', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '26', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('291', '291', 'Purchase', 'Albendazole 400mg', 'Zentel 400 Tablet', '1', '0', '5', 'A75S', '2029-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('292', '292', 'Purchase', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '1', '0', '5', 'BPL557A', '2026-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('293', '293', 'Purchase', 'Ivermectin BP 6mg', 'Iverkot-6', '3', '0', '10', 'BPL587A', '2026-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('294', '294', 'Purchase', 'Lidocaine HCl/Cetylpyridinium', 'Dentinox 10g', '1', '0', '2', 'AD', '2026-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('295', '295', 'Purchase', 'Trimetabol', 'Trimetabol Solution', '1', '0', '3', 'LMH013', '2028-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('296', '296', 'Purchase', 'Diclofenac/Linseed/Methyl Salicylate/Racementhol/Benzyl Alcohol', 'VOLINI GEL, 100MG', '1', '0', '2', 'SXF2400A', '2026-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('297', '297', 'Purchase', 'Good Morning Lung Tonic', 'Good Morning, 60mL', '1', '0', '5', '2505110', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('298', '298', 'Purchase', 'Kofgon Red', 'KOFGON Syr, 60mL', '1', '0', '5', 'L25H080', '2028-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('299', '299', 'Purchase', 'Tricohist ', 'Tricohist, 100mL', '1', '0', '5', '725055', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('300', '300', 'Purchase', 'Tricohist ', 'Tricohist, 60mL', '1', '0', '5', '0625096', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('301', '301', 'Purchase', 'Diphenhydramine 10mg/Promethazine 5mg/Ammonium Chloride 180mg', 'Tripozed, Expectorant, 100mL', '1', '0', '5', 'I25246', '2028-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('302', '302', 'Purchase', 'Diclofenac Sodium Slow Release 100mg', 'Diclomol SR 100mg, Tablets', '33', '0', '1', 'C00225', '2028-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '1', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('303', '303', 'Purchase', 'Tretinoine 0.05% w/w', 'Acnesol Cream, 25g', '1', '0', '4', '14628', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '4', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('304', '304', 'Purchase', 'Telmisartan 80mg/Amlodipine 5mg', 'AMTEL 80/5 Tabs', '10', '0', '30', '1241270', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '30', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('305', '305', 'Purchase', 'Sulfadoxine/Pyrimethamine', 'Malodar Tbalets, 3s', '1', '0', '10', '87306', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('306', '306', 'Purchase', 'Flucloxacillin/Amoxicillin 250mg/5mL', 'MoxaForte Suspension, 100mL', '1', '0', '3', '2505213', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('307', '307', 'Purchase', 'Sensodyne Toothpaste', 'Sensodyne Multicare, 40mL', '1', '0', '2', 'Y0248C', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('308', '308', 'Purchase', 'Sildenafil 100mg/5g', 'KAMAGRA ORAL JELLY 100MG', '1', '0', '7', 'CT00375', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '7', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('309', '309', 'Purchase', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', '3', '0', '100', 'BPL897A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-14 14:55:08', '80', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('310', '310', 'Purchase', 'Canullas G22 (Blue)', 'Canullas G22 (Blue)', '1', '0', '5', '2505282', '2030-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('311', '311', 'Purchase', 'Canullas G24(Yellow)', 'Canullas G24(Yellow)', '1', '0', '5', '3146425D', '2030-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('312', '312', 'Purchase', 'FloraNorm Satchets', 'FloraNorm Satchets', '3', '0', '10', '53B24039A', '2026-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('313', '313', 'Purchase', 'Amoxicillin 1g/Clavulanate 200mg', 'GAMOK Injection, Vial', '1', '0', '5', '240921', '2026-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('314', '314', 'Purchase', 'Insulin Syringes, 0.5mL', 'Insulin Syringes, 0.5mL', '3', '0', '10', '4142075 B', '2029-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('315', '315', 'Purchase', 'Insulin Syringes, 1mL', 'Insulin Syringes, 1mL', '3', '0', '10', '2276334 D', '2027-10-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('316', '316', 'Purchase', 'Diclofenac/Paracetamol/Chlorzoxazone', 'LOBAK Tablets, 100s', '33', '0', '100', 'GL015004', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('317', '317', 'Purchase', 'Tranexamic Acid 500mg/5mL', 'MENOZIP INJ, 500mg/5mL', '2', '0', '5', 'BMZ-2403', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('318', '318', 'Purchase', 'Carbocisteine 100mg', 'NASITHIOL INFANT, 60mL', '1', '0', '5', '225', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('319', '319', 'Purchase', 'Carbocisteine 100mg/Promethazine 2.5mg', 'NASITHIOL PROM, 100mL', '1', '0', '5', '425', '2028-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('320', '320', 'Purchase', 'Carbocisteine 100mg/Promethazine 2.5mg', 'NASITHIOL PROM, 60mL', '1', '0', '5', '325', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('321', '321', 'Purchase', 'Needle G21', 'Needle G21', '33', '0', '100', '83', '2028-04-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('322', '322', 'Purchase', 'Needle G23', 'Needle G23', '33', '0', '98', 'N0311625', '2030-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '98', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('323', '323', 'Purchase', 'Omeprazole Inj 40mg', 'Ompac 40mg', '1', '0', '4', '64563', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '4', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('324', '324', 'Purchase', 'Ondasetron 4mg/2mL', 'ONDEX 4MG', '2', '0', '5', 'BOD-2501', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('325', '325', 'Purchase', 'Dihydroartemisinin 40mg/Piperaquine 320mg', 'P-ALAXIN 9S', '1', '0', '5', 'D1AFN006', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('326', '326', 'Purchase', 'Paracetamol/Codeine Phosphate/Doxylamine, Caffeine', 'TAMEPYN, 20S', '7', '0', '40', 'BPL970A', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '40', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('327', '327', 'Purchase', 'Lubricating Jelly', 'Veri-Lube, 42g', '1', '0', '3', '30386', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('328', '328', 'Purchase', 'Carbocisteine 100mg/Promethazine 2.5mg', 'Vithiol Syrup, 125mg', '1', '0', '3', 'L24M050', '2027-11-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('329', '329', 'Purchase', 'Timolol Eye Drops 0.5%', 'TIMOGLAX EYE DROPS, 5mL', '1', '0', '3', 'HE12712', '2026-09-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('330', '330', 'Purchase', 'Herbal Cough Lozenges', 'ZECUF LOZENGES, LEMON', '7', '0', '10', 'KHZ23005', '2028-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('331', '331', 'Purchase', 'Herbal Cough Lozenges', 'ZECUF LOZENGES, ORANGE', '7', '0', '10', 'KOJ23009', '2028-07-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('332', '332', 'Purchase', 'Miconazole Nitrate 2%w/w', 'MUCOBEN CREAM', '1', '0', '5', '223', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('333', '333', 'Purchase', 'Medicated Soap', 'TETMOSOL Medicated Soap', '1', '0', '5', 'FSM23', '2028-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('334', '334', 'Purchase', 'Strepsils', 'Strepsils Soothing, Honey & Lemon', '33', '0', '50', 'ABD1921', '2027-04-02 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '50', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('335', '335', 'Purchase', 'Strepsils', 'Strepsils Regular', '33', '0', '50', 'ABD0045', '2027-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '50', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('336', '336', 'Purchase', 'Mupirocin Ointment 2%', 'Zupricin Ointment, 15g', '1', '0', '2', '10242365', '2026-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('337', '337', 'Purchase', 'Mupirocin 2%/Betamethasone 0.5% Ointment', 'Zupricin B Ointment, 15g', '1', '0', '2', '10250108', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('338', '338', 'Purchase', 'Anti-Rabies Vaccine', 'Anti-Rabies Vaccine, Vial', '1', '0', '3', '202408217AY', '2027-08-18 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '3', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('339', '339', 'Purchase', 'Vitamin B Complex', 'Neurobion Forte Tablets', '1', '0', '150', '5123C83907', '2026-05-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '150', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('340', '340', 'Purchase', 'Insulin 70/30', 'Mixtard 30, Vial', '1', '0', '2', 'RT6HD52', '2027-08-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('341', '341', 'Purchase', 'Bisacodyl 5mg', 'Bicolex 5, Tablets', '33', '0', '100', '250132', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('342', '342', 'Purchase', 'Clindamycin 300mg', 'Clindacin-300 Capsules', '1', '0', '5', 'CDC501', '2027-12-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('343', '343', 'Purchase', 'Diloxanide 250mg/Metronidazole 200mg/Dicyclomine 10mg', 'Entamaxin Capsules,', '1', '0', '2', '242EXC', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '2', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('344', '344', 'Purchase', 'Fluconazole 200mg ', 'Diconazol 200mg Tablets', '33', '0', '100', '250729', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '100', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('345', '345', 'Purchase', 'Crotamiton+Sulphur', 'Scabion Cream, 20g', '1', '0', '5', 'GK017J', '2026-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '5', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('346', '346', 'Purchase', 'Surgical Masks', 'Surgical Mask', '17', '0', '150', 'MBLFM3/25/07/14', '2030-06-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '150', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('347', '347', 'Purchase', 'MENTHO PLUS BALM', 'EMAMI MENTHO PLUS', '1', '0', '12', 'CS0061B', '2028-01-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '12', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('348', '348', 'Purchase', 'Albendazole 400mg', 'NILWORM 400MG, TABLET', '1', '0', '35', 'DC0036', '2027-02-28 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '35', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('349', '349', 'Purchase', 'Albendazole 400mg/10mL', 'TANZOL SUSPENSION', '1', '0', '10', '5130379', '2028-03-30 00:00:00', 'Philmed ', '0', '2025-09-04 00:00:00', '10', 'active', 'Dr Kamande');
INSERT INTO `stocks` VALUES('350', '42', 'Negative Adjustments', 'Ibuprofen 100mg/Paracetamol 125mg BP', 'Brustan Suspension', NULL, '9', '0', 'ALB0201', '2026-09-30 00:00:00', 'None', '7', '2025-09-14 14:55:08', '3', 'Completed', ' ');
INSERT INTO `stocks` VALUES('351', '6', 'Sales', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', NULL, '40', '0', '0', '2027-08-30 00:00:00', 'None', '20', '2025-09-06 10:15:22', '20', 'Active', 'admin');
INSERT INTO `stocks` VALUES('352', '6', 'Sales', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', NULL, '20', '0', '0', '2027-08-30 00:00:00', 'None', '11', '2025-09-14 14:55:08', '8', 'Active', 'admin');
INSERT INTO `stocks` VALUES('353', '18', 'Sales', 'Paracetamol Tablet BP 650mg/Phenylephrine Hydrochloride 5mg/Chlorpheniramine Maleate 2mg', 'CONTUS-650 Tablet', NULL, '5', '0', '25', '2028-01-30 00:00:00', 'None', '2', '2025-09-06 10:48:45', '3', 'Active', 'admin');
INSERT INTO `stocks` VALUES('354', '1', 'Sales', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', NULL, '1978', '0', '0', '2027-10-30 00:00:00', 'None', '111', '2025-09-14 10:30:33', '1867', 'Active', 'admin');
INSERT INTO `stocks` VALUES('355', '7', 'Quarantined', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', NULL, '30', '0', '2308255', '2026-07-30 00:00:00', 'None', '10', '2025-09-22 22:04:52', '10', 'Completed', ' ');
INSERT INTO `stocks` VALUES('356', '309', 'Expired', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', NULL, '100', '0', 'BPL897A', '2027-11-30 00:00:00', 'None', '20', '2025-10-13 00:12:57', '90', 'Completed', ' ');
INSERT INTO `stocks` VALUES('357', '42', 'Returns', 'Ibuprofen 100mg/Paracetamol 125mg BP', 'Brustan Suspension', NULL, '2', '1', 'ALB0201', '2026-09-30 00:00:00', 'None', '0', '2025-09-14 15:33:10', '2', 'Completed', ' ');
INSERT INTO `stocks` VALUES('358', '6', 'PQM', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', NULL, '9', '0', '0', '2027-08-30 00:00:00', 'None', '1', '2025-09-19 14:55:54', '2', 'Completed', ' ');
INSERT INTO `stocks` VALUES('359', '42', 'Expired', 'Ibuprofen 100mg/Paracetamol 125mg BP', 'Brustan Suspension', NULL, '3', '0', 'ALB0201', '2026-09-30 00:00:00', 'None', '1', '2025-09-18 10:01:13', '1', 'Completed', ' ');
INSERT INTO `stocks` VALUES('360', '46', 'Sales', 'Paracetamol 500mg/Hyoscine Butylbromide 10mg', 'Hismopan Plus Tablets (Buscopan Plus Generic)', NULL, '200', '0', '0', '2026-06-30 00:00:00', 'None', '15', '2025-09-14 12:57:38', '185', 'Active', 'admin');
INSERT INTO `stocks` VALUES('361', '323', 'Sales', 'Omeprazole Inj 40mg', 'Ompac 40mg', NULL, '4', '0', '64563', '2027-08-30 00:00:00', 'None', '3', '2025-09-14 13:13:24', '1', 'Active', 'admin');
INSERT INTO `stocks` VALUES('362', '313', 'Sales', 'Amoxicillin 1g/Clavulanate 200mg', 'GAMOK Injection, Vial', NULL, '5', '0', '240921', '2026-08-30 00:00:00', 'None', '4', '2025-09-14 13:23:17', '1', 'Active', 'admin');
INSERT INTO `stocks` VALUES('363', '116', 'Sales', 'Amoxicillin/Clavulanate 1000mg', 'Acinet 1000 Tablets', NULL, '50', '0', '0', '2027-02-28 00:00:00', 'None', '3', '2025-09-14 13:43:07', '47', 'Active', 'admin');
INSERT INTO `stocks` VALUES('364', '1', 'Sales', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', NULL, '1867', '0', '0', '2027-10-30 00:00:00', 'None', '451', '2025-09-17 06:53:26', '1416', 'Active', 'admin');
INSERT INTO `stocks` VALUES('365', '42', 'Expired', 'Ibuprofen 100mg/Paracetamol 125mg BP', 'Brustan Suspension', NULL, '2', '0', 'ALB0201', '2026-09-30 00:00:00', 'None', '1', '2025-09-18 10:01:13', '0', 'Completed', ' ');
INSERT INTO `stocks` VALUES('366', '42', 'Donated', 'Ibuprofen 100mg/Paracetamol 125mg BP', 'Brustan Suspension', NULL, '1', '0', 'ALB0201', '2026-09-30 00:00:00', 'None', '1', '2025-09-18 10:01:13', '0', 'Completed', ' ');
INSERT INTO `stocks` VALUES('367', '6', 'Negative Adjustments', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', NULL, '8', '0', '0', '2027-08-30 00:00:00', 'None', '6', '2025-09-19 14:55:54', '2', 'Completed', ' ');
INSERT INTO `stocks` VALUES('368', '40', 'Negative Adjustments', 'Ibuprofen 100mg/5ml', 'Triofen 100mL', NULL, '30', '0', 'L25G073', '2028-06-30 00:00:00', 'None', '20', '2025-09-19 14:56:17', '10', 'Completed', ' ');
INSERT INTO `stocks` VALUES('369', '7', 'Expired', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', NULL, '20', '0', '2308255', '2026-07-30 00:00:00', 'None', '10', '2025-10-13 00:12:57', '8', 'Completed', ' ');
INSERT INTO `stocks` VALUES('370', '7', 'Expired', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', NULL, '10', '0', '2308255', '2026-07-30 00:00:00', 'None', '2', '2025-10-13 00:12:57', '6', 'Completed', ' ');
INSERT INTO `stocks` VALUES('371', '7', 'Donated', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', NULL, '8', '0', '2308255', '2026-07-30 00:00:00', 'None', '2', '2025-10-13 00:12:57', '6', 'Completed', ' ');
INSERT INTO `stocks` VALUES('372', '309', 'Returns', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', NULL, '80', '10', 'BPL897A', '2027-11-30 00:00:00', 'None', '0', '2025-10-13 00:12:57', '77', 'Completed', ' ');
INSERT INTO `stocks` VALUES('373', '309', 'Quarantined', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', NULL, '90', '0', 'BPL897A', '2027-11-30 00:00:00', 'None', '13', '2025-10-13 00:12:57', '77', 'Completed', ' ');
INSERT INTO `stocks` VALUES('374', '7', 'Sales', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', NULL, '6', '0', '2308255', '2026-07-30 00:00:00', 'None', '4', '2025-10-17 12:17:22', '2', 'Active', 'admin');
INSERT INTO `stocks` VALUES('375', '292', 'Purchase', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '10', '5', '12', 'sitti-001', '2026-04-24 00:00:00', 'SittiPharm', '0', '2025-10-17 20:28:19', '13', 'active', 'Admin User');
INSERT INTO `stocks` VALUES('382', '38', 'sales', 'Paracetamol 120mg/5ml', 'Calpol Suspension 60mL', '1', '10', '0', '', NULL, '', '5', '2025-10-17 21:27:32', '5', NULL, '0');
INSERT INTO `stocks` VALUES('383', '2', 'sales', 'Paracetamol Tablet BP 500mg', 'Cetamol 500mg', '33', '1990', '0', '', NULL, '', '1', '2025-10-17 21:27:32', '1989', NULL, '0');
INSERT INTO `stocks` VALUES('384', '37', 'sales', 'Paracetamol 120mg/5ml', 'Curamol Suspension 100mL', '1', '30', '0', '', NULL, '', '4', '2025-10-17 21:27:32', '26', NULL, '0');
INSERT INTO `stocks` VALUES('385', '292', 'sales', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '1', '13', '0', '', NULL, '', '4', '2025-10-17 21:29:30', '9', NULL, '0');
INSERT INTO `stocks` VALUES('386', '128', 'sales', 'Ampicillin 60mg/cloxacillin 30mg/0.6mL', 'Ampliclo-Dawa (Neonata Amplicox Gen)', '1', '5', '0', '', NULL, '', '1', '2025-10-17 21:29:30', '4', NULL, '0');
INSERT INTO `stocks` VALUES('387', '292', 'sales', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '1', '9', '0', '', NULL, '', '1', '2025-10-17 21:34:44', '8', NULL, '0');
INSERT INTO `stocks` VALUES('388', '292', 'sales', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '1', '8', '0', '', NULL, '', '2', '2025-10-17 21:50:30', '6', NULL, '0');
INSERT INTO `stocks` VALUES('389', '187', 'sales', 'Chlorpheniramine 2mg/Pseudoephedrine 10mg/Paracetamol 120mg', 'Coldcap Syrup, 100mL', '1', '4', '0', '', NULL, '', '2', '2025-10-17 21:50:30', '2', NULL, '0');
INSERT INTO `stocks` VALUES('390', '6', 'sales', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', '3', '2', '0', '', NULL, '', '1', '2025-10-17 21:56:03', '1', NULL, '0');
INSERT INTO `stocks` VALUES('391', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '49', '0', '', NULL, '', '5', '2025-10-17 21:56:03', '44', NULL, '0');
INSERT INTO `stocks` VALUES('392', '7', 'sales', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', '3', '2', '0', '', NULL, '', '1', '2025-10-17 21:57:58', '1', NULL, '0');
INSERT INTO `stocks` VALUES('393', '7', 'sales', 'Aceclofenac 100mg/Paracetamol 500mg/Chlorzoxazone 375mg', 'ACEPAR-MR Caplets', '3', '1', '0', '', NULL, '', '1', '2025-10-17 21:58:06', '0', NULL, '0');
INSERT INTO `stocks` VALUES('394', '37', 'sales', 'Paracetamol 120mg/5ml', 'Curamol Suspension 100mL', '1', '26', '0', '', NULL, '', '10', '2025-10-17 22:03:21', '16', NULL, '0');
INSERT INTO `stocks` VALUES('395', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '44', '0', '', NULL, '', '5', '2025-10-17 22:05:20', '39', NULL, '0');
INSERT INTO `stocks` VALUES('396', '40', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 100mL', '1', '10', '0', '', NULL, '', '4', '2025-10-17 22:05:20', '6', NULL, '0');
INSERT INTO `stocks` VALUES('397', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '39', '0', '', NULL, '', '11', '2025-10-17 22:13:32', '28', NULL, '0');
INSERT INTO `stocks` VALUES('398', '38', 'sales', 'Paracetamol 120mg/5ml', 'Calpol Suspension 60mL', '1', '5', '0', '', NULL, '', '1', '2025-10-17 22:15:16', '4', NULL, '0');
INSERT INTO `stocks` VALUES('399', '309', 'sales', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', '3', '77', '0', '', NULL, '', '10', '2025-10-17 22:15:16', '67', NULL, '0');
INSERT INTO `stocks` VALUES('400', '6', 'sales', 'Ibuprofen 400mg/Paracetamol 325mg BP', 'Brustan Tablets', '3', '1', '0', '', NULL, '', '1', '2025-10-17 22:15:16', '0', NULL, '0');
INSERT INTO `stocks` VALUES('401', '36', 'sales', 'Paracetamol 120mg/5ml', 'Curamol Suspension 60mL', '1', '50', '0', '', NULL, '', '4', '2025-10-17 22:15:16', '46', NULL, '0');
INSERT INTO `stocks` VALUES('402', '26', 'sales', 'Paracetamol 500mg/Chlorzoxazone 250mg', 'Myospaz Tablet', '33', '100', '0', '', NULL, '', '2', '2025-10-17 22:27:02', '98', NULL, '0');
INSERT INTO `stocks` VALUES('403', '181', 'sales', 'Dextromethorphan 10mg/Chlorpheniramine 2mg/Pseudoephedrine 30mg/Paracetamol', 'Flu-Gone P+, 60mL', '1', '3', '0', '', NULL, '', '1', '2025-10-17 22:27:02', '2', NULL, '0');
INSERT INTO `stocks` VALUES('404', '326', 'sales', 'Paracetamol/Codeine Phosphate/Doxylamine, Caffeine', 'TAMEPYN, 20S', '7', '40', '0', '', NULL, '', '8', '2025-10-17 22:27:02', '32', NULL, '0');
INSERT INTO `stocks` VALUES('405', '76', 'sales', 'Adult Pants L', 'ADFIT  PANTS LARGE', '10', '30', '0', '', NULL, '', '15', '2025-10-17 22:32:36', '15', NULL, '0');
INSERT INTO `stocks` VALUES('406', '140', 'sales', 'Glucosamine/Chondroitin/Cod Liver/Omega 3', 'Cartil Omega Softgel Capsules', '10', '30', '0', '', NULL, '', '4', '2025-10-17 22:32:36', '26', NULL, '0');
INSERT INTO `stocks` VALUES('407', '267', 'sales', 'Bandages', 'Crepe Bandage Spandex 15cm', '1', '12', '0', '', NULL, '', '10', '2025-10-17 22:32:36', '2', NULL, '0');
INSERT INTO `stocks` VALUES('408', '269', 'sales', 'Diclofenac Gel 1%', 'DICLOGEN 1% GEL', '1', '28', '0', '', NULL, '', '3', '2025-10-17 22:32:36', '25', NULL, '0');
INSERT INTO `stocks` VALUES('409', '1', 'sales', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', '33', '1416', '0', '', NULL, '', '45', '2025-10-17 22:32:36', '1371', NULL, '0');
INSERT INTO `stocks` VALUES('410', '46', 'sales', 'Paracetamol 500mg/Hyoscine Butylbromide 10mg', 'Hismopan Plus Tablets (Buscopan Plus Generic)', '33', '185', '0', '', NULL, '', '10', '2025-10-17 22:32:36', '175', NULL, '0');
INSERT INTO `stocks` VALUES('411', '73', 'sales', 'Absorbent Cotton Wool 400mg', 'Velvex', '1', '10', '0', '', NULL, '', '2', '2025-10-17 22:40:57', '8', NULL, '0');
INSERT INTO `stocks` VALUES('412', '338', 'sales', 'Anti-Rabies Vaccine', 'Anti-Rabies Vaccine, Vial', '1', '3', '0', '', NULL, '', '1', '2025-10-17 22:40:57', '2', NULL, '0');
INSERT INTO `stocks` VALUES('413', '11', 'sales', 'Paracetamol Tablet BP 500mg/Caffeine 65mg', 'Panadol Extra Tablets (Pairs)', '17', '96', '0', '', NULL, '', '20', '2025-10-17 22:51:49', '76', NULL, '0');
INSERT INTO `stocks` VALUES('414', '303', 'sales', 'Tretinoine 0.05% w/w', 'Acnesol Cream, 25g', '1', '4', '0', '', NULL, '', '3', '2025-10-17 22:59:29', '1', NULL, '0');
INSERT INTO `stocks` VALUES('415', '303', 'sales', 'Tretinoine 0.05% w/w', 'Acnesol Cream, 25g', '1', '1', '0', '', NULL, '', '1', '2025-10-17 23:00:20', '0', NULL, '0');
INSERT INTO `stocks` VALUES('416', '38', 'sales', 'Paracetamol 120mg/5ml', 'Calpol Suspension 60mL', '1', '4', '0', '', NULL, '', '1', '2025-10-17 23:04:56', '3', NULL, '0');
INSERT INTO `stocks` VALUES('417', '73', 'sales', 'Absorbent Cotton Wool 400mg', 'Velvex', '1', '8', '0', '', NULL, '', '1', '2025-10-17 23:12:16', '7', NULL, '0');
INSERT INTO `stocks` VALUES('418', '70', 'sales', 'Levocetirizine 5mg', 'VIVACET L 5', '3', '100', '0', '', NULL, '', '12', '2025-10-17 23:18:52', '88', NULL, '0');
INSERT INTO `stocks` VALUES('419', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '28', '0', '', NULL, '', '11', '2025-10-18 12:38:26', '17', NULL, '0');
INSERT INTO `stocks` VALUES('420', '323', 'sales', 'Omeprazole Inj 40mg', 'Ompac 40mg', '1', '1', '0', '', NULL, '', '1', '2025-10-18 13:56:52', '0', NULL, '0');
INSERT INTO `stocks` VALUES('421', '66', 'sales', 'Ampicillin/Cloxacillin 250mg/250mg', 'Ampiclo-Dawa 500 Capsule', '33', '480', '0', '', NULL, '', '1', '2025-10-18 14:03:40', '479', NULL, '0');
INSERT INTO `stocks` VALUES('422', '126', 'sales', 'Amoxicillin/Clavulanate 457mg/5mL', 'ACINET DRY SYRUP 457', '1', '5', '0', '', NULL, '', '2', '2025-10-18 21:36:35', '3', NULL, '0');
INSERT INTO `stocks` VALUES('423', '127', 'sales', 'Ampicillin 60mg/cloxacillin 30mg/0.6mL', 'Neonatal Ampiclox Drops', '1', '2', '0', '', NULL, '', '1', '2025-10-18 21:37:12', '1', NULL, '0');
INSERT INTO `stocks` VALUES('424', '117', 'sales', 'Amoxicillin/Clavulanate 375mg', 'Acinet 375 Tablets', '1', '100', '0', '', NULL, '', '6', '2025-10-18 21:41:43', '94', NULL, '0');
INSERT INTO `stocks` VALUES('425', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '17', '0', '', NULL, '', '3', '2025-10-19 19:18:59', '14', NULL, '0');
INSERT INTO `stocks` VALUES('426', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '14', '0', '', NULL, '', '2', '2025-10-19 19:20:30', '12', NULL, '0');
INSERT INTO `stocks` VALUES('427', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '12', '0', '', NULL, '', '1', '2025-10-19 19:25:02', '11', NULL, '0');
INSERT INTO `stocks` VALUES('428', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '11', '0', '', NULL, '', '4', '2025-10-19 20:08:52', '7', NULL, '0');
INSERT INTO `stocks` VALUES('429', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '7', '0', '', NULL, '', '1', '2025-10-19 20:45:21', '6', NULL, '0');
INSERT INTO `stocks` VALUES('430', '154', 'sales', 'Aluminium Hydroxide 120mg/Magnesium Trisilicate 250mg', 'Alugel Suspension, 100mL', '1', '10', '0', '', NULL, '', '4', '2025-10-19 21:33:08', '6', NULL, '0');
INSERT INTO `stocks` VALUES('431', '1', 'sales', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', '33', '1371', '0', '', NULL, '', '211', '2025-10-19 21:37:46', '1160', NULL, '0');
INSERT INTO `stocks` VALUES('432', '270', 'sales', 'Clotrimazole 1% w/w/Beclomethasone Dipropionate 0.025% w/w', 'Bulkot-B Cream', '1', '10', '0', '', NULL, '', '2', '2025-10-20 13:26:52', '8', NULL, '0');
INSERT INTO `stocks` VALUES('433', '170', 'sales', 'Multivitamin Syrup', 'Filwel Kids, 100mL', '1', '5', '0', '', NULL, '', '1', '2025-10-20 13:26:52', '4', NULL, '0');
INSERT INTO `stocks` VALUES('434', '131', 'sales', 'Ceftriaxone 1g', '\'GALAXY\'S SEFIN Injection', '1', '50', '0', '', NULL, '', '11', '2025-10-21 09:57:28', '39', NULL, '0');
INSERT INTO `stocks` VALUES('435', '117', 'sales', 'Amoxicillin/Clavulanate 375mg', 'Acinet 375 Tablets', '1', '94', '0', '', NULL, '', '8', '2025-10-21 10:03:36', '86', NULL, '0');
INSERT INTO `stocks` VALUES('436', '13', 'sales', 'Etoricoxib Tablet 60mg', 'Tory 60 Tablet', '10', '30', '0', '', NULL, '', '21', '2025-10-21 12:57:15', '9', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('437', '73', 'sales', 'Absorbent Cotton Wool 400mg', 'Velvex', '1', '7', '0', '', NULL, '', '6', '2025-10-21 15:04:11', '1', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('438', '277', 'sales', 'Betamethasone Valerate 0.1% w/w', 'MEDIVEN Cream 15g', '1', '10', '0', '', NULL, '', '3', '2025-10-21 15:04:11', '7', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('439', '257', 'sales', 'Condoms', 'Kiss Strawberry Condoms, 3s', '8', '23', '0', '', NULL, '', '16', '2025-10-21 15:04:11', '7', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('440', '154', 'sales', 'Aluminium Hydroxide 120mg/Magnesium Trisilicate 250mg', 'Alugel Suspension, 100mL', '1', '6', '0', '', NULL, '', '2', '2025-10-21 15:50:45', '4', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('441', '236', 'sales', 'Losartan 50mg/Hydrochlorothiazide 12.5mg', 'Angilock-Plus 50/12.5 Tablets', '10', '150', '0', '', NULL, '', '10', '2025-10-21 15:50:45', '140', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('442', '342', 'sales', 'Clindamycin 300mg', 'Clindacin-300 Capsules', '1', '5', '0', '', NULL, '', '2', '2025-10-21 15:50:45', '3', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('443', '1', 'sales', 'Ibuprofen Tablet BP 400mg', 'Gesic 400 ADL Brufen', '33', '1160', '0', '', NULL, '', '3', '2025-11-07 12:12:34', '1157', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('444', '39', 'sales', 'Ibuprofen 100mg/5ml', 'Triofen 60mL', '1', '6', '0', '', NULL, '', '1', '2025-11-07 12:12:34', '5', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('445', '292', 'sales', 'Ketoconazole 2% w/v', 'Hitoral  Shampoo, 100mL', '1', '6', '0', '', NULL, '', '3', '2025-11-07 12:13:30', '3', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('446', '112', 'sales', 'Amoxicillin/Clavulanate 457mg/5mL', 'Augmentin 457 Suspension', '1', '2', '0', '', NULL, '', '1', '2025-12-10 15:19:41', '1', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('447', '309', 'sales', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', '3', '67', '0', '', NULL, '', '1', '2025-12-10 15:19:41', '66', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('448', '88', 'sales', 'Cefixime 400mg USP', 'Theofix - 400 Tablets', '3', '90', '0', '', NULL, '', '10', '2025-12-10 19:14:46', '80', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('449', '309', 'sales', 'aceclofenac/Paracetamol/Chlorzoxazone', 'ACETAL MR Tablets', '3', '66', '0', '', NULL, '', '10', '2025-12-10 19:15:05', '56', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('450', '36', 'sales', 'Paracetamol 120mg/5ml', 'Curamol Suspension 60mL', '1', '46', '0', '', NULL, '', '12', '2025-12-10 19:15:05', '34', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('451', '126', 'sales', 'Amoxicillin/Clavulanate 457mg/5mL', 'ACINET DRY SYRUP 457', '1', '3', '0', '', NULL, '', '2', '2025-12-13 14:20:37', '1', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('452', '113', 'sales', 'Amoxicillin/Clavulanate 228.5mg/5mL', 'Augmentin 228 Suspension', '1', '3', '0', '', NULL, '', '1', '2025-12-13 14:29:13', '2', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('453', '186', 'sales', 'Chlorpheniramine 2mg/Sodium Citrate 44mg/Guaifenesin 80mg/Ammonium Chloride 100mg/Levomenthol 0.8mg', 'Cadistin Expectorant, 100mL', '1', '9', '0', '', NULL, '', '2', '2025-12-13 15:14:41', '7', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('454', '173', 'sales', 'Cyproheptadine 4mg/ B Vitamins/Minerals', 'Becoactin Syrup, 200mL', '10', '8', '0', '', NULL, '', '3', '2025-12-13 15:35:13', '5', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('455', '342', 'sales', 'Clindamycin 300mg', 'Clindacin-300 Capsules', '1', '3', '0', '', NULL, '', '2', '2025-12-13 15:36:14', '1', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('456', '65', 'sales', 'Ampicillin/Cloxacillin 250mg/5mL', 'Ampiclo-Dawa Suspension', '1', '20', '0', '', NULL, '', '11', '2025-12-13 15:36:54', '9', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('457', '65', 'sales', 'Ampicillin/Cloxacillin 250mg/5mL', 'Ampiclo-Dawa Suspension', '1', '9', '0', '', NULL, '', '7', '2025-12-13 17:36:47', '2', NULL, 'Admin User');
INSERT INTO `stocks` VALUES('458', '112', 'sales', 'Amoxicillin/Clavulanate 457mg/5mL', 'Augmentin 457 Suspension', '1', '1', '0', '', NULL, '', '1', '2025-12-13 20:54:35', '0', NULL, 'Admin User');



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
  `transactionType` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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


DELIMITER ;;
CREATE TRIGGER `calculate_sale_item_profit` BEFORE INSERT ON `sale_items` FOR EACH ROW BEGIN
    DECLARE product_unit_price DECIMAL(10,2);
    DECLARE buying_total DECIMAL(10,2);
    
    -- Get unit_price from products table
    SELECT unit_price INTO product_unit_price
    FROM products 
    WHERE brandname = NEW.brandname
    LIMIT 1;
    
    -- If product not found, set default values
    IF product_unit_price IS NULL THEN
        SET product_unit_price = 0;
    END IF;
    
    -- Set the unit_price in sale_items
    SET NEW.unit_price = product_unit_price;
    
    -- Calculate buying_price_total (unit_price * quantity)
    SET buying_total = product_unit_price * NEW.quantity;
    SET NEW.buying_price_total = buying_total;
    
    -- Calculate profit (total_amount - buying_price_total)
    -- Note: If grand_total is available, use that instead of total_amount
    IF NEW.grand_total > 0 THEN
        SET NEW.profit = NEW.grand_total - buying_total;
    ELSE
        SET NEW.profit = NEW.total_amount - buying_total;
    END IF;
END;;
DELIMITER ;

