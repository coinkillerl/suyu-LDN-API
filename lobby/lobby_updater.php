<?php
    header('Content-Type: application/json');
    //Update player data
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $jsonReceived = file_get_contents("php://input");
        $jsonReceived = json_decode($jsonReceived,true);
        $jsonFile = json_decode(file_get_contents("room.json"), true);

        if(!isset($jsonReceived["name"])){
            $jsonFile["players"] = $jsonReceived["players"];
        }

        $fs = fopen("room.json", "w");
        fwrite($fs, json_encode($jsonFile));
        fclose($fs);
    }
    else if($_SERVER["REQUEST_METHOD"] == "DELETE"){
        rrmdir(__DIR__);
        exit();
    }
    else{
        http_response_code(400);
    }

    function rrmdir($directory){
        $files=glob($directory.'/*');
        foreach ($files as $file)
        {
            if(is_dir($file))
            {
                rrmdir($file);
                continue;
            }
         unlink($file);
        }
        rmdir($directory); 
    }

?>