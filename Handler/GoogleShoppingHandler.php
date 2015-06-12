<?php


namespace GoogleShopping\Handler;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\ConfigQuery;
use Thelia\Model\ProductImage;
use Thelia\Tools\URL;

class GoogleShoppingHandler
{
    /** @var  Request */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function createGoogleClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');
        $client->setAccessToken($this->request->getSession()->get('oauth_access_token'));

        return $client;
    }

    /**
     * @param ProductImage $image
     * @return ImageEvent
     */
    public function createProductImageEvent(ProductImage $image)
    {
        $imageEvent = new ImageEvent($this->request);

        $baseSourceFilePath = ConfigQuery::read('images_library_path');

        if ($baseSourceFilePath === null) {
            $baseSourceFilePath = THELIA_LOCAL_DIR . 'media' . DS . 'images';
        } else {
            $baseSourceFilePath = THELIA_ROOT . $baseSourceFilePath;
        }

        // Put source image file path
        $sourceFilePath = sprintf(
            '%s/%s/%s',
            $baseSourceFilePath,
            'product',
            $image->getFile()
        );

        $imageEvent->setSourceFilepath($sourceFilePath);
        $imageEvent->setCacheSubdirectory('product');

        return $imageEvent;
    }

    public function checkGtin($gtin)
    {
        $cleanedCode = $this->clean($gtin);

        return $this->isValidGtin($cleanedCode);
    }

    protected function clean($gtin, $fill = 14)
    {
        if (is_numeric($gtin)) {
            return $this->zfill($gtin, $fill);
        } elseif (is_string($gtin)) {
            return $this->zfill(trim(str_replace("-", "", $gtin)), $fill);
        }
        return false;
    }

    protected function isValidGtin($cleanedCode)
    {
        if (!is_numeric($cleanedCode)) {
            return false;
        } elseif (!in_array(strlen($cleanedCode), array(8, 12, 13, 14, 18))) {
            return false;
        }

        return $this->isGtinChecksumValid($cleanedCode);
    }

    protected function isGtinChecksumValid($code)
    {
        $lastPart = substr($code, -1);
        $checkSum = $this->gtinCheckSum(substr($code, 0, strlen($code)-1));
        return $lastPart == $checkSum;
    }

    protected function gtinCheckSum($code)
    {
        $total = 0;

        $codeArray = str_split($code);
        foreach (array_values($codeArray) as $i => $c) {
            if ($i % 2 == 1) {
                $total = $total + $c;
            } else {
                $total = $total + (3*$c);
            }
        }
        $checkDigit = (10 - ($total % 10)) % 10;
        return $checkDigit;
    }

    protected function zfill($int, $cnt)
    {
        $int = intval($int);
        $nulls = "";
        for ($i=0; $i<($cnt-strlen($int)); $i++) {
            $nulls .= '0';
        }
        return $nulls.$int;
    }

}
