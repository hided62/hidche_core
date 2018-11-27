$(function() {
    $('#citySelector').select2({
        theme: 'bootstrap4',
        placeholder: "도시를 선택해 주세요.",
        allowClear: false,
        language: "ko",
        containerCss: {
            display: "inline-block !important",
            color: 'white !important'
        },
        containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });
    $('#citySelector').on('select2:select', function(e){
        var data = e.params.data;
        if(!data.selected || data.disabled){
            return;
        }
        var $obj = $('#citySelector').parents('form');
        $obj.submit();
        console.log($obj);
    });
});