<?php

    session_start();
    require_once("database.php");

    if (isset($_SESSION['gameCreated']) && $_SESSION['gameCreated'] == true){
        $mysql = new dataBase();
        $checkGame = json_decode($mysql->checkGame($_SESSION['gameId']));
        if ($checkGame->error == true){
            echo json_encode(['result'=>false]);
            die();
        }
        $updateInformation = $mysql->updateInformation($_SESSION['gameId'], $_SESSION['userId']);
        if ($updateInformation == false){
            echo json_encode(['result'=>false]);
        }
        else{
            $updateInformation = json_decode($updateInformation, true);
            if ($updateInformation["gameClosed"]){
                $_SESSION=[];
                $result = [
                    "result"=>true,
                    "gameClosed"=>true
                ];
                echo json_encode($result);
                die();
            }
            if (array_key_exists('gameProcess', $updateInformation)){
                $gameProcess = json_decode($updateInformation['gameProcess'], true);
                
                $result = [
                    "result"=>true
                ];
                $result['fieldCapacity'] = $gameProcess["world"]["fieldCapacity"];

                $result['cowsBet'] = "";
                if ($gameProcess["maxCows"] == 3){
                    $result['cowsBet'] = "<div class=\"col-3\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"0\" onclick=\"submitCows(0)\" /></div>
                    <div class=\"col-3\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"1\" onclick=\"submitCows(1)\" /></div>
                    <div class=\"col-3\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"2\" onclick=\"submitCows(2)\" /></div>
                    <div class=\"col-3\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"3\" onclick=\"submitCows(3)\" /></div>";
                }
                if ($gameProcess["maxCows"] == 4){
                    $result['cowsBet'] = "<div class=\"col-1\"></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"0\" onclick=\"submitCows(0)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"1\" onclick=\"submitCows(1)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"2\" onclick=\"submitCows(2)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"3\" onclick=\"submitCows(3)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"4\" onclick=\"submitCows(4)\" /></div>
                    <div class=\"col-1\"></div>";
                }
                if ($gameProcess["maxCows"] == 5){
                    $result['cowsBet'] = "<div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"0\" onclick=\"submitCows(0)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"1\" onclick=\"submitCows(1)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"2\" onclick=\"submitCows(2)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"3\" onclick=\"submitCows(3)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"4\" onclick=\"submitCows(4)\" /></div>
                    <div class=\"col-2\"><input type=\"button\" class=\"btn btn-primary w-100\" value=\"5\" onclick=\"submitCows(5)\" /></div>";
                }

                $result['players'] = [];
                $result['light'] = "";
                foreach($gameProcess['players'] as $player){
                    $result['players'][] = [
                        'name'=>$player['name'],
                        'sector'=>$player['sector'],
                        'money'=>$player['money'],
                        'cows'=>$player['cows'],
                        'fedCows'=>$player['fedCows']
                    ];
                    if ($player["id"] == $_SESSION["userId"]){
                        $result['light'] = $player['sector'];
                    }
                }
                $result['sectors'] = [];
                $result['sectors']['sector1'] = ["capacity"=>$gameProcess['world']['sector1']['capacity']];
                $result['sectors']['sector2'] = ["capacity"=>$gameProcess['world']['sector2']['capacity']];
                $result['sectors']['sector3'] = ["capacity"=>$gameProcess['world']['sector3']['capacity']];
                $result['sectors']['sector4'] = ["capacity"=>$gameProcess['world']['sector4']['capacity']];
                if ($updateInformation['gameBegin'] == true){
                    $result["round"] = "Раунд ".$gameProcess["round"]."/21";
                }
                if ($updateInformation["newRound"] == true){
                    $result["newRound"] = true;
                }
                if ($updateInformation["roundEnd"] == true){
                    $result["roundEnd"] = true;
                }
                if ($updateInformation["gameEnd"] == true){
                    $result["endReason"] = $updateInformation["endReason"];
                    $result["gameEnd"] = true;
                }
                $result["yield"] = "Год был обычный. Изменений в емкости не произошло.";
                if ($gameProcess["yieldSector"] != 0){
                    $result["yield"] = "Этот год был ".($gameProcess["isYieldGood"]?"урожайным":"неурожайным").". Емкость сектора ".$gameProcess["yieldSector"].($gameProcess["isYieldGood"]?" увеличена.":" уменьшена.");
                }
                $result["trampled"] = "Коровы ничего не потоптали.";
                if (count($gameProcess["trampledSectors"]["sectors"])){
                    $result["trampled"] = "Некоторым коровам не хватило пищи на своих секторах и они затоптали сектора (корова игрока:затоптанный сектор): ";
                    for ($i = 0; $i < count($gameProcess["trampledSectors"]["sectors"]); $i++){
                        $result["trampled"] .= $gameProcess["players"][$gameProcess["trampledSectors"]["players"][$i]]["name"].":".$gameProcess["trampledSectors"]["sectors"][$i];
                        if ($i != count($gameProcess["trampledSectors"]["sectors"])-1){
                            $result["trampled"] .= ", ";
                        }
                        else{
                            $result["trampled"] .= ".";
                        }
                    }
                }

                if ($updateInformation['gamePausedByModerator'] == true){
                    $result['gamePausedByModerator'] = true;
                }
                else{
                    $result['gamePausedByModerator'] = false;
                }
            }
            else{
                $_SESSION=[];
                $result = [
                    "result"=>true,
                    "gameClosed"=>true
                ];
            }
            echo json_encode($result);
        }
    }
    