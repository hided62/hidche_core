
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
        case 0: document.location.reload(); break;
        case 2: turn(arg2); break;
        case 3: $(arg2).submit(); break;
        case 4:
            arg2.submit();
            message.msg.value = "";
            document.getElementById("msg").value = "";
            message.msg.focus();
            break;
    }
    return false;
}

function turn(type) {
    $.post({
        url:'j_turn.php',
        dataType:'json',
        data:{
            type:type,
            sel:form2.sel.value
        }
    }).then(function(data){
        if(!data.result){
            alert(data.reason);
        }
        reloadCommandList();
    });
}

function reloadCommandList(){
    $.get({
        url:'commandlist.php',
        cache: false,
    }).then(function(rdata){
        $('#commandlist').html(rdata);
    });
}

function myclock() {

    var $clock = $('#clock');
    var now_clock = moment();

    if(!$clock.attr('data-time-diff')){
        var base_clock = moment($clock.attr('data-server-time'));
        $clock.attr('data-time-diff', base_clock.diff(now_clock));
    }

    var game_clock = now_clock.add(parseInt($clock.attr('data-time-diff')), 'milliseconds');

    $('#clock').val(game_clock.format('YYYY-MM-DD HH:mm:ss'));
    
    window.setTimeout(function(){
        myclock();
    }, 500);
}

jQuery(function($){
    $('#message').submit(function(event){
        var $this = $(this);
        var target = $('[name="genlist"]').val();
        var msg = $('#msg').val();
        return false;
    });

    $('#mainBtnSubmit').click(function(){

    });

    $('#form2').submit(function(){
        var values = $(this).serializeArray();
        console.log(values);
        $.post({
            url:'j_preprocessing.php',
            dataType:'json',
            data:values
        }).then(function(data){
            if(!data.result){
                alert(data.reason);
                reloadCommandList();
                return;
            }

            if(!data.nextPage){
                reloadCommandList();
                return;
            }

            document.location = data.nextPage;
            return;
        }, function(){
            alert('알 수 없는 에러');
            location.reload();
        });
        return false;
    });

    myclock();
});