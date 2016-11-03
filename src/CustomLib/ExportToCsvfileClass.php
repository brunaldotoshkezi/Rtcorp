<?php

class ExportToCsvfileClass{
    //$out="";
    /**
     * prepara la stringa per il db
     * 
     * @param type $string
     * @return type
     */
       public function replaceString($string){
	  if (strpos($string,"'") !== false) {
		  $string=str_replace("'","\"",$string);
	  }
	  if(strpos($string,",") !== false){
	  $string=str_replace(","," ",$string);
	  }
	  /*if(strpos($string,".") !== false){
	  $string=str_replace(".",",",$string);
	  }*/
	  return $string;
   }
   
   
   /**
    * Prepara il file csv  e permette il download
    * 
    * @param type $result
    */
    public function generateCsvFile($result,$counter) {
		$out=""; 
		set_time_limit(2500);
		$filename = "reports".date('m_d_h_i')."_".$counter.".csv";
		if($counter<2){
		$out.="Report Date;GDS;RateTiger Hotel Code;Property ID;Hotel Name;Address;City;Country;Star Rating;Arrival Date;Day of Week;LOS;Occupancy;Currency ;Rate (Inclusive Tax);Rate (Exclusive Tax);Rate (As On Site);Room Description;Advance booking;\r\n";
        }
		if($result){
			while($arr = $result->FetchRow()){
				$out.="\"".date_format(date_create($arr['ReportDate']), 'd/m/Y')
				."\";\"".$arr['Booking']
				."\";\"".$arr['RateTiger']
				."\";\"".$arr['Property']
				."\";\"".$this->replaceString($arr['HotelName'])
				."\";\"".$this->replaceString($arr['Address'])
				."\";\"".$this->replaceString($arr['City'])
				."\";\"".$this->replaceString($arr['Country'])
				."\";\"".$arr['Star_Rate']
				."\";\"".date_format(date_create($arr['ArrivalDate']), 'd/m/Y')
				."\";\"".$arr['DayofWeek']
				."\";\"".$arr['LOS']
				."\";\"".$arr['Occupation']
				."\";\"".$arr['Currency']
				."\";\"".str_replace(".",",",$arr['Rate_Inclusive_Tax'])
				."\";\"".str_replace(".",",",$arr['Rate_Exclusive_Tax'])
				."\";\"".str_replace(".",",",$arr['Rate_As_On_Site'])
				."\";\"".$this->replaceString($arr['RoomDescription'])
                ."\";\"".$this->replaceString($arr['AdvanceBooking'])
				."\"\r\n";
				
			}
		}
		/*if($counter!=0){
			
		$file = fopen(__DIR__."/../../tempCsv/".$filename,"w");
                fwrite($file,$out);
                fclose($file);
                return __DIR__."/../../tempCsv/".$filename;}else {return $out;} */
		return $out;
	
	}	
	
		public function genCsv($out){ 
			$filename = "reports".date('m_d_h_i').".csv";
				header('Content-Type: text/csv');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
			echo $out; 
			$file = fopen('php://output','w');
		
				exit;

		}
        
        public function joinFiles(array $files, $result) {
        $wH = fopen($result, "w+");

        foreach($files as $file) {
            $fh = fopen($file, "r");
            while(!feof($fh)) {
                fwrite($wH, fgets($fh));
            }
            fclose($fh);
            unset($fh);
            fwrite($wH, "\n"); 
        }
        fclose($wH);
        unset($wH);
        }
    
    
}

