
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
-- googleshopping_configuration
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `googleshopping_configuration`;

CREATE TABLE `googleshopping_configuration`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255),
    `merchant_id` VARCHAR(255) NOT NULL,
    `lang_id` INTEGER,
    `country_id` INTEGER,
    `currency_id` INTEGER,
    `is_default` TINYINT(1),
    `sync` TINYINT(1),
    PRIMARY KEY (`id`),
    INDEX `FI_googleshopping_configuration_lang_id` (`lang_id`),
    INDEX `FI_googleshopping_configuration_country_id` (`country_id`),
    INDEX `FI_googleshopping_configuration_id` (`currency_id`),
    CONSTRAINT `fk_googleshopping_configuration_lang_id`
        FOREIGN KEY (`lang_id`)
        REFERENCES `lang` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_googleshopping_configuration_country_id`
        FOREIGN KEY (`country_id`)
        REFERENCES `country` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_googleshopping_configuration_id`
        FOREIGN KEY (`currency_id`)
        REFERENCES `currency` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- googleshopping_product_sync_queue
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `googleshopping_product_sync_queue`;

CREATE TABLE `googleshopping_product_sync_queue`
(
    `product_sale_elements_id` INTEGER NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`product_sale_elements_id`),
    CONSTRAINT `fk_googleshopping_product_sync_queue_pse`
        FOREIGN KEY (`product_sale_elements_id`)
        REFERENCES `product_sale_elements` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
