<?php

    session_start();
    require_once("database.php");

    if (isset($_SESSION['spectateGameId']) && isset($_REQUEST['password'])){
        if ($_REQUEST['password'] == 'tomatpomidor12345'){
            $mysql = new dataBase();
            $checkGame = json_decode($mysql->checkGame($_SESSION['spectateGameId']));
            if ($checkGame->error == true){
                echo json_encode(['result'=>false]);
                die();
            }
            $submitCows = json_decode($mysql->adminPauseGame($_SESSION["spectateGameId"]), true);
            if ($submitCows["success"] == true){
                echo json_encode(['result'=>true, 'pause'=>$submitCows['pause']]);
            }
        }
        else{
            echo json_encode(['result'=>false]);
        }
    }
    