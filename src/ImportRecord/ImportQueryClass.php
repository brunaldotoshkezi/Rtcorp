<?php

include_once ROOTPATH . '/src/CustomLib/DbConnectClass.php';

class ImportQueryClass extends DbConnectClass {

    private $db;
//attivo per i test rendere vuoto in produzione TEST_ in test
    private $test;
    private $errorSourceFile;

    public function __construct() {
        parent::__construct();
        $this->test = "";
        $this->errorSourceFile = false;
    }

    public function reinit() {
        $this->errorSourceFile = false;
    }

    /**
     *  Recupero tutte le zone disponibili
     * @return type
     */
    function getZones() {

        $sql = "select ID,Nome from dbo.ShooperCodiceZone;";
        $rs = $this->adoExecQuery($sql);
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
        $grade = 100;
        $error = 0;
        //se becco una condizione impossibile arrivo prima di partenza setto tutti i dati di excel a grade 1
        if ($this->errorSourceFile) {
            $error = 50;
        }

        if ($gradeControll == "not_found" || $gradeControll == "not_available")
            $grade = 90;
        if ($gradeControll == "error_connecting_site")
            $grade = 80;
        if ($row[9] < $row[0]) {
            $grade = 0;
            $this->errorSourceFile = true;
        }

        $grade = $grade - $error;
		if($grade<0)$grade=0;
        return $grade;
    }

    function checkinsertArrivaDate($reportDate, $zone) {
        $sql = "select ID FROM  ShooperDates_" . $this->test . $zone . " where ReportDate = '" . $this->convertExcelData($reportDate) . "'";

        $rs = $this->adoExecQuery($sql);
        if (is_null($rs)) {
            return false;
        } else {
            return true;
        }
    }

    function insertRow($row, $zone, $namefile, $rowInc) {
       
        $grade = 100;
        //definisco un valore al dato in base a riconosciuti errori
        $grade = $this->gradeControll($row);
	
        $sql = "insert into ShooperDates_" . $this->test . $zone . " ";
        $sql .= " values ('" . $this->convertExcelData($row[0]) . "', $zone, '" . $this->chekData($row[1]) . "', '" . $this->chekData($row[2]) . "', '" . $this->chekData($row[3]) . "', '" . $this->chekData($row[4]) . "', '" . $this->chekData($row[5]) . "', '" . $this->chekData($row[6]) . "', " . $this->chekData($row[7]) . ", '" . $this->chekData($row[8]) . "', '" . $this->convertExcelData($row[9]) . "', '" . $this->chekData($row[10]) . "', " . $this->chekData($row[11]) . ", " . $this->chekData($row[12]) . ", '" . $this->chekData($row[13]) . "', " . $this->chekData($row[14]) . ", " . $this->chekData($row[15]) . ", " . $this->chekData($row[16]) . ", '" . $this->chekData($row[17]) . "', '" . $this->chekData($row[18]) . "', " . $this->chekData($row[19]) . ", " . $this->chekData($row[20]) . ", '" . $this->chekData($namefile) . "', $rowInc, $grade);";
        if ($this->adoExecInsQuery($sql) == true) {
            return true;
        } else {
            echo $errors = '<BR>Error IMPORT DATA SQL: ' . $sql . "<br>";
            return $errors;
        }
    }

    function generateSql($row, $zone, $namefile, $rowInc,$idLogs) {
     
        $row = array_values($row); 
        //print " <br>" . $row[0] . " < " . $row[9] . "<br>";
        $grade = 1;
        //definisco un valore al dato in base a riconosciuti errori
        $grade = $this->gradeControll($row);
		$sql="";
		if($row[0]!="" AND $row[9]!="" AND $row[3]!="" ){ 
				$sql .= "insert into ShooperDates_" . $this->test . $zone . " ";
				$sql .= " values ('" . $this->convertExcelData($row[0]) . "', $zone, '" . $this->chekData($row[1]) . "', '" . $this->chekData($row[2]) . "', '" . $this->chekData($row[3]) . "', '" . $this->chekData($row[4]) . "', '" . $this->chekData($row[5]) . "', '" . $this->chekData($row[6]) . "', " . $this->chekData($row[7]) . ", '" . $this->chekData($row[8]) . "', '" . $this->convertExcelData($row[9]) . "', '" . $this->chekData($row[10]) . "', " . $this->chekData($row[11]) . ", " . $this->chekData($row[12]) . ", '" . $this->chekData($row[13]) . "', " . $this->chekData($row[14]) . ", " . $this->chekData($row[15]) . ", " . $this->chekData($row[16]) . ", '" . $this->chekData($row[17]) . "', '" . $this->chekData($row[18]) . "', " . $this->chekData($row[19]) . ", " . $this->chekData($row[20]) . ", '" . $this->chekData($namefile) . "', $rowInc, $grade,$idLogs);";
		}       
	   return $sql;
    }

    function commit() {
        return $this->adoDBCommit();
    }

    function convertExcelData($intData) {
        $UNIX_DATE = ($intData - 25569) * 86400;
        return gmdate("Y-m-d", $UNIX_DATE);
    }

    function chekData($data) {
        $out = str_replace("'", "''", $data);
        return $out = str_replace('"', "''", $out);
    }
	
	function ArrivalDateError($CsvData){
       $rownr=0;
       $iserror=false;
        foreach ($CsvData as $Row) {
            if($rownr==1){
                if($Row['A']>$Row['J']){
                    $iserror=true;
                    break;
                }else{
                    break;
                }
            }
         $rownr++;   
        }
        return $iserror;
    }

}
