CREATE TABLE `customers_compliment` (  
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category` INT(11) NOT NULL,
  `customer_name` VARCHAR(50) NOT NULL,
  `content` TEXT NOT NULL,
  `customer_image` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) 
) CHARSET=utf8 COLLATE=utf8_general_ci;