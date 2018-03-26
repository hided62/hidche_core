
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
            var titles = title.split(/【|[0-9]*】/);
            var category = titles.length>2?titles[1]:'-';

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
$(function(){
    loadScenarios();
    $('#scenario_sel').change(scenarioPreview);
    //loadScenarioPreview(8);
})
