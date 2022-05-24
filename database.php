<?php

class dataBase{

    private $_mysql;

    public function __construct(){
        $this->_mysql = mysqli_connect('localhost', 's156049_common_cowse', 'cnhjwtyrj290399', 's156049_common_cowse');
    }

    public function __destruct(){
        mysqli_close($this->_mysql);
    }

    public function checkUser($userId, $chatId){
        $result = [
            'error' => false,
            'success' => false,
            'message' => ""
        ];
        
        if (preg_match("/[^0-9]/", $userId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }
        
        $query = "SELECT * FROM `USERS` WHERE `user_id`=$userId";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            if ($chatId == $row['chat_id']){
                $result['success'] = true;
            }
            else{
                $result['error'] = true;
                $result['message'] = "Wrong chat id";
            }
        }

        return json_encode($result);
    }

    public function addUser($userId, $chatId){
        $result = [
            'error' => false,
            'success' => false,
            'message' => ""
        ];

        if (preg_match("/[^0-9]/", $userId) !== 0 || preg_match("/[^0-9]/", $chatId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        $query = "INSERT INTO `USERS`(`user_id`, `chat_id`) VALUES ($userId, '$chatId')";
        $request = mysqli_query($this->_mysql, $query);
        if ($request === true){
            $result['success'] = true;
        }
        else{
            $result['error'] = true;
            $result['message'] = "this chat_id already in use";
        }

        return json_encode($result);
    }

    public function checkForGames($userId){
        $result = [
            'error' => false,
            'success' => false,
            'message' => ""
        ];

        if (preg_match("/[^0-9]/", $userId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        $query = "SELECT * FROM `GAMES` WHERE `host_id`=$userId";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $result['success'] = true;
        }

        return json_encode($result);
    }

    public function createGame($userId, $username){
        $result = [
            'error' => false,
            'success' => false,
            'message' => ""
        ];

        if (preg_match("/[^0-9]/", $userId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        $gameProcess = json_decode(file_get_contents('gameProcess.json'), true);
        $gameProcess['players']['player1']['id'] = $userId;
        $gameProcess['players']['player1']['name'] = $username;
        $query = "INSERT INTO `GAMES`(`name`, `lastCheckForHost`, `host_id`, `gameProcess`) VALUES (?, ".time().", $userId, ?)";
        $statement = mysqli_prepare($this->_mysql, $query);
        $statement->bind_param("ss", $username, json_encode($gameProcess));
        $statement->execute();
        $result['success'] = true;
        $result['id'] = mysqli_insert_id($this->_mysql);

        return json_encode($result);
    }

    public function closeGame($userId, $gameId){
        $result = [
            'error' => false,
            'success' => false,
            'message' => ""
        ];

        if (preg_match("/[^0-9]/", $userId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }
        if (preg_match("/[^0-9]/", $gameId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        mysqli_begin_transaction($this->_mysql);
        $query = "SELECT * FROM `GAMES` WHERE `id`=$gameId FOR UPDATE";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $gameProcess = json_decode($row['gameProcess'], true);
            if ($userId == $row['host_id']){
                if ($gameProcess["players"]['player1']['id'] != 0 && $gameProcess["players"]['player1']['id'] != $userId){
                    $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player1']['id']." WHERE `id`=$gameId";
                    mysqli_query($this->_mysql, $query);
                }
                else if ($gameProcess["players"]['player2']['id'] != 0 && $gameProcess["players"]['player2']['id'] != $userId){
                    $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player2']['id']." WHERE `id`=$gameId";
                    mysqli_query($this->_mysql, $query);
                }
                else if ($gameProcess["players"]['player3']['id'] != 0 && $gameProcess["players"]['player3']['id'] != $userId){
                    $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player3']['id']." WHERE `id`=$gameId";
                    mysqli_query($this->_mysql, $query);
                }
                else if ($gameProcess["players"]['player4']['id'] != 0 && $gameProcess["players"]['player4']['id'] != $userId){
                    $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player4']['id']." WHERE `id`=$gameId";
                    mysqli_query($this->_mysql, $query);
                }
                else{
                    $query = "DELETE FROM `GAMES` WHERE `id`=$gameId";
                    mysqli_query($this->_mysql, $query);
                    mysqli_commit($this->_mysql);
                    $result['success'] = true;
                    return json_encode($result);
                }
            }
            $player = "";
            if ($userId == $gameProcess['players']['player1']["id"]){
                $player = 'player1';
            }
            if ($userId == $gameProcess['players']['player2']["id"]){
                $player = 'player2';
            }
            if ($userId == $gameProcess['players']['player3']["id"]){
                $player = 'player3';
            }
            if ($userId == $gameProcess['players']['player4']["id"]){
                $player = 'player4';
            }
            $gameProcess['players'][$player]['id'] = 0;
            $gameProcess['players'][$player]['name'] = "";
            if ($gameProcess["gameStarted"]){
                if (!$gameProcess["players"][$player]["playerOut"]) {
                    $gameProcess["players"][$player]['playerOut'] = true;
                    $gameProcess["players"][$player]["secretCows"] = 0;
                    $gameProcess["players"][$player]["money"] = 0;
                    $gameProcess["maxCows"]++;
                    $gameProcess['countOfPlayers'] -= 1;
                }
            }
            else{
                $gameProcess['countOfPlayers'] -= 1;
            }
            if ($gameProcess["countOfPlayers"] == 1 && $gameProcess["gameStarted"]){
                $gameProcess["gameEnd"] = true;
            }
            $query = "UPDATE `GAMES` SET `gameProcess`=? WHERE `id`=$gameId";
            $statement = mysqli_prepare($this->_mysql, $query);
            $statement->bind_param("s", json_encode($gameProcess));
            $statement->execute();
            if (!$gameProcess["gameStarted"]){
                $query = "UPDATE `GAMES` SET `countOfPlayers`=".$gameProcess['countOfPlayers']." WHERE `id`=$gameId";
                mysqli_query($this->_mysql, $query);
            }
            $result['success'] = true;
            break;
        }
        mysqli_commit($this->_mysql);
        return json_encode($result);
        
    }

    public function loadGames(){
        $result = [];

        $query = "SELECT * FROM `GAMES`";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $time = time();
            if ($time > $row['lastCheckForHost']+65){
                $query = "DELETE FROM `GAMES` WHERE `id`=".$row['id'];
                mysqli_query($this->_mysql, $query);
                continue;
            }
            if ($row['countOfPlayers'] == 4){
                continue;
            }
            $result[] = [
                "id" => $row['id'],
                "gamename" => $row['name'],
                "countOfPlayers" => $row['countOfPlayers']
            ];
        }

        return json_encode($result);
    }

    public function loadSpectateGames(){
        $result = [];

        $query = "SELECT * FROM `GAMES`";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $result[] = [
                "id" => $row['id'],
                "gamename" => $row['name'],
                "countOfPlayers" => $row['countOfPlayers']
            ];
        }

        return json_encode($result);
    }

    public function updateInformation($gameId, $userId){
        $result = [
            'error' => false,
            'success' => false,
            'message' => "",
            "newRound" => false,
            "roundEnd" => false,
            "gameEnd" => false,
            "gameClosed" => false,
            "log" => "",
            "gamePausedByModerator" => false
        ];

        if (preg_match("/[^0-9]/", $gameId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }
        if (preg_match("/[^0-9]/", $userId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        mysqli_begin_transaction($this->_mysql);
        $query = "SELECT * FROM `GAMES` WHERE `id`=$gameId FOR UPDATE";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $gameProcess = json_decode($row['gameProcess'], true);
            if ($userId == $row['host_id'] && !$gameProcess["gameEnd"]){
                $query = "UPDATE `GAMES` SET `lastCheckForHost`=".time()." WHERE `id`=$gameId";
                mysqli_query($this->_mysql, $query);
                if ($row['countOfPlayers'] == 4 && !$gameProcess["gameStarted"]){
                    $gameProcess['gameStarted'] = true;
                    $gameProcess['round'] = 0;
                    $query = "UPDATE `GAMES` SET `gameProcess`=? WHERE `id`=$gameId";
                    $statement = mysqli_prepare($this->_mysql, $query);
                    $statement->bind_param("s", json_encode($gameProcess));
                    $statement->execute();
                    $gameProcess["gamePaused"] = true;
                    $gameProcess["time"] = time();
                }
                if ($gameProcess["gamePausedByModerator"]){
                    $gameProcess["time"] = time();
                }
                else if ($gameProcess["gamePaused"]){
                    $wait = 10;
                    if ($gameProcess["round"] == 0){
                        $wait = 5;
                    }
                    if (time() > $gameProcess["time"]+$wait){
                        $gameProcess["gamePaused"] = false;
                        $gameProcess["time"] = time();
                    }
                }
                else{
                    if ($gameProcess["gameStarted"]) {
                        $newRound = false;
                        if ($gameProcess["round"] == $gameProcess["lastRound"]) {
                            $gameProcess["round"]++;
                            if ($gameProcess["round"] == 22){
                                $gameProcess["round"] = 21;
                                $gameProcess["gameEnd"] = true;
                            }
                            else{
                                $gameProcess["time"] = time();
                                $gameProcess["gamePaused"] = false;
                                $newRound = true;
                            }
                        }

                        $isMovesMade = true;
                        $time = time();
                        foreach ($gameProcess["players"] as $key => $player) {
                            if (!$player["playerOut"] && $time > $player["checked"]+62){
                                $gameProcess["players"][$key]['playerOut'] = true;
                                $gameProcess["players"][$key]["secretCows"] = 0;
                                $gameProcess["maxCows"]++;
                                if ($player["id"] == $row["host_id"]){
                                    if (!$gameProcess["players"]['player2']['playerOut']){
                                        $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player2']['id']." WHERE `id`=$gameId";
                                        mysqli_query($this->_mysql, $query);
                                    }
                                    else if (!$gameProcess["players"]['player3']['playerOut']){
                                        $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player3']['id']." WHERE `id`=$gameId";
                                        mysqli_query($this->_mysql, $query);
                                    }
                                    else if (!$gameProcess["players"]['player4']['playerOut']){
                                        $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player4']['id']." WHERE `id`=$gameId";
                                        mysqli_query($this->_mysql, $query);
                                    }
                                }
                                $gameProcess["countOfPlayers"] -= 1;
                                if ($gameProcess["countOfPlayers"] == 1){
                                    $gameProcess["gameEnd"] = true;
                                }
                            }
                            if ($newRound && !$gameProcess["players"][$key]['playerOut']) {
                                if ($gameProcess["players"][$key]['money'] == 0){
                                    $gameProcess["players"][$key]['playerOut'] = true;
                                    $gameProcess["players"][$key]["secretCows"] = 0;
                                    $gameProcess["maxCows"]++;
                                    if ($player["id"] == $row["host_id"]){
                                        if (!$gameProcess["players"]['player2']['playerOut']){
                                            $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player2']['id']." WHERE `id`=$gameId";
                                            mysqli_query($this->_mysql, $query);
                                        }
                                        else if (!$gameProcess["players"]['player3']['playerOut']){
                                            $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player3']['id']." WHERE `id`=$gameId";
                                            mysqli_query($this->_mysql, $query);
                                        }
                                        else if (!$gameProcess["players"]['player4']['playerOut']){
                                            $query = "UPDATE `GAMES` SET `host_id`=".$gameProcess["players"]['player4']['id']." WHERE `id`=$gameId";
                                            mysqli_query($this->_mysql, $query);
                                        }
                                    }
                                    $gameProcess["countOfPlayers"] -= 1;
                                    if ($gameProcess["countOfPlayers"] == 1){
                                        $gameProcess["gameEnd"] = true;
                                    }
                                }
                                else{
                                    $gameProcess["players"][$key]["isNewRound"] = true;
                                    $gameProcess["players"][$key]["money"]--;
                                    $gameProcess["players"][$key]["cows"] = 0;
                                    $gameProcess["players"][$key]["fedCows"] = 0;
                                    $gameProcess["players"][$key]["secretCows"] = -1;
                                }                                
                            }
                            if ($newRound){
                                $gameProcess["yieldSector"] = 0;
                                $gameProcess["isYieldGood"] = false;
                                $gameProcess["trampledSectors"] = [
                                    "players" => [],
                                    "sectors" => []
                                ];
                            }
                            if ($gameProcess["players"][$key]["secretCows"] == -1 && !$gameProcess["players"][$key]['playerOut']) {
                                $isMovesMade = false;
                            }
                            if ($newRound && $gameProcess["round"] != 1) {
                                $gameProcess["players"][$key]["sector"] = ($player["sector"]%4)+1;
                            }
                        }
                        if ($isMovesMade || (time()-1 > $gameProcess["time"]+30)) {
                            $gameProcess["gamePaused"] = true;
                            $gameProcess["time"] = time();
                            $gameProcess["lastRound"]++;

                            $d6 = rand(1,6);
                            if ($d6 == 1){
                                $gameProcess["world"]["fieldCapacity"]--;
                                $sector = rand(1, 4);
                                $firstSector = $sector;
                                $allSectorsDead = false;
                                while ($gameProcess["world"]["sector$sector"]["capacity"] == 0){
                                    $sector = ($sector%4)+1;
                                    if ($firstSector == $sector){
                                        $allSectorsDead = true;
                                        break;
                                    }
                                }
                                if ($allSectorsDead){
                                    $result["gameEnd"] = true;
                                }
                                $gameProcess["world"]["sector$sector"]["capacity"]--;
                                $gameProcess['yieldSector'] = $sector;
                                $gameProcess["isYieldGood"] = false;
                            }
                            if ($d6 == 6){
                                if ($gameProcess["world"]["fieldCapacity"] != 10){
                                    $gameProcess["world"]["fieldCapacity"]++;
                                    $badSectors = [];
                                    $minCapacity = 4;
                                    for ($i = 1; $i <= 4; $i++){
                                        if ($gameProcess["world"]["sector$i"]["capacity"] < $minCapacity){
                                            $minCapacity = $gameProcess["world"]["sector$i"]["capacity"];
                                            $badSectors = [];
                                            $badSectors[] = $i;
                                        }
                                        else if($gameProcess["world"]["sector$i"]["capacity"] == $minCapacity){
                                            $badSectors[] = $i;
                                        }
                                    }
                                    $sector = $badSectors[rand(0, count($badSectors)-1)];
                                    $gameProcess["world"]["sector$sector"]["capacity"] += 1;
                                    $gameProcess['yieldSector'] = $sector;
                                    $gameProcess["isYieldGood"] = true;
                                }
                            }

                            $temp = [];
                            $temp["freeSectors"] = [];
                            $temp["notFededCows"] = [];
                            $temp["needTrampleSectors"] = 0;
                            foreach ($gameProcess["players"] as $key => $player) {
                                $gameProcess["players"][$key]["cows"] = $player["secretCows"]==-1?0:$player["secretCows"];
                                $gameProcess["players"][$key]["isRoundEnd"] = true;

                                if ($gameProcess["players"][$key]["cows"] == $gameProcess["world"]["sector".$gameProcess["players"][$key]["sector"]]["capacity"]) {
                                    $gameProcess["players"][$key]["fedCows"] = $gameProcess["players"][$key]["cows"];
                                    $gameProcess["players"][$key]["money"] += $gameProcess["players"][$key]["cows"];
                                } 
                                elseif ($gameProcess["players"][$key]["cows"] < $gameProcess["world"]["sector".$gameProcess["players"][$key]["sector"]]["capacity"]) {
                                    $gameProcess["players"][$key]["fedCows"] = $gameProcess["players"][$key]["cows"];
                                    $gameProcess["players"][$key]["money"] += $gameProcess["players"][$key]["cows"];
                                    $temp["freeSectors"][] = [
                                        "sector" => "sector".$gameProcess["players"][$key]["sector"],
                                        "capacity" => $gameProcess["world"]["sector".$gameProcess["players"][$key]["sector"]]["capacity"] - $gameProcess["players"][$key]["cows"]
                                    ];
                                } 
                                else {
                                    $gameProcess["players"][$key]["fedCows"] = $gameProcess["world"]["sector".$gameProcess["players"][$key]["sector"]]["capacity"];
                                    $gameProcess["players"][$key]["money"] += $gameProcess["world"]["sector".$gameProcess["players"][$key]["sector"]]["capacity"];
                                    $temp["notFededCows"][] = [
                                        "player" => $key,
                                        "cows" => $gameProcess["players"][$key]["cows"] - $gameProcess["world"]["sector".$gameProcess["players"][$key]["sector"]]["capacity"]
                                ];
                                }
                            }

                            while(count($temp["freeSectors"]) && count($temp["notFededCows"])){
                                $randPlayer = rand(1, count($temp["notFededCows"]))-1;
                                $randSector = rand(1, count($temp["freeSectors"]))-1;
                                $temp["notFededCows"][$randPlayer]["cows"] -= 1;
                                $temp["freeSectors"][$randSector]["capacity"] -= 1;
                                $gameProcess["players"][$temp["notFededCows"][$randPlayer]["player"]]["fedCows"] += 1;
                                $gameProcess["players"][$temp["notFededCows"][$randPlayer]["player"]]["money"] += 1;
                                if ($temp["notFededCows"][$randPlayer]["cows"] == 0){
                                    unset($temp["notFededCows"][$randPlayer]);
                                    sort($temp["notFededCows"]);
                                }
                                if ($temp["freeSectors"][$randSector]["capacity"] == 0){
                                    unset($temp["freeSectors"][$randSector]);
                                    sort($temp["freeSectors"]);
                                }
                            }

                            while (count($temp["notFededCows"])){
                                $randPlayer = rand(1, count($temp["notFededCows"]))-1;
                                $temp["notFededCows"][$randPlayer]["cows"] -= 1;
                                $temp["needTrampleSectors"] += 1;
                                $gameProcess["trampledSectors"]["players"][] = $temp["notFededCows"][$randPlayer]["player"];
                                if ($temp["notFededCows"][$randPlayer]["cows"] == 0){
                                    unset($temp["notFededCows"][$randPlayer]);
                                    sort($temp["notFededCows"]);
                                }
                            }

                            while($temp["needTrampleSectors"]){
                                $randSector = (rand(1, 100)%4)+1;
                                $firstSector = $randSector;
                                while ($gameProcess["world"]["sector$firstSector"]["capacity"] == 0){
                                    $firstSector = ($firstSector%4)+1;
                                    if ($randSector == $firstSector){
                                        break;
                                    }
                                }
                                $gameProcess["world"]["sector$firstSector"]["capacity"]--;
                                $gameProcess["world"]["fieldCapacity"]--;
                                $temp["needTrampleSectors"] -= 1;
                                $gameProcess["trampledSectors"]["sectors"][] = $firstSector;
                            }

                            if ($gameProcess["world"]["fieldCapacity"] < 0){
                                $gameProcess["gameEnd"] = true;
                                $result["gameEnd"] = true;
                            }
                        }
                    }
                }
                $query = "UPDATE `GAMES` SET `gameProcess`=? WHERE `id`=$gameId";
                $statement = mysqli_prepare($this->_mysql, $query);
                $statement->bind_param("s", json_encode($gameProcess));
                $statement->execute();
            }
            if ($gameProcess['gamePausedByModerator']){
                $result['gamePausedByModerator'] = true;
            }
            $player = "";
            if ($userId == $gameProcess['players']['player1']["id"]) {
                $player = 'player1';
            }
            if ($userId == $gameProcess['players']['player2']["id"]) {
                $player = 'player2';
            }
            if ($userId == $gameProcess['players']['player3']["id"]) {
                $player = 'player3';
            }
            if ($userId == $gameProcess['players']['player4']["id"]) {
                $player = 'player4';
            }
            if ($gameProcess["gameEnd"] == true){
                if ($gameProcess["world"]["fieldCapacity"] <= 0){
                    $result["endReason"] = "Поле полностью уничтожено коровами. Победителей нет :(";
                }
                else if ($gameProcess["round"] == 21){
                    $maxMoney = 0;
                    $winners = [];
                    foreach($gameProcess["players"] as $playerq){
                        if (!$playerq["playerOut"]) {
                            if ($playerq["money"] > $maxMoney) {
                                $maxMoney = $playerq["money"];
                                $winners = [$playerq];
                            } 
                            elseif ($playerq["money"] == $maxMoney) {
                                $winners[] = $playerq;
                            }
                        }
                    }
                    if (count($winners) == 1){
                        $result["endReason"] = "Победил игрок ".$winners[0]["name"].", набрав ".$winners[0]["money"]." монет.";
                    }
                    else{
                        $result["endReason"] = "Победили игроки ";
                        $winnersCount = count($winners);
                        for ($i = 0; $i < $winnersCount; $i++){
                            $result["endReason"] .= $winners[$i]["name"];
                            if ($i < $winnersCount-2){
                                $result["endReason"] .= ", ";
                            }
                            if ($i == $winnersCount-2){
                                $result["endReason"] .= " и ";
                            }
                        }
                        $result["endReason"] .= ", набрав ".$winners[0]["money"]." монет.";
                    }
                }
                else if ($gameProcess["countOfPlayers"] <= 0){
                    $result["endReason"] = "Все игроки потеряли деньги. Победителей нет :(";
                }
                else if ($gameProcess["countOfPlayers"] == 1){
                    $result["endReason"] = "Все игроки, кроме Вас, покинули игру или потеряли деньги. Вы победили!";
                }
                $result["gameEnd"] = true;
            }
            $gameProcess["players"][$player]["checked"] = time();
            if ($gameProcess["players"][$player]["isNewRound"] && $gameProcess["players"][$player]['money'] != -1) {
                $gameProcess["players"][$player]["isNewRound"] = false;
                $result["newRound"] = true;
            }
            if ($gameProcess["players"][$player]["isRoundEnd"] && $gameProcess["players"][$player]['money'] != -1) {
                $gameProcess["players"][$player]["isRoundEnd"] = false;
                $result["roundEnd"] = true;
            }
            $query = "UPDATE `GAMES` SET `gameProcess`=? WHERE `id`=$gameId";
            $statement = mysqli_prepare($this->_mysql, $query);
            $statement->bind_param("s", json_encode($gameProcess));
            $statement->execute();
            $result['gameBegin'] = false;
            if ($gameProcess['gameStarted'] == true){
                $result['gameBegin'] = true;
            }
            $result['gameProcess'] = json_encode($gameProcess);
            break;
        }
        mysqli_commit($this->_mysql);
        return json_encode($result);
    }

    public function updateSpectateInformation($gameId){
        $result = [
            'error' => false,
            'success' => false,
            'message' => "",
            'gameProcess' => ""
        ];

        if (preg_match("/[^0-9]/", $gameId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        $query = "SELECT `gameProcess` FROM `GAMES` WHERE `id`=$gameId";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $result["gameProcess"] = $row["gameProcess"];
            $result['success'] = true;
            break;
        }

        return json_encode($result);
    }

    public function checkGame($gameId){
        $result = [
            'error' => false,
            'success' => false,
            'message' => ""
        ];

        if (preg_match("/[^0-9]/", $gameId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        $query = "SELECT * FROM `GAMES` WHERE `id`=$gameId";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $result['success'] = true;
            break;
        }

        return json_encode($result);
    }

    public function joinGame($gameId, $userId, $username){
        $result = [
            'error' => false,
            'success' => false,
            'message' => ""
        ];

        if (preg_match("/[^0-9]/", $gameId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }
        if (preg_match("/[^0-9]/", $userId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        mysqli_begin_transaction($this->_mysql);
        $query = "SELECT * FROM `GAMES` WHERE id=$gameId FOR UPDATE";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            if ($row['countOfPlayers'] != 4) {
                $gameProcess = json_decode($row['gameProcess'], true);
                $gameProcess['countOfPlayers'] += 1;
                $emptySlot = "";
                foreach($gameProcess["players"] as $key => $player){
                    if ($player["id"] == 0){
                        $emptySlot = $key;
                        break;
                    }
                }
                if ($emptySlot != ""){
                    $gameProcess['players'][$emptySlot]['id'] = $userId;
                    $gameProcess['players'][$emptySlot]['name'] = $username;
                    $query = "UPDATE `GAMES` SET `gameProcess`=?, `countOfPlayers`=".$gameProcess['countOfPlayers']." WHERE `id`=$gameId";
                    $statement = mysqli_prepare($this->_mysql, $query);
                    $statement->bind_param("s", json_encode($gameProcess));
                    $statement->execute();
                }
                else{
                    $result['error'] = "true";
                    $result['message'] = "game already full";
                }
            }
            else{
                $result['error'] = "true";
                $result['message'] = "game already full";
            }
            break;
        }
        mysqli_commit($this->_mysql);
        return json_encode($result);
    }

    public function submitCows($userId, $gameId, $cows){
        $result = [
            'error' => false,
            'success' => false,
            'message' => ""
        ];

        if (preg_match("/[^0-9]/", $gameId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }
        if (preg_match("/[^0-9]/", $userId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }
        if ($cows < 0){
            $cows = 0;
        }

        mysqli_begin_transaction($this->_mysql);
        $query = "SELECT * FROM `GAMES` WHERE `id`=$gameId FOR UPDATE";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $gameProcess = json_decode($row['gameProcess'], true);
            $player = "";
            if ($userId == $gameProcess['players']['player1']["id"]){
                $player = 'player1';
            }
            if ($userId == $gameProcess['players']['player2']["id"]){
                $player = 'player2';
            }
            if ($userId == $gameProcess['players']['player3']["id"]){
                $player = 'player3';
            }
            if ($userId == $gameProcess['players']['player4']["id"]){
                $player = 'player4';
            }
            $maxCows = $gameProcess["maxCows"];
            if ($cows > $maxCows){
                $cows = 0;
                $result["message"] = "Не обманывай! Тебе наказание - выставлено 0 коров.";
            }
            if ($gameProcess['players'][$player]["secretCows"] == -1){
                $gameProcess['players'][$player]["secretCows"] = $cows;
                $query = "UPDATE `GAMES` SET `gameProcess`=? WHERE `id`=$gameId";
                $statement = mysqli_prepare($this->_mysql, $query);
                $statement->bind_param("s", json_encode($gameProcess));
                $statement->execute();
            }
            break;
        }
        $result["success"] = true;
        mysqli_commit($this->_mysql);
        return json_encode($result);
    }

    public function adminPauseGame($gameId){
        $result = [
            'error' => false,
            'success' => false,
            'message' => "",
            'pause' => false
        ];

        if (preg_match("/[^0-9]/", $gameId) !== 0){
            $result['error'] = "true";
            $result['message'] = "bad arguments";
            return json_encode($result);
        }

        mysqli_begin_transaction($this->_mysql);
        $query = "SELECT * FROM `GAMES` WHERE `id`=$gameId FOR UPDATE";
        $request = mysqli_query($this->_mysql, $query);
        while($row = mysqli_fetch_array($request)){
            $gameProcess = json_decode($row['gameProcess'], true);
            $result['pause'] = !$gameProcess['gamePausedByModerator'];
            $gameProcess['gamePausedByModerator'] = !$gameProcess['gamePausedByModerator'];
            $query = "UPDATE `GAMES` SET `gameProcess`=? WHERE `id`=$gameId";
            $statement = mysqli_prepare($this->_mysql, $query);
            $statement->bind_param("s", json_encode($gameProcess));
            $statement->execute();
            break;
        }
        $result["success"] = true;
        mysqli_commit($this->_mysql);
        return json_encode($result);
    }
}