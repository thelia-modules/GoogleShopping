<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Tools\URL;

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
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');

        if (isset($_SESSION['oauth_access_token'])) {
            $client->setAccessToken($_SESSION['oauth_access_token']);
            $this->service = new \Google_Service_ShoppingContent($client);
        } elseif (isset($_GET['code'])) {
            $token = $client->authenticate($_GET['code']);
            $_SESSION['oauth_access_token'] = $token;
            if (isset($_SESSION['gshopping_from_url'])) {
                return RedirectResponse::create(URL::getInstance()->absoluteUrl($_SESSION['gshopping_from_url']));
            }
        } else {
            $_SESSION['gshopping_from_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . $client->createAuthUrl());
            exit;
        }
    }
}
