<?php


namespace GoogleShopping\Loop;

use GoogleShopping\Model\GoogleshoppingProductSynchronisation;
use GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\Country;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;

class ProductSync extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('product_id', null, true),
            Argument::createAnyTypeArgument('country'),
            Argument::createAnyTypeArgument('locale')
        );
    }

        
    public function buildModelCriteria()
    {
        $query = GoogleshoppingProductSynchronisationQuery::create()
            ->filterByProductId($this->getProductId());

        if ($this->getCountry()) {
            $targetCountry = $this->getCountry();
        } else {
            $targetCountry = Country::getDefaultCountry()->getIsoalpha2();
        }

        if ($this->getLocale()) {
            $lang =  LangQuery::create()->findOneByLocale($this->getLocale())->getCode();

        } else {
            $lang = Lang::getDefaultLanguage()->getCode();
        }

        $query->filterByTargetCountry($targetCountry)
            ->filterByLang($lang);

        return $query;
    }
    
    
    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        /** @var GoogleshoppingProductSynchronisation $productSync */
        foreach ($loopResult->getResultDataCollection() as $productSync) {
            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("ENABLE", $productSync->getSyncEnable());

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
