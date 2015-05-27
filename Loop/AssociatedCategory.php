<?php

namespace GoogleShopping\Loop;


use GoogleShopping\Model\GoogleshoppingTaxonomy;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\CategoryQuery;

class AssociatedCategory extends BaseLoop implements PropelSearchLoopInterface
{
    public function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

    public function buildModelCriteria()
    {
        return GoogleshoppingTaxonomyQuery::create();
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var GoogleshoppingTaxonomy $data */
        foreach ($loopResult->getResultDataCollection() as $data) {
            $loopResultRow = new LoopResultRow();
            $theliaCategory = CategoryQuery::create()
                ->findOneById($data->getTheliaCategoryId());
            $loopResultRow->set("THELIA_CATEGORY_ID", $data->getTheliaCategoryId());
            $loopResultRow->set("THELIA_CATEGORY_TITLE", $theliaCategory->getTitle());
            $loopResultRow->set("GOOGLE_CATEGORY", $data->getGoogleCategory());

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

}