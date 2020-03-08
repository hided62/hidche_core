jQuery(function($){

$('#refreshPage').click(function(){
    document.location.reload();
    return false;
});


function reloadCommandList(){
    $.get({
        url:'j_get_reserved_command.php',
        dataType:'json',
        cache: false,
    }).then(function(data){
        if(!data){
            return;
        }
        if(!data.result){
            return;
        }
        var game_clock = moment(data.date);
        var now_clock = moment();
        var $clock = $('#clock');
        $clock.data('time-diff', game_clock.diff(now_clock));
        $clock.val(game_clock.format('YYYY-MM-DD HH:mm:ss'));

        var turnTime = moment(data.turnTime);
        var nextTurnTime = turnTime.clone();

        var year = data.year;
        var month = data.month;

        $.each(data.turn, function(turnIdx, turnInfo){
            var $tr = $('#command_{0}'.format(turnIdx));
            
            $tr.find('.time_pad').text(nextTurnTime.format('HH:mm'));
            $tr.find('.month_pad').text('{0}年 {1}月'.format(year, month));
            var $turn_pad = $tr.find('.turn_pad');
            var $turn_text = $turn_pad.find('.turn_text');
            $turn_text.text(turnInfo.brief).css('font-size','13px');

            var oWidth = $turn_pad.innerWidth();
            var iWidth = $turn_text.outerWidth();
            if(iWidth > oWidth * 0.95){
                var newFontSize = 13 * oWidth / iWidth * 0.9;
                $turn_text.css('font-size', '{0}px'.format(newFontSize));
            }
            
            nextTurnTime.add(data.turnTerm, 'minutes');
            month+=1;
            if(month >= 13){
                year += 1;
                month -= 12;
            }
        });
        console.log(data);

        
    });
}

function myclock() {

    var $clock = $('#clock');
    var now_clock = moment();

    if($clock.data('time-diff') == null){
        return;
    }

    var game_clock = now_clock.add(parseInt($clock.data('time-diff')), 'milliseconds');

    $('#clock').val(game_clock.format('YYYY-MM-DD HH:mm:ss'));
}

function pushTurn(pushAmount){
    $.post({
        url:'j_turn.php',
        dataType:'json',
        data:{
            amount:pushAmount
        }
    }).then(function(data){
        if(!data.result){
            alert(data.reason);
        }
        reloadCommandList();
    }, errUnknown);
}

function repeatTurn(repeatAmount){
    $.post({
        url:'j_turn.php',
        dataType:'json',
        data:{
            amount:repeatAmount,
            is_repeat:true
        }
    }).then(function(data){
        if(!data.result){
            alert(data.reason);
        }
        reloadCommandList();
    }, errUnknown);
}

$('#pullTurn').click(function(){
    pushTurn(-parseInt($('#repeatAmount').val())); 
});

$('#pushTurn').click(function(){
    pushTurn(parseInt($('#repeatAmount').val())); 
});

$('#repeatTurn').click(function(){
    repeatTurn(parseInt($('#repeatAmount').val())); 
});


function reserveTurn(turnList, command){
    console.log(turnList, command);
    $.post({
        url:'j_set_general_command.php',
        dataType:'json',
        data:{
            action:command,
            turnList:turnList
        }
    }).then(function(data){
        if(!data.result){
            alert(data.reason);
        }
        reloadCommandList();
    }, errUnknown);
}

$('#reserveTurn').click(function(){
    var turnList = $('#generalTurnSelector').val().map(function(v){return parseInt(v);});
    var $command = $('#generalCommandList option:selected');
    if($command.data('reqarg')){
        $.redirect(
            "b_processing.php", {
                command: $command.val(),
                turnList: turnList.join('_')
        }, "GET"); 
    }
    else{
        reserveTurn(turnList, $command.val());
    }
    return false;
})

setInterval(myclock, 500);
reloadCommandList();
});