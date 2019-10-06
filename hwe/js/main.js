jQuery(function($){

$('#do_refresh').click(function(){
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
            $tr.find('.turn_pad').text(turnInfo.brief);
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

    /*
    if(!$clock.attr('data-time-diff')){
        var base_clock = moment($clock.attr('data-server-time'));
        $clock.attr('data-time-diff', base_clock.diff(now_clock));
    }
    */

    var game_clock = now_clock.add(parseInt($clock.data('time-diff')), 'milliseconds');

    $('#clock').val(game_clock.format('YYYY-MM-DD HH:mm:ss'));
}

setInterval(myclock, 500);
reloadCommandList();
});