<?php

namespace GoogleShopping\Loop;


use GoogleShopping\Model\GoogleshoppingProductQuery;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;

class GoogleShoppingProduct extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createAnyTypeArgument('product_id', null, true)
        );
    }

    public function buildModelCriteria()
    {
        $productId = $this->getProductId();

        return ProductQuery::create()
            ->filterById($productId);
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var Product $product */
        foreach ($loopResult->getResultDataCollection() as $product) {
            $alreadyExist = GoogleshoppingProductQuery::create()
                ->findOneByProductId($product->getId()) === null ? false : true;
            $isCategoryAssociated = GoogleshoppingTaxonomyQuery::create()
                ->findOneByTheliaCategoryId($product->getDefaultCategoryId()) !== null ? true : false;

            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("EXIST", $alreadyExist);
            $loopResultRow->set("CATEGORY_ASSOCIATED", $isCategoryAssociated);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}