<?php

include_once ROOTPATH . '/src/CustomLib/DbConnectClass.php';
include_once ROOTPATH . '/src/CustomLib/FtpConnectClass.php';
include_once ROOTPATH . '/src/CustomLib/ShooperLogClass.php';
include_once ROOTPATH . '/include/PhpExcel/PHPExcel.php';

include_once ROOTPATH . '/src/ImportRecord/ImportQueryClass.php';

/**
 * Importazione dei dati da FTp
 * 1- recupera la lista dei file sulla cartella ftp in relazione alla zona scelta
 * 2- salva in locale il file da importare e scrive sul log il file che si sta lavorando
 * 3- verifica che il file non sia gia stato processato ( verifica nome file)
 * 4- verifica che ArrivaData non sia già stato importatate ( verifica a livello dei dati)
 * 5- importa i dati
 * 
 * Esite un sistema di log che memorizza eventuali errori di importazioni o query non riuscite
 */
class ImportRecordClass {

    private $db;
    private $query;
    private $ftp;
    private $shoplog;
    private $delimiter;
    private $enclosure;
    private $inportFileNum;
    private $excelReader;

    function __construct() {
        $this->db = new DbConnectClass();

        $this->query = new ImportQueryClass();
        $this->ftp = new FtpConnectClass();
        $this->shoplog = new ShooperLog();
        $this->excelReader = PHPExcel_IOFactory::createReader('Excel2007');
        $this->delimiter = ';';
        $this->enclosure = '"';
        $this->inportFileNum = 0;
    }

    /**
     *  *  1 "Seneca_Centro";
     *  2 "Seneca_Estero";
     *  3 "Seneca_Important";
     *  4 "Seneca_Isole";
     *  5 "Seneca_Lazio";
     *  6 "Seneca_NordEst";
     *  7 "Seneca_NordOvest";
     *  8 "Seneca_Sud";
     * 
     * @param type $zone
     * @return string
     */
    private function getFtpPathZone($zone) {
        switch ($zone) {
            case 1:
                $pathZone = "Seneca_Centro";
                break;
            case 2:
                $pathZone = "Seneca_Estero";
                break;
            case 3:
                $pathZone = "Seneca_Important";
                break;
            case 4:
                $pathZone = "Seneca_Isole";
                break;
            case 5:
                $pathZone = "Seneca_Lazio";
                break;
            case 6:
                $pathZone = "Seneca_NordEst";
                break;
            case 7:
                $pathZone = "Seneca_NordOvest";
                break;
            case 8:
                $pathZone = "Seneca_Sud";
                break;
        }
		echo "<hr>ZONA IMPORTATA {$pathZone}<hr>";
        return $pathZone;
    }

    /**
     * Inserisce i dati nella zona prescelta
     *  1 "Seneca_Centro";
     *  2 "Seneca_Estero";
     *  3 "Seneca_Important";
     *  4 "Seneca_Isole";
     *  5 "Seneca_Lazio";
     *  6 "Seneca_NordEst";
     *  7 "Seneca_NordOvest";
     *  8 "Seneca_Sud";
     * 
     * @param type $zone
     */
    public function insertLastDay($zone = 1,$maxDayLimit=30, $filesystemPath = null) {
			
        ob_start();
		$maxDay=0;
        $incFile = 0;
        $idLogs = 0;
        $arrivalerror = false;
        $arrivalerrorvalue = 0;
        $rownumber = 0;
        $incFile = 0;
        echo "<br>START IMPORT:" . date('h:i:s u', mktime()) . "<br>";
        $pathZone = $this->getFtpPathZone($zone);
        $mostRecent = array();
        $listFile = "";
        $listFile = $this->listOfFile($pathZone, $filesystemPath);
        //-2 per escludere . ..
        echo "<br>Recupero lista file num. file:" . count($listFile) - 2;
        $mostRecents = $this->findLastFtpFile($listFile);
        // echo "<br>Ultimi file creati:<br>" . var_dump($mostRecents);

        //$listOfFile = $this->findFileToInsert($zone, $listFile, $filesystemPath);

        foreach ($mostRecents as $mostRecent) {
	//chiudo ftp in modo da riaprirla ad ogni connessione
			//$this->ftp->ftpClose();
            set_time_limit(2500);
            echo "<br>Inizio IMPORT " . date('h:i:s u', mktime()) . " FILE " . $mostRecent['fileFtp'] . "<br>";
            //scarica su folder test
            $fileRemote = $mostRecent['fileFtp'];
            $filename = basename($mostRecent['fileFtp']);
            //echo $filename . ';';
            $local = "tempFile/" . $filename;
           

			//verifico se e' gia stato importato
			$fileReaded = $this->checkToLogImport($local, $filename, basename($fileRemote), $zone, date('Y-m-d H:i:s', $mostRecent['time']), $rownumber, $arrivalerrorvalue);
			try{
			if ($fileReaded) { 
			//se non scarico il file blocco tutto
				if (!$this->ftpDownload($local, $fileRemote, $filesystemPath)) {
					die("error download File " . $fileRemote);
				} else {
					//chiudo ftp in modo da riaprirla ad ogni connessione
					$this->ftp->ftpClose();
					 
				
					 if ($fileReaded) {
						echo "<br>INIZIO ANALISI FILE<br>";
						if (is_null($filesystemPath)) {
							//$rows=$this->convertXlsToArray($mostRecent['fileFtp']);}
							$rows = $this->convertXlsToArray($local);
						} else {
							$rows = $this->convertXlsToArray(ROOTPATH . $filesystemPath . "/" . $mostRecent['fileFtp']);
						}
						$rownumber = count($rows) - 1;
						$arrivalerror = $this->query->ArrivalDateError($rows);
						if ($arrivalerror) {
							$arrivalerrorvalue = 1;
						} else {
							$arrivalerrorvalue = 0;
						}
						//solo dopo import inserisco nel log
						$this->InsertToLogImport($local, $filename, basename($fileRemote), $zone, date('Y-m-d H:i:s', $mostRecent['time']), $rownumber, $arrivalerrorvalue);
					}
				}
			} else {
						echo '<br>ALREADY inserted file ' . $filename . " PROCESSO TERMINATO<br>";
					}
            $idLogs = $this->shoplog->getLogId($filename, date('Y-m-d H:i:s', $mostRecent['time']), $zone);
            //echo $idLogs.';';
            //$filename = basename($mostRecent['fileFtp']);
            $pathIn = ROOTPATH . "/" . $local;
            $filenameRegex = preg_replace('/\s+/', '', $filename);


            if ($fileReaded) {
                //se sto leggendo da file fisico prendo la path
                if (is_null($filesystemPath)) {
                    echo "<br> FTP FILE ORIGINE:" . $pathIn . "<br>";
                    //$this->insertCsvToDb($this->convertXlsToArray($mostRecent['fileFtp']), $zone, $filename,$idLogs); 
                    $this->insertCsvToDb($this->convertXlsToArray($pathIn), $zone, $filename, $idLogs);
                    //remove used file
                    unlink($pathIn);
                } else {
                    echo "<br> FILE ORIGINE:" . $mostRecent['fileFtp'] . "<br>";
                    $this->insertCsvToDb($this->convertXlsToArray(ROOTPATH . $filesystemPath . "/" . $mostRecent['fileFtp']), $zone, $filename, $idLogs);
                }
            }
	


            //unlink($pathOut);
            echo "END IMPORT " . date('h:i:s u', mktime()) . " FILE " . $mostRecent['fileFtp'] . "<br><hr>";
		ob_flush();
        flush();
            /* echo '<br>Peak: ' . number_format(memory_get_peak_usage(), 0, '.', ',') . " bytes\n";
              echo '<br>End: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes\n";
              //garbage memory
              gc_collect_cycles();
              echo '<br>GARBAGE<br>Peak: ' . number_format(memory_get_peak_usage(), 0, '.', ',') . " bytes\n";
              echo '<br>End: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes\n"; */
        
		}catch (Exception $ex){
			echo "ERRORE".$ex;	
		}
		if($maxDay>$maxDayLimit)break;
		$maxDay++;
		}
        if (is_null($filesystemPath)) {
            //solo in ftp devo chiudere
            $this->ftp->ftpClose();
        }

        echo "END TOTAL IMPORT " . date('h:i:s u', mktime()) . "<br>";
        ob_flush();
        flush();
    }

    /**
     * Autoselect formato del file
     * 
     * @param type $pathZone
     * @param type $filesystem
     * @return type
     */
    public function listOfFile($pathZone, $filesystem = null) {
        $listFile = "";
        if (is_null($filesystem)) {
            //il sistema e' connesso in ftp direttamente nella cartella principale
//scorro le cartelle sapendo il nome della cartella
            $listFile = $this->ftp->getListFile("./" . $pathZone);
        } else {
            //se definisco una path allora prendo come filesistem
            $listFile = scandir(ROOTPATH . $filesystem);
        }
        return $listFile;
    }

    /**
     * Salvo il file scricato sul db
     * 
     * @param type $CsvData
     * @param type $Row
     * @param type $zone
     * @param type $filename
     * @param int $rowInc
     */
    private function insertCsvToDb($CsvData, $zone, $filename, $idLogs) {

        $this->query->reinit();
        $this->inportFileNum++;
        $rowInc = 0;
        $rowImported = 0;
        echo "<br><br>ROW DA IMPORTARE :" . count($CsvData) . "<br><br>";
        echo "Righe importate:<Br>";
        $result = "";
        $insert = true;

        foreach ($CsvData as $Row) {
            //ob_start();

            //verifico la linea 2 con i primi dati per il test
            /* if ($rowInc == 1) {
              //check se il file non e' stato importate verifico che arrivadate non sia già presente
              if ($this->query->checkinsertArrivaDate($Row['A'], $zone)) {
              $insert = false;
              $result = "<br>Arrival Date " . $this->query->convertExcelData($Row['A']) . " " . $filename . " gia' inserito si prega di verificare<br>";
              break;
              }
              } */
            //salto header
            if ($rowInc >= 1) {
                $result .= $this->query->generateSql($Row, $zone, $filename, $rowInc, $idLogs);
            }
            $rowInc++;
            /*echo ".";
            if ($rowInc % 250 == 0)
                echo "<br>";
            ob_flush();
            flush();*/
        }
        //echo $result;
        if ($insert) {
            $result = $this->query->adoExecInsQuery($result);
        }

        echo "<br>Result :" . $result . "<br><hr>";
        ob_flush();
        flush();
    }

    /**
     * seleziona il file da Ftp o filesystem
     * 
     * @param type $zone
     * @param type $listFile
     * @param type $filesystemPath
     * @return type
     */
    private function findFileToInsert($zone, $listFile, $filesystemPath = null) {
        if (is_null($filesystemPath)) {
            return $this->findFtpFileToInsert($zone, $listFile);
        } else {
            return $this->findSystemFileToInsert($zone, $listFile, $filesystemPath);
        }
    }

    /**
     * Trova l'ultimo file creato in ftp nella path
     * 
     * @param type $listFile
     * @return type
     */
    private function findLastFtpFile($listFile) {
        $mostRecent =array();
		$i=0;
        foreach ($listFile as $file) {
			$mostRecent[$i]=array();
            $ext = substr($file, -4);
            if ($ext == "xlsx") {
                $timeFile = $this->ftp->getLastModFile($file);
                
// this file is the most recent so far
                    $mostRecent[$i]['time'] = $timeFile;
                    $mostRecent[$i]['fileFtp'] = $file;
					$i++;
                
            }
        }
		usort($mostRecent, function($a, $b) {
			return $a['time'] < $b['time'];
		});
        return $mostRecent;
    }

	
	
	
    /**
     * Find all file not imported in FTP
     * 
     * @param type $zone
     * @param type $listFile
     * @return array
     */
    private function findSystemFileToInsert($zone, $listFile, $filesystemPath) {
        $mostRecent = array();
        foreach ($listFile as $file) {

            $ext = substr($file, -4);
            if ($ext == "xlsx") {
                if (!$this->shoplog->isInserted($zone, $file)) {
                    $timeFile = filemtime(ROOTPATH . $filesystemPath . "/" . $file);
                    $mostRecent[] = array(
                        'time' => $timeFile,
                        'fileFtp' => $file
                    );
                } else {
                    echo "<br>FILE " . $file . " GIA' IMPORTATO<br>";
                }
            }
        }
        return $mostRecent;
    }

    /**
     * Find all file not imported in FTP
     * 
     * @param type $zone
     * @param type $listFile
     * @return array
     */
    private function findFtpFileToInsert($zone, $listFile) {
        $mostRecent = array();
        foreach ($listFile as $file) {
            $ext = substr($file, -4);
            if ($ext == "xlsx") {
                if (!$this->shoplog->isInserted($zone, $file)) {
                    $timeFile = $this->ftp->getLastModFile($file);

                    $mostRecent[] = array(
                        'time' => $timeFile,
                        'fileFtp' => $file
                    );
                } else {
                    echo "<br>FILE " . $file . " GIA' IMPORTATO<br>";
                }
            }
        }
        return $mostRecent;
    }

    /**
     * Verifico download in locale del file 
     * se sono in locale non scarico ma true
     * 
     * @param type $local
     * @param type $fileRemote
     * @param type $filesystemPath
     * @return boolean
     */
    private function ftpDownload($local, $fileRemote, $filesystemPath = null) {
        if (is_null($filesystemPath)) {
            if ($this->ftp->isdownloaded($local, $fileRemote)) {
                echo '<br>success download ' . $local . '!';
                //controlo se file e inserito
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Salvataggio del log
     * 
     * @param type $esito
     * @param type $desc
     * @param type $ftpPath
     * @param type $zone
     * @param type $fileDate
     */
    private function saveToLog($esito, $desc, $ftpPath, $zone, $fileDate) {
        $this->shoplog->insert($esito, $desc, $ftpPath, date('NOW'), $zone);
    }

    private function checkAndInsertToLogImport($local, $filename, $ftpPath, $zone, $fileDate, $rownumber, $arrivalerror) {

        if ($this->shoplog->isInserted($zone, $ftpPath)) {
            //file gia inserito blocco
            echo $this->shoplog->insert(0, 'ALREADY inserted file ' . $filename, $ftpPath, $fileDate, $zone, $rownumber, $arrivalerror);
            return false;
        } else {
            echo $this->shoplog->insert(1, 'NEW inserted file ' . $filename, $ftpPath, $fileDate, $zone, $rownumber, $arrivalerror);
            return true;
        }
    }

	private function InsertToLogImport($local, $filename, $ftpPath, $zone, $fileDate, $rownumber, $arrivalerror) {

            echo $this->shoplog->insert(1, 'NEW inserted file ' . $filename, $ftpPath, $fileDate, $zone, $rownumber, $arrivalerror);
            return true;
        
    }
	
	private function checkToLogImport($local, $filename, $ftpPath, $zone, $fileDate, $rownumber, $arrivalerror) {
		
        if ($this->shoplog->isInserted($zone, $ftpPath)) {
            //file gia inserito blocco
            echo $this->shoplog->insert(0, 'ALREADY inserted file ' . $filename, $ftpPath, $fileDate, $zone, $rownumber, $arrivalerror);
            return false;
        } else {
            return true;
        }
    }
	
	
    /**
     * @param string $filename Path to the CSV file
     * @param string $delimiter The separator used in the file
     * @return array
     */
    function parseCsvToArray($filename = '') {
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = 0;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $this->delimiter, $this->enclosure)) !== FALSE) {
                if ($header > 0) {
                    $data[] = $row;
                } else {
                    //non considero header del file
                    $header++;
                }
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * 
     * @param type $pathIn
     * @param type $pathOut
     */
    public function convertXlsToCsv($pathIn, $pathOut) {

        $reader = PHPExcel_IOFactory::createReader('Excel2007');
        $reader->setReadDataOnly(true);
        $excel = $reader->load($pathIn);
        $excel->getSheet(0);
        $objWriter = new PHPExcel_Writer_CSV($excel);
        $objWriter->setDelimiter($this->delimiter);
        $objWriter->setEnclosure($this->enclosure);
        $objWriter->setPreCalculateFormulas(false);
        $objWriter->setLineEnding("\r\n");
        $objWriter->setSheetIndex(0);
        $objWriter->save($pathOut);
        $excel->disconnectWorksheets();
        unset($objWriter, $reader, $excel);
    }

    /**
     * 
     * @param type $pathIn
     * @param type $pathOut
     */
    public function convertXlsToArray($pathIn) {
        unset($out);
        $this->excelReader->setReadDataOnly(true);
        $excel = $this->excelReader->load($pathIn);
        $excel->getSheet(0);
        $out = $excel->getActiveSheet()->toArray(null, true, true, true);
        $excel->disconnectWorksheets();
        unset($reader, $excel);
        return $out;
    }

}
