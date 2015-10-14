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
    }
}
