jQuery(function($){
    $('#by_scenario').change(function(){
        var scenarioIdx = $(this).val();
        $.redirect('a_hallOfFame.php', {scenarioIdx:scenarioIdx}, 'get');
    })
    //$.redirect("processing.php",{ commandtype: commandtype, turn: turn}, 'post'); 
});