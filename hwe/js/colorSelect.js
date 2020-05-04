jQuery(function($){
    function changeNationColorPlate(){
        var $this = $('#colorType');
        var $option = $this.find('option:selected');
        $this.css({
            'background-color':$option.css('background-color'),
            'color':$option.css('color')
        });
    }
    $('#colorType').change(function(){
        changeNationColorPlate();
    });
    changeNationColorPlate();
});