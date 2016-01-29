<?php

namespace GoogleShopping\Model\Base;

use \Exception;
use \PDO;
use GoogleShopping\Model\GoogleshoppingProductSyncQueue as ChildGoogleshoppingProductSyncQueue;
use GoogleShopping\Model\GoogleshoppingProductSyncQueueQuery as ChildGoogleshoppingProductSyncQueueQuery;
use GoogleShopping\Model\Map\GoogleshoppingProductSyncQueueTableMap;
use GoogleShopping\Model\Thelia\Model\ProductSaleElements;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'googleshopping_product_sync_queue' table.
 *
 *
 *
 * @method     ChildGoogleshoppingProductSyncQueueQuery orderByProductSaleElementsId($order = Criteria::ASC) Order by the product_sale_elements_id column
 * @method     ChildGoogleshoppingProductSyncQueueQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildGoogleshoppingProductSyncQueueQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method     ChildGoogleshoppingProductSyncQueueQuery groupByProductSaleElementsId() Group by the product_sale_elements_id column
 * @method     ChildGoogleshoppingProductSyncQueueQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildGoogleshoppingProductSyncQueueQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method     ChildGoogleshoppingProductSyncQueueQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildGoogleshoppingProductSyncQueueQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildGoogleshoppingProductSyncQueueQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildGoogleshoppingProductSyncQueueQuery leftJoinProductSaleElements($relationAlias = null) Adds a LEFT JOIN clause to the query using the ProductSaleElements relation
 * @method     ChildGoogleshoppingProductSyncQueueQuery rightJoinProductSaleElements($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ProductSaleElements relation
 * @method     ChildGoogleshoppingProductSyncQueueQuery innerJoinProductSaleElements($relationAlias = null) Adds a INNER JOIN clause to the query using the ProductSaleElements relation
 *
 * @method     ChildGoogleshoppingProductSyncQueue findOne(ConnectionInterface $con = null) Return the first ChildGoogleshoppingProductSyncQueue matching the query
 * @method     ChildGoogleshoppingProductSyncQueue findOneOrCreate(ConnectionInterface $con = null) Return the first ChildGoogleshoppingProductSyncQueue matching the query, or a new ChildGoogleshoppingProductSyncQueue object populated from the query conditions when no match is found
 *
 * @method     ChildGoogleshoppingProductSyncQueue findOneByProductSaleElementsId(int $product_sale_elements_id) Return the first ChildGoogleshoppingProductSyncQueue filtered by the product_sale_elements_id column
 * @method     ChildGoogleshoppingProductSyncQueue findOneByCreatedAt(string $created_at) Return the first ChildGoogleshoppingProductSyncQueue filtered by the created_at column
 * @method     ChildGoogleshoppingProductSyncQueue findOneByUpdatedAt(string $updated_at) Return the first ChildGoogleshoppingProductSyncQueue filtered by the updated_at column
 *
 * @method     array findByProductSaleElementsId(int $product_sale_elements_id) Return ChildGoogleshoppingProductSyncQueue objects filtered by the product_sale_elements_id column
 * @method     array findByCreatedAt(string $created_at) Return ChildGoogleshoppingProductSyncQueue objects filtered by the created_at column
 * @method     array findByUpdatedAt(string $updated_at) Return ChildGoogleshoppingProductSyncQueue objects filtered by the updated_at column
 *
 */
abstract class GoogleshoppingProductSyncQueueQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \GoogleShopping\Model\Base\GoogleshoppingProductSyncQueueQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\GoogleShopping\\Model\\GoogleshoppingProductSyncQueue', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildGoogleshoppingProductSyncQueueQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildGoogleshoppingProductSyncQueueQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \GoogleShopping\Model\GoogleshoppingProductSyncQueueQuery) {
            return $criteria;
        }
        $query = new \GoogleShopping\Model\GoogleshoppingProductSyncQueueQuery();
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
     * @return ChildGoogleshoppingProductSyncQueue|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = GoogleshoppingProductSyncQueueTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(GoogleshoppingProductSyncQueueTableMap::DATABASE_NAME);
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
     * @return   ChildGoogleshoppingProductSyncQueue A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT PRODUCT_SALE_ELEMENTS_ID, CREATED_AT, UPDATED_AT FROM googleshopping_product_sync_queue WHERE PRODUCT_SALE_ELEMENTS_ID = :p0';
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
            $obj = new ChildGoogleshoppingProductSyncQueue();
            $obj->hydrate($row);
            GoogleshoppingProductSyncQueueTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildGoogleshoppingProductSyncQueue|array|mixed the result, formatted by the current formatter
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
     * @return ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::PRODUCT_SALE_ELEMENTS_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::PRODUCT_SALE_ELEMENTS_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the product_sale_elements_id column
     *
     * Example usage:
     * <code>
     * $query->filterByProductSaleElementsId(1234); // WHERE product_sale_elements_id = 1234
     * $query->filterByProductSaleElementsId(array(12, 34)); // WHERE product_sale_elements_id IN (12, 34)
     * $query->filterByProductSaleElementsId(array('min' => 12)); // WHERE product_sale_elements_id > 12
     * </code>
     *
     * @see       filterByProductSaleElements()
     *
     * @param     mixed $productSaleElementsId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function filterByProductSaleElementsId($productSaleElementsId = null, $comparison = null)
    {
        if (is_array($productSaleElementsId)) {
            $useMinMax = false;
            if (isset($productSaleElementsId['min'])) {
                $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::PRODUCT_SALE_ELEMENTS_ID, $productSaleElementsId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($productSaleElementsId['max'])) {
                $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::PRODUCT_SALE_ELEMENTS_ID, $productSaleElementsId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::PRODUCT_SALE_ELEMENTS_ID, $productSaleElementsId, $comparison);
    }

    /**
     * Filter the query on the created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE created_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::CREATED_AT, $createdAt, $comparison);
    }

    /**
     * Filter the query on the updated_at column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdatedAt('2011-03-14'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt('now'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt(array('max' => 'yesterday')); // WHERE updated_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $updatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related \GoogleShopping\Model\Thelia\Model\ProductSaleElements object
     *
     * @param \GoogleShopping\Model\Thelia\Model\ProductSaleElements|ObjectCollection $productSaleElements The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function filterByProductSaleElements($productSaleElements, $comparison = null)
    {
        if ($productSaleElements instanceof \GoogleShopping\Model\Thelia\Model\ProductSaleElements) {
            return $this
                ->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::PRODUCT_SALE_ELEMENTS_ID, $productSaleElements->getId(), $comparison);
        } elseif ($productSaleElements instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::PRODUCT_SALE_ELEMENTS_ID, $productSaleElements->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByProductSaleElements() only accepts arguments of type \GoogleShopping\Model\Thelia\Model\ProductSaleElements or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ProductSaleElements relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function joinProductSaleElements($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ProductSaleElements');

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
            $this->addJoinObject($join, 'ProductSaleElements');
        }

        return $this;
    }

    /**
     * Use the ProductSaleElements relation ProductSaleElements object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \GoogleShopping\Model\Thelia\Model\ProductSaleElementsQuery A secondary query class using the current class as primary query
     */
    public function useProductSaleElementsQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinProductSaleElements($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ProductSaleElements', '\GoogleShopping\Model\Thelia\Model\ProductSaleElementsQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildGoogleshoppingProductSyncQueue $googleshoppingProductSyncQueue Object to remove from the list of results
     *
     * @return ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function prune($googleshoppingProductSyncQueue = null)
    {
        if ($googleshoppingProductSyncQueue) {
            $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::PRODUCT_SALE_ELEMENTS_ID, $googleshoppingProductSyncQueue->getProductSaleElementsId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the googleshopping_product_sync_queue table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GoogleshoppingProductSyncQueueTableMap::DATABASE_NAME);
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
            GoogleshoppingProductSyncQueueTableMap::clearInstancePool();
            GoogleshoppingProductSyncQueueTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildGoogleshoppingProductSyncQueue or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildGoogleshoppingProductSyncQueue object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(GoogleshoppingProductSyncQueueTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(GoogleshoppingProductSyncQueueTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        GoogleshoppingProductSyncQueueTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            GoogleshoppingProductSyncQueueTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

    // timestampable behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(GoogleshoppingProductSyncQueueTableMap::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(GoogleshoppingProductSyncQueueTableMap::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(GoogleshoppingProductSyncQueueTableMap::UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(GoogleshoppingProductSyncQueueTableMap::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return     ChildGoogleshoppingProductSyncQueueQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(GoogleshoppingProductSyncQueueTableMap::CREATED_AT);
    }

} // GoogleshoppingProductSyncQueueQuery
