export function colorSelect(): void{
    function changeNationColorPlate(){
        const $this = $('#colorType');
        const $option = $this.find('option:selected');
        $this.css({
            'background-color':$option.css('background-color'),
            'color':$option.css('color')
        });
    }
    $('#colorType').on('change', function(){
        changeNationColorPlate();
    });
    changeNationColorPlate();
}