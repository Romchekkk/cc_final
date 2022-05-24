<?php
    session_start();
    require_once("database.php");
    $mysql = new dataBase();
    if (isset($_SESSION['userId']) && isset($_GET["gameId"])){
        
        $checkGame = json_decode($mysql->checkGame($_GET['gameId']));

        if ($checkGame->error == true){
            $_SESSION = [];
            echo json_encode(['result'=>true,'gameClosed'=>true]);
        }
        else if ($checkGame->success == true){
            $_SESSION['spectateGameId'] = $_GET['gameId'];
            $_SESSION['lastRoundWatched'] = 0;
            echo json_encode(['result'=>true, 'location' => preg_replace("/\/index.php\?/", "/spectator.php?", $_SERVER["HTTP_REFERER"])]);
        }
        else{
            echo json_encode(['result'=>false]);
        }
    }
    else{
        echo json_encode(['result'=>false]);
    }
    