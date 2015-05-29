Google Shopping
===

Beta
--
Becareful this module is in beta stage. 
He need lot of improvements and can contains some issues. 

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
$ php composer.phar require thelia/google-shopping-module:~1.0
```

3. Usage <a name="usage_en_US"></a>
---

Before install this module you need to create a project in your google account for that you can follow Google's guide [Here](https://developers.google.com/shopping-content/v2/quickstart)
Until part 5. For the part 4 you have to choose "Web application".

After that you will be able to get your credentials to set in Thelia BackOffice after module activation.
Then you need to associate your own categories with Google's one.

When everything is configured you can go to a product edit page in backOffice, tab module and if everything is good you will see a button for send product to google.

For send a product to google he need a valid GTIN (EAN)
