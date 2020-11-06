<?php 
    include('checkvideo.php');

    if(isset($_GET)){
        $video = new Video();
        // $video->run();
        $res = $video->getdatavideolocal();
        $data = $res->fetch_all(MYSQLI_ASSOC);
        header("Access-Control-Allow-Origin: *");
        echo json_encode($data);die;
    }
?>