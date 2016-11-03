<?php

include_once ROOTPATH. '/src/CustomLib/DbConnectClass.php';
include_once ROOTPATH. '/src/CustomLib/ExportToCsvfileClass.php';

include_once 'ReportQueryClass.php';
class ReportClass {

    private $db;
    private $query;

    function __construct() {
        $this->db = new DbConnectClass();
       
        $this->query = new ReportQueryClass();
    }

    /**
     * Recupera tutte le zone disponibili
     * 
     * @return string
     */
    public function getAllZone() {
        $result = "<option>Nessun parametro</option> ";
        $rs = $this->query->getZones();
        if (!is_null($rs)) {
            $result = "";
            while ($arr = $rs->FetchRow()) {
                $arr = array_values($arr); 
                $result .= "<option value=" . $arr[0] . ">" . $arr[1] . "</option> ";
            }
        }
		$result .= '<option value="9">Francavilla</option> ';
        return $result;
    }

    public function getAllMounth() {
        $rs = $this->query->getAllAvailableMonths();
        $result = "";
        $year = date('Y');
        if ($rs) {
            while ($arr = $rs->FetchRow()) {
                $arr = array_values($arr); 
                if (strlen((string) $arr[0]) > 1) {
                    $month = (string) $arr[0];
                } else {
                    $month = '0' . (string) $arr[0];
                }
                if ($month . "" . $arr[1] != $month . $year) {
                    $result.= "<option value=" . $month . "" . $arr[2] . ">" . $month . "/" . $arr[2] . "</option> ";
                }
            }
        }
        return $result;
    }
	
	public  function getDatesOfMonth($dal,$al){
        $dates=array();
        $begin = new DateTime( $dal );
        $end = new DateTime( $al );
        $end = $end->modify( '+1 day' ); 

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval ,$end);

        foreach($daterange as $date){
            $dates[]=$date->format("Y-m-d") ;
        }
        return $dates;
    }
	
    public function exportData() {
		ini_set('memory_limit', '1792M');
        $dal = $al = $selzone = $datacreazioneReport = $hotelname = $mese=$sito = "";
        $reportData = "";
        $exportToCsv = new ExportToCsvfileClass();


        if (isset($_POST["selmese"]) && isset($_POST["selzone"]) && isset($_POST["Date"])&&isset($_POST["Sito"])) {
            $dal = $_POST["dal"];
            $al = $_POST["al"];
			//echo $dal.'-'.$al;
            $selzone = "";
            $datacreazioneReport = "";
            if (isset($_POST["selromemilano"])) {
                $selzone = $_POST["sel2"];
            } else {
                $selzone = $_POST["selzone"];
            }
            if(isset($_POST["Date"])) $datacreazioneReport = $_POST["Date"];
			//echo $datacreazioneReport.';';
            if (isset($_POST["selhotel"])) {
                $hotelname = $_POST["selhotel"];
            }
			$sito=$_POST["Sito"];
            $selmese = $_POST["selmese"];
            $mese = substr($selmese, 0, 2);
            $year = substr($selmese, 2);

            if ($dal == "" || $al == "") {
                $dal = $year . '-' . $mese . '-01';
                $al = date("Y-m-t", strtotime($dal));
            }
            
            $vl=(strtotime($al)-strtotime($dal))/86400;
            //creare array con le date per separare i messi 3 copie delle date
				$arr=array();
                $al1=date("Y-m-d");  
                $al2=date("Y-m-d"); 
                $al3=date("Y-m-d");
				$al4=date("Y-m-d");
				$al5=date("Y-m-d");
				$al6=date("Y-m-d");
				//echo $vl;
				
                if($vl==30){
                        $al1=date('Y-m-d',strtotime($dal . "+5 days"));
                        $arr[$dal]=$al1;
                        $al1=date('Y-m-d',strtotime($al1 . "+1 days"));
                        $al2=date('Y-m-d',strtotime($al1 . "+4 days"));
                        $arr[$al1]=$al2;
                        $al2=date('Y-m-d',strtotime($al2 . "+1 days"));
                        $al3=date('Y-m-d',strtotime($al2 . "+4 days"));
                        $arr[$al2]=$al3;
						$al3=date('Y-m-d',strtotime($al3 . "+1 days"));
                        $al4=date('Y-m-d',strtotime($al3 . "+4 days"));
                        $arr[$al3]=$al4;
						$al4=date('Y-m-d',strtotime($al4 . "+1 days"));
                        $al5=date('Y-m-d',strtotime($al4 . "+4 days"));
                        $arr[$al4]=$al5;
						$al5=date('Y-m-d',strtotime($al5 . "+1 days"));
                        $al6=date('Y-m-d',strtotime($al5 . "+4 days"));
                        $arr[$al5]=$al6;
                }else if($vl==29){
                        $al1=date('Y-m-d',strtotime($dal . "+4 days"));
                        $arr[$dal]=$al1;
                        $al1=date('Y-m-d',strtotime($al1 . "+1 days"));
                        $al2=date('Y-m-d',strtotime($al1 . "+4 days"));
                        $arr[$al1]=$al2;
                        $al2=date('Y-m-d',strtotime($al2 . "+1 days"));
                        $al3=date('Y-m-d',strtotime($al2 . "+4 days"));
                        $arr[$al2]=$al3;
						$al3=date('Y-m-d',strtotime($al3 . "+1 days"));
                        $al4=date('Y-m-d',strtotime($al3 . "+4 days"));
                        $arr[$al3]=$al4;
						$al4=date('Y-m-d',strtotime($al4 . "+1 days"));
                        $al5=date('Y-m-d',strtotime($al4 . "+4 days"));
                        $arr[$al4]=$al5;
						$al5=date('Y-m-d',strtotime($al5 . "+1 days"));
                        $al6=date('Y-m-d',strtotime($al5 . "+4 days"));
                        $arr[$al5]=$al6;
                }else if($vl==27){
                        $al1=date('Y-m-d',strtotime($dal . "+4 days"));
                        $arr[$dal]=$al1;
                        $al1=date('Y-m-d',strtotime($al1 . "+1 days"));
                        $al2=date('Y-m-d',strtotime($al1 . "+4 days"));
                        $arr[$al1]=$al2;
                        $al2=date('Y-m-d',strtotime($al2 . "+1 days"));
                        $al3=date('Y-m-d',strtotime($al2 . "+4 days"));
                        $arr[$al2]=$al3;
						$al3=date('Y-m-d',strtotime($al3 . "+1 days"));
                        $al4=date('Y-m-d',strtotime($al3 . "+4 days"));
                        $arr[$al3]=$al4;
						$al4=date('Y-m-d',strtotime($al4 . "+1 days"));
                        $al5=date('Y-m-d',strtotime($al4 . "+3 days"));
                        $arr[$al4]=$al5;
						$al5=date('Y-m-d',strtotime($al5 . "+1 days"));
                        $al6=date('Y-m-d',strtotime($al5 . "+3 days"));
                        $arr[$al5]=$al6;
                }else if($vl==28){
                        $al1=date('Y-m-d',strtotime($dal . "+4 days"));
                        $arr[$dal]=$al1;
                        $al1=date('Y-m-d',strtotime($al1 . "+1 days"));
                        $al2=date('Y-m-d',strtotime($al1 . "+4 days"));
                        $arr[$al1]=$al2;
                        $al2=date('Y-m-d',strtotime($al2 . "+1 days"));
                        $al3=date('Y-m-d',strtotime($al2 . "+4 days"));
                        $arr[$al2]=$al3;
						$al3=date('Y-m-d',strtotime($al3 . "+1 days"));
                        $al4=date('Y-m-d',strtotime($al3 . "+4 days"));
                        $arr[$al3]=$al4;
						$al4=date('Y-m-d',strtotime($al4 . "+1 days"));
                        $al5=date('Y-m-d',strtotime($al4 . "+4 days"));
                        $arr[$al4]=$al5;
						$al5=date('Y-m-d',strtotime($al5 . "+1 days"));
                        $al6=date('Y-m-d',strtotime($al5 . "+3 days"));
                        $arr[$al5]=$al6;
                }else{

                }
				/*echo '<pre>';
				print_r($arr);
				echo '</pre>';*/
			
				
                $counter=0; 
                $out="";
				//caso Francavilla
				if($selzone==9){
				
					$hotelid=array();
					$filename=__DIR__."/../../tempCsv/config.csv";
					$value=  file_get_contents($filename);
				
						if($value!=""){
						   $hotelid=  explode(";", $value);
						}
					
					//loop per hotel id che sono leti dal file config	
					foreach ($hotelid as $hotel){
						$counter++;
						
						$reportData = $this->query->getDataReport2($dal, $al, $selzone, $datacreazioneReport, $hotelname,$hotel);  
						$out.=$exportToCsv->generateCsvFile($reportData,$counter);
					}
					
					 
                
				}
				else{
					//caso dal-al
					if(count($arr)==0){
						
						if ($selzone ==10) {
						$reportData = $this->query->getDataFilter1Report($dal, $al, $selzone, $datacreazioneReport, $hotelname,$sito);
						}
					
						else {
						$reportData = $this->query->getDataReport($dal, $al, $selzone, $datacreazioneReport, $hotelname,$sito);
						}
						$out.=$exportToCsv->generateCsvFile($reportData,$counter);
					}
					else {
						//caso per i messi
						
						foreach($arr as $k=>$a){
						$counter++;
						
						if ($selzone == 10) {
						$reportData = $this->query->getDataFilter1Report($k, $a, $selzone, $datacreazioneReport, $hotelname,$sito);
						} 
						
						else {
						$reportData = $this->query->getDataReport($k, $a, $selzone, $datacreazioneReport, $hotelname,$sito);
						}
						$out.=$exportToCsv->generateCsvFile($reportData,$counter);
						}	
					}
					
				}
				
                //ho i dati da db 
				$exportToCsv->genCsv($out);
			
			/*$counter=0;
                            $out="";
                            $output="";
                            $arraycsv=array(); 
                            if(count($arr)==0){
                                        if ($selzone > 8) {
                                //Casi particolari di filtri
                            $reportData = $this->query->getDataFilter1Report($dal, $al, $selzone, $datacreazioneReport, $hotelname);
                            } else {
                                $reportData = $this->query->getDataReport($dal, $al, $selzone, $datacreazioneReport, $hotelname);
                            }
                                        $output=$exportToCsv->generateCsvFile($reportData,$counter);
                                        
                            }else{
                                foreach($arr as $k=>$a){
                                            $counter++;
                                    if ($selzone > 8) {
                                        //Casi particolari di filtri
                                        $reportData = $this->query->getDataFilter1Report($k, $a, $selzone, $datacreazioneReport, $hotelname);
                                    } else {
                                        $reportData = $this->query->getDataReport($k, $a, $selzone, $datacreazioneReport, $hotelname);
                                    }
                                                $arraycsv[]=$exportToCsv->generateCsvFile($reportData,$counter);
                                     }
									 var_dump($arraycsv);
                                     //$exportToCsv->joinFiles($arraycsv, __DIR__."/../../tempCsv/merge.csv");
                                     //$output=file_get_contents(__DIR__."/../../tempCsv/merge.csv");
                                    }
									//$exportToCsv->genCsv($output);
								/*$files = glob(__DIR__."/../../tempCsv/"); 
								foreach($files as $file){ 
								  if(is_file($file))
									unlink($file); 
								}*/
        }
    }

}
