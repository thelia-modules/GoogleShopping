<?php

namespace GoogleShopping\Model\Map;

use GoogleShopping\Model\GoogleshoppingConfiguration;
use GoogleShopping\Model\GoogleshoppingConfigurationQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'googleshopping_configuration' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class GoogleshoppingConfigurationTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;
    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'GoogleShopping.Model.Map.GoogleshoppingConfigurationTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'thelia';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'googleshopping_configuration';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\GoogleShopping\\Model\\GoogleshoppingConfiguration';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'GoogleShopping.Model.GoogleshoppingConfiguration';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 8;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 8;

    /**
     * the column name for the ID field
     */
    const ID = 'googleshopping_configuration.ID';

    /**
     * the column name for the TITLE field
     */
    const TITLE = 'googleshopping_configuration.TITLE';

    /**
     * the column name for the MERCHANT_ID field
     */
    const MERCHANT_ID = 'googleshopping_configuration.MERCHANT_ID';

    /**
     * the column name for the LANG_ID field
     */
    const LANG_ID = 'googleshopping_configuration.LANG_ID';

    /**
     * the column name for the COUNTRY_ID field
     */
    const COUNTRY_ID = 'googleshopping_configuration.COUNTRY_ID';

    /**
     * the column name for the CURRENCY_ID field
     */
    const CURRENCY_ID = 'googleshopping_configuration.CURRENCY_ID';

    /**
     * the column name for the IS_DEFAULT field
     */
    const IS_DEFAULT = 'googleshopping_configuration.IS_DEFAULT';

    /**
     * the column name for the SYNC field
     */
    const SYNC = 'googleshopping_configuration.SYNC';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'Title', 'MerchantId', 'LangId', 'CountryId', 'CurrencyId', 'IsDefault', 'Sync', ),
        self::TYPE_STUDLYPHPNAME => array('id', 'title', 'merchantId', 'langId', 'countryId', 'currencyId', 'isDefault', 'sync', ),
        self::TYPE_COLNAME       => array(GoogleshoppingConfigurationTableMap::ID, GoogleshoppingConfigurationTableMap::TITLE, GoogleshoppingConfigurationTableMap::MERCHANT_ID, GoogleshoppingConfigurationTableMap::LANG_ID, GoogleshoppingConfigurationTableMap::COUNTRY_ID, GoogleshoppingConfigurationTableMap::CURRENCY_ID, GoogleshoppingConfigurationTableMap::IS_DEFAULT, GoogleshoppingConfigurationTableMap::SYNC, ),
        self::TYPE_RAW_COLNAME   => array('ID', 'TITLE', 'MERCHANT_ID', 'LANG_ID', 'COUNTRY_ID', 'CURRENCY_ID', 'IS_DEFAULT', 'SYNC', ),
        self::TYPE_FIELDNAME     => array('id', 'title', 'merchant_id', 'lang_id', 'country_id', 'currency_id', 'is_default', 'sync', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Title' => 1, 'MerchantId' => 2, 'LangId' => 3, 'CountryId' => 4, 'CurrencyId' => 5, 'IsDefault' => 6, 'Sync' => 7, ),
        self::TYPE_STUDLYPHPNAME => array('id' => 0, 'title' => 1, 'merchantId' => 2, 'langId' => 3, 'countryId' => 4, 'currencyId' => 5, 'isDefault' => 6, 'sync' => 7, ),
        self::TYPE_COLNAME       => array(GoogleshoppingConfigurationTableMap::ID => 0, GoogleshoppingConfigurationTableMap::TITLE => 1, GoogleshoppingConfigurationTableMap::MERCHANT_ID => 2, GoogleshoppingConfigurationTableMap::LANG_ID => 3, GoogleshoppingConfigurationTableMap::COUNTRY_ID => 4, GoogleshoppingConfigurationTableMap::CURRENCY_ID => 5, GoogleshoppingConfigurationTableMap::IS_DEFAULT => 6, GoogleshoppingConfigurationTableMap::SYNC => 7, ),
        self::TYPE_RAW_COLNAME   => array('ID' => 0, 'TITLE' => 1, 'MERCHANT_ID' => 2, 'LANG_ID' => 3, 'COUNTRY_ID' => 4, 'CURRENCY_ID' => 5, 'IS_DEFAULT' => 6, 'SYNC' => 7, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'title' => 1, 'merchant_id' => 2, 'lang_id' => 3, 'country_id' => 4, 'currency_id' => 5, 'is_default' => 6, 'sync' => 7, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('googleshopping_configuration');
        $this->setPhpName('GoogleshoppingConfiguration');
        $this->setClassName('\\GoogleShopping\\Model\\GoogleshoppingConfiguration');
        $this->setPackage('GoogleShopping.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('TITLE', 'Title', 'VARCHAR', false, 255, null);
        $this->addColumn('MERCHANT_ID', 'MerchantId', 'VARCHAR', true, 255, null);
        $this->addForeignKey('LANG_ID', 'LangId', 'INTEGER', 'lang', 'ID', false, null, null);
        $this->addForeignKey('COUNTRY_ID', 'CountryId', 'INTEGER', 'country', 'ID', false, null, null);
        $this->addForeignKey('CURRENCY_ID', 'CurrencyId', 'INTEGER', 'currency', 'ID', false, null, null);
        $this->addColumn('IS_DEFAULT', 'IsDefault', 'BOOLEAN', false, 1, null);
        $this->addColumn('SYNC', 'Sync', 'BOOLEAN', false, 1, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Lang', '\\GoogleShopping\\Model\\Thelia\\Model\\Lang', RelationMap::MANY_TO_ONE, array('lang_id' => 'id', ), 'CASCADE', 'RESTRICT');
        $this->addRelation('Country', '\\GoogleShopping\\Model\\Thelia\\Model\\Country', RelationMap::MANY_TO_ONE, array('country_id' => 'id', ), 'CASCADE', 'RESTRICT');
        $this->addRelation('Currency', '\\GoogleShopping\\Model\\Thelia\\Model\\Currency', RelationMap::MANY_TO_ONE, array('currency_id' => 'id', ), 'CASCADE', 'RESTRICT');
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {

            return (int) $row[
                            $indexType == TableMap::TYPE_NUM
                            ? 0 + $offset
                            : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
                        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? GoogleshoppingConfigurationTableMap::CLASS_DEFAULT : GoogleshoppingConfigurationTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     * @return array (GoogleshoppingConfiguration object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = GoogleshoppingConfigurationTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = GoogleshoppingConfigurationTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + GoogleshoppingConfigurationTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = GoogleshoppingConfigurationTableMap::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            GoogleshoppingConfigurationTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = GoogleshoppingConfigurationTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = GoogleshoppingConfigurationTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                GoogleshoppingConfigurationTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(GoogleshoppingConfigurationTableMap::ID);
            $criteria->addSelectColumn(GoogleshoppingConfigurationTableMap::TITLE);
            $criteria->addSelectColumn(GoogleshoppingConfigurationTableMap::MERCHANT_ID);
            $criteria->addSelectColumn(GoogleshoppingConfigurationTableMap::LANG_ID);
            $criteria->addSelectColumn(GoogleshoppingConfigurationTableMap::COUNTRY_ID);
            $criteria->addSelectColumn(GoogleshoppingConfigurationTableMap::CURRENCY_ID);
            $criteria->addSelectColumn(GoogleshoppingConfigurationTableMap::IS_DEFAULT);
            $criteria->addSelectColumn(GoogleshoppingConfigurationTableMap::SYNC);
        } else {
            $criteria->addSelectColumn($alias . '.ID');
            $criteria->addSelectColumn($alias . '.TITLE');
            $criteria->addSelectColumn($alias . '.MERCHANT_ID');
            $criteria->addSelectColumn($alias . '.LANG_ID');
            $criteria->addSelectColumn($alias . '.COUNTRY_ID');
            $criteria->addSelectColumn($alias . '.CURRENCY_ID');
            $criteria->addSelectColumn($alias . '.IS_DEFAULT');
            $criteria->addSelectColumn($alias . '.SYNC');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(GoogleshoppingConfigurationTableMap::DATABASE_NAME)->getTable(GoogleshoppingConfigurationTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getServiceContainer()->getDatabaseMap(GoogleshoppingConfigurationTableMap::DATABASE_NAME);
      if (!$dbMap->hasTable(GoogleshoppingConfigurationTableMap::TABLE_NAME)) {
        $dbMap->addTableObject(new GoogleshoppingConfigurationTableMap());
      }
    }

    /**
     * Performs a DELETE on the database, given a GoogleshoppingConfiguration or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or GoogleshoppingConfiguration object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GoogleshoppingConfigurationTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \GoogleShopping\Model\GoogleshoppingConfiguration) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(GoogleshoppingConfigurationTableMap::DATABASE_NAME);
            $criteria->add(GoogleshoppingConfigurationTableMap::ID, (array) $values, Criteria::IN);
        }

        $query = GoogleshoppingConfigurationQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) { GoogleshoppingConfigurationTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) { GoogleshoppingConfigurationTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the googleshopping_configuration table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return GoogleshoppingConfigurationQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a GoogleshoppingConfiguration or Criteria object.
     *
     * @param mixed               $criteria Criteria or GoogleshoppingConfiguration object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GoogleshoppingConfigurationTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from GoogleshoppingConfiguration object
        }

        if ($criteria->containsKey(GoogleshoppingConfigurationTableMap::ID) && $criteria->keyContainsValue(GoogleshoppingConfigurationTableMap::ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.GoogleshoppingConfigurationTableMap::ID.')');
        }


        // Set the correct dbName
        $query = GoogleshoppingConfigurationQuery::create()->mergeWith($criteria);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = $query->doInsert($con);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

} // GoogleshoppingConfigurationTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
GoogleshoppingConfigurationTableMap::buildTableMap();
