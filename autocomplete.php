<?php
include_once 'src/config.php';
//include("include/adodb5/adodb.inc.php");
require_once ("src/CustomLib/DbConnectClass.php");
//'/src/CustomLib/DbConnectClass.php';

$db = new DbConnectClass();
$sql = "";
// i have set the limit to 40 to speed up results 
$term = $_GET["q"];
$zone = $_GET["p"];
if ((int)$zone < 9) {

    $sql = "select distinct
shd1.HotelName as id,shd1.HotelName 
from dbo.ShooperDates_" . $zone . " as shd1
 WHERE shd1.HotelName LIKE '%" . $term . "%'  
ORDER BY shd1.HotelName  ASC;
  ";
} else if((int)$zone ==9){
	$hotelid=array();
					$filename=__DIR__."/tempCsv/config.csv";
					$value=  file_get_contents($filename);
				
						if($value!=""){
						   $hotelid=  explode(";", $value);
						}
						$strhotels="";
						foreach ($hotelid as $hotel){
							$strhotels.=$hotel.",";
						}
						
    $sql = "select distinct
shd1.HotelName  as id,shd1.HotelName 
from dbo.ShooperDates_1 as shd1
 WHERE shd1.Property in (".substr($strhotels,0,strlen($strhotels)-1).")
ORDER BY shd1.HotelName  ASC;;
  ";
}
else {
    $sql = "select distinct  A.HotelName as id, A.HotelName  from
(select distinct
shd1.HotelName
from dbo.ShooperDates_3 as shd1
union all
select distinct
shd1.HotelName 
from dbo.ShooperDates_5 as shd1
union all
select distinct
shd1.HotelName 
from dbo.ShooperDates_7 as shd1)
A WHERE A.HotelName LIKE '%" . $term . "%' 
ORDER BY A.HotelName  ASC;";
}
$db->getConnection()->SetFetchMode(ADODB_FETCH_BOTH);
$result = $db->getConnection()->Execute($sql);
$hname_list = array();
if ($result) {
    while ($arr = $result->FetchRow()) {
        $hname_list[] = array("id" => $arr["HotelName"], "text" => $arr["HotelName"]);
    }
}
// $items=array('items' =>$hname_list );
echo json_encode($hname_list, 128);


