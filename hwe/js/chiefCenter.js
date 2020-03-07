var filledChiefList = {};

var chiefTableObj = {};

function clearTable(){
    $('.chiefLevelText').html('-');
    $('.chiefTurnTime, .chiefTurnText, .chiefName').html('&nbsp;');
}

function genChiefTableObj(){
    var objTable = {
        btns: $('#turnPush,#turnPull,#setCommand')
    };

    for(var chiefIdx = 5; chiefIdx <= 12; chiefIdx++){
        var $plate = $('#chief_{0}'.format(chiefIdx));
        var $levelText = $plate.find('.chiefLevelText');
        var $name = $plate.find('.chiefName');
        var turn = [];
        for(var turnIdx=0;turnIdx<maxChiefTurn;turnIdx++){
            var $turn = $plate.find('.turn{0}'.format(turnIdx+1));
            var $turnTime = $turn.find('.chiefTurnTime');
            var $turnText = $turn.find('.chiefTurnText');
            turn.push({turnTime:$turnTime,turnText:$turnText});
        }
        objTable[chiefIdx] = {
            levelText: $levelText,
            name: $name,
            turn: turn
        };
    }

    return objTable;
}



function clearChief(chiefIdx){
    var $plate = $('#chief_{0}'.format(chiefIdx));
    $plate.find('.chiefLevelText').html('-');
    $plate.find('.chiefTurnTime, .chiefTurnText, .chiefName').html('&nbsp;');
}

function reloadTable(){
    $.post({
        url:'j_getChiefTurn.php',
        dataType:'json',
    }).then(function(data){
        if(!data.result){
            alert(data.reason);
            return;
        }
        var turnTerm = data.turnTerm;
        var tmpFilledChiefList = {};

        if(data.isChief){
            chiefTableObj.btns.css('visibility', 'visible');
        }
        else{
            chiefTableObj.btns.css('visibility', 'hidden');
        }
        $.each(data.nationTurnBrief, function(chiefIdx, chiefInfo){
            tmpFilledChiefList[chiefIdx] = true;
            filledChiefList[chiefIdx] = true;

            var plateObj = chiefTableObj[chiefIdx];
            var $name = $('<span>{0}</span>'.format(chiefInfo.name));
            var nameColor = getNpcColor(chiefInfo.npcType);
            if(nameColor){
                $name.css('color',nameColor);
            }
            plateObj.levelText.text(chiefInfo.levelText);
            plateObj.name.html($name);

            var turnTimeObj = moment(chiefInfo.turnTime);
            var turnList = plateObj.turn;
            $.each(chiefInfo.turn, function(turnIdx, turnText){
                turnTimeObj = turnTimeObj.add(turnTerm, 'minute');
                turnList[turnIdx].turnTime.text(turnTimeObj.format('hh:mm'));
                turnList[turnIdx].turnText.text(turnText);
                
            });
        });

        for(var idx=5;idx<=12;idx++){
            if(idx in tmpFilledChiefList){
                continue;
            }
            if(idx in filledChiefList){
                clearChief(chiefIdx);
            }
        }
        filledChiefList = tmpFilledChiefList;
    }, errUnknown);
}

function reserveTurn(turnList, command){
    console.log(turnList, command);
    $.post({
        url:'j_set_chief_command.php',
        dataType:'json',
        data:{
            action:command,
            turnList:turnList
        }
    }).then(function(data){
        if(!data.result){
            alert(data.reason);
        }
        reloadTable();
    }, errUnknown);
}

jQuery(function($){

chiefTableObj= genChiefTableObj();
reloadTable();
$('#reloadTable').click(reloadTable);
$('#setCommand').click(function(){
    var turnList = $('#chiefTurnSelector').val().map(function(v){return parseInt(v);});
    var $command = $('#chiefCommandList option:selected');
    if($command.data('reqarg')){
        $.redirect(
            "b_processing.php", {
                command: $command.val(),
                turnList: turnList.join('_'),
                is_chief: true
        }, "GET"); 
    }
    else{
        reserveTurn(turnList, $command.val());
    }
    return false;
});
    
})