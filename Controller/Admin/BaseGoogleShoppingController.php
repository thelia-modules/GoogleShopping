<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
use Thelia\Controller\Admin\BaseAdminController;

class BaseGoogleShoppingController extends BaseAdminController
{
    protected $merchant_id;
    protected $service;

    public function authorization()
    {
        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri("http://gshopping.openstudio-lab.com/googleshopping/oauth2callback");
        $client->setScopes('https://www.googleapis.com/auth/content');

        if (isset($_SESSION['oauth_access_token'])) {
            $client->setAccessToken($_SESSION['oauth_access_token']);
            $this->service = new \Google_Service_ShoppingContent($client);
        } elseif (isset($_GET['code'])) {
            $token = $client->authenticate($_GET['code']);
            $_SESSION['oauth_access_token'] = $token;
        } else {
            header('Location: ' . $client->createAuthUrl());
            exit;
        }
    }
}
