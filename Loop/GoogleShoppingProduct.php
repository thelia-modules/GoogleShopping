<?php

namespace GoogleShopping\Loop;

use GoogleShopping\GoogleShopping;
use GoogleShopping\Model\GoogleshoppingProductSynchronisation;
use GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\CategoryQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Type;
use Thelia\Type\TypeCollection;

class GoogleShoppingProduct extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('category_id'),
            Argument::createIntTypeArgument('product_id'),
            Argument::createBooleanTypeArgument('visible', false),
            Argument::createAnyTypeArgument('locale', 'en_US'),
            new Argument(
                'product_order',
                new TypeCollection(
                    new Type\EnumListType(
                        [
                            'id', 'id_reverse',
                            'alpha', 'alpha_reverse',
                            'ref', 'ref_reverse',
                            'ean', 'ean_reverse'
                        ]
                    )
                ),
                'alpha'
            )
        );
    }

    public function buildModelCriteria()
    {
        $categoryId = $this->getCategoryId();
        $productId = $this->getProductId();
        $productOrders = $this->getProductOrder();

        $query = ProductQuery::create()
            ->joinWithI18n($this->getLocale());

        if (true === $this->getVisible()){
            $query->filterByVisible(true);
        }

        if ($categoryId) {
            $query->useProductCategoryQuery()
                ->filterByDefaultCategory(true)
                ->filterByCategoryId($categoryId)
            ->endUse();
        }

        if ($productId) {
            $query->filterById($productId);
        }

        if ($productOrders) {
            foreach ($productOrders as $productOrder) {
                switch ($productOrder) {
                    case "id":
                        $query->orderById(Criteria::ASC);
                        break;
                    case "id_reverse":
                        $query->orderById(Criteria::DESC);
                        break;
                    case "alpha":
                        $query->addAscendingOrderByColumn('product_i18n.TITLE');
                        break;
                    case "alpha_reverse":
                        $query->addDescendingOrderByColumn('product_i18n.TITLE');
                        break;
                    case "ref":
                        $query->orderByRef(Criteria::ASC);
                        break;
                    case "ref_reverse":
                        $query->orderByRef(Criteria::DESC);
                        break;
                }
            }
        }
        return $query;
    }

    protected function checkEan(Product $product, $colorAttributeId, $sizeAttributeId)
    {
        $isCombination = false;

        $defaultPse = $product->getDefaultSaleElements();

        $combinationAttribute = $colorAttributeId.','.$sizeAttributeId;
        if (null !== $combinationAttribute) {
            $combination = AttributeAvQuery::create()
                ->useAttributeCombinationQuery()
                ->filterByAttributeId(explode(',', $combinationAttribute), Criteria::IN)
                ->filterByProductSaleElementsId($defaultPse->getId())
                ->endUse()
                ->findOne();
            if (null !== $combination) {
                 $isCombination = true;
            }
        }

        if (false === $isCombination) {
            if (null === $defaultPse->getEanCode()){
                return false;
            }
        } else {
            $productSaleElementss = $product->getProductSaleElementss();
            foreach ($productSaleElementss as $productSaleElements) {
                if (null == $productSaleElements->getEanCode()){
                    return false;
                }
            }
        }
        return true;
    }

    public function eanSortedParseResults(LoopResult $loopResult, $sort)
    {
        $eanArray = [];
        $notEanArray = [];

        $colorAttributeId = GoogleShopping::getConfigValue('attribute_color');
        $sizeAttributeId = GoogleShopping::getConfigValue('attribute_size');
        /** @var Product $product */
        foreach ($loopResult->getResultDataCollection() as $product) {

            $checkEan = $this->checkEan($product, $colorAttributeId, $sizeAttributeId);

            $isCategoryAssociated = GoogleshoppingTaxonomyQuery::create()
                ->findOneByTheliaCategoryId($product->getDefaultCategoryId()) !== null ? true : false;

            if ($checkEan === false) {
                $notEanArray[] = [
                    "product" => $product,
                    "category_associated" => $isCategoryAssociated,
                    "valid_ean" => $checkEan
                ];
            } else {
                $eanArray[] = [
                    "product" => $product,
                    "category_associated" => $isCategoryAssociated,
                    "valid_ean" => $checkEan
                ];
            }

        }

        if ($sort === "ean") {
            $loopResultArray = array_merge($eanArray, $notEanArray);
        } else {
            $loopResultArray = array_merge($notEanArray, $eanArray);
        }

        foreach ($loopResultArray as $loopResultProduct) {
            $loopResultRow = new LoopResultRow();
            $product = $loopResultProduct["product"];
            $loopResultRow->set("ID", $product->getId());
            $loopResultRow->set("REF", $product->getRef());
            $loopResultRow->set("TITLE", $product->getTitle());
            $loopResultRow->set("CATEGORY_ASSOCIATED", $loopResultProduct["category_associated"]);
            $loopResultRow->set("VALID_EAN", $loopResultProduct["valid_ean"]);

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

    public function normalParseResults(LoopResult $loopResult)
    {
        $colorAttributeId = GoogleShopping::getConfigValue('attribute_color');
        $sizeAttributeId = GoogleShopping::getConfigValue('attribute_size');

        /** @var Product $product */
        foreach ($loopResult->getResultDataCollection() as $product) {

            $checkEan = $this->checkEan($product, $colorAttributeId, $sizeAttributeId);

            $isCategoryAssociated = GoogleshoppingTaxonomyQuery::create()
                ->findOneByTheliaCategoryId($product->getDefaultCategoryId()) !== null ? true : false;

            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("ID", $product->getId());
            $loopResultRow->set("REF", $product->getRef());
            $loopResultRow->set("TITLE", $product->getTitle());
            $loopResultRow->set("CATEGORY_ASSOCIATED", $isCategoryAssociated);
            $loopResultRow->set("VALID_EAN", $checkEan);

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        $productOrders = $this->getProductOrder() !== null ? $this->getProductOrder() : array();

        foreach ($productOrders as $productOrder) {
            switch ($productOrder) {
                case "ean":
                    $loopResult = $this->eanSortedParseResults($loopResult, "ean");
                    break;
                case "ean_reverse":
                    $loopResult = $this->eanSortedParseResults($loopResult, "ean_reverse");
                    break;
                default:
                    $loopResult = $this->normalParseResults($loopResult);
                    break;
            }
        }
        return $loopResult;
    }
}
