function changePermission(isAmbassador, rawGeneralList){
    console.log(isAmbassador);
    console.log(rawGeneralList);

    var generalList = [];
    $.each(rawGeneralList, function(idx, value){
        generalList.push(parseInt(value.id));
    })

    $.post({
        url:'j_general_set_permission.php',
        dataType:'json',
        data:{
            isAmbassador:isAmbassador,
            genlist:generalList
        },
    }).then(function(data){
        if(!data){
            alert('변경하지 못했습니다.');
            return false;
        }
        if(!data.result){
            alert('변경하지 못했습니다. : '+data.reason);
            return false;
        }
        alert('변경했습니다.');
        location.reload();
    }, errUnknown);
}

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
        if(!confirm('외교권자를 변경할까요?')){
            return false;
        }
        
        changePermission(true, $('#selectAmbassador').select2('data'));
        return false;
    });

    $('#changeAuditor').click(function(){
        if(!confirm('조언자를 변경할까요?')){
            return false;
        }

        changePermission(false, $('#selectAuditor').select2('data'));
        return false;
    })
})