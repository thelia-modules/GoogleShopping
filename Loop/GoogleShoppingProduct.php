<?php

namespace GoogleShopping\Loop;

use GoogleShopping\Model\GoogleshoppingProductQuery;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\CategoryQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Type;
use Thelia\Type\TypeCollection;

class GoogleShoppingProduct extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('category_id'),
            Argument::createIntTypeArgument('product_id'),
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
            ->joinWithI18n();

        if ($categoryId) {
            $category = CategoryQuery::create()->findPk($categoryId);
            $query->filterByCategory($category);
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

    public function eanSortedParseResults(LoopResult $loopResult, $sort)
    {
        $eanArray = [];
        $notEanArray = [];
        /** @var Product $product */
        foreach ($loopResult->getResultDataCollection() as $product) {
            $alreadyExist = GoogleshoppingProductQuery::create()
                ->findOneByProductId($product->getId()) === null ? false : true;

            $checkEan = "";

            $productSaleElements = $product->getProductSaleElementss();
            foreach ($productSaleElements as $productSaleElement) {
                $ean = $productSaleElement->getEanCode();

                if (!$ean) {
                    $checkEan = false;
                }
            }

            $isCategoryAssociated = GoogleshoppingTaxonomyQuery::create()
                ->findOneByTheliaCategoryId($product->getDefaultCategoryId()) !== null ? true : false;

            if ($checkEan === false) {
                $notEanArray[] = [
                    "product" => $product,
                    "exist" => $alreadyExist,
                    "category_associated" => $isCategoryAssociated,
                    "valid_ean" => $checkEan
                ];
            } else {
                $eanArray[] = [
                    "product" => $product,
                    "exist" => $alreadyExist,
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
            $loopResultRow->set("EXIST", $loopResultProduct["exist"]);
            $loopResultRow->set("CATEGORY_ASSOCIATED", $loopResultProduct["category_associated"]);
            $loopResultRow->set("VALID_EAN", $loopResultProduct["valid_ean"]);

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

    public function normalParseResults(LoopResult $loopResult)
    {
        /** @var Product $product */
        foreach ($loopResult->getResultDataCollection() as $product) {
            $alreadyExist = GoogleshoppingProductQuery::create()
                ->findOneByProductId($product->getId()) === null ? false : true;

            $checkEan = "";

            $productSaleElements = $product->getProductSaleElementss();
            foreach ($productSaleElements as $productSaleElement) {
                $ean = $productSaleElement->getEanCode();

                if (!$ean) {
                    $checkEan = false;
                }
            }

            $isCategoryAssociated = GoogleshoppingTaxonomyQuery::create()
                ->findOneByTheliaCategoryId($product->getDefaultCategoryId()) !== null ? true : false;

            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("ID", $product->getId());
            $loopResultRow->set("REF", $product->getRef());
            $loopResultRow->set("TITLE", $product->getTitle());
            $loopResultRow->set("EXIST", $alreadyExist);
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
