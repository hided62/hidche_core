
if (document.all) {
    document.onkeydown = function () {
        var key_f5 = 116; // 116 = F5
        var key_enter = 13; // 13 = 엔터
        if (key_f5 == event.keyCode) {
            event.keyCode=0;
            refreshing(null, 0,0);
            return false;
        } else if(key_enter == event.keyCode) {
            event.keyCode=0;
            refreshing(null, 4,message);
            return false;
        }
        return true;
    }
}

function refreshing(obj, arg1, arg2) {
    if(obj !== null){
        var $obj = $(obj);
        if($obj.find('button:disabled').length > 0){
            console.log('locked');
            return;
        }
    }
//    if(term <= 0) {
    switch(arg1) {
        case 0: location.reload(); break;
        case 1: go(arg2); break;
        case 2: turn(arg2); break;
        case 3: arg2.submit(); break;
        case 4:
            arg2.submit();
            message.msg.value = "";
            document.getElementById("msg").value = "";
            message.msg.focus();
            break;
    }
}

function moveProcessing(commandtype, turn){
    console.log(commandtype, turn);
    $.redirect("processing.php",{ commandtype: commandtype, turn: turn}, 'post'); 
}

function go(type) {
    if(type == 1) location.replace('b_nationboard.php');
    else if(type == 2) location.replace('b_troop.php');
    else if(type == 3) location.replace('b_nationrule.php');
    else if(type == 4) location.replace('b_chiefboard.php');
    else if(type == 5) location.replace('b_chiefcenter.php');
    else if(type == 6) window.open('b_genList.php');
    else if(type == 7) location.replace('b_myKingdomInfo.php');
    else if(type == 8) location.replace('b_myCityInfo.php');
    else if(type == 9) location.replace('b_myGenInfo.php');
    else if(type == 10) location.replace('b_myBossInfo.php');
    else if(type == 11) location.replace('b_currentCity.php');
    else if(type == 12) location.replace('b_myPage.php');
    else if(type == 13) location.replace('b_dipcenter.php');
    else if(type == 14) location.replace('b_diplomacy.php');
    else if(type == 15) window.open('b_tournament.php');
    else if(type == 16) window.open('b_betting.php');
    else if(type == 17) window.open('b_auction.php');
    else if(type == 18) window.open('b_battleCenter.php');
}

function turn(type) {
    num = form2.sel.value;
    commandlist.location.replace('turn.php?type=' + type + '&sel=' + num);
}

jQuery(function($){
    $('#message').submit(function(event){
        var $this = $(this);
        var target = $('[name="genlist"]').val();
        var msg = $('#msg').val();
        console.log(target, msg);
        return false;
    });
});