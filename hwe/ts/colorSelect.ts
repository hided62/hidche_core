import $ from 'jquery';

$(function($){
    function changeNationColorPlate(){
        const $this = $('#colorType');
        const $option = $this.find('option:selected');
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