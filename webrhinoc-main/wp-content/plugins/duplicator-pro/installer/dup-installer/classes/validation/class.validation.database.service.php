<?php

/**
 * Validation object
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapOS;

require_once(DUPX_INIT . '/api/class.cpnl.ctrl.php');

class DUPX_Validation_database_service
{

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @var mysqli
     */
    private $dbh = null;

    /**
     *
     * @var bool
     */
    private $skipOtherTests = false;

    /**
     *
     * @var DUPX_cPanel_Controller
     */
    private $cpnlAPI = null;

    /**
     *
     * @var string
     */
    private $cpnlToken = null;

    /**
     *
     * @var DUPX_cPanelHost
     */
    private $cpnlConnection = null;

    /**
     *
     * @var bool
     */
    private $userCreated = false;

    /**
     *
     * @var bool
     */
    private $dbCreated = false;

    /**
     *
     * @var array
     */
    private $supportedCharsetList = array();

    /**
     *
     * @var array
     */
    private $supportedCollates = array();

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->cpnlAPI = new DUPX_cPanel_Controller();
    }

    /**
     *
     * @return mysqli <p>Returns an object which represents the connection to a MySQL Server.</p>
     */
    public function getDbConnection()
    {
        if (is_null($this->dbh)) {
            $paramsManager = PrmMng::getInstance();

            $this->dbh = DUPX_DB_Functions::getInstance()->dbConnection(array(
                'dbhost' => $paramsManager->getValue(PrmMng::PARAM_DB_HOST),
                'dbuser' => $paramsManager->getValue(PrmMng::PARAM_DB_USER),
                'dbpass' => $paramsManager->getValue(PrmMng::PARAM_DB_PASS),
                'dbname' => null
            ));

            if (empty($this->dbh)) {
                DUPX_DB_Functions::getInstance()->closeDbConnection();
                $this->dbh                  = false;
                $this->supportedCharsetList = array();
                $this->supportedCollates    = array();
            } else {
                $this->supportedCharsetList = DUPX_DB::getSupportedCharSetList($this->dbh);
                $this->supportedCollates    = DUPX_DB::getSupportedCollates($this->dbh);
            }
        }

        return $this->dbh;
    }

    /**
     * close db connection if is open
     */
    public function closeDbConnection()
    {
        if (!is_null($this->dbh)) {
            mysqli_close($this->dbh);
            $this->dbh = null;
        }
    }

    public function setSkipOtherTests($skip = true)
    {
        $this->skipOtherTests = (bool) $skip;
    }

    public function skipDatabaseTests()
    {
        return $this->skipOtherTests;
    }

    /**
     *
     * @return string
     */
    public function getCpnlToken()
    {
        if (is_null($this->cpnlToken)) {
            try {
                $paramsManager   = PrmMng::getInstance();
                $this->cpnlToken = $this->cpnlAPI->create_token(
                    $paramsManager->getValue(PrmMng::PARAM_CPNL_HOST),
                    $paramsManager->getValue(PrmMng::PARAM_CPNL_USER),
                    $paramsManager->getValue(PrmMng::PARAM_CPNL_PASS)
                );
            } catch (Exception $e) {
                Log::logException($e, Log::LV_DEFAULT, 'CPANEL CREATE TOKEN EXCEPTION: ');
                $this->cpnlToken = false;
            } catch (Error $e) {
                Log::logException($e, Log::LV_DEFAULT, 'CPANEL CREATE TOKEN EXCEPTION: ');
                $this->cpnlToken = false;
            }
        }

        return $this->cpnlToken;
    }

    /**
     *
     * @return DUPX_cPanelHost
     */
    public function getCpnlConnection()
    {
        if (is_null($this->cpnlConnection)) {
            if ($this->getCpnlToken() === false) {
                $this->cpnlConnection = false;
            } else {
                try {
                    $this->cpnlConnection = $this->cpnlAPI->connect($this->cpnlToken);
                } catch (Exception $e) {
                    Log::logException($e, Log::LV_DEFAULT, 'CPANEL CONNECTION EXCEPTION: ');
                    $this->cpnlConnection = false;
                } catch (Error $e) {
                    Log::logException($e, Log::LV_DEFAULT, 'CPANEL CONNECTION EXCEPTION: ');
                    $this->cpnlConnection = false;
                }
            }
        }

        return $this->cpnlConnection;
    }

    public function cpnlCreateDbUser(&$userResult = null)
    {
        if ($this->userCreated) {
            return true;
        }

        try {
            if (!$this->getCpnlConnection()) {
                throw new Exception('Cpanel not connected');
            }

            $paramsManager = PrmMng::getInstance();
            $userResult    = $this->cpnlAPI->create_db_user(
                $this->cpnlToken,
                $paramsManager->getValue(PrmMng::PARAM_DB_USER),
                $paramsManager->getValue(PrmMng::PARAM_DB_PASS)
            );
        } catch (Exception $e) {
            $userResult['status'] = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'CPANEL CREATE DB USER EXCEPTION: ');
            return false;
        } catch (Error $e) {
            $userResult['status'] = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'CPANEL CREATE DB USER EXCEPTION: ');
            return false;
        }

        if ($userResult['status'] === true) {
            $this->userCreated = true;
            return true;
        } else {
            return false;
        }
    }

    public function databaseExists(&$errorMessage = null)
    {
        try {
            $result = true;

            if (!$this->getDbConnection()) {
                throw new Exception('Database not connected');
            }

            $paramsManager = PrmMng::getInstance();
            if (mysqli_select_db($this->dbh, $paramsManager->getValue(PrmMng::PARAM_DB_NAME)) !== true) {
                $errorMessage = mysqli_error($this->dbh);
                $result       = false;
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE SELECT EXCEPTION: ');
            $result       = false;
        } catch (Error $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE SELECT EXCEPTION: ');
            $result       = false;
        }

        return $result;
    }

    public function createDatabase(&$errorMessage = null)
    {
        if ($this->dbCreated) {
            return true;
        }

        try {
            $result = true;

            if (!$this->getDbConnection()) {
                throw new Exception('Database not connected');
            }

            $dbName = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME);

            switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_VIEW_MODE)) {
                case 'basic':
                    $query = 'CREATE DATABASE `' . mysqli_real_escape_string($this->dbh, $dbName) . '`';
                    if (DUPX_DB::mysqli_query($this->dbh, $query) === false) {
                        $errorMessage = mysqli_error($this->dbh);
                        $result       = false;
                    }

                    if ($result && $this->databaseExists() === false) {
                        $errorMessage = 'Can\'t select database after creation';
                        $result       = false;
                    }
                    break;
                case 'cpnl':
                    $result = $this->cpnlAPI->create_db($this->cpnlToken, $dbName);
                    if ($result['status'] !== true) {
                        $errorMessage = $result['status'];
                        $result       = false;
                    }
                    break;
                default:
                    $result       = false;
                    $errorMessage = 'Invalid db view mode';
                    break;
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CREATE EXCEPTION: ');
            $result       = false;
        } catch (Error $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CREATE EXCEPTION: ');
            $result       = false;
        }

        if ($result) {
            $this->dbCreated = true;
            return true;
        } else {
            return false;
        }
    }

    public function isDatabaseCreated()
    {
        return $this->dbCreated;
    }

    public function cleanUpDatabase(&$errorMessage = null)
    {
        if (!$this->dbCreated) {
            return true;
        }

        $result = true;

        try {
            $dbName = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME);
            switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_VIEW_MODE)) {
                case 'basic':
                    //DELETE DB
                    if (DUPX_DB::mysqli_query($this->dbh, "DROP DATABASE IF EXISTS `" . mysqli_real_escape_string($this->dbh, $dbName) . "`") === false) {
                        $errorMessage = mysqli_error($this->dbh);
                        $result       = false;
                    }
                    break;
                case 'cpnl':
                    //DELETE DB
                    $result = $this->cpnlAPI->delete_db($this->cpnlToken, $dbName);
                    if ($result['status'] !== true) {
                        $errorMessage = $result['status'];
                        $result       = false;
                    }
                    break;
                default:
                    $errorMessage = 'Invalid db view mode';
                    $result       = false;
                    break;
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CLEANUP EXCEPTION: ');
            $result       = false;
        } catch (Error $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CLEANUP EXCEPTION: ');
            $result       = false;
        }

        if ($result) {
            $this->dbCreated = false;
        }
        return $result;
    }

    public function isUserCreated()
    {
        return $this->userCreated;
    }

    public function cleanUpUser(&$errorMessage = null)
    {
        if (!$this->userCreated) {
            return true;
        }

        $result = true;

        try {
            $dbUser = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER);
            switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_VIEW_MODE)) {
                case 'cpnl':
                    //DELETE DB USER
                    $result = $this->cpnlAPI->delete_db_user($this->cpnlToken, $dbUser);
                    if ($result['status'] !== true) {
                        $errorMessage = $result['status'];
                        $result       = false;
                    }
                    break;
                case 'basic':
                default:
                    $result       = false;
                    $errorMessage = 'Invalid db view mode';
                    break;
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE USER CLEANUP EXCEPTION: ');
            $result       = false;
        } catch (Error $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE USER CLEANUP EXCEPTION: ');
            $result       = false;
        }

        if ($result) {
            $this->userCreated = false;
        }
        return $result;
    }

    public function getDatabases()
    {
        if (!$this->getDbConnection()) {
            return array();
        }

        switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_VIEW_MODE)) {
            case 'basic':
                $dbUser    = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER);
                $host_user = substr_replace($dbUser, '', strpos($dbUser, '_'));
                break;
            case 'cpnl':
                $host_user = PrmMng::getInstance()->getValue(PrmMng::PARAM_CPNL_USER);
                break;
            default:
                return array();
        }
        return DUPX_DB::getDatabases($this->dbh, $host_user);
    }

    /**
     * @param string $dbAction The DB action chosen by the user
     * @return string[] Array of tables that are affect by the DB action chosen
     * @throws Exception
     */
    public function getDBActionAffectedTables($dbAction)
    {
        $affectedTables = array();
        $excludeTables = DUPX_DB_Functions::getExcludedTables();
        $escapedDbName  = mysqli_real_escape_string($this->dbh, PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME));
        $allTables      = DUPX_DB::queryColumnToArray($this->dbh, 'SHOW TABLES FROM `' . $escapedDbName . '`');

        switch ($dbAction) {
            case DUPX_DBInstall::DBACTION_EMPTY:
            case DUPX_DBInstall::DBACTION_RENAME:
                $affectedTables = array_diff($allTables, $excludeTables);
                break;
            case DUPX_DBInstall::DBACTION_REMOVE_ONLY_TABLES:
                $affectedTables = array_intersect(
                    DUPX_DB_Tables::getInstance()->getNewTablesNames(),
                    array_diff($allTables, $excludeTables)
                );
                break;
            default:
                break;
        }

        return $affectedTables;
    }

    public function checkDbVisibility(&$errorMessage = null)
    {
        $result = true;

        try {
            if (!$this->getDbConnection()) {
                throw new Exception('Database not connected');
            }

            $dbName = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME);
            $dbUser = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER);
            switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_VIEW_MODE)) {
                case 'basic':
                    $result = $this->databaseExists($errorMessage);
                    break;
                case 'cpnl':
                    $result = $this->cpnlAPI->is_user_in_db($this->cpnlToken, $dbName, $dbUser);
                    if ($result['status'] !== true) {
                        $result = $this->cpnlAPI->assign_db_user($this->cpnlToken, $dbName, $dbUser);
                        if ($result['status'] !== true) {
                            $errorMessage = $result['status'];
                            $result       = false;
                        }
                    }
                    break;
                default:
                    $errorMessage = 'Invalid db view mode';
                    $result       = false;
                    break;
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK VISIBILITY EXCEPTION: ');
            $result       = false;
        } catch (Error $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK VISIBILITY EXCEPTION: ');
            $result       = false;
        }

        return $result;
    }

    public function dbTablesCount(&$errorMessage = null)
    {
        $result = true;

        try {
            if (!$this->getDbConnection()) {
                throw new Exception('Database not connected');
            }

            $dbName = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME);
            $result = DUPX_DB::countTables($this->dbh, $dbName);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE TABLES COUNT EXCEPTION: ');
            $result       = false;
        } catch (Error $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE TABLES COUNT EXCEPTION: ');
            $result       = false;
        }

        return $result;
    }

    /**
     *
     * @param array $perms
     * @param array $errorMessages
     * @return int // test result level
     */
    public function dbCheckUserPerms(&$perms = array(), &$errorMessages = array())
    {

        $perms = array(
            'create'  => DUPX_Validation_abstract_item::LV_SKIP,
            'insert'  => DUPX_Validation_abstract_item::LV_SKIP,
            'select'  => DUPX_Validation_abstract_item::LV_SKIP,
            'update'  => DUPX_Validation_abstract_item::LV_SKIP,
            'delete'  => DUPX_Validation_abstract_item::LV_SKIP,
            'drop'    => DUPX_Validation_abstract_item::LV_SKIP,
            'view'    => DUPX_Validation_abstract_item::LV_SKIP,
            'proc'    => DUPX_Validation_abstract_item::LV_SKIP,
            'func'    => DUPX_Validation_abstract_item::LV_SKIP,
            'trigger' => DUPX_Validation_abstract_item::LV_SKIP
        );

        $errorMessages = array();
        try {
            if (!$this->getDbConnection()) {
                throw new Exception('Database not connected');
            }

            $dbName = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME);

            if (mysqli_select_db($this->dbh, $dbName) === false) {
                throw new Exception('Can\'t select database ' . $dbName);
            }

            $tmpTable        = '__dpro_temp_' . rand(1000, 9999) . '_' . date("ymdHis");
            $tmpTableEscaped = '`' . mysqli_real_escape_string($this->dbh, $tmpTable) . '`';

            if (
                $this->isQueryWorking("CREATE TABLE " . $tmpTableEscaped . " ("
                    . "`id` int(11) NOT NULL AUTO_INCREMENT, "
                    . "`text` text NOT NULL, "
                    . "PRIMARY KEY (`id`))", $errorMessages)
            ) {
                $perms['create'] = DUPX_Validation_abstract_item::LV_PASS;
            } else {
                $perms['create'] = DUPX_Validation_abstract_item::LV_FAIL;
            }

            if ($perms['create']) {
                if ($this->isQueryWorking("INSERT INTO " . $tmpTableEscaped . " (`text`) VALUES ('TEXT-1')", $errorMessages)) {
                    $perms['insert'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['insert'] = DUPX_Validation_abstract_item::LV_FAIL;
                }

                if ($this->isQueryWorking("SELECT COUNT(*) FROM " . $tmpTableEscaped, $errorMessages)) {
                    $perms['select'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['select'] = DUPX_Validation_abstract_item::LV_FAIL;
                }

                if ($this->isQueryWorking("UPDATE " . $tmpTableEscaped . " SET text = 'TEXT-2' WHERE text = 'TEXT-1'", $errorMessages)) {
                    $perms['update'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['update'] = DUPX_Validation_abstract_item::LV_FAIL;
                }

                if ($this->isQueryWorking("DELETE FROM " . $tmpTableEscaped . " WHERE text = 'TEXT-2'", $errorMessages)) {
                    $perms['delete'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['delete'] = DUPX_Validation_abstract_item::LV_FAIL;
                }

                if ($this->isQueryWorking("DROP TABLE IF EXISTS " . $tmpTableEscaped . ";", $errorMessages)) {
                    $perms['drop'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['drop'] = DUPX_Validation_abstract_item::LV_FAIL;
                }
            }

            if ($this->dbHasViews()) {
                if ($this->dbCheckGrants(array("CREATE VIEW"), $errorMessages)) {
                    $perms['view'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['view'] = DUPX_Validation_abstract_item::LV_HARD_WARNING;
                }
            }

            if ($this->dbHasProcedures()) {
                if ($this->dbCheckGrants(array("CREATE ROUTINE", "ALTER ROUTINE"), $errorMessages)) {
                    $perms['proc'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['proc'] = DUPX_Validation_abstract_item::LV_HARD_WARNING;
                }
            }

            if ($this->dbHasFunctions()) {
                if ($this->dbCheckGrants(array("CREATE ROUTINE", "ALTER ROUTINE"), $errorMessages)) {
                    $perms['func'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['func'] = DUPX_Validation_abstract_item::LV_HARD_WARNING;
                }
            }

            if ($this->dbHasTriggers()) {
                if ($this->dbCheckGrants(array("TRIGGER"), $errorMessages)) {
                    $perms['trigger'] = DUPX_Validation_abstract_item::LV_PASS;
                } else {
                    $perms['trigger'] = DUPX_Validation_abstract_item::LV_SOFT_WARNING;
                }
            }
        } catch (Exception $e) {
            $errorMessages[] = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK USER PERMS EXCEPTION: ');
        } catch (Error $e) {
            $errorMessages[] = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK USER PERMS EXCEPTION: ');
        }

        return min($perms);
    }

    /**
     *
     * @param string $query The SQL query
     * @param array $errorMessages Optionally you can capture the errors in this array
     * @return boolean returns true if running the query did not fail
     */
    public function isQueryWorking($query, &$errorMessages = array())
    {
        $result = true;

        try {
            if (DUPX_DB::mysqli_query($this->dbh, $query) === false) {
                $currentError = mysqli_error($this->dbh);
                $result       = false;
            }
        } catch (Exception $e) {
            $currentError = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'TESTING QUERY: ');
            $result       = false;
        } catch (Error $e) {
            $currentError = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'TESTING QUERY: ');
            $result       = false;
        }

        if ($result === false) {
            $errorMessages[] = $currentError;
        }
        return $result;
    }

    /**
     *
     * @param array $grants // list of grants to check
     * @param array $errorMessages
     * @return boolean
     */
    public function dbCheckGrants($grants, &$errorMessages = array())
    {
        try {
            if (($queryResult = DUPX_DB::mysqli_query($this->dbh, "SHOW GRANTS")) === false) {
                $errorMessages[] = mysqli_error($this->dbh);
                return false;
            }

            $dbName  = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME);
            $regex   = '/^GRANT\s+(?!USAGE)(.+)\s+ON\s+(?:\*|`' . preg_quote($dbName, '/') . '`)\..*$/';
            $matches = null;

            while ($row = mysqli_fetch_array($queryResult)) {
                if (preg_match($regex, $row[0], $matches)) {
                    Log::info('SHOW GRANTS CURRENT DB: ' . $row[0], Log::LV_DEBUG);
                    break;
                }
            }

            if (empty($matches)) {
                Log::info('GRANTS LINE OF CURRENT DB NOT FOUND');
                return false;
            }

            if ($matches['1'] === 'ALL PRIVILEGES') {
                return true;
            }

            $usrePrivileges = preg_split('/\s*,\s*/', $matches['1']);
            if (($notGrants      = array_diff($grants, $usrePrivileges))) {
                $message         = "The mysql user does not have the '" . implode(', ', $notGrants) . "' permission.";
                Log::info('NO GRANTS: ' . $message);
                $errorMessages[] = $message;

                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            $errorMessages[] = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK PERM EXCEPTION: ');
            return false;
        }

        return false;
    }

    public function dbHasProcedures()
    {
        if (DUPX_ArchiveConfig::getInstance()->dbInfo->procCount > 0) {
            Log::info("SOURCE SITE DB HAD PROCEDURES", Log::LV_DEBUG);
            return true;
        }

        if (($result = DUPX_DB::mysqli_query($this->dbh, "SHOW PROCEDURE STATUS"))) {
            if (mysqli_num_rows($result) > 0) {
                Log::info("INSTALL SITE HAS PROCEDURES", Log::LV_DEBUG);
                return true;
            }
        }

        return false;
    }

    public function dbHasFunctions()
    {
        if (DUPX_ArchiveConfig::getInstance()->dbInfo->funcCount > 0) {
            Log::info("SOURCE SITE DB HAD FUNCTIONS", Log::LV_DEBUG);
            return true;
        }

        if (($result = DUPX_DB::mysqli_query($this->dbh, "SHOW FUNCTION STATUS"))) {
            if (mysqli_num_rows($result) > 0) {
                Log::info("INSTALL SITE HAS FUNCTIONS", Log::LV_DEBUG);
                return true;
            }
        }

        return false;
    }

    public function dbHasTriggers()
    {
        if (($result = DUPX_DB::mysqli_query($this->dbh, "SHOW TRIGGERS"))) {
            if (mysqli_num_rows($result) > 0) {
                Log::info("INSTALL SITE HAS TRIGGERS", Log::LV_DEBUG);
                return true;
            }
        }

        return false;
    }

    public function dbHasViews()
    {
        if (DUPX_ArchiveConfig::getInstance()->dbInfo->viewCount > 0) {
            Log::info("SOURCE SITE DB HAD VIEWS", Log::LV_DEBUG);
            return true;
        }

        if (($result = DUPX_DB::mysqli_query($this->dbh, "SHOW FULL TABLES WHERE Table_Type = 'VIEW'"))) {
            if (mysqli_num_rows($result) > 0) {
                Log::info("INSTALL SITE HAS VIEWS", Log::LV_DEBUG);
                return true;
            }
        }

        return false;
    }

    public function dbGtidModeEnabled(&$errorMessage = array())
    {
        try {
            $gtidModeEnabled = false;
            if (($result          = DUPX_DB::mysqli_query($this->dbh, 'SELECT @@GLOBAL.GTID_MODE', Log::LV_DEBUG)) === false) {
                if (Log::isLevel(Log::LV_DEBUG)) {
                    // It is normal for this query to generate an error when the GTID is not active. So normally it is better not to worry users with managed error messages.
                    $errorMessage = mysqli_error($this->dbh);
                }
            } else {
                if (($row = mysqli_fetch_array($result, MYSQLI_NUM)) !== false) {
                    if (strcasecmp($row[0], 'on') === 0) {
                        $gtidModeEnabled = true;
                    }
                }
            }

            $result = $gtidModeEnabled;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK CHARSET EXCEPTION: ');
            $result       = false;
        } catch (Error $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK CHARSET EXCEPTION: ');
            $result       = false;
        }

        return $result;
    }

    /**
     *
     * @param string $errorMessage
     *
     * @return int // -1 fail
     */
    public function caseSensitiveTablesValue(&$errorMessage = array())
    {
        try {
            if (!$this->getDbConnection()) {
                throw new Exception('Database not connected');
            }

            if (($lowerCaseTableNames = DUPX_DB::getVariable($this->dbh, 'lower_case_table_names')) === null) {
                if (SnapOS::isWindows()) {
                    $lowerCaseTableNames = 1;
                } else if (SnapOS::isOSX()) {
                    $lowerCaseTableNames = 2;
                } else {
                    $lowerCaseTableNames = 0;
                }
            }

            $result = $lowerCaseTableNames;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK CHARSET EXCEPTION: ');
            $result       = -1;
        } catch (Error $e) {
            $errorMessage = $e->getMessage();
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK CHARSET EXCEPTION: ');
            $result       = -1;
        }

        return (int) $result;
    }

    /**
     * @return array|false
     */
    public function getUserResources()
    {
        try {
            $host  = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_HOST);
            $user  = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER);
            $query = "SELECT max_questions, max_updates, max_connections FROM mysql.user WHERE user = '{$user}' AND host = '{$host}'";

            if (($result = DUPX_DB::mysqli_query($this->dbh, $query, Log::LV_DEFAULT)) != false && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        } catch (Exception $e) {
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK USER RESOURCE EXCEPTION: ');
        } catch (Error $e) {
            Log::logException($e, Log::LV_DEFAULT, 'DATABASE CHECK USER RESOURCE ERROR: ');
        }

        return false;
    }

    private function __clone()
    {
    }
}
