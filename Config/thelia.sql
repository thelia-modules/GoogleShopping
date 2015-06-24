
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- googleshopping_taxonomy
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `googleshopping_taxonomy`;

CREATE TABLE `googleshopping_taxonomy`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `thelia_category_id` INTEGER NOT NULL,
    `google_category` VARCHAR(255) NOT NULL,
    `lang_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `FI_googleshopping_thelia_category_id` (`thelia_category_id`),
    INDEX `FI_googleshopping_thelia_lang_id` (`lang_id`),
    CONSTRAINT `fk_googleshopping_thelia_category_id`
        FOREIGN KEY (`thelia_category_id`)
        REFERENCES `category` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_googleshopping_thelia_lang_id`
        FOREIGN KEY (`lang_id`)
        REFERENCES `lang` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- googleshopping_product_synchronisation
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `googleshopping_product_synchronisation`;

CREATE TABLE `googleshopping_product_synchronisation`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `target_country` VARCHAR(255) NOT NULL,
    `lang` VARCHAR(255) NOT NULL,
    `sync_enable` TINYINT(1),
    PRIMARY KEY (`id`),
    INDEX `FI_googleshopping_product_synchronisation_product_id` (`product_id`),
    CONSTRAINT `fk_googleshopping_product_synchronisation_product_id`
        FOREIGN KEY (`product_id`)
        REFERENCES `product` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
