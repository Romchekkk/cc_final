<?php

    session_start();
    require_once("database.php");

    $mysql = new dataBase();
    $showGames = json_decode($mysql->loadGames());
    $games = "";
    foreach($showGames as $game){
        $games .= '
    <div class="row mt-2 border" onclick="joinGame('.$game->id.')">
        <div class="col-sm-6 col-md-6 col-lg-6 col-xxl-6">'.$game->gamename.'</div>
        <div class="col-sm-3 col-md-3 col-lg-3 col-xxl-3">Кол-во игроков:</div>
        <div class="col-sm-3 col-md-3 col-lg-3 col-xxl-3">'.$game->countOfPlayers.'</div>
    </div>';
    }

    echo $games;