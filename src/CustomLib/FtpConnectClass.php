<?php

class FtpConnectClass {

    public $c;

    function __construct() {

        return $this->connect();
    }

    function connect() {
        $this->c = ftp_connect(FTP_ADDRESS) or die("CONNESSIONE FTP NON RIUSCITA");
        ftp_login($this->c, FTP_USERNAME, FTP_PASSWORD) or die("USER O PASSWORD FTP PROBLEM");
        ftp_pasv($this->c, true);
        ftp_set_option($this->c, FTP_TIMEOUT_SEC, 1728000);
        echo "<br>CONNECT TO FTP " . FTP_ADDRESS . "<br>";
        return $this->c;
    }

    /**
     * Verifico se la connessione e' ancora attiva altrimenti la ripatro prima di fare il download
     * 
     * @param type $local
     * @param type $location
     * @return boolean
     */
    public function isdownloaded($local, $location) {
        if(!ftp_pwd($this->c)) {
            $this->connect();
        }
		//resto in attesa di riattivazione connessione
		$incSic=0;
		while(!ftp_pwd($this->c)) {
			echo "IN attesa di connessione FTP";
			if($incSic>200)die("Connessione FTP non ripristinata");
			$incSic++;
		}
        if (ftp_get($this->c, $local, $location, FTP_BINARY)) {
            return true;
        } else {
            return FALSE;
        }
    }

    public function getListFile($path) {
        $list = ftp_nlist($this->c, $path);
        return $list;
    }

    public function getLastModFile($path) {
        $time = ftp_mdtm($this->c, $path);
        if ($time != -1) {
            
        } else {
            echo "<br>MDTM NOT SUPPORT Couldn't get mdtime" . $time . "<br>";
        }
        return $time;
    }

    public function ftpClose() {
        ftp_close($this->c) or die("Can't close");
    }

}
