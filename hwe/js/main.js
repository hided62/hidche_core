
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
        case 0: location.reload(true); break;
        case 2: turn(arg2); break;
        case 3: arg2.submit(); break;
        case 4:
            arg2.submit();
            message.msg.value = "";
            document.getElementById("msg").value = "";
            message.msg.focus();
            break;
    }
    return false;
}

function moveProcessing(commandtype, turn){
    console.log(commandtype, turn);
    $.redirect("processing.php",{ commandtype: commandtype, turn: turn}, 'post'); 
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

    $('#mainBtnSubmit').click(function(){

    });
});