<?php

include_once ROOTPATH . '/src/CustomLib/DbConnectClass.php';

class ImportQueryClass {

    private $db;
//attivo per i test rendere vuoto in produzione TEST_ in test
    private $test;

    public function __construct() {
        $this->db = new DbConnectClass();
        $this->test = "TEST_";
    }

    /**
     *  Recupero tutte le zone disponibili
     * @return type
     */
    function getZones() {

        $sql = "select ID,Nome from dbo.ShooperCodiceZone;";
        $rs = $this->db->adoExecQuery($sql);
        return $rs;
    }

    /**
     * Controlli per verificare se i dati non sono molto stabili
     * 
     * @param type $value
     * @return int
     */
    private function gradeControll($row) {
        $gradeControll = trim(strtolower(str_replace(' ', '_', $row[17])));
        $grade = 10;
        if ($gradeControll == "not_found" || $gradeControll == "not_available")
            $grade = 9;
        if ($gradeControll == "error_connecting_site")
            $grade = 8;
        if (strtotime($row[0]) < strtotime($row[9]))
            $grade = 0;
        return $grade;
    }

    function checkinsertArrivaDate($reportDate) {


        $sql = "select ID FROM  ShooperDates_" . $this->test . $zone . "where ReportDate = '" . $this->convertExcelData($reportDate) . "'";

        $rs = $this->db->adoExecQuery($sql);
        if (is_null($rs)) {
            return false;
        } else {
            return true;
        }
    }

    function insertRow($row, $zone, $namefile, $rowInc) {
        $grade = 1;
        $grade = $this->gradeControll($row);
        //definisco un valore al dato in base a riconosciuti errori


        $sql = "insert into ShooperDates_" . $this->test . $zone . " ";
        $sql .= " values ('" . $this->convertExcelData($row[0]) . "', $zone, '" . $this->chekData($row[1]) . "', '" . $this->chekData($row[2]) . "', '" . $this->chekData($row[3]) . "', '" . $this->chekData($row[4]) . "', '" . $this->chekData($row[5]) . "', '" . $this->chekData($row[6]) . "', " . $this->chekData($row[7]) . ", '" . $this->chekData($row[8]) . "', '" . $this->convertExcelData($row[9]) . "', '" . $this->chekData($row[10]) . "', " . $this->chekData($row[11]) . ", " . $this->chekData($row[12]) . ", '" . $this->chekData($row[13]) . "', " . $this->chekData($row[14]) . ", " . $this->chekData($row[15]) . ", " . $this->chekData($row[16]) . ", '" . $this->chekData($row[17]) . "', '" . $this->chekData($row[18]) . "', " . $this->chekData($row[19]) . ", " . $this->chekData($row[20]) . ", '" . $this->chekData($namefile) . "', $rowInc, $grade);
";
        if ($this->db->adoExecInsQuery($sql) == true) {
            return true;
        } else {
            echo $errors = '<BR>Error IMPORT DATA SQL: ' . $sql . "<br>";
            return $errors;
        }
    }

    function commit() {
        return $this->db->adoDBCommit();
    }

    function convertExcelData($intData) {
        $UNIX_DATE = ($intData - 25569) * 86400;
        return gmdate("Y-m-d", $UNIX_DATE);
    }

    function chekData($data) {
        $out = str_replace("'", "''", $data);
        return $out = str_replace('"', "''", $out);
        return $data;
    }

}
