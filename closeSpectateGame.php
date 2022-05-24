<?php
    session_start();
    require_once("database.php");

    if (isset($_SESSION['spectateGameId'])){
        unset($_SESSION['spectatGameId']);
    }
    echo json_encode(['result'=>true, 'location' => preg_replace("/\/spectator.php\?/", "/index.php?", $_SERVER["HTTP_REFERER"])]);
    