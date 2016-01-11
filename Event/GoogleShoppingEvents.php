<?php


namespace GoogleShopping\Event;

class GoogleShoppingEvents
{
    const GOOGLE_PRODUCT_CREATE_PRODUCT = "googleshopping.product.create";
    const GOOGLE_PRODUCT_CREATE_PSE = "googleshopping.product.create.pse";
    const GOOGLE_PRODUCT_SEND = "googleshopping.product.send";
    const GOOGLE_PRODUCT_BATCH = "googleshopping.product.batch";
    const GOOGLE_PRODUCT_DELETE = "googleshopping.product.delete";
    const GOOGLE_PRODUCT_TOGGLE_SYNC = "googleshopping.product.toggle.sync";
    const GOOGLE_SYNC_CATALOG = "googleshopping.catalog.sync";
}
