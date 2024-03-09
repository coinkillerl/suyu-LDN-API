<?php
header('Content-Type: application/json');
$localhost_enabled = true; //If the API and the room are on the same server, you need to set this to false, otherwise the room ip will be localhost
//and the room won't be accessible to external clients!

//when announcing a room, yuzu-room will POST this file, build the json and add the room to the API
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $json = file_get_contents("php://input");
    $json = json_decode($json,true);

    $json["netVersion"] = 1;
    $json["externalGuid"] = "testRoom0ExtGUID";
    if($json["hasPassword"] == false){
        $json["password"] = "";
    }
    if(!isset($json["owner"])){
        $json["owner"] = "Could not get host's username!";
    }
    $json["players"] = array();
    $json["address"] = $_SERVER["REMOTE_ADDR"];
    $json["id"] = md5($json["name"].$_SERVER["REMOTE_ADDR"].$json["port"])."/";
    if($json["address"] == "::1"){
        if($localhost_enabled == true) $json["address"] = "127.0.0.1";
        else $json["address"] = file_get_contents("http://ipecho.net/plain"); //Get the room's public IP address in case it's being hosted on the same server as the API
    }

    @mkdir($json["id"]);
    $fs = fopen($json["id"]."room.json", "w");
    fwrite($fs, json_encode($json));
    fclose($fs);

    //evil self-duplicating code
    //TOTALLY NOT A GIGANTIC SECURITY HOLE, TRUST ME
    copy("lobby_updater.php", $json["id"]."index.php");
}

else{
    $dirs = scandir(".");
    $json["rooms"] = array(); //Prevent crashes if there are no rooms
    $skipped = 0;
    for($index = 0; $index < sizeof($dirs); $index++){
        $currentDir = $dirs[$index];
        if(is_dir($currentDir) && $currentDir != "." && $currentDir != ".."){

            if(file_exists($currentDir."/room.json")){
                $json["rooms"][$index - $skipped] = json_decode(file_get_contents($currentDir."/room.json"), true); 
            }
            else{
                rmdir($currentDir); //If the dir is empty delete it, because that means that room does not exist anymore
                $skipped++;
            }  
        }
        else{
            $skipped++;
        } 
    }
}

echo json_encode($json);
?>