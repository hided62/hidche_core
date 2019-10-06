function reserveTurn(turnList, command, arg){
    $.post({
        url:'j_set_general_command.php',
        dataType:'json',
        data:{
            action:command,
            turnList:turnList,
            arg:JSON.stringify(arg)
        }
    }).then(function(data){
        if(!data.result){
            alert(data.reason);
            return;
        }

        if(!isChiefTurn){
            window.location.href = './';
        }
        else{
            window.location.href = 'b_chiefcenter.php';
        }
        
    }, errUnknown);
}

jQuery(function($){

$('#commonSubmit').click(function(){

    //checkCommandArg 참고
    var availableArgumentList = {
        'string':[
            'nationName', 'optionText', 'itemType', 'nationType',
        ],
        'int':[
            'crewType', 'destGeneralID', 'destCityID', 'destNationID',
            'amount', 'colorType', 'itemCode',
            'month',
            'year', 'itemCode', 'destGeneralID', 'destCityID', 'destNationID', 'amount', 'crewType',
        ],
        'boolean':[
            'isGold', 'buyRice',
        ],
        'integerArray':[
            'destNationIDList', 'destGeneralIDList'
        ]
    }

    var handlerList = {
        'string':function($obj){
            return $.trim($obj.eq(0).val());
        },
        'int':function($obj){
            return parseInt($obj.eq(0).val());
        },
        'boolean':function($obj){
            return !!($obj.eq(0).val());
        },
        'integerArray':function($obj){
            return $obj.map(function(){
                return parseInt($(this).val());
            });
        }
    }

    var argument = {};
    for (var typeName in availableArgumentList) {
        availableArgumentList[typeName].forEach(function(argName){
            var $obj = $('#'+argName);
            if($obj.length == 0){
                return;
            }
    
            argument[argName] = handlerList[typeName]($obj);
        });
    }

    console.log(argument);
    reserveTurn(turnList, command, argument);
});


});