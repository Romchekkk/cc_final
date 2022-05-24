<?php
    session_start();
    require_once("database.php");

    if (isset($_SESSION['gameCreated']) && $_SESSION['gameCreated'] == true){
        $mysql = new dataBase();
        $checkGame = json_decode($mysql->checkGame($_SESSION['gameId']));

        if ($checkGame->error == true){
            $_SESSION = [];
            echo json_encode(['result'=>true]);
        }
        else if($checkGame->error == false && $checkGame->success == false){
            $_SESSION = [];
            echo json_encode(['result'=>true]);
        }
        else{
            $closeGame = json_decode($mysql->closeGame($_SESSION['userId'], $_SESSION['gameId']));
            if ($closeGame->success == true){
                unset($_SESSION['gameCreated']);
                echo json_encode(['result'=>true]);
            }
            else{
                echo json_encode(['result'=>false]);
            }
        }
    }
    