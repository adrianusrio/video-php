<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <style>
            body {
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
            video#videoarea { 
                position: fixed;
                top: 50%;
                left: 50%;
                min-width: 50%;
                min-height: 50%;
                width: auto;
                height: auto;
                z-index: -100;
                -ms-transform: translateX(-50%) translateY(-50%);
                -moz-transform: translateX(-50%) translateY(-50%);
                -webkit-transform: translateX(-50%) translateY(-50%);
                transform: translateX(-50%) translateY(-50%);
            
                background-size: cover; 
            }
            video::-webkit-media-controls {
                display: none   
            }

            /* Could Use thise as well for Individual Controls */
            video::-webkit-media-controls-play-button {
                display: none
            }

            video::-webkit-media-controls-volume-slider {
                display: none
            }

            video::-webkit-media-controls-mute-button {
                display: none
            }

            video::-webkit-media-controls-timeline {
                display: none
            }

            video::-webkit-media-controls-current-time-display {
                display: none
            }


        </style>
    </head>
    <body>
        <?php 
            include("./checkvideo.php");

            $checkvideo = new Video();
            $checkvideo->run();
            $res = $checkvideo->getdatavideolocal();
            $data = $res->fetch_all(MYSQLI_ASSOC);


        ?>
        <video id="videoarea" onended="next(this)" poster="" src="" autoplay muted></video>
        <button id="btn-fullscreen" onclick="fs()">fullscreen</button>
        <button id="btn-exitfullscreen" style="display: none;">exit fullscreen</button>
        <script src="./assets/jquery.min.js"></script>
        <script>
            document.addEventListener('contextmenu', event => event.preventDefault());
            var i = 0;
            var data = <?= json_encode($data) ?>;
            data.sort((a, b) => {
                return a.sort - b.sort;
            });
            var totalVideos = data.length - 1;

            $(function () {
                $("#videoarea").attr({
                    "src": "video/"+data[0]['filename']
                })
                setTimeout(function(){
                    // var l = document.getElementById('btn-fullscreen');
                    // l.click();
                    document.documentElement.onclick = fs();
                    document.onkeydown = fs();
                }, 3000);
            });

            function fs(){
                var elem = document.getElementById("videoarea");
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                    $('#videoarea').prop("controls", false);
                } else if (elem.mozRequestFullScreen) {
                    elem.mozRequestFullScreen();
                    $('#videoarea').prop("controls", false);
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                    $('#videoarea').prop("controls", false);
                } else if (elem.msRequestFullscreen) { 
                    elem.msRequestFullscreen();
                    $('#videoarea').prop("controls", false);
                }
            }

            function next(e) {
                console.log("total = "+totalVideos)
                if (i == totalVideos ) {
                    i = 0;
                    $("#videoarea").attr({
                        "src": "video/"+data[i]['filename']
                    });
                }else{
                    i += 1;
                    console.log("i = "+i);
                    $("#videoarea").attr({
                        "src": "video/"+data[i]['filename']
                    });
                }
            }

            setTimeout(function(){
                window.location.reload();
            }, 1000 * 60 * 60)
        </script>
    </body>
</html>