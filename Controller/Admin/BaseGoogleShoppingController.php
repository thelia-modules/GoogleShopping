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

    public function getAuthorization()
    {
        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');

        $oAuthToken = $this->getSession()->get('oauth_access_token');
        $code = $this->getRequest()->query->get('code');

        if (isset($oAuthToken)) {
            $client->setAccessToken($oAuthToken);
            $this->service = new \Google_Service_ShoppingContent($client);
        } elseif (isset($code)) {
            $token = $client->authenticate($code);
            $this->getRequest()->getSession()->set('oauth_access_token', $token);
            if ($this->getSession()->get('gshopping_from_url')) {
                return RedirectResponse::create(URL::getInstance()
                    ->absoluteUrl($this->getSession()->get('gshopping_from_url')));
            }
        } else {
            $this->getSession()->set('gshopping_from_url', $_SERVER['REQUEST_URI']);
            header('Location: ' . $client->createAuthUrl());
            exit;
        }
    }

    public function createGoogleClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');
        $client->setAccessToken($this->getSession()->get('oauth_access_token'));

        return $client;
    }
}
