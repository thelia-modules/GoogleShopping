<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace GoogleShopping;

use GoogleShopping\Model\GoogleshoppingAccount;
use GoogleShopping\Model\GoogleshoppingAccountQuery;
use GoogleShopping\Model\GoogleshoppingProductSynchronisation;
use GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;
use Thelia\Model\ModuleConfigQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Module\BaseModule;

set_include_path(get_include_path() . PATH_SEPARATOR . '/Google/src');

class GoogleShopping extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'googleshopping';

    public static function getModuleId()
    {
        return ModuleQuery::create()->findOneByCode("GoogleShopping")->getId();
    }

    public function postActivation(ConnectionInterface $con = null)
    {
        if (!self::getConfigValue('is_initialized', false)) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/thelia.sql"]);
            self::setConfigValue('is_initialized', true);
            $this->setConfigValue("sync_secret", md5(uniqid(rand(), true)));
        }

        try {
            $gShoppingAccount = GoogleshoppingAccountQuery::create()
                ->findOne();
        } catch (\Exception $e) {
            //Update for multi account (0.6)
            $merchantId = self::getConfigValue('merchant_id');

            if (null !== $merchantId) {
                $googleShoppingAccount = new GoogleshoppingAccount();
                $googleShoppingAccount->setMerchantId($merchantId)
                    ->save();

                $googleShoppingProducts = GoogleshoppingProductSynchronisationQuery::create()
                    ->filterByGoogleshoppingAccountId(null)
                    ->find();

                if (null !== $googleShoppingProducts) {
                    /** @var GoogleshoppingProductSynchronisation $googleShoppingProduct */
                    foreach ($googleShoppingProducts as $googleShoppingProduct) {
                        $googleShoppingProduct->setGoogleshoppingAccountId($googleShoppingAccount->getId())
                            ->save();
                    }
                }
            }
        }
    }

    public function update($currentVersion, $newVersion, ConnectionInterface $con)
    {
        if (file_exists(__DIR__ . "/Config/Update/$newVersion.sql")) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/Update/$newVersion.sql"]);
        }

        if ($newVersion === "0.6") {
            $merchantId = self::getConfigValue('merchant_id');

            if (null !== $merchantId) {
                $googleShoppingAccount = new GoogleshoppingAccount();
                $googleShoppingAccount->setMerchantId($merchantId)
                    ->save();

                $googleShoppingProducts = GoogleshoppingProductSynchronisationQuery::create()
                    ->find();

                if (null !== $googleShoppingProducts) {
                    /** @var GoogleshoppingProductSynchronisation $googleShoppingProduct */
                    foreach ($googleShoppingProducts as $googleShoppingProduct) {
                        $googleShoppingProduct->setGoogleshoppingAccountId($googleShoppingAccount->getId())
                            ->save();
                    }
                }
            }
        }
    }
}
