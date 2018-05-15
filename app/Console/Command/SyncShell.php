<?php

session_save_path(TMP.'/sessions');

App::uses('DataWarehouseComponent', 'Controller/Component');
App::uses('ComponentCollection', 'Controller');

class SyncShell extends AppShell {
    public $uses = ['BYUAPI'];

    public function getOptionParser() {
        $parser = parent::getOptionParser();
        $parser->addArgument('tables-file', [
            'help' => 'Path to a JSON file containing the list of tables to be synced',
            'required' => true
        ]);
        return $parser;
    }

    // Override inherited function to not print welcome message to logs
    public function _welcome() {
    }

    public function main() {
        $this->out(date('Y-m-d H:i:s'));

        $tablesJSON = file_get_contents($this->args[0]);
        if ($tablesJSON === false) {
            $this->out('Error reading the list of tables.', 2);
            return;
        }
        $tables = json_decode($tablesJSON, true);

        $arrAllColumns = [];
        foreach ($tables as $table) {
            $arrAllColumns[$table['schema']][$table['table']] =
                $this->BYUAPI->oracleColumns($table['schema'], $table['table']);
        }

        $hash = hash('sha256', json_encode($arrAllColumns));
        if (file_exists(TMP.'/cache/databaseColumnsHash')) {
            $previousHash = file_get_contents(TMP.'/cache/databaseColumnsHash');
            if ($hash === $previousHash) {
                $this->out(date('H:i:s').' - No change detected in the database.', 2);
                return;
            } else {
                $this->out(date('H:i:s').' - New hash didn\'t match the saved one. Double-checking the result.');
                $arrDoubleCheckColumns = [];
                foreach ($tables as $table) {
                    $arrDoubleCheckColumns[$table['schema']][$table['table']] =
                        $this->BYUAPI->oracleColumns($table['schema'], $table['table']);
                }
                $doubleCheckHash = hash('sha256', json_encode($arrDoubleCheckColumns));
                if ($doubleCheckHash !== $hash) {
                    $this->out(date('H:i:s').' - Inconsistent results. Aborting.', 2);
                    return;
                } else {
                    $this->out(date('H:i:s').' - Change in database asserted.', 2);
                }
            }
        } else {
            $this->out(date('H:i:s').' - No saved hash found.', 2);
        }
        file_put_contents(TMP.'/cache/databaseColumnsHash', $hash);

        $collection = new ComponentCollection();
        $this->DataWarehouse = $collection->load('DataWarehouse');
        foreach ($arrAllColumns as $schemaName => $schemaTables) {
            foreach ($schemaTables as $tableName => $columns) {
                $resp = $this->DataWarehouse->syncDataWarehouse($schemaName, $tableName, $columns);
                $level = isset($resp['noChange']) ? Shell::VERBOSE : Shell::NORMAL;
                $this->out(date('H:i:s').' - '.$resp['message'], 1, $level);
            }
        }
        $this->out(' ');
    }
}
