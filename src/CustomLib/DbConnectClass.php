<?php

include_once ROOTPATH . '/include/adodb5/adodb.inc.php';

class DbConnectClass {

    protected $_connection;
    private $debug;

    public function __construct() {
        $db = & ADONewConnection('odbc_mssql');
        $db->Connect(DSN, DB_USER, DB_PASSWORD) or die("Unable select db seneca.\n");
        $db->SetFetchMode(ADODB_FETCH_ASSOC);
        $db->debug = 0;
        $this->_connection = $db;
    }

    private function __clone() {
        
    }

    public function getConnection() {
        return $this->_connection;
    }

    /**
     * stampa la SQL per tutte le chiamate
     * 
     * @return type
     */
    public function setDebug() {
        return $this->debug = 1;
    }

    /**
     * se non ho risultati ritorno null
     * 
     * @param type $sql
     * @return type
     */
    function adoExecQuery($sql, $line = "", $print = false) {
        $this->_connection->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($print || $this->debug == 1) {
            echo "<br>" . $sql . "<br>";
        }
        try {
            $rs = &$this->_connection->Execute($sql);
            if ($rs === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: Linea ' . $line . "---" . $this->_connection->ErrorMsg(), E_USER_ERROR);
                return NULL;
            } else {
                if ($rs->RecordCount() == 0) {
                    return null;
                } else {
                    return $rs;
                }
            }
        } catch (Exception $e) {
            return $out = "Wrong SQL: " . $sql . " Error: Linea " . $line . "---" . $this->_connection->ErrorMsg() . "  Error:" . $e;
        }
    }

    /**
     * se non ho risultati ritorno null
     * 
     * @param type $sql
     * @return type
     */
    function adoExecInsQuery($sql, $line = "", $print = false) {
        $this->_connection->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($print || $this->debug == 1) {
            echo "<br>" . $sql . "<br>";
        }
        try {
            $rs = &$this->_connection->Execute($sql);
            if ($rs === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: Linea ' . $line . "---" . $this->_connection->ErrorMsg(), E_USER_ERROR);
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return $out = "Wrong SQL: " . $sql . " Error: Linea " . $line . "---" . $this->_connection->ErrorMsg() . "  Error:" . $e;
        }
    }

    /**
     * non effetuta autoCommit si deve chiamare separatamente
     * 
     * @param type $sql
     * @return type
     */
    public function adoDBRollExec($sql, $line = "", $print = false) {

        if ($print || $this->debug == 1) {
            echo "<br>" . $sql . "<br>";
        }
        //transazione inizio
        $this->_connection->SetTransactionMode("");
        $this->_connection->BeginTrans();
        $this->_connection->Execute($sql);
        $trans = $this->_connection->CompleteTrans();
        if ($trans) {
            echo "<BR>**COMMIT CONCLUSO";
            return true;
        } else {
            echo "<br>##RILEVATI ERRORI - COMMIT ROLLBACK -" . $sql;
            return false;
        }
    }

    public
            function adoDBCommit() {
        //autorollback in caso di errore
        $trans = $this->_connection->CompleteTrans();
        if ($trans) {
            echo "<BR>**COMMIT CONCLUSO";
            return true;
        } else {
            echo "<br>##RILEVATI ERRORI - COMMIT ROLLBACK";
            return false;
        }
    }

    function adoLastInsertId($table) {

        $this->db->SetFetchMode(ADODB_FETCH_NUM);
        $rs = &$this->db->Execute("SELECT MAX(ID) FROM " . $table);
        if ($rs === false) {
            return trigger_error('Wrong SQL: ' . $sql . ' Error: Linea ' . $line . "---" . $this->db->ErrorMsg(), E_USER_ERROR);
        } else {
            $data = $rs->FetchRow();
            return $data[0];
        }
    }

}
