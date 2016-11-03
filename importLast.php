<?php
header('Content-Type: text/html; charset=utf-8');
ob_implicit_flush(true);

include_once 'src/config.php';
require_once ROOTPATH.'/src/ImportRecord/ImportRecordClass.php';
ob_start();	
 echo "<br>Inizio IMPORT GLOBALE" . date('h:m:i u', mktime());
 echo ini_get('memory_limit');
 echo "<br> set to 2GB :";
ini_set('memory_limit', '2048M');
echo ini_get('memory_limit');
$import=new ImportRecordClass();
ob_flush();
flush();
if(!isset($_REQUEST['zona']))$_REQUEST['zona']=3;
//zona da importare, limite massimo di giorni da verificare partendo da oggi a ritroso
$import->insertLastDay($_REQUEST['zona'],$_REQUEST['daylimit']); 

ob_flush();
flush();
    