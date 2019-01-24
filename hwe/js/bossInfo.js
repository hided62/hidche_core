$(function(){
    $('#selectAmbassador').select2({
        theme: 'bootstrap4',
        placeholder: "",
        allowClear: true,
        language: "ko",
        width:300,
        maximumSelectionLength: 2,
        containerCss: {
            display: "inline-block !important;",
            color: 'white !important'
        },
        data:candidateAmbassadors,
        //containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });

    $('#selectAuditor').select2({
        theme: 'bootstrap4',
        placeholder: "",
        allowClear: true,
        language: "ko",
        width:300,
        maximumSelectionLength: 2,
        containerCss: {
            display: "inline-block !important;",
            color: 'white !important'
        },
        data:candidateAuditors,
        //containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });

    $('#changeAmbassador').click(function(){
        //TODO: 변경 코드
    });
})