<?php

    session_start();
    require_once("database.php");

    if (isset($_SESSION['gameCreated']) && $_SESSION['gameCreated'] == true && isset($_GET["cows"])){
        $mysql = new dataBase();
        $checkGame = json_decode($mysql->checkGame($_SESSION['gameId']));
        if ($checkGame->error == true){
            echo json_encode(['result'=>false]);
            die();
        }
        $submitCows = json_decode($mysql->submitCows($_SESSION["userId"], $_SESSION["gameId"], $_GET["cows"]), true);
        if ($submitCows["success"] == true){
            echo json_encode(['result'=>true]);
        }
    }
    