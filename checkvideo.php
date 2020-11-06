<?php 
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    include("./config.php");

    class Video
    {
        public $conn;
        public $conntrx;
        private static $machine_code = 'AA001';

        function __construct() {
            $config = new Config();
            $this->conn = new mysqli($config->local()[0], $config->local()[1], $config->local()[2], $config->local()[3]);
            $this->conntrx = new mysqli($config->trx()[0], $config->trx()[1], $config->trx()[2], $config->trx()[3]);
        }

        function getdatavideolocal(){
            $sql = "SELECT * 
                    FROM video";
            $result = $this->conn->query($sql);
            return $result;
        }
    
        function getdatavideopusat(){
            $sql = "SELECT * 
                    FROM video";
            $result = $this->conntrx->query($sql);
            return $result;
        }
    
        function insertnewrecordlocal(){
            $str = "";
            $i = 1;
            $datetime = date("Y-m-d H:i:s");
            $result_video_pusat = $this->getdatavideopusat();
            $data = $result_video_pusat->fetch_all(MYSQLI_ASSOC);
            foreach($data as $d){
                if($i == count($data)){
                    $str .= "('" . $d['id'] ."', '" . $d['name'] ."', '" . $d['sort'] ."', '". $d['filename'] ."');";
                }else{
                    $str .= "('" . $d['id'] ."', '" . $d['name'] ."', '" . $d['sort'] ."', '". $d['filename'] ."'), ";
                }
                $i++;
            }
            
            $sql = "INSERT INTO video (`id`, `name`, `sort`, `filename`)
                    VALUES ". $str;
            
            $save = $this->conn->query($sql);
            if ($save === TRUE) {
                // echo "New record created successfully";
            } else {
                // echo "Error: " . $sql . "<br>" . $this->conn->error;
            }
        }
    
        function updaterecordlocal(){
            $this->conn->begin_transaction();
            try {
                $this->deleteallrecordlocal();
                $this->insertnewrecordlocal();
                $this->conn->commit();
            } catch (\Exception $e) {
                $this->conn->rollback();
                echo $e->getMessage();
            }
        }
    
        function deleteallrecordlocal(){
            $sql = "DELETE FROM video;";
            $delete = $this->conn->query($sql);
        }

        function pushvideomachinedetailpusat(){
            $str = "";
            $i = 1;
            $datetime = date("Y-m-d H:i:s");
            $machine_code = self::$machine_code;
            $sql = "SELECT * 
                    FROM video_machine_detail
                    WHERE machine_code = '$machine_code'";
            $result = $this->conntrx->query($sql);
            if($result->num_rows == 0){
                // belom ada record di pusat
                $result_video_local = $this->getdatavideolocal();
                $data = $result_video_local->fetch_all(MYSQLI_ASSOC);
                foreach($data as $d){
                    if($i == count($data)){
                        $str .= "(NULL, '" . $machine_code ."', '" . $d['id'] ."', '". $datetime ."');";
                    }else{
                        $str .= "(NULL, '" . $machine_code ."', '" . $d['id'] ."', '". $datetime ."'), ";
                    }
                    $i++;
                }
                
                $sql = "INSERT INTO video_machine_detail (`id`, `machine_code`, `video_id`, `updated_at`)
                        VALUES ". $str;
                
                $save = $this->conntrx->query($sql);
                if ($save === TRUE) {
                    echo "New record created successfully";
                } else {
                    echo "Error: " . $sql . "<br>" . $this->conntrx->error;
                }
            }else{
                // sudah ada record di pusat
                $this->conntrx->begin_transaction();
                try {
                    $sqldelete = "DELETE FROM video_machine_detail WHERE machine_code = '$machine_code'";
                    if (!$this->conntrx->query($sqldelete) === TRUE) {
                        throw new Exception("Error Processing Delete");
                    }else{
                        $result_video_local = $this->getdatavideolocal();
                        $data = $result_video_local->fetch_all(MYSQLI_ASSOC);
                        foreach($data as $d){
                            if($i == count($data)){
                                $str .= "(NULL, '" . $machine_code ."', '" . $d['id'] ."', '". $datetime ."');";
                            }else{
                                $str .= "(NULL, '" . $machine_code ."', '" . $d['id'] ."', '". $datetime ."'), ";
                            }
                            $i++;
                        }
                        
                        $sql = "INSERT INTO video_machine_detail (`id`, `machine_code`, `video_id`, `updated_at`)
                                VALUES ". $str;
                        
                        $save = $this->conntrx->query($sql);
                        if (!$save === TRUE) {
                            throw new Exception("Error Processing Delete");
                        }
                    }
                    $this->conntrx->commit();
                } catch (\Exception $e) {
                    $this->conntrx->rollback();
                    echo $e->getMessage();
                }
            }
        }

        function collect_file($url){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            return($result);
        }
    
        function write_to_file($text,$new_filename){
            $fp = fopen($new_filename, 'w');
            fwrite($fp, $text);
            fclose($fp);
        }

        function is_connected()
        {
            $connected = @fsockopen("www.vending.biru.id", 80); 
            if ($connected){
                $is_conn = true; //action when connected
                fclose($connected);
            }else{
                $is_conn = false; //action in connection failure
            }
            return $is_conn;
        }

        function run(){
            try {
                $video = new Video();
                if($video->is_connected()){
                    $res_video_local = $video->getdatavideolocal();
                    if($res_video_local->num_rows == 0){
                        $video->insertnewrecordlocal(); // belom ada record di local, ambil record pusat ke local
                    }else{
                        $video->updaterecordlocal(); // sudah ada record di local
                    }
                    $data_ = $video->getdatavideolocal();
                    $data = $data_->fetch_all(MYSQLI_ASSOC);
                    foreach($data as $d){
                        $new_file_name = "video/".$d['filename'];
                        $url = "http://vending.biru.id/dashboard/video-vm/".$d['filename'];
                    
                        $temp_file_contents = $video->collect_file($url);
                        $video->write_to_file($temp_file_contents,$new_file_name);
                    }
                    $video->pushvideomachinedetailpusat();
                    $video->conn->close();
                    $video->conntrx->close();
                }else{
                    $video->conn->close();
                    $video->conntrx->close();
                    throw new Exception("Error No Internet Acccess");
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    // $video = new Video();
    // var_dump($video->is_connected());die;
    
?>