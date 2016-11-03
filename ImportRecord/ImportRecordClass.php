<?php

include_once ROOTPATH . '/src/CustomLib/DbConnectClass.php';
include_once ROOTPATH . '/src/CustomLib/FtpConnectClass.php';
include_once ROOTPATH . '/src/CustomLib/ShooperLogClass.php';
include_once ROOTPATH . '/include/PhpExcel/PHPExcel.php';

include_once ROOTPATH . '/src/ImportRecord/ImportQueryClass.php';

class ImportRecordClass {

    private $db;
    private $query;
    private $ftp;
    private $shoplog;
    private $delimiter;
    private $enclosure;

    function __construct() {
        $this->db = new DbConnectClass();

        $this->query = new ImportQueryClass();
        $this->ftp = new FtpConnectClass();
        $this->shoplog = new ShooperLog();
        $this->delimiter = ';';
        $this->enclosure = '"';
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
    public function insertLastDay($zone = 1) {

        $pathZone = $this->getFtpPathZone($zone);
        $mostRecent = array(
            'time' => 0,
            'fileFtp' => null
        );
        $listFile = "";
//il sistema e' connesso in ftp direttamente nella cartella principale
//scorro le cartelle sapendo il nome della cartella
        $listFile = $this->ftp->getListFile("./" . $pathZone);
        echo "<br>Recupero lista file num. file:" . count($listFile);
        $mostRecent = $this->findLastFtpFile($listFile);
        echo "<br>Ultimo file creato:<br>" . $mostRecent['fileFtp'];


        //scarica su folder test
        $fileRemote = $mostRecent['fileFtp'];
        $filename = basename($mostRecent['fileFtp']);
        $local = "tempFile/" . $filename;
        //se non scarico il file blocco tutto
        
        if (!$this->ftpDownload($local, $fileRemote)) {
            die("error download File " . $fileRemote);
        } else {
            /* x TEST
            if ($this->checkAndInsertToLogImport($local, $filename, $fileRemote, $zone, date('Y-m-d H:i:s', $mostRecent['time']))) {
                echo "<br>INIZIO ANALISI FILE<br>";
            } else {
                die('<br>ALREADY inserted file ' . $filename . " PROCESSO TERMINATO");
            }*/
        }

        $this->ftp->ftpClose();

        $filename = basename($mostRecent['fileFtp']);
        $pathIn = ROOTPATH . "/" . $local;
        $filenameRegex = preg_replace('/\s+/', '', $filename);
        $pathOut = ROOTPATH . "/tempFile/" . substr($filenameRegex, 0, -5) . ".csv";
        echo "<br> FILE ORIGINE:" . $pathIn . "<br> FILE DESTINAZIONE:" . $pathOut;

        $this->convertXlsToCsv($pathIn, $pathOut);
        $CsvData = $this->parseCsvToArray($pathOut);

        $this->insertCsvToDb($CsvData, $zone, $filename);
        //remove used file
        unlink($pathIn);
        unlink($pathOut);
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
    private function insertCsvToDb($CsvData, $zone, $filename) {
        $rowInc = 2;
        $rowImported = 0;
        echo "<br><br>ROW DA IMPORTARE :" . count($CsvData) . "<br><br>";

        foreach ($CsvData as $Row) {
            //check se il file non e' stato importate verifico che arrivadate non sia già presente
            if($this->query->checkinsertArrivaDate($Row[0])){
                die("Arrival Date ".$filename." già inserito si prega di verificare");
            }
            $result = $this->query->insertRow($Row, $zone, $filename, $rowInc);
            if ($result != true) {
                echo $result;
                 $this->saveToLog('0', $result, $filename, $zone);
            } else {
                $rowImported++;
            }
            $rowInc++;
        }
        /*disattivato ci sono problemi con il commit rollback
         * if ($this->query->commit()) {
            echo " FILE " . $filename . " IMPORTATO";
            $this->saveToLog('10', "FILE IMPORTATO RIGHE: ".$rowImported, $filename, $zone);
        } else {
            $this->saveToLog('5', "FILE CON ERRORI NON IMPORTATO", $filename, $zone);
            echo " ERRORE FILE NON IMPORTATO" . $filename . " IMPORTATO";
        }*/

        echo "<br><br>ROW  IMPORTATE :" . $rowImported . "<br><br>";
    }

    /**
     * Trova l'ultimo file creato in ftp nella path
     * 
     * @param type $listFile
     * @return type
     */
    private function findLastFtpFile($listFile) {
        $mostRecent = array(
            'time' => 0,
            'fileFtp' => null
        );
        foreach ($listFile as $file) {
            $ext = substr($file, -4);
            if ($ext == "xlsx") {
                $timeFile = $this->ftp->getLastModFile($file);
                if ($timeFile > $mostRecent['time']) {
// this file is the most recent so far
                    $mostRecent['time'] = $timeFile;
                    $mostRecent['fileFtp'] = $file;
                }
            }
        }
        return $mostRecent;
    }

    /**
     * Verifico download in locale del file 
     * 
     * @param type $local
     * @param type $fileRemote

     * @return boolean
     */
    private function ftpDownload($local, $fileRemote) {
        if ($this->ftp->isdownloaded($local, $fileRemote)) {
            echo '<br>success download ' . $local . '!';
            //controlo se file e inserito
            return true;
        } else {
            return false;
        }
    }

    private function saveToLog($esito, $desc, $ftpPath, $zone, $fileDate) {
        $this->shoplog->insert($esito, $desc, $ftpPath, date('NOW'), $zone);
    }

    private function checkAndInsertToLogImport($local, $filename, $ftpPath, $zone, $fileDate) {

        if ($this->shoplog->isInserted($zone, $ftpPath)) {
            //file gia inserito blocco
            echo $this->shoplog->insert(0, 'ALREADY inserted file ' . $filename, $ftpPath, $fileDate, $zone);
            return false;
        } else {
            echo $this->shoplog->insert(1, 'NEW inserted file ' . $filename, $ftpPath, $fileDate, $zone);
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
        $objWriter = new PHPExcel_Writer_CSV($excel);
        $objWriter->setDelimiter($this->delimiter);
        $objWriter->setEnclosure($this->enclosure);
        $objWriter->setPreCalculateFormulas(false);
        $objWriter->setLineEnding("\r\n");
        $objWriter->setSheetIndex(0);
        $objWriter->save($pathOut);
    }

}
