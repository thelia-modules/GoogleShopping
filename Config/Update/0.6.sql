
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- googleshopping_account
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `googleshopping_account`;

CREATE TABLE `googleshopping_account`
(
  `id` INTEGER NOT NULL AUTO_INCREMENT,
  `merchant_id` VARCHAR(255) NOT NULL,
  `default_country_id` INTEGER,
  PRIMARY KEY (`id`),
  INDEX `FI_googleshopping_account_country_id` (`default_country_id`),
  CONSTRAINT `fk_googleshopping_account_country_id`
  FOREIGN KEY (`default_country_id`)
  REFERENCES `country` (`id`)
    ON UPDATE RESTRICT
    ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE `googleshopping_product_synchronisation`
ADD `googleshopping_account_id` INTEGER NOT NULL;

ALTER TABLE `googleshopping_product_synchronisation`
  ADD CONSTRAINT `fk_googleshopping_prod_sync_googleshopping_account_id`
  FOREIGN KEY (`googleshopping_account_id`)
  REFERENCES `googleshopping_account` (`id`)
    ON UPDATE RESTRICT
    ON DELETE CASCADE;

ALTER TABLE `googleshopping_product_synchronisation`
  ADD INDEX `FI_googleshopping_prod_sync_googleshopping_account_id` (`googleshopping_account_id`);


# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
