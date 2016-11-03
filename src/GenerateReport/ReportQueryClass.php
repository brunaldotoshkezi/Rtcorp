<?php

include_once ROOTPATH . '/src/CustomLib/DbConnectClass.php';

class ReportQueryClass extends DbConnectClass {

    private $db;

    public function __construct() {
        parent::__construct();
      
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
     * A partire dai mesi disponibili recupera tutti i mesi in cui si possono estrarre i dati
     * 
     * @return type
     */
    function getAllAvailableMonths() {

        $sql = "   select distinct 
                datepart(month,shd1.ReportDate) as month 
                ,datename(month,shd1.ReportDate) as monthname 
                ,datepart(year,shd1.ReportDate)  as year
                from dbo.ShooperDates_1 as shd1
                 ORDER BY year DESC,month DESC
                ";
        //echo $sql;
        $rs = $this->adoExecQuery($sql);
        return $rs;
    }
	
	/**
     * Get Dates of Months Availave
     * @param type $dal
     * @param type $al
     * @return type
     */
    function getDatesOfMonth($dal,$al){
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
	
	    /**
     * Prende le date duplicate
     * @param type $selzone
     * @param type $dal
     * @param type $al
     * @return type
     * 
     * 
     */
    function getDuplicatedDates($selzone,$dal,$al){
       $duplicates=array();
        $sqlduplicated=" select ShooperLog.LastDateChars 
                    from ShooperLog where CategoryZone={$selzone} and  ShooperLog.LastDateChars >=CONVERT(datetime, '".$dal."', 101) and ShooperLog.LastDateChars<=CONVERT(datetime, '".$al."', 101) AND esito=1
                    group by ShooperLog.LastDateChars  
                    having COUNT(ShooperLog.LastDateChars )>1";
                    
        $sqlduplicates = $this->adoExecQuery($sqlduplicated);
        if($sqlduplicates){
        $duplicates=$sqlduplicates->GetArray();}
        return $duplicates;
    }
	/**
         * Prende le date duplicate per zona Roma-Milano
         * @param type $selzone
         * @param type $dal
         * @param type $al
         * @return type
         */
    function getDuplicatedDatesRM($selzone,$dal,$al){
       $duplicates=array();
        $sqlduplicated="select ShooperLog.LastDateChars 
                    from ShooperLog where CategoryZone={$selzone}  AND esito=1 and  ShooperLog.LastDateChars >=CONVERT(datetime, '".$dal."', 101) and ShooperLog.LastDateChars<=CONVERT(datetime, '".$al."', 101) and ShooperLog.ID in (select ShooperDates_{$selzone}.IDLogs from ShooperDates_{$selzone} WHERE (LOWER(LTRIM(rtrim(City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(City))) LIKE '%milan%'))
                    group by ShooperLog.LastDateChars  
                    having COUNT(ShooperLog.LastDateChars )>1";
                    
        $sqlduplicates = $this->adoExecQuery($sqlduplicated);
        if($sqlduplicates){
        $duplicates=$sqlduplicates->GetArray();}
        return $duplicates;
    }
	
	    /**
         * Prende le date duplicate per zona Francavilla
         * @param type $selzone
         * @param type $dal
         * @param type $al
         * @return type
         */
    function getDuplicatedDatesFR($selzone,$dal,$al){
       $duplicates=array();
        $sqlduplicated="select ShooperLog.LastDateChars 
                    from ShooperLog where CategoryZone=1  AND esito=1 and  ShooperLog.LastDateChars >=CONVERT(datetime, '".$dal."', 101) and ShooperLog.LastDateChars<=CONVERT(datetime, '".$al."', 101) and ShooperLog.ID in (select ShooperDates_1.IDLogs from ShooperDates_1 WHERE LOWER(LTRIM(rtrim(City))) LIKE '%francavilla%') 
                    group by ShooperLog.LastDateChars  
                    having COUNT(ShooperLog.LastDateChars )>1";
                    
        $sqlduplicates = $this->adoExecQuery($sqlduplicated);
        if($sqlduplicates){
        $duplicates=$sqlduplicates->GetArray();}
        return $duplicates;
    }
	
	  
    
	 /**
     * Get ID on Interval
     * @param type $selzone
     * @param type $dal
     * @param type $al
     * @param type $date
     * @return type
     */
    function getIds($selzone,$dal,$al,$date){
        $ids=array();
        $sqlids="  select distinct IDLogs as ID from ShooperDates_{$selzone} si  where si.CategoryZone={$selzone} and si.ReportDate>=CONVERT(datetime, '".$dal."', 101) and si.ReportDate<=CONVERT(datetime, '".$al."', 101);";
        $resids = $this->adoExecQuery($sqlids);
         if($resids){
        $ids=$resids->GetArray();}
        
        return $ids;
    }
    /**
     * Get ID on Interval per zona Roma-Milano
     * @param type $selzone
     * @param type $dal
     * @param type $al
     * @param type $date
     * @return type
     */
    function getIdsRM($selzone,$dal,$al,$date){
        $ids=array();
        $sqlids="  select distinct IDLogs as ID from ShooperDates_{$selzone} si  where si.CategoryZone={$selzone} and si.ReportDate>=CONVERT(datetime, '".$dal."', 101) and si.ReportDate<=CONVERT(datetime, '".$al."', 101) and (LOWER(LTRIM(rtrim(si.City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(si.City))) LIKE '%milan%');";
        $resids = $this->adoExecQuery($sqlids);
         if($resids){
        $ids=$resids->GetArray();}
        
        return $ids;
    }
	
	      /**
     * Get ID on Interval per zona Francavilla
     * @param type $selzone
     * @param type $dal
     * @param type $al
     * @param type $date
     * @return type
     */
    function getIdsFR($selzone,$dal,$al,$date){
        $ids=array();
        $sqlids="  select distinct IDLogs as ID from ShooperDates_1 si  where si.CategoryZone=1 and si.ReportDate>=CONVERT(datetime, '".$dal."', 101) and si.ReportDate<=CONVERT(datetime, '".$al."', 101) and LOWER(LTRIM(rtrim(si.City))) LIKE '%francavilla%' ;";
        $resids = $this->adoExecQuery($sqlids);
         if($resids){
        $ids=$resids->GetArray();}
        
        return $ids;
    }
	
	
	  /**
     * Prende le date che sono dentro questo intervalo
     * Get all dates nella zona
     * @param type $selzone
     * @param type $dal
     * @param type $al
     */
    function getDateShooper($selzone,$dal,$al,$limit){
        $resdate=array();
        $strlimit=" ";
        if($limit){
           $strlimit.=" TOP 1 "; 
        }
        $dates="select distinct ".$strlimit." ReportDate  from ShooperDates_".$selzone." where   ShooperDates_".$selzone.".ReportDate >=CONVERT(datetime, '".$dal."', 101) and ShooperDates_".$selzone.".ReportDate<=CONVERT(datetime, '".$al."', 101)";
        
		$resdates = $this->adoExecQuery($dates);
         if($resdates){
         $resdate=$resdates->GetArray();}
        return $resdate;
    }
    /**
     * Prende le date che sono dentro questo intervalo per zona Roma-Milano
     * @param type $selzone
     * @param type $dal
     * @param type $al
     * @param type $limit
     * @return type
     */
    function getDateShooperRM($selzone,$dal,$al,$limit){
       $resdate=array();
        $strlimit=" ";
        if($limit){
           $strlimit.=" TOP 1 "; 
        }
        $dates="select distinct ".$strlimit." ReportDate  from ShooperDates_".$selzone." where   ShooperDates_".$selzone.".ReportDate >=CONVERT(datetime, '".$dal."', 101) and ShooperDates_".$selzone.".ReportDate<=CONVERT(datetime, '".$al."', 101) AND (LOWER(LTRIM(rtrim(City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(City))) LIKE '%milan%') ";
   
        $resdates = $this->adoExecQuery($dates);
         if($resdates){
         $resdate=$resdates->GetArray();}
        return $resdate;
    }
	
	    /**
     * Prende le date che sono dentro questo intervalo per zona Francavilla
     * @param type $selzone
     * @param type $dal
     * @param type $al
     * @param type $limit
     * @return type
     */
    function getDateShooperFR($selzone,$dal,$al,$limit){
       $resdate=array();
        $strlimit=" ";
        if($limit){
           $strlimit.=" TOP 1 "; 
        }
        $dates="select distinct ".$strlimit." ReportDate  from ShooperDates_1 where   ShooperDates_1.ReportDate >=CONVERT(datetime, '".$dal."', 101) and ShooperDates_1.ReportDate<=CONVERT(datetime, '".$al."', 101) AND LOWER(LTRIM(rtrim(City))) LIKE '%francavilla%'  ";
   
        $resdates = $this->adoExecQuery($dates);
         if($resdates){
         $resdate=$resdates->GetArray();}
        return $resdate;
    }
	
	 /**
     * Prende i dati nella tabella log che sono duplicati
     * @param type $selzone
     * @param type $strduplicated
     * @param type $strid
     * @return type
     */
    function getLogDataDuplicated( $selzone, $strduplicated,$strid) {
       $resdate=array();
       $lastdate="";
       $id="";
               if($strduplicated!=""){
                $lastdate="  and sl.LastDateChars in({$strduplicated}) ";
               
                 if($strid!=""){
                $id="  and sl.ID in({$strid}) ";
               }}else{
				    $id="  and sl.ID in(0) ";
			   }
               
        $data="select sl.ID,sl.LastDateChars,sl.ArrivalDateError,sl.TimeOfLastDate from  ShooperLog sl where sl.CategoryZone={$selzone} ".$lastdate.$id." ORDER BY sl.LastDateChars ASC ";
		//echo $data.';';
         $resdata = $this->adoExecQuery($data);
         if($resdata){
          $resdate=$resdata->GetArray();}
       
        return $resdate;
    }
	
	
	
	 /**
     * Pernde i dati che sono nell Log
     * @param type $selzone
     * @param type $strid
     * @return type
     */
      function getLogData($selzone,$strid,$date) {
     
          $id="";
          $data="";
           if($strid!=""){
                $id="  and sl.ID in({$strid}) ";
                 $data="select sl.ID,sl.LastDateChars,sl.ArrivalDateError from  ShooperLog sl where sl.CategoryZone={$selzone} ".$id.";";
               }else{
                 $data="select sl.ID,sl.LastDateChars,sl.ArrivalDateError from  ShooperLog sl where sl.CategoryZone={$selzone} and sl.LastDateChars='".$date."';";   
               }
          $resdate=array();       //$data="select TOP 1 sl.ID from  ShooperLog sl where sl.CategoryZone={$selzone}  and sl.ID in ({$strid})";
         $resdata = $this->adoExecQuery($data);
         if($resdata){
          $resdate=$resdata->GetArray();}
      
        return $resdate;
    }
	
	/**
    * Si usa per i casi dove non ci sono i duplicati
    * @param type $selzone
    * @param type $strdup
    * @param type $strid
    * @param type $arrival
    * @return string
    */ 
    function strconcat($selzone,$strdup,$strid,$arrival,$zona) {
        $str="";
        if($strdup==""&&$strid==""){ $str.="";}else{
            if($strdup!=""&&$strid!=""){
             $str.="  IDLogs in ( select ShooperLog.ID from ShooperLog where ShooperLog.ID  in (".substr($strdup,0,strlen($strdup)-1).")
         and CategoryZone={$selzone} and ArrivalDateError={$arrival}
          UNION ALL  
         select   ShooperLog.ID from ShooperLog where  ShooperLog.ArrivalDateError={$arrival}  and ShooperLog.ID in (".substr($strid,0,strlen($strid)-1)."))";
             
        }else if($strdup!=""&&$strid==""){
              $str.="  IDLogs in ( select ShooperLog.ID from ShooperLog where ShooperLog.ID  in (".substr($strdup,0,strlen($strdup)-1)."
        )
         and CategoryZone={$selzone} and ArrivalDateError={$arrival} )";
        }else if($strdup==""&&$strid!=""){
            $str.=" IDLogs in ( select   ShooperLog.ID from ShooperLog where  ShooperLog.ArrivalDateError={$arrival}  and ShooperLog.ID in (".substr($strid,0,strlen($strid)-1).")) ";
        }
        } 
         return $str ;
    }
    /**
     * il funzione array_column per Php 5.4
     * @param array $input
     * @param type $columnKey
     * @param type $indexKey
     * @return boolean|array
     */
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( ! isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( ! isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
    
    /**
     * Eseque la query per recuperare i dati dalle varie zone
     * 
     * 
     * @param type $dal
     * @param type $al
     * @param type $selzone
     * @param type $date
     * @param type $hotelname
     * @return type Object
     */
    function getDataReport($dal, $al, $selzone, $date, $hotelname,$sito) {
		
        set_time_limit(500);
	$dateshooper2=array();
        
        //prendo tutti le date del periodo
        $dates=  $this->getDatesOfMonth($dal, $al);
        
        //prendo le date duplicati
        $duplicated=  $this->getDuplicatedDates($selzone,$dal, $al);
        $duplicated=  $this->array_column($duplicated, 'LastDateChars');
	
        $strduplicated="";
        foreach ($duplicated as $dup){
            $strduplicated.="'".$dup."',";
        }
        
        //prendo le date che sono su db
        $dateshooper=  $this->getDateShooper($selzone, $dal, $al,false);
        $i=0;
        foreach ($dateshooper as $ds){
            $i++;
        }
        if($i==0){
             $dateshooper2=  $this->getDateShooper($selzone, date("Y-m-01", strtotime($dal)), date("Y-m-t", strtotime($dal)),true);
            $dateshooper=  $this->array_column($dateshooper2, 'ReportDate');
        }else{
             $dateshooper=  $this->array_column($dateshooper, 'ReportDate');
        }
        

        //non ci sono i dati richiesti
        $datenotinarray=array();
        foreach ($dates as $d ){
            if(!in_array($d, $dateshooper)){
               $datenotinarray[]=$d; 
            }
        }

        //Un array dove escono come key data che non esiste e come valutazione data che e' in database $valuesofdatesneeded
        $values=array();
        $minvalues=array();
        $valuesofdates=array();
        $valuesofdatesneeded=array();
        foreach ($datenotinarray as $dat){
           $datevalue=  strtotime($dat);
           foreach ($dateshooper as $ds){
               $dshooper=  strtotime($ds);
               $diff=abs($datevalue-$dshooper);
               $values[$ds]=$diff;
              
           }
           foreach ($values as $k=>$v){
               $vl=min($values);
               if($v==$vl){
               $minvalues[]=$k;}
             
            
           }  
           foreach ($minvalues as $mv){
               if(in_array($mv, $duplicated)){
                   $valuesofdatesneeded[$dat]=$mv;
               }else{
                  $valuesofdatesneeded[$dat]=$mv;
                               
               }
           }
          
        }

		
        $idss=  $this->getIds($selzone, $dal, $al, $date);
        $strid=""; 
        foreach($idss as $id){
			if($id['IDLogs']!='0'){
            $strid.=$id['IDLogs'].',';
			}
        }

        //Loop dove escono i id dei file duplicati per i casi 3,4,5
        $dataduplicated=  $this->getLogDataDuplicated($selzone, substr($strduplicated,0,  strlen($strduplicated)-1),substr($strid,0,  strlen($strid)-1) );
			$ids="";
			$idduplicated=array();
			if(count($dataduplicated)>0){
		  $tmparray=array();
        $counter=0;
        $dataduplicateds = array_map('array_values', $dataduplicated);
        $array=array();
        $array2=array();
        for($i=0;$i<count($dataduplicateds);$i++){
           for($j=0;$j<4;$j++){
              $array[]=$dataduplicateds[$i][$j];	}
                if($i%2==1){
                     $array2[]=$array;
                     $array=array();
                }
        }

        foreach ($array2 as $dd) {
			if($dd[2]==0 && $dd[6]==1){
				$ids.=$dd[0].',';
			}else if($dd[2]==1 && $dd[6]==0){
				$ids.=$dd[4].',';
			}else if($dd[2]==1 && $dd[6]==1){
				if($dd[3]>$dd[7]){
					$ids.=$dd[0].',';
				}else{
					$ids.=$dd[4].',';
				}
			}else if($dd[2]==0 && $dd[6]==0){
				if($dd[3]>$dd[7]){
					$ids.=$dd[0].',';
				}else{
					$ids.=$dd[4].',';
				}
			}
		}
		$idduplicated=  $this->array_column( $dataduplicated,'ID');
		}
        $stridduplicated="";
        foreach ($idduplicated as $idd){
             $stridduplicated.=$idd.",";
        }

        //prendo id che non sono duplicati
      
        $stridvalues="";
        foreach($idss as $i){
            if(!in_array($i['IDLogs'], $idduplicated)){
                $stridvalues.=$i['IDLogs'].',';
            }
        }
        
        //la stringa per hotelname
        $hotelclause="";
        if($hotelname!=""){
            $hotelclause=" HotelName='".$hotelname."' ";
        }
        
	//la stringa perche non serve moonlight	
	$moonlightclause="";
        if($selzone==3 || $selzone==5 || $selzone==7){
            $moonlightclause=" Booking!='MoonLight' ";
        }
         
       
        //creazione della sql stringa per i deti che sono su db
	$sql=" ";
	$sql2=" ";
	$strAnd=" AND ";
	$strWhere =" WHERE ";
	$sitoclause=" ";
    if($sito!=''){
    $sitoclause=" AND Booking='{$sito}' ";
    }
	$strconcat1=$this->strconcat($selzone, $stridvalues, $ids, 1,$selzone);
	$strconcat0=$this->strconcat($selzone, $stridvalues, $ids, 0,$selzone);
	$selectsql1=" select  ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +1, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription,DATEDIFF(day,ReportDate,DATEADD(dd, +1, ArrivalDate)) as AdvanceBooking "
                . "   from ShooperDates_{$selzone} ";
	$selectsql0=" select  ReportDate, Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate, ArrivalDate,DayofWeek,LOS,Occupation,Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription , DATEDIFF(day,ReportDate, ArrivalDate) as AdvanceBooking "
                . "     from ShooperDates_{$selzone} ";
        if($strconcat1!=""){
        $sql.=$selectsql1.$strWhere.$strconcat1.$sitoclause;
              if($hotelclause!=""){
                      $sql.=$strAnd.$hotelclause;
              }
              if($moonlightclause!=""){
                      $sql.=$strAnd.$moonlightclause;
              }
              }
        if($strconcat0!=""){
        $sql2.=$selectsql0.$strWhere.$strconcat0.$sitoclause;
              if($hotelclause!=""){
                      $sql2.=$strAnd.$hotelclause;
              }
              if($moonlightclause!=""){
                      $sql2.=$strAnd.$moonlightclause;
              }
              }
              if($sql2!="" && $strconcat0!=""){
               $sql.=" UNION ALL ".$sql2;
              }
                        
        if($hotelclause!=""){
	 $hotelclause=$strAnd.$hotelclause;	 
        }
	if($moonlightclause!=""){
	$moonlightclause=$strAnd.$moonlightclause;
	}
        
        //prendo i id e la data per i dati che sono su db
        $iddays=  $this->getLogData($selzone, substr($strid, 0,  strlen($strid)-1),$dateshooper[0]);
       
        foreach($valuesofdatesneeded as $k=> $v){
     
            $idin="";
            $arrival=-1;
            $difference=  (strtotime($k)-strtotime($v))/86400;
            //se le date sono duplicate caso 2
            if(in_array($v,$duplicated)&& count($duplicated)>0){ 
                
                foreach ($array2 as $a){
                    if($a[1]==$v){
                       if($a[3]<$a[7]){
                           $idin.=$a[0];
                            $arrival=$a[2];
                       } else{
                           $idin.=$a[4];
                           $arrival=$a[6];
                       }
                    
                          if($arrival==1){
                           $difference=$difference+1;

                 if(trim($sql)!=""){
                 $sql.=" UNION ALL ";}
                          $sql.=" select '{$k}' as ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +{$difference}, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
                         Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription,DATEDIFF(day,'{$k}',DATEADD(dd, +{$difference}, ArrivalDate)) as AdvanceBooking from ShooperDates_{$selzone} 
                           where IDLogs={$idin} ".$sitoclause.$hotelclause.$moonlightclause;
                          // echo $sql2.';';
                }
                if($arrival==0){
                    
                   if(trim($sql)!=""){
                 $sql.=" UNION ALL ";}
                          $sql.=" select '{$k}' as ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +{$difference}, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
                         Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription,DATEDIFF(day,'{$k}',DATEADD(dd, +{$difference}, ArrivalDate)) as AdvanceBooking from ShooperDates_{$selzone} 
                           where IDLogs={$idin} ".$sitoclause.$hotelclause.$moonlightclause;

                   
                }
             }
         } 
            }
           else{//se le date non sono duplicate caso 6
               
                 foreach ($iddays as $days){
                      if($days['LastDateChars']==$v){

                           $idin.=$days['ID'];
                            $arrival=$days['ArrivalDateError'];
                       

                       if($arrival==1){
                           $difference=$difference+1;
                          
                          if(trim($sql)!=""){
                        $sql.=" UNION ALL ";}
                          $sql.= " select '{$k}' as ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +{$difference}, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
                                'NA' as Currency,'NA' as Rate_Inclusive_Tax,'NA' as Rate_Exclusive_Tax,'NA' as Rate_As_On_Site,'NA' as RoomDescription,DATEDIFF(day,'{$k}',DATEADD(dd, +{$difference}, ArrivalDate)) as AdvanceBooking from ShooperDates_{$selzone} 
                                  where IDLogs={$idin} ".$sitoclause.$hotelclause.$moonlightclause;
                       }
                       if($arrival==0){


                           if(trim($sql)!=""){
                              $sql.=" UNION ALL ";}
                          $sql.=" select '{$k}' as ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +{$difference}, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
                                'NA' as Currency,'NA' as Rate_Inclusive_Tax,'NA' as Rate_Exclusive_Tax,'NA' as Rate_As_On_Site,'NA' as RoomDescription,DATEDIFF(day,'{$k}',DATEADD(dd, +{$difference}, ArrivalDate)) as AdvanceBooking from ShooperDates_{$selzone} 
                                  where IDLogs={$idin} ".$sitoclause.$hotelclause.$moonlightclause;
                       }
                    }
                }


			}
		}
		
      $result = $this->adoExecQuery($sql);
        
      return $result;
	  //echo $sql.';';
    }
	
	/**
     * Casistica particolare in quanto sono gestioni di piu zone per recuperare citta specifiche
     * Eseque la query per recuperare i dati dalle varie zone
     * 
     * @param type $dal
     * @param type $al
     * @param type $selzone
     * @param type $date
     * @param type $hotelname
     * @return type
     */
    function getDataFilter1Report($dal, $al, $selzone, $date, $hotelname,$sito) {
       set_time_limit(500);
       $zone=array(3,5,7);
       //$res=array();
       $sql="";
     
       foreach ($zone as $z){
          // $selzone=$z;
		  $selzone=$z;
	
           ${"sqlz" . $z}="";
        $dateshooper2=array();
         //prendo tutti le date del periodo
        $dates=  $this->getDatesOfMonth($dal, $al);
        //prendo le date duplicati
        $duplicated=  $this->getDuplicatedDatesRM($selzone,$dal, $al);
        $duplicated=  $this->array_column($duplicated, 'LastDateChars');
        $strduplicated="";
        foreach ($duplicated as $dup){
            $strduplicated.="'".$dup."',";
        }
        
        //prendo le date che sono su db
        $dateshooper=  $this->getDateShooperRM($selzone, $dal, $al,false);
		$i=0;
        foreach ($dateshooper as $ds){
            $i++;
        }
        if($i==0){
            $dateshooper2=  $this->getDateShooperRM($selzone, date("Y-m-01", strtotime($dal)), date("Y-m-t", strtotime($dal)),true);
            $dateshooper=  $this->array_column($dateshooper2, 'ReportDate');
        }else{
             $dateshooper=  $this->array_column($dateshooper, 'ReportDate');
        }
        
        //non ci sono i dati richiesti
        $datenotinarray=array();
         foreach ($dates as $d ){
            if(!in_array($d, $dateshooper)){
               $datenotinarray[]=$d; 
            }
        }
        
        //tutti id in questo periodo
        $idss=  $this->getIdsRM($selzone, $dal, $al, $date);
        $strid="";
        foreach($idss as $id){
            $strid.=$id['IDLogs'].',';
        }	
        //Un array dove escono come key data che non esiste e come valutazione data che e' in database $valuesofdatesneeded
        $values=array();
        $minvalues=array();
        $valuesofdates=array();
        $valuesofdatesneeded=array();
        foreach ($datenotinarray as $dat){
           $datevalue=  strtotime($dat);
           foreach ($dateshooper as $ds){
               $dshooper=  strtotime($ds);
               $diff=abs($datevalue-$dshooper);
               $values[$ds]=$diff;
              
           }
           foreach ($values as $k=>$v){
               $vl=min($values);
               if($v==$vl){
               $minvalues[]=$k;}
             
            
           }  
           foreach ($minvalues as $mv){
               if(in_array($mv, $duplicated)){
                   $valuesofdatesneeded[$dat]=$mv;
               }else{
                  $valuesofdatesneeded[$dat]=$mv;
                               
               }
           }
        
        }
		

            
        //Loop dove escono i id dei file duplicati per i casi 3,4,5
         $dataduplicated=  $this->getLogDataDuplicated($selzone, substr($strduplicated,0,  strlen($strduplicated)-1),substr($strid,0,  strlen($strid)-1) );
			$ids="";
			$idduplicated=array();
			if(count($dataduplicated)>0){
		  $tmparray=array();
        $counter=0;
        $dataduplicateds = array_map('array_values', $dataduplicated);
        $array=array();
        $array2=array();
        for($i=0;$i<count($dataduplicateds);$i++){
           for($j=0;$j<4;$j++){
              $array[]=$dataduplicateds[$i][$j];	}
                if($i%2==1){
                     $array2[]=$array;
                     $array=array();
                }
        }

        
        
        foreach ($array2 as $dd) {
            
        
			if($dd[2]==0 && $dd[6]==1){
				$ids.=$dd[0].',';
			}else if($dd[2]==1 && $dd[6]==0){
				$ids.=$dd[4].',';
			}else if($dd[2]==1 && $dd[6]==1){
				if($dd[3]>$dd[7]){
					$ids.=$dd[0].',';
				}else{
					$ids.=$dd[4].',';
				}
			}else if($dd[2]==0 && $dd[6]==0){
				if($dd[3]>$dd[7]){
					$ids.=$dd[0].',';
				}else{
					$ids.=$dd[4].',';
				}
			}
		}
		$idduplicated=  $this->array_column( $dataduplicated,'ID');
		}
         $stridduplicated="";
         foreach ($idduplicated as $idd){
             $stridduplicated.=$idd.",";
         }
         
        //la stringa per nome hotel
        $hotelclause="";
        if($hotelname!=""){
            $hotelclause="  AND HotelName='".$hotelname."' ";
        }
	
        //la stringa per moonlight
        $moonlightclause="";
        if($selzone==3 || $selzone==5 || $selzone==7){
            $moonlightclause=" AND Booking!='MoonLight' ";
        }
        
         //prendo id che non sono duplicati
        $stridvalues="";
        foreach($idss as $i){
            if(!in_array($i['IDLogs'], $idduplicated)){
                $stridvalues.=$i['IDLogs'].',';
            }
        }
	
	//creazione della sql stringa per i deti che sono su db		
	$sql20="";
        $sql21="";
	$strAnd=" AND ";
	 $sitoclause="";
	if($sito!=""){
	 $sitoclause=" AND Booking='{$sito}' ";
	 }
	$strWhere =" WHERE ";
        $strunion=" UNION ALL ";
	$strconcat1=$this->strconcat($selzone, $stridvalues, $ids, 1,8);
	$strconcat0=$this->strconcat($selzone, $stridvalues, $ids, 0,8);
	$selectsql1=" select  ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +1, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
        Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription,DATEDIFF(day,ReportDate,DATEADD(dd, +1, ArrivalDate)) as AdvanceBooking
        from ShooperDates_{$selzone} 
        where (LOWER(LTRIM(rtrim(City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(City))) LIKE '%milan%') ".$sitoclause;
	$selectsql0=" select   ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate, ArrivalDate,DayofWeek,LOS,Occupation,
        Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription,DATEDIFF(day,ReportDate, ArrivalDate) as AdvanceBooking from ShooperDates_{$selzone} 
        where (LOWER(LTRIM(rtrim(City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(City))) LIKE '%milan%') ".$sitoclause;
        if($strconcat1!=""){
        $sql21.=$selectsql1.$strAnd.$strconcat1;
        if($hotelclause!=""){
                    $sql21.=$hotelclause;
        }
        if($moonlightclause!=""){
        $sql21.=$moonlightclause;
        }
        }

        if($strconcat0!=""){
	$sql20.=$selectsql0.$strAnd.$strconcat0;
	if($hotelclause!=""){
	$sql20.=$hotelclause;
	}
        if($moonlightclause!=""){
                $sql20.=$moonlightclause;
        }
        }
		if($strid!=""&&  count($dateshooper)>0){
        $iddays=  $this->getLogData($selzone, substr($strid, 0,  strlen($strid)-1),$dateshooper[0]);
        //i casi con i dati che non sono su db
        $sql3="";
        foreach($valuesofdatesneeded as $k=> $v){
     
            $idin="";
            $arrival=-1;
            $difference=  (strtotime($k)-strtotime($v))/86400;
            //i casi con i dati che non sono per dati duplicati
            if(in_array($v,$duplicated) && count($duplicated)>0){
              
                foreach ($array2 as $a){
                    if($a[1]==$v){
                       if($a[3]<$a[7]){
                           $idin.=$a[0];
                            $arrival=$a[2];
                       } else{
                           $idin.=$a[4];
                           $arrival=$a[6];
                       }
                    
                        if($arrival==1){
                           $difference=$difference+1;
                 

                            if(trim($sql3)!==""){
                                 $sql3.=" UNION ALL ";
                            }
                            $sql3.="select '{$k}' as ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +{$difference}, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
                            Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription,DATEDIFF(day,'{$k}',DATEADD(dd, +{$difference}, ArrivalDate)) as AdvanceBooking from ShooperDates_{$selzone} 
                            where IDLogs={$idin} and (LOWER(LTRIM(rtrim(City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(City))) LIKE '%milan%') ".$sitoclause.$hotelclause.$moonlightclause;

                        }
                        if($arrival==0){



                           if(trim($sql3)!==""){
                                      $sql3.=" UNION ALL ";}
                                $sql3.="select   '{$k}' as ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +{$difference}, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
                                 Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription,DATEDIFF(day,'{$k}',DATEADD(dd, +{$difference}, ArrivalDate)) as AdvanceBooking from ShooperDates_{$selzone} 
                                   where IDLogs={$idin} and (LOWER(LTRIM(rtrim(City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(City))) LIKE '%milan%') ".$sitoclause.$hotelclause.$moonlightclause;




                        }
                    }
            } 
            }
           else{
               
                //i casi con i dati che non sono 
                foreach ($iddays as $days){
                      if($days['LastDateChars']==$v){

                           $idin.=$days['ID'];
                            $arrival=$days['ArrivalDateError'];
                        

                       if($arrival==1){
                           $difference=$difference+1;
                          

                        if(trim($sql3)!==""){
                            $sql3.=" UNION ALL ";
                        }
                                 $sql3.="select  '{$k}' as ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +{$difference}, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
                                'NA' as Currency,'NA' as Rate_Inclusive_Tax,'NA' as Rate_Exclusive_Tax,'NA' as Rate_As_On_Site,'NA' as RoomDescription,DATEDIFF(day,'{$k}',DATEADD(dd, +{$difference}, ArrivalDate)) as AdvanceBooking from ShooperDates_{$selzone} 
                                  where IDLogs={$idin} and (LOWER(LTRIM(rtrim(City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(City))) LIKE '%milan%') ".$sitoclause.$hotelclause.$moonlightclause;
                       }
                       if($arrival==0){
                          

                            if(trim($sql3) !== ""){
                              $sql3.=" UNION ALL ";
                              
                            }
                            $sql3.="select  '{$k}' as ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,DATEADD(dd, +{$difference}, ArrivalDate)as ArrivalDate,DayofWeek,LOS,Occupation,
                            'NA' as Currency,'NA' as Rate_Inclusive_Tax,'NA' as Rate_Exclusive_Tax,'NA' as Rate_As_On_Site,'NA' as RoomDescription,DATEDIFF(day,'{$k}',DATEADD(dd, +{$difference}, ArrivalDate)) as AdvanceBooking from ShooperDates_{$selzone} 
                            where IDLogs={$idin} and (LOWER(LTRIM(rtrim(City))) LIKE '%rome%' OR LOWER(LTRIM(rtrim(City))) LIKE '%milan%') ".$sitoclause.$hotelclause.$moonlightclause;
                       }
                    }
                }


        }

           
       }
    //concatenazione delle tutte stringe
       if(trim($sql21)!=""&&trim($sql20)!=""&&trim($sql3)!=""){
                     ${"sqlz" . $z}.=$sql21.$strunion.$sql20.$strunion.$sql3;
        }
        else if(trim($sql21)!=""&&trim($sql20)!=""&&trim($sql3)==""){
                     ${"sqlz" . $z}.=$sql21.$strunion.$sql20;
        }
        else if(trim($sql21)!=""&&trim($sql20)==""&&trim($sql3)!=""){
                     ${"sqlz" . $z}.=$sql21.$strunion.$sql3;
        }
        else if(trim($sql21)==""&&trim($sql20)!=""&&trim($sql3)!=""){
                     ${"sqlz" . $z}.=$sql20.$strunion.$sql3;
        }
        else if(trim($sql21)!=""&&trim($sql20)==""&&trim($sql3)==""){
            ${"sqlz" . $z}.=$sql21;
        }
        else if(trim($sql21)==""&&trim($sql20)!=""&&trim($sql3)==""){
            ${"sqlz" . $z}.=$sql20;
        }
        else if(trim($sql21)==""&&trim($sql20)==""&&trim($sql3)!=""){
            ${"sqlz" . $z}.=$sql3;
        }
        }
         if($sql!="" ){
             if(${"sqlz" . $z}!=""){
             $sql.=" UNION ALL ";
             $sql.=${"sqlz" . $z};
             }
            }  else {
                if(${"sqlz" . $z}!=""){
                $sql=${"sqlz" . $z};
                }
            }
        
         
   }
 $result = $this->adoExecQuery($sql);
                
 return $result;
}

function getDataReport2($dal, $al, $selzone, $date, $hotelname,$hotelid) {
        set_time_limit(500);
        $selzone=1;
		$hotelclause="";
		if($hotelname!=""){
			$hotelclause.=" AND HotelName ='".$hotelname."' ";
		}
		$hotelid=trim($hotelid);
        $sql=" select distinct ReportDate,Booking,RateTiger,Property,HotelName,Address,City,Country,Star_Rate,ArrivalDate,DayofWeek,LOS,Occupation, Currency,Rate_Inclusive_Tax,Rate_Exclusive_Tax,Rate_As_On_Site,RoomDescription
        from ShooperDates_{$selzone} 
        where   ShooperDates_1.{$date} >=CONVERT(datetime, '".$dal."', 101) and ShooperDates_1.{$date} <=CONVERT(datetime, '".$al."', 101) AND Property='{$hotelid}' ".$hotelclause."  ORDER BY {$date} ASC ";
		
        $result = $this->adoExecQuery($sql);
        
		return $result;
    }
}
