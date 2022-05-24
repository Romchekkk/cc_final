function loadGames(){
    $.ajax({
        url: "loadGames.php",
        method: "post",
        data: {},
        success: function(result) {
            document.querySelector("#gamesList").innerHTML = result;
        },
        async: false
    })
    setTimeout(loadGames, 5000);
}
function createGame(){
    $.ajax({
        url: "createGame.php",
        method: "post",
        data: {},
        success: function(result) {
            result = JSON.parse(result)
            if (result.result == true){
                location.reload()
            }
            else{
                //error
            }
        },
        async: false
    })
}
function joinGame(gameId){
    $.ajax({
        url: "joinGame.php",
        method: "post",
        data: {
            gameId: gameId
        },
        success: function(result) {
            console.dir(result)
            result = JSON.parse(result)
            if (result.result == true){
                location.reload()
            }
            else{
                //error
            }
        },
        async: false
    })
}
function closeGame(){
    $.ajax({
        url: "closeGame.php",
        method: "post",
        data: {},
        success: function(result) {
            console.dir(result)
            result = JSON.parse(result)
            if (result.result == true){
                location.reload()
            }
            else{
                //error
            }
        },
        async: false
    })
}
function updateInformation(){
    $.ajax({
        url: "updateInformation.php",
        method: "post",
        data: {},
        success: function(result) {
            console.dir(result)
            result = JSON.parse(result)
            if (result.result == true){
                if ('gameClosed' in result){
                    location.reload()
                }
                else{
                    for (let i in result.players){
                        let sector = document.querySelector("#sector"+result.players[i].sector)
                        sector.children[2].children[0].innerHTML = result.players[i].name == ""?"Не подключился":result.players[i].name
                        sector.children[3].children[0].children[1].innerHTML = result.players[i].money
                        sector.children[3].children[1].children[1].innerHTML = result.players[i].cows
                    }
                }
            }
            else{
                //error
            }
        },
        async: false
    })
    setTimeout(updateInformation, 1000)
}