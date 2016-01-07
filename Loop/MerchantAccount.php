<?php


namespace GoogleShopping\Loop;

use GoogleShopping\Model\GoogleshoppingAccount;
use GoogleShopping\Model\GoogleshoppingAccountQuery;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class MerchantAccount extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(

        );
    }

        
    public function buildModelCriteria()
    {
        $query = GoogleshoppingAccountQuery::create();

        return $query;
    }
    
    
    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        /** @var GoogleshoppingAccount $account */
        foreach ($loopResult->getResultDataCollection() as $account) {
            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("ID", $account->getId());
            $loopResultRow->set("MERCHANT_ID", $account->getMerchantId());
            $loopResultRow->set("DEFAULT_COUNTRY_ID", $account->getDefaultCountryId());

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
