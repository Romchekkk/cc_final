<?php

    session_start();
    require_once("database.php");

    if (isset($_SESSION['spectateGameId'])){
        $mysql = new dataBase();
        $checkGame = json_decode($mysql->checkGame($_SESSION['spectateGameId']));
        if ($checkGame->error == true){
            echo json_encode(['result'=>false]);
            die();
        }
        $updateInformation = json_decode($mysql->updateSpectateInformation($_SESSION['spectateGameId']), true);
        
        $gameProcess = json_decode($updateInformation["gameProcess"], true);

        $result = [];
        foreach($gameProcess["players"] as $player){
            $result["players"][] = [
                'name' => $player["name"],
                'cows' => $player["cows"],
                'fedCows' => $player["fedCows"],
                'sector' => $player["sector"],
                'money' => $player["money"]
            ];
        }
        $result["sectors"] = [
            "sector1" => $gameProcess["world"]["sector1"]["capacity"],
            "sector2" => $gameProcess["world"]["sector2"]["capacity"],
            "sector3" => $gameProcess["world"]["sector3"]["capacity"],
            "sector4" => $gameProcess["world"]["sector4"]["capacity"],
        ];
        $result["fieldCapacity"] = $gameProcess["world"]["fieldCapacity"];

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

        if ($gameProcess['gameStarted'] == true){
            $result["round"] = "Раунд ".$gameProcess["round"]."/21";
        }

        if ($_SESSION['lastRoundWatched'] != $gameProcess['lastRound']){
            $_SESSION['lastRoundWatched'] = $gameProcess['lastRound'];
            $result['roundEnd'] = true;
        }

        echo json_encode(['result'=>true,'game'=>$result]);
    }
    