<?php
    session_start();
    require_once("database.php");
    if (!isset($_GET['user_id']) || !isset($_GET['chat_id']) || !isset($_GET['username'])){
        print "Тыы! Неее! Пройдеееешь!";
    }
    else{
        $mysql = new dataBase();
        $userCheck = json_decode($mysql->checkUser($_GET['user_id'], $_GET['chat_id']));

        if ($userCheck->error == true) {
            //error
            print $userCheck->message;
        } 
        elseif ($userCheck->success == true) {
            $_SESSION['userAuthorised'] = true;
            $_SESSION['userId'] = $_GET['user_id'];
            $_SESSION['username'] = $_GET['username'];
        } 
        else {
            $addUser = json_decode($mysql->addUser($_GET['user_id'], $_GET['chat_id']));
            if ($addUser->error == true) {
                //error
                print $addUser->$message;
            } 
            else {
                $_SESSION['userAuthorised'] = true;
                $_SESSION['userId'] = $_GET['user_id'];
                $_SESSION['username'] = $_GET['username'];
            }
        }
        if (isset($_SESSION['userAuthorised']) && $_SESSION['userAuthorised'] == true) {
            if (isset($_SESSION['gameCreated']) && $_SESSION['gameCreated'] == true && json_decode($mysql->checkGame($_SESSION['gameId']))->success == true) {
                $html = file_get_contents('field_page.html');
            } 
            else {
                $html = file_get_contents('start_page.html');
            }
            print $html;
        }
    }
?>

