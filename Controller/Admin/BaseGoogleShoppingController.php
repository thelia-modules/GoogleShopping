<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
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

        $oAuthToken = GoogleShopping::getConfigValue('oauth_access_token');
        $code = $this->getRequest()->query->get('code');

        $redirection = '/admin/module/GoogleShopping';

        //Manage redirection after auth
        if ($url = $this->getRequest()->query->get('redirect')) {
            $redirection = $url;
            $this->getSession()->set('google_action_url', $redirection);
        }

        if (isset($oAuthToken)) {
            $client->setAccessToken($oAuthToken);
            if ($client->isAccessTokenExpired()) {
                $client->refreshToken(GoogleShopping::getConfigValue('oauth_refresh_token'));
                $newToken = $client->getAccessToken();
                GoogleShopping::setConfigValue('oauth_access_token', $newToken);
            }
            return $this->generateRedirect($redirection);
        } elseif (isset($code)) {
            $client->authenticate($code);
            $token = $client->getAccessToken();
            $refreshToken = $client->getRefreshToken();

            GoogleShopping::setConfigValue('oauth_access_token', $token);

            if ($refreshToken) {
                GoogleShopping::setConfigValue('oauth_refresh_token', $refreshToken);
            }

            return $this->generateRedirect($redirection);
        } else {
            return $this->generateRedirect($client->createAuthUrl());
        }
    }

    public function checkGoogleAuth()
    {
        $token = GoogleShopping::getConfigValue('oauth_access_token');

        if (!$token) {
            return false;
        }

        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');

        $client->setAccessToken($token);

        if (true === $client->isAccessTokenExpired()) {
            return false;
        }

        return true;
    }

    public function createGoogleClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');
        $client->setAccessToken(GoogleShopping::getConfigValue('oauth_access_token'));

        return $client;
    }
}
