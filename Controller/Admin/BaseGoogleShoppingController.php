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

    public function setAuthorization()
    {
        $client = new \Google_Client();
        $client->setAccessType('offline');
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');

        $oAuthToken = $this->getSession()->get('oauth_access_token');
        $code = $this->getRequest()->query->get('code');

        if (isset($oAuthToken)) {
            $client->setAccessToken($oAuthToken);
            if ($client->isAccessTokenExpired()) {
                $client->refreshToken($this->getRequest()->getSession()->get('oauth_refresh_token'));
                $newToken = $client->getAccessToken();
                $this->getRequest()->getSession()->set('oauth_access_token', $newToken);
            }
            return $this->generateRedirectFromRoute('admin');
        } elseif (isset($code)) {
            $client->authenticate($code);
            $token = $client->getAccessToken();
            $refreshToken = $client->getRefreshToken();

            $this->getRequest()->getSession()->set('oauth_access_token', $token);
            $this->getRequest()->getSession()->set('oauth_refresh_token', $refreshToken);

            return $this->generateRedirectFromRoute('admin');
        } else {
            return $this->generateRedirect($client->createAuthUrl());
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
