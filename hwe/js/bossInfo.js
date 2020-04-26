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
    });

    $('#btn_kick').click(function(){
        var $kickSelect = $('#genlist_kick option:selected');
        var generalID = $kickSelect.val();
        if(!generalID){
            alert('장수를 선택해주세요');
            return false;
        }
        var generalName = $kickSelect.data('name');
        if(!confirm('{0}를 추방하시겠습니까?'.format(generalName))){
            return false;
        }
        $.post({
            url:'j_myBossInfo.php',
            dataType:'json',
            data:{
                action:'추방',
                destGeneralID:generalID
            },
        }).then(function(data){
            if(!data){
                alert('추방하지 못했습니다.');
                return false;
            }
            if(!data.result){
                alert('추방하지 못했습니다. : '+data.reason);
                return false;
            }
            alert('{0}를 추방했습니다.'.format(generalName));
            location.reload();
        }, errUnknown);
        return false;
    });

    $('.btn_appoint').click(function(){
        var $btn = $(this);
        var officerLevel = $btn.data('officer_level');
        var officerLevelText = $btn.data('officer_level_text');
        var cityID = 0;
        var cityName = '_';
        var $generalSelect = $('#genlist_{0} option:selected'.format(officerLevel));
        var $citySelect = $('#citylist_{0} option:selected'.format(officerLevel));

        var generalID = $generalSelect.val();
        var generalName = $generalSelect.data('name');
        var generalOfficerLevel = $generalSelect.data('officer_level');
        

        if(officerLevel >= 5){
            if(generalID == 0){
                if(!confirm('{0}직을 비우시겠습니까?'.format(officerLevelText))){
                    return false;
                }
            }
            else if(generalOfficerLevel >= 5){
                if(!confirm('이미 수뇌인 {0}을(를) {1}직에 임명하시겠습니까?'.format(generalName, officerLevelText))){
                    return false;
                }
            }
            else{
                if(!confirm('{0}을(를) {1}직에 임명하시겠습니까?'.format(generalName, officerLevelText))){
                    return false;
                }
            }
        }
        else{
            cityID = $citySelect.val();
            if(!cityID){
                alert('도시를 선택해주세요');
                return false;
            }
            cityName = $citySelect.find('option:selected .name_field').text();

            if(generalID == 0){
                if(!confirm('{0} {1}직을 비우시겠습니까?'.format(cityName, officerLevelText))){
                    return false;
                }
            }
            else if(generalOfficerLevel >= 5){
                if(!confirm('수뇌인 {0}을(를) {1} {2}직에 임명하시겠습니까?'.format(generalName, cityName, officerLevelText))){
                    return false;
                }
            }
            else{
                if(!confirm('{0}을(를) {1} {2}직에 임명하시겠습니까?'.format(generalName, cityName, officerLevelText))){
                    return false;
                }
            }
        }

        $.post({
            url:'j_myBossInfo.php',
            dataType:'json',
            data:{
                action:'임명',
                destGeneralID:generalID,
                destCityID:cityID,
                officerLevel:officerLevel
            },
        }).then(function(data){
            if(!data){
                alert('임명하지 못했습니다.');
                return false;
            }
            if(!data.result){
                alert('임명하지 못했습니다. : '+data.reason);
                return false;
            }

            if(generalID){
                alert('{0}을(를) 임명했습니다.'.format(generalName));
            }
            else{
                alert('관직을 비웠습니다.');
            }
            
            location.reload();
        }, errUnknown);

    })
})