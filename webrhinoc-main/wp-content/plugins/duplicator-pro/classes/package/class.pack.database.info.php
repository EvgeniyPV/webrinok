<?php

/**
 * Class for gathering system information about a database
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUP_PRO_DatabaseInfo
{

    /**
     * The SQL file was built with mysqldump or PHP
     */
    public $buildMode = 'PHP';

    /**
     * A unique list of all the charSet table types used in the database
     */
    public $charSetList = array();

    /**
     * A unique list of all the collation table types used in the database
     */
    public $collationList = array();

    /**
     * A unique list of all the engine types used in the database
     */
    public $engineList = array();

    /**
     * Does any filtered table have an upper case character in it
     */
    public $isTablesUpperCase = false;

    /**
     * Does the database name have any filtered characters in it
     */
    public $isNameUpperCase = false;

    /**
     * The real name of the database
     */
    public $name = '';

    /**
     * The full count of all tables in the database
     */
    public $tablesBaseCount = 0;

    /**
     * The count of tables after the tables filter has been applied
     */
    public $tablesFinalCount = 0;

    /**
     * The count of tables filtered programmatically for multi-site purposes
     */
    public $muFilteredTableCount = 0;

    /**
     * The number of rows from all filtered tables in the database
     */
    public $tablesRowCount = 0;

    /**
     * The estimated data size on disk from all filtered tables in the database
     */
    public $tablesSizeOnDisk = 0;

    /**
     *
     * @var array
     */
    public $tablesList = array();

    /**
     * Gets the server variable lower_case_table_names
     *
     * 0 store=lowercase;   compare=sensitive   (works only on case sensitive file systems )
     * 1 store=lowercase;   compare=insensitive
     * 2 store=exact;       compare=insensitive (works only on case INsensitive file systems )
     * default is 0/Linux ; 1/Windows
     */
    public $varLowerCaseTables = false;

    /**
     * The database engine (MySQL/MariaDB/Percona)
     * @var string
     * @example MariaDB
     */
    public $dbEngine = '';

    /**
     * The simple numeric version number of the database server
     * @exmaple: 5.5
     */
    public $version = 0;

    /**
     * The full text version number of the database server
     * @exmaple: 10.2 mariadb.org binary distribution
     */
    public $versionComment = 0;

    /**
     * @var int Number of VIEWs in the database
     */
    public $viewCount = 0;

    /**
     * @var int Number of PROCEDUREs in the database
     */
    public $procCount = 0;

    /**
     * @var int Number of PROCEDUREs in the database
     */
    public $funcCount = 0;

    /**
     * @var array Trigger information
     */
    public $triggerList = array();

    /**
     * Classs constructor
     */
    public function __construct()
    {
    }

    /**
     *
     * @param stirng $name              // table name
     * @param int $inaccurateRows       // This data is intended as a preliminary count and therefore not necessarily accurate
     * @param int $size                 // This data is intended as a preliminary count and therefore not necessarily accurate
     * @param int|bool $insertedRows    // This value, if other than false, is the exact line value inserted into the dump file
     */
    public function addTableInList($name, $inaccurateRows, $size, $insertedRows = false)
    {
        $this->tablesList[$name] = array(
            'inaccurateRows' => (int) $inaccurateRows,
            'insertedRows'   => $insertedRows,
            'size'           => (int) $size
        );
    }

    /**
     *
     * @param string $name
     * @param int $count // the real inseret rows cont for table
     */
    public function addInsertedRowsInTableList($name, $count)
    {
        if (!isset($this->tablesList[$name])) {
            throw new Exception('No found table ' . $name . ' in table info');
        } else {
            $this->tablesList[$name]['insertedRows'] = (int) $count;
        }
    }

    /**
     * Add triggers to list
     *
     * @return array
     */
    public function addTriggers()
    {
        global $wpdb;
        $this->triggerList = array();

        if (!is_array($triggers = $wpdb->get_results("SHOW TRIGGERS", ARRAY_A))) {
            return $this->triggerList;
        }

        foreach ($triggers as $trigger) {
            $name   = $trigger["Trigger"];
            $create = $wpdb->get_row("SHOW CREATE TRIGGER `{$name}`", ARRAY_N);
            $this->triggerList[$name] = array(
                "event" => $trigger["Event"],
                "table" => $trigger["Table"],
                "timing" => $trigger["Timing"],
                "create" => "DELIMITER ;;\n" . $create[2] . ";;\nDELIMITER ;"
            );
        }

        $this->triggerList;
    }
}
