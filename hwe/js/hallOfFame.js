jQuery(function($){
    $('#by_scenario').change(function(){
        var $this = $(this);
        var scenarioIdx = $this.val();
        var seasonIdx = $(this).find('option:selected').data('season');
        
        
        $.redirect('a_hallOfFame.php', {scenarioIdx:scenarioIdx, seasonIdx:seasonIdx}, 'get');
    })
});