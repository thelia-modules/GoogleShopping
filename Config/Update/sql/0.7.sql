
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- googleshopping_account
-- ---------------------------------------------------------------------

ALTER TABLE `googleshopping_account`
  ADD `default_currency_id` INTEGER,
  ADD `is_default` TINYINT(1),
  ADD INDEX `FI_googleshopping_account_currency_id` (`default_currency_id`),
  ADD CONSTRAINT `fk_googleshopping_account_currency_id`
  FOREIGN KEY (`default_currency_id`)
  REFERENCES `currency` (`id`)
    ON UPDATE RESTRICT
    ON DELETE CASCADE;


# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
