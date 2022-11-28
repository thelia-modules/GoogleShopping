<?php


namespace GoogleShopping\Loop;

use GoogleShopping\Model\GoogleshoppingAccount;
use GoogleShopping\Model\GoogleshoppingAccountQuery;
use GoogleShopping\Model\GoogleshoppingConfiguration;
use GoogleShopping\Model\GoogleshoppingConfigurationQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class Configuration extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(

        );
    }


    public function buildModelCriteria()
    {
        $query = GoogleshoppingConfigurationQuery::create();

        return $query;
    }


    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        /** @var GoogleshoppingConfiguration $configuration */
        foreach ($loopResult->getResultDataCollection() as $configuration) {
            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("ID", $configuration->getId());
            $loopResultRow->set("TITLE", $configuration->getTitle());
            $loopResultRow->set("MERCHANT_ID", $configuration->getMerchantId());
            $loopResultRow->set("LANG_ID", $configuration->getLangId());
            $loopResultRow->set("COUNTRY_ID", $configuration->getCountryId());
            $loopResultRow->set("CURRENCY_ID", $configuration->getCurrencyId());
            $loopResultRow->set("IS_DEFAULT", $configuration->getIsDefault());
            $loopResultRow->set("SYNC", $configuration->getSync());

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
