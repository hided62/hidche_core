import $ from 'jquery';
import 'bootstrap';
import 'select2/dist/js/select2.full.js'


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
        const data = e.params.data;
        if(!data.selected || data.disabled){
            return;
        }
        const $obj = $('#citySelector').parents('form');
        $obj.trigger('submit');
        console.log($obj);
    });
});