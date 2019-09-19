
/*
function showCityGeneral(cityIdx){

}
*/
/*
function loadScenarioPreview(scenarioIdx){
    $(function(){
        reloadWorldMap({
            clickableAll:true,
            selectCallback:showCityGeneral,
            hrefTemplate:'#',
            neutralView:true,
            showMe:false,
            targetJson:'j_get_scenario_map.php?scenarioIdx={0}'.format(scenarioIdx)
        });
    });
}
*/



function loadScenarios(){
    $.ajax({
        method:'post',
        url:'j_load_scenarios.php',
        dataType:'json'
    }).then(function(result){
        if(!result.result){
            var deferred = $.Deferred();
            deferred.reject('fail');
            return deferred.promise();
        }


        var list = {};


        $.each(result.scenario, function(idx, value){
            var title = value.title || "-";
            var pat = /【(.*?)[0-9\-_\.a-zA-Z]*】/;
            var category = pat.exec(title);
            category = category?category[1]:'-';

            value.title = title;
            value.category = category;
            value.idx = idx;
            value.year = value.year || 180;

            if(!(category in list)){
                list[category] = {};
            }

            list[category][idx] = value;
        });

        var $select = $('#scenario_sel');
        $.each(list, function(category, items){
            var $optgroup = $('<optgroup>').attr('label', category);
            
            $.each(items, function(idx, scenario){
                var $option = $('<option>')
                    .data('scenario',scenario)
                    .val(idx)
                    .html(scenario.title);
                $optgroup.append($option);
            });

            $select.append($optgroup);
        });

        $select.val(0);
        $select.change();
    });
}

function scenarioPreview(){
    var $select = $(this);
    var $option = $select.find('option:selected');

    var $year = $('#scenario_begin');
    var $npc = $('#scenario_npc');
    var $npcEx = $('#scenario_npc_extend');
    var $nation = $('#scenario_nation');

    var scenario = $option.data('scenario');
    console.log(scenario.idx, scenario.title);

    $year.html('{0}년'.format(scenario.year));
    $npc.html('{0}명'.format(scenario.npc_cnt));
    if(scenario.npcEx_cnt == 0){
        $npcEx.html('');
    }
    else{
        $npcEx.html('+{0}명'.format(scenario.npcEx_cnt));
    }

    $nation.html('');
    $.each(scenario.nation, function(idx, nation){
        $nation.append('<span style="color:{0}">{1}</span> {2}명. {3}<br>'.format(
            nation.color, nation.name, nation.generals, nation.cities.join(', ')
        ));
    });
}

function formSetup(){
    $.validator.addMethod("autorun_user", function(value,element){
        var parent = $(element).parent('.input-group');
        var checkCnt = parent.find('input:checked').length;
        if(value <= 0){
            if(checkCnt > 0){
                return false;
            }
            return true;
        }

        
        if(checkCnt == 0){
            return false;
        }
        return true;
    }, "유효 시간과 옵션은 동시에 설정해야합니다.");
    $('#game_form').validate({
        rules:{
            turnterm:"required",
            sync:"required",
            scenario:"required",
            fiction:"required",
            extend:"required",
            npcmode:"required",
            show_img_level:"required",
            tournament_trig:"required",
            join_mode:'required',
            autorun_user_minutes:{required: true, autorun_user: true, min:0}
        },
        errorElement: "div",
        errorPlacement: function ( error, element ) {
            // Add the `help-block` class to the error element
            error.addClass( "invalid-feedback" );

            if ( element.prop( "type" ) === "checkbox" ) {
                error.insertAfter( element.parent( "label" ) );
            } else {
                error.insertAfter( element );
            }
        },
        highlight: function ( element, errorClass, validClass ) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element, errorClass, validClass) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        }
    });
    $('#game_form').submit(function(e){
        e.preventDefault();
        if(!$("#game_form").valid()){
            return;
        }
        
        var autorun_user_minutes = parseInt($('#autorun_user_minutes').val());
        var autorun_user = [];
        if(autorun_user_minutes > 0){
            $('.autorun_user_chk:checked').each(function(){
                autorun_user.push($(this).data('key'));
            });
        }

        $.ajax({
            cache:false,
            type:'post',
            url:'j_install.php',
            dataType:'json',
            data:{
                turnterm:$('#turnterm input:radio:checked').val(),
                sync:$('#sync input:radio:checked').val(),
                scenario:$('#scenario_sel').val(),
                fiction:$('#fiction input:radio:checked').val(),
                extend:$('#extend input:radio:checked').val(),
                npcmode:$('#npcmode input:radio:checked').val(),
                show_img_level:$('#show_img_level input:radio:checked').val(),
                tournament_trig:$('#tournament_trig input:radio:checked').val(),
                reserve_open:$('#reserve_open').val(),
                pre_reserve_open:$('#pre_reserve_open').val(),
                join_mode:$('#join_mode input:radio:checked').val(),
                autorun_user_minutes:autorun_user_minutes,
                autorun_user:autorun_user
            }
        }).then(function(result){
            var deferred = $.Deferred();

            if(!result.result){
                alert(result.reason);
                deferred.reject('fail');
            }
            else{
                alert('게임이 리셋되었습니다.');
                deferred.resolve();
            }

            return deferred.promise();
        }).then(function(){
            location.href = '..';
        });
        
    });
}

$(function(){
    loadScenarios();
    $('#scenario_sel').change(scenarioPreview);
    formSetup();
})
