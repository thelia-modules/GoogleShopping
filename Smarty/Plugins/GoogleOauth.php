<?php

namespace GoogleShopping\Smarty\Plugins;

use GoogleShopping\GoogleShopping;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Tools\URL;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class GoogleOauth extends AbstractSmartyPlugin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getPluginDescriptors()
    {
        return array(
            new SmartyPluginDescriptor("function", "oAuthCheckToken", $this, "oAuthCheckToken")
        );
    }

    public function oAuthCheckToken($params, $smarty)
    {
        $token = $this->request->getSession()->get('oauth_access_token');

        if (!$token) {
            $smarty->assign('tokenExist', 'none');
            return;
        }

        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');

        $client->setAccessToken($token);

        if (true === $client->isAccessTokenExpired()) {
            $smarty->assign('tokenExpired', true);
        }
    }

}