
function showCityGeneral(cityIdx){

}

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

$(function(){
    loadScenarioPreview(8);
})