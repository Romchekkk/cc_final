<?php
    session_start();
    require_once("database.php");
    $mysql = new dataBase();
    if (isset($_SESSION['userId']) && isset($_SESSION['gameCreated']) && $_SESSION['gameCreated'] == true){
        if (!isset($_SESSION["gameId"])){
            unset($_SESSION['gameCreated']);
        }
        else{
            $checkGame = json_decode($mysql->checkGame($_SESSION['gameId']));
            if ($checkGame->error == false && $checkGame->success == false) {
                unset($_SESSION['gameCreated']);
            }
        }
    }
    if (isset($_SESSION['userId']) && (!isset($_SESSION['gameCreated']) || $_SESSION['gameCreated'] == false) && isset($_GET["gameId"])){
        
        $checkGame = json_decode($mysql->checkGame($_GET['gameId']));

        if ($checkGame->error == true){
            $_SESSION = [];
            echo json_encode(['result'=>true,'gameClosed'=>true]);
        }
        else if ($checkGame->success == true){
            $joinGame = $mysql->joinGame($_GET['gameId'], $_SESSION['userId'], $_SESSION['username']);
            if ($joinGame == false){
                echo json_encode(['result'=>false]);
            }
            else{
                $_SESSION['gameCreated'] = true;
                $_SESSION['gameId'] = $_GET['gameId'];
                echo json_encode(['result'=>true]);
            }
        }
        else{
            echo json_encode(['result'=>false]);
        }
    }
    else{
        echo json_encode(['result'=>false]);
    }
    