Google Shopping
===

Beta
--
Becareful this module is in beta stage. 
He need lot of improvements, can contains some issues and can have breaking compatibility during the beta.

Summary
---

1. [Description](#description_en_US)
2. [Install](#install_en_US)
3. [Usage](#usage_en_US)


1. Description <a name="#description_en_US"></a>
---

This module allow you to add product to your Google Shopping account through the api.


2. Install <a name="install_en_US"></a>
---

You can install this module with composer:

```sh
$ php composer.phar require thelia/google-shopping-module:~0.2
```

3. Usage <a name="usage_en_US"></a>
---

Before install this module you need to create a project in your google account for that you can follow Google's guide [Here](https://developers.google.com/shopping-content/v2/quickstart)
Until part 5. For the part 4 you have to choose "Web application".

After that you will be able to get your credentials to set in Thelia BackOffice after module activation.

Then you need to associate your own categories with Google's one.

When everything is configured you can add product to your google shopping account through the catalog management tab in google configuration modules.
If a product don't have gtin/ean and is unique you have to check the box to specify to google that product is unique and he don't need an unique identifier.

The synchronisation button is useful when you want implements a synchronisation auto with a cron, the url to call with cron is on GoogleShopping configuration.
Each time this url is called all the product with synchronisation enable will be updated. You can also sync manually them with a button in Catalog management tab.
