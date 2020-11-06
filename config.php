<?php 
    date_default_timezone_set('Asia/Jakarta');
    class Config {

        private $servername;
        private $username;
        private $password;
        private $database;
       
        function local() {
            $this->servername = "localhost";
            $this->username = "rio";
            $this->password = "password";
            $this->database = "biruid_dev2";

            return [$this->servername, $this->username, $this->password, $this->database];
        }

        function trx() {
            $this->servername = "localhost";
            $this->username = "rio";
            $this->password = "password";
            $this->database = "biruid_dev2_pusat";

            return [$this->servername, $this->username, $this->password, $this->database];
        }
    }

?>

