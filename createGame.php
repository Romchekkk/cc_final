<?php
    session_start();
    require_once("database.php");
    if (isset($_SESSION['userId'])){
        $mysql = new dataBase();

        $checkForGames = json_decode($mysql->checkForGames($_SESSION['userId']));

        if ($checkForGames->error == true){
            echo json_encode(['result'=>false]);
        }
        else if ($checkForGames->success == true){
            echo json_encode(['result'=>false]);
        }
        else{
            $createGame = json_decode($mysql->createGame($_SESSION['userId'], $_SESSION['username']));
            if ($createGame->error == true){
                echo json_encode(['result'=>false]);
            }
            else{
                $_SESSION['gameCreated'] = true;
                $_SESSION['gameId'] = $createGame->id;
                echo json_encode(['result'=>true]);
            }
        }
    }
    