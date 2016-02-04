<?php


namespace GoogleShopping\Event;

class GoogleShoppingEvents
{
    const GOOGLE_PRODUCT_CREATE_PRODUCT = "googleshopping.product.create";
    const GOOGLE_PRODUCT_CREATE_PSE = "googleshopping.product.create.pse";
    const GOOGLE_PRODUCT_SEND = "googleshopping.product.send";
    const GOOGLE_PRODUCT_BATCH = "googleshopping.product.batch";
    const GOOGLE_PRODUCT_DELETE_PRODUCT = "googleshopping.product.delete";
    const GOOGLE_PRODUCT_DELETE_PSE = "googleshopping.product.delete.pse";
    const GOOGLE_SYNC_CATALOG = "googleshopping.catalog.sync";
}
