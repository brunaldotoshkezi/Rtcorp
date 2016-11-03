<?php

include_once ROOTPATH . '/src/CustomLib/DbConnectClass.php';

class ShooperLog {

    public $db;

    function __construct() {

        // connecting to database  
        $this->db = new DbConnectClass();
    }
    function replaceString($str){
    $str=  str_replace("-", ":", $str);
    $str=  str_replace("_", " ", $str);
    return $str;
    }
    function insert($esito, $desc, $path, $date, $id,$rownumber,$arrivalerror) {
        // 1,'Insert into '.$filename[1], $filename[1], $date, $category
		$lastcharacters=date('Y-m-d', strtotime($this->replaceString(substr($path,strlen($path)-25,20))));
		$timeofdate=strtotime($this->replaceString(substr($path,strlen($path)-25,20)));
        $insertSQL = "insert into ShooperLog(DataInserimento,DateFile,esito,Nome,Descrizione,CategoryZone,ArrivalDateError,RowCounts,LastDateChars,TimeOfLastDate)"
                . " Values(GETDATE(),'$date',$esito,'$path','$desc',$id,$arrivalerror,$rownumber,'$lastcharacters',$timeofdate);";
        if ($this->db->adoExecInsQuery($insertSQL) == true) {
            return '<br>success Insert Log ' . $path . ';';
        } else {
            return '<br>error Insert Log ' . $path . ';';
        }
    }

   

    function isInserted($category, $ftpPath) {
        $query = "SELECT  * FROM ShooperLog WHERE CategoryZone=" . $category . " AND Nome='" . $ftpPath . "'";
        $rs = $this->db->adoExecQuery($query);
        if (is_null($rs)) {
            unset($rs);
            return false;
        } else {
            unset($rs);
            return true;
        }
    }
	
	function getLogId( $path, $date, $id) {
         $idlog=0;
        $query = "SELECT ID FROM ShooperLog WHERE CategoryZone=" . $id . " AND Nome='" . $path . "' AND DateFile='".$date."' AND esito=1;";
        $rs = $this->db->adoExecQuery($query);
		if($rs!=null){
        while ($arr = $rs->FetchRow()) {
            $idlog= $arr['ID'] ;
          } }
          return (int)$idlog;
    }

}
