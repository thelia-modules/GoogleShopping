<?php

namespace GoogleShopping\Model\Base;

use \Exception;
use \PDO;
use GoogleShopping\Model\GoogleshoppingProductSynchronisation as ChildGoogleshoppingProductSynchronisation;
use GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery as ChildGoogleshoppingProductSynchronisationQuery;
use GoogleShopping\Model\Map\GoogleshoppingProductSynchronisationTableMap;
use GoogleShopping\Model\Thelia\Model\Product;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'googleshopping_product_synchronisation' table.
 *
 *
 *
 * @method     ChildGoogleshoppingProductSynchronisationQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildGoogleshoppingProductSynchronisationQuery orderByProductId($order = Criteria::ASC) Order by the product_id column
 * @method     ChildGoogleshoppingProductSynchronisationQuery orderByTargetCountry($order = Criteria::ASC) Order by the target_country column
 * @method     ChildGoogleshoppingProductSynchronisationQuery orderByLang($order = Criteria::ASC) Order by the lang column
 * @method     ChildGoogleshoppingProductSynchronisationQuery orderBySyncEnable($order = Criteria::ASC) Order by the sync_enable column
 * @method     ChildGoogleshoppingProductSynchronisationQuery orderByGoogleshoppingAccountId($order = Criteria::ASC) Order by the googleshopping_account_id column
 *
 * @method     ChildGoogleshoppingProductSynchronisationQuery groupById() Group by the id column
 * @method     ChildGoogleshoppingProductSynchronisationQuery groupByProductId() Group by the product_id column
 * @method     ChildGoogleshoppingProductSynchronisationQuery groupByTargetCountry() Group by the target_country column
 * @method     ChildGoogleshoppingProductSynchronisationQuery groupByLang() Group by the lang column
 * @method     ChildGoogleshoppingProductSynchronisationQuery groupBySyncEnable() Group by the sync_enable column
 * @method     ChildGoogleshoppingProductSynchronisationQuery groupByGoogleshoppingAccountId() Group by the googleshopping_account_id column
 *
 * @method     ChildGoogleshoppingProductSynchronisationQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildGoogleshoppingProductSynchronisationQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildGoogleshoppingProductSynchronisationQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildGoogleshoppingProductSynchronisationQuery leftJoinProduct($relationAlias = null) Adds a LEFT JOIN clause to the query using the Product relation
 * @method     ChildGoogleshoppingProductSynchronisationQuery rightJoinProduct($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Product relation
 * @method     ChildGoogleshoppingProductSynchronisationQuery innerJoinProduct($relationAlias = null) Adds a INNER JOIN clause to the query using the Product relation
 *
 * @method     ChildGoogleshoppingProductSynchronisationQuery leftJoinGoogleshoppingAccount($relationAlias = null) Adds a LEFT JOIN clause to the query using the GoogleshoppingAccount relation
 * @method     ChildGoogleshoppingProductSynchronisationQuery rightJoinGoogleshoppingAccount($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GoogleshoppingAccount relation
 * @method     ChildGoogleshoppingProductSynchronisationQuery innerJoinGoogleshoppingAccount($relationAlias = null) Adds a INNER JOIN clause to the query using the GoogleshoppingAccount relation
 *
 * @method     ChildGoogleshoppingProductSynchronisation findOne(ConnectionInterface $con = null) Return the first ChildGoogleshoppingProductSynchronisation matching the query
 * @method     ChildGoogleshoppingProductSynchronisation findOneOrCreate(ConnectionInterface $con = null) Return the first ChildGoogleshoppingProductSynchronisation matching the query, or a new ChildGoogleshoppingProductSynchronisation object populated from the query conditions when no match is found
 *
 * @method     ChildGoogleshoppingProductSynchronisation findOneById(int $id) Return the first ChildGoogleshoppingProductSynchronisation filtered by the id column
 * @method     ChildGoogleshoppingProductSynchronisation findOneByProductId(int $product_id) Return the first ChildGoogleshoppingProductSynchronisation filtered by the product_id column
 * @method     ChildGoogleshoppingProductSynchronisation findOneByTargetCountry(string $target_country) Return the first ChildGoogleshoppingProductSynchronisation filtered by the target_country column
 * @method     ChildGoogleshoppingProductSynchronisation findOneByLang(string $lang) Return the first ChildGoogleshoppingProductSynchronisation filtered by the lang column
 * @method     ChildGoogleshoppingProductSynchronisation findOneBySyncEnable(boolean $sync_enable) Return the first ChildGoogleshoppingProductSynchronisation filtered by the sync_enable column
 * @method     ChildGoogleshoppingProductSynchronisation findOneByGoogleshoppingAccountId(int $googleshopping_account_id) Return the first ChildGoogleshoppingProductSynchronisation filtered by the googleshopping_account_id column
 *
 * @method     array findById(int $id) Return ChildGoogleshoppingProductSynchronisation objects filtered by the id column
 * @method     array findByProductId(int $product_id) Return ChildGoogleshoppingProductSynchronisation objects filtered by the product_id column
 * @method     array findByTargetCountry(string $target_country) Return ChildGoogleshoppingProductSynchronisation objects filtered by the target_country column
 * @method     array findByLang(string $lang) Return ChildGoogleshoppingProductSynchronisation objects filtered by the lang column
 * @method     array findBySyncEnable(boolean $sync_enable) Return ChildGoogleshoppingProductSynchronisation objects filtered by the sync_enable column
 * @method     array findByGoogleshoppingAccountId(int $googleshopping_account_id) Return ChildGoogleshoppingProductSynchronisation objects filtered by the googleshopping_account_id column
 *
 */
abstract class GoogleshoppingProductSynchronisationQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \GoogleShopping\Model\Base\GoogleshoppingProductSynchronisationQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\GoogleShopping\\Model\\GoogleshoppingProductSynchronisation', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildGoogleshoppingProductSynchronisationQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery) {
            return $criteria;
        }
        $query = new \GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildGoogleshoppingProductSynchronisation|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = GoogleshoppingProductSynchronisationTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(GoogleshoppingProductSynchronisationTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildGoogleshoppingProductSynchronisation A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, PRODUCT_ID, TARGET_COUNTRY, LANG, SYNC_ENABLE, GOOGLESHOPPING_ACCOUNT_ID FROM googleshopping_product_synchronisation WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildGoogleshoppingProductSynchronisation();
            $obj->hydrate($row);
            GoogleshoppingProductSynchronisationTableMap::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildGoogleshoppingProductSynchronisation|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the product_id column
     *
     * Example usage:
     * <code>
     * $query->filterByProductId(1234); // WHERE product_id = 1234
     * $query->filterByProductId(array(12, 34)); // WHERE product_id IN (12, 34)
     * $query->filterByProductId(array('min' => 12)); // WHERE product_id > 12
     * </code>
     *
     * @see       filterByProduct()
     *
     * @param     mixed $productId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterByProductId($productId = null, $comparison = null)
    {
        if (is_array($productId)) {
            $useMinMax = false;
            if (isset($productId['min'])) {
                $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::PRODUCT_ID, $productId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($productId['max'])) {
                $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::PRODUCT_ID, $productId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::PRODUCT_ID, $productId, $comparison);
    }

    /**
     * Filter the query on the target_country column
     *
     * Example usage:
     * <code>
     * $query->filterByTargetCountry('fooValue');   // WHERE target_country = 'fooValue'
     * $query->filterByTargetCountry('%fooValue%'); // WHERE target_country LIKE '%fooValue%'
     * </code>
     *
     * @param     string $targetCountry The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterByTargetCountry($targetCountry = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($targetCountry)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $targetCountry)) {
                $targetCountry = str_replace('*', '%', $targetCountry);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::TARGET_COUNTRY, $targetCountry, $comparison);
    }

    /**
     * Filter the query on the lang column
     *
     * Example usage:
     * <code>
     * $query->filterByLang('fooValue');   // WHERE lang = 'fooValue'
     * $query->filterByLang('%fooValue%'); // WHERE lang LIKE '%fooValue%'
     * </code>
     *
     * @param     string $lang The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterByLang($lang = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lang)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $lang)) {
                $lang = str_replace('*', '%', $lang);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::LANG, $lang, $comparison);
    }

    /**
     * Filter the query on the sync_enable column
     *
     * Example usage:
     * <code>
     * $query->filterBySyncEnable(true); // WHERE sync_enable = true
     * $query->filterBySyncEnable('yes'); // WHERE sync_enable = true
     * </code>
     *
     * @param     boolean|string $syncEnable The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterBySyncEnable($syncEnable = null, $comparison = null)
    {
        if (is_string($syncEnable)) {
            $sync_enable = in_array(strtolower($syncEnable), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::SYNC_ENABLE, $syncEnable, $comparison);
    }

    /**
     * Filter the query on the googleshopping_account_id column
     *
     * Example usage:
     * <code>
     * $query->filterByGoogleshoppingAccountId(1234); // WHERE googleshopping_account_id = 1234
     * $query->filterByGoogleshoppingAccountId(array(12, 34)); // WHERE googleshopping_account_id IN (12, 34)
     * $query->filterByGoogleshoppingAccountId(array('min' => 12)); // WHERE googleshopping_account_id > 12
     * </code>
     *
     * @see       filterByGoogleshoppingAccount()
     *
     * @param     mixed $googleshoppingAccountId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterByGoogleshoppingAccountId($googleshoppingAccountId = null, $comparison = null)
    {
        if (is_array($googleshoppingAccountId)) {
            $useMinMax = false;
            if (isset($googleshoppingAccountId['min'])) {
                $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::GOOGLESHOPPING_ACCOUNT_ID, $googleshoppingAccountId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($googleshoppingAccountId['max'])) {
                $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::GOOGLESHOPPING_ACCOUNT_ID, $googleshoppingAccountId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::GOOGLESHOPPING_ACCOUNT_ID, $googleshoppingAccountId, $comparison);
    }

    /**
     * Filter the query by a related \GoogleShopping\Model\Thelia\Model\Product object
     *
     * @param \GoogleShopping\Model\Thelia\Model\Product|ObjectCollection $product The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterByProduct($product, $comparison = null)
    {
        if ($product instanceof \GoogleShopping\Model\Thelia\Model\Product) {
            return $this
                ->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::PRODUCT_ID, $product->getId(), $comparison);
        } elseif ($product instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::PRODUCT_ID, $product->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByProduct() only accepts arguments of type \GoogleShopping\Model\Thelia\Model\Product or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Product relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function joinProduct($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Product');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Product');
        }

        return $this;
    }

    /**
     * Use the Product relation Product object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \GoogleShopping\Model\Thelia\Model\ProductQuery A secondary query class using the current class as primary query
     */
    public function useProductQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinProduct($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Product', '\GoogleShopping\Model\Thelia\Model\ProductQuery');
    }

    /**
     * Filter the query by a related \GoogleShopping\Model\GoogleshoppingAccount object
     *
     * @param \GoogleShopping\Model\GoogleshoppingAccount|ObjectCollection $googleshoppingAccount The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function filterByGoogleshoppingAccount($googleshoppingAccount, $comparison = null)
    {
        if ($googleshoppingAccount instanceof \GoogleShopping\Model\GoogleshoppingAccount) {
            return $this
                ->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::GOOGLESHOPPING_ACCOUNT_ID, $googleshoppingAccount->getId(), $comparison);
        } elseif ($googleshoppingAccount instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::GOOGLESHOPPING_ACCOUNT_ID, $googleshoppingAccount->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByGoogleshoppingAccount() only accepts arguments of type \GoogleShopping\Model\GoogleshoppingAccount or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GoogleshoppingAccount relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function joinGoogleshoppingAccount($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GoogleshoppingAccount');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GoogleshoppingAccount');
        }

        return $this;
    }

    /**
     * Use the GoogleshoppingAccount relation GoogleshoppingAccount object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \GoogleShopping\Model\GoogleshoppingAccountQuery A secondary query class using the current class as primary query
     */
    public function useGoogleshoppingAccountQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinGoogleshoppingAccount($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GoogleshoppingAccount', '\GoogleShopping\Model\GoogleshoppingAccountQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildGoogleshoppingProductSynchronisation $googleshoppingProductSynchronisation Object to remove from the list of results
     *
     * @return ChildGoogleshoppingProductSynchronisationQuery The current query, for fluid interface
     */
    public function prune($googleshoppingProductSynchronisation = null)
    {
        if ($googleshoppingProductSynchronisation) {
            $this->addUsingAlias(GoogleshoppingProductSynchronisationTableMap::ID, $googleshoppingProductSynchronisation->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the googleshopping_product_synchronisation table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GoogleshoppingProductSynchronisationTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            GoogleshoppingProductSynchronisationTableMap::clearInstancePool();
            GoogleshoppingProductSynchronisationTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildGoogleshoppingProductSynchronisation or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildGoogleshoppingProductSynchronisation object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GoogleshoppingProductSynchronisationTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(GoogleshoppingProductSynchronisationTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        GoogleshoppingProductSynchronisationTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            GoogleshoppingProductSynchronisationTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // GoogleshoppingProductSynchronisationQuery
