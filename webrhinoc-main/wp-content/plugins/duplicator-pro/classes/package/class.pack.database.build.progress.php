<?php

/**
 * Class used for determining the state of the Database build
 * This class is only used when PHP is in chunking mode
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Database build progress class
 *
 */
class DUP_PRO_DB_Build_Progress
{

    /**
     *
     * @var string[] // table to process
     */
    public $tablesToProcess  = array();
    public $validationStage1 = false;
    public $doneInit         = false;
    public $doneFiltering    = false;
    public $doneCreates      = false;
    public $completed        = false;
    public $startTime        = 0;
    public $wasInterrupted   = false;
    public $errorOut         = false;
    public $failureCount     = 0;
    public $countCheckData   = array(
        'impreciseTotalRows' => 0,
        'countTotal'         => 0,
        'tables'             => array()
    );

    /**
     * Initializes the structure used by the validation to verify the count of entries.
     *
     * @return void
     */
    public function countCheckSetStart()
    {
        $this->countCheckData = array(
            'countTotal'         => 0,
            'impreciseTotalRows' => DUP_PRO_DB::getImpreciseTotaTablesRows($this->tablesToProcess),
            'tables'             => array()
        );

        foreach ($this->tablesToProcess as $table) {
            $this->countCheckData['tables'][$table] = array(
                'start'  => 0,
                'end'    => 0,
                'count'  => 0,
                'create' => false
            );
        }
    }

    /**
     * set count value at the beginning of table insert
     *
     * @param string $table
     * @throws Exception
     */
    public function tableCountStart($table)
    {
        if (!isset($this->countCheckData['tables'][$table])) {
            throw new Exception('Table ' . $table . ' no found in progress strunct');
        }
        $tablesRows = DUP_PRO_DB::getTablesRows($table);

        if (!isset($tablesRows[$table])) {
            throw new Exception('Table ' . $table . ' in database not found');
        }
        $this->countCheckData['tables'][$table]['start'] = $tablesRows[$table];
    }

    /**
     * set count valute ad end of table insert and real count of rows dumped
     * @param string $table
     * @param int $count
     * @throws Exception
     */
    public function tableCountEnd($table, $count)
    {
        if (!isset($this->countCheckData['tables'][$table])) {
            throw new Exception('Table ' . $table . ' no found in progress strunct');
        }
        $tablesRows = DUP_PRO_DB::getTablesRows($table);

        if (!isset($tablesRows[$table])) {
            throw new Exception('Table ' . $table . ' in database not found');
        }
        $this->countCheckData['tables'][$table]['end']   = $tablesRows[$table];
        $this->countCheckData['tables'][$table]['count'] = (int) $count;
        $this->countCheckData['countTotal']              += (int) $count;
    }
}
