function reserveTurn(turnList, command, arg){
    var target;
    if(isChiefTurn){
        target = 'j_set_chief_command.php';
    }
    else{
        target = 'j_set_general_command.php';
    }
    $.post({
        url:target,
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

window.submitAction = function(){

    //checkCommandArg 참고
    var availableArgumentList = {
        'string':[
            'nationName', 'optionText', 'itemType', 'nationType', 'itemCode',
        ],
        'int':[
            'crewType', 'destGeneralID', 'destCityID', 'destNationID',
            'amount', 'colorType', 
            'year', 'month',
        ],
        'boolean':[
            'isGold', 'buyRice',
        ],
        'integerArray':[
            'destNationIDList', 'destGeneralIDList', 'amountList'
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
            switch ($obj.eq(0).val().toLowerCase()) {
            case "true": case "yes": case "1":
                return true;
            case "false": case "no": case "0":
                return false;
            default:
                throw new Error ("Boolean.parse: Cannot convert string to boolean.");
            }
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
                $obj = $('.'+argName);
                if($obj.length == 0){
                    return;
                }
            }
    
            argument[argName] = handlerList[typeName]($obj);
        });
    }

    console.log(argument);
    reserveTurn(turnList, command, argument);
};

$('#commonSubmit').click(submitAction);


});