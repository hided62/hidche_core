

function submitLetter(){
    var $brief = $('#inputBrief');
    var $detail = $('#inputDetail');
    var $prevNo = $('#inputPrevNo');
    var $destNation = $('#inputDestNation');
    var brief = $.trim($brief.val());
    var detail = $.trim($detail.val());
    var prevNo = parseInt($prevNo.val());
    var destNation = parseInt($destNation.val());
    
    if(prevNo !== null && prevNo < 1){
        prevNo = null;
    }

    console.log(brief);
    if(!brief){
        return false;
    }

    $brief.val('');
    $detail.val('');

    $.post({
        url:'j_diplomacy_send_letter.php',
        dataType:'json',
        data:{
            brief:brief,
            detail:detail,
            destNation:destNation,
            prevNo:prevNo
        }
    }).then(function(data){
        if(!data){
            $brief.val(brief);
            $detail.val(detail);
            return quickReject('외교 서신을 보내는데 실패했습니다.');
        }
        if(!data.result){
            $brief.val(brief);
            $detail.val(detail);
            return quickReject('외교 서신을 보내는데 실패했습니다. : '+data.reason);
        }

        alert('전송했습니다.');
        location.reload();

    }, errUnknown)
    .fail(function(reason){
        alert(reason);
    });

    return false;
}

function repondLetter(letterNo, isAgree, reason){
    $.post({
        url:'j_diplomacy_respond_letter.php',
        dataType:'json',
        data:{
            letterNo:letterNo,
            isAgree:isAgree,
            reason:reason,
        }
    }).then(function(data){
        if(!data){
            alert()
            return quickReject('응답을 실패했습니다.');
        }
        if(!data.result){
            return quickReject('응답을 실패했습니다. : '+data.reason);
        }

        if(isAgree){
            alert('승인했습니다.');
        }
        else{
            alert('거부했습니다.');
        }

        location.reload();
        return false;

    }, errUnknown)
    .fail(function(reason){
        alert(reason);
    });

    return false;
}

function rollbackLetter(letterNo){
    $.post({
        url:'j_diplomacy_rollback_letter.php',
        dataType:'json',
        data:{
            letterNo:letterNo,
        }
    }).then(function(data){
        if(!data){
            alert()
            return quickReject('회수를 실패했습니다.');
        }
        if(!data.result){
            return quickReject('회수를 실패했습니다. : '+data.reason);
        }

        alert('회수 했습니다.');

        location.reload();
        return false;

    }, errUnknown)
    .fail(function(reason){
        alert(reason);
    });

    return false;
}

function destroyLetter(letterNo){
    $.post({
        url:'j_diplomacy_destroy_letter.php',
        dataType:'json',
        data:{
            letterNo:letterNo,
        }
    }).then(function(data){
        if(!data){
            alert()
            return quickReject('파기를 실패했습니다.');
        }
        if(!data.result){
            return quickReject('파기를 실패했습니다. : '+data.reason);
        }

        if(data.state == 'activated'){
            alert('파기를 요청 했습니다.');
        }
        else{
            alert('파기 되었습니다.');
        }

        location.reload();
        return false;

    }, errUnknown)
    .fail(function(reason){
        alert(reason);
    });

    return false;
}

function drawLetter(idx, letterObj){
    
    if(letterObj.state == 'cancelled'){
        //TODO: 취소되거나, 대체된 문서도 보여줄 방법을 찾아볼 것
        return;
    }

    console.log(letterObj);

    var $letterFrame = $('#letterTemplate > .letterFrame');

    var srcColorFormat = {
        'background-color':letterObj.src.nationColor,
        'color':isBrightColor(letterObj.src.nationColor)?'#000000':'#ffffff'
    };

    var destColorFormat = {
        'background-color':letterObj.dest.nationColor,
        'color':isBrightColor(letterObj.dest.nationColor)?'#000000':'#ffffff'
    };

    var targetNation = letterObj.src.nationID==myNationID?letterObj.dest:letterObj.src;
    var targetColor = letterObj.src.nationID==myNationID?destColorFormat:srcColorFormat;

    var $letter = $letterFrame.clone();

    if(letterObj.state == 'replaced'){
        $letter.hide();
    }

    $letter.addClass('letterObj')
        .data('no', letterObj.no)
        .data('brief', letterObj.brief)
        .data('detail', letterObj.detail)
        .attr('id', 'letter_'+letterObj.no);

    $letter.find('.letterHeader').css(targetColor);
    $letter.find('.letterNationName').text(targetNation.nationName);
    $letter.find('.letterDate').text(letterObj.date);
    $letter.find('.letterNo').text('#'+letterObj.no);

    var stateText = {
        'proposed':'제안됨',
        'activated':'승인됨',
        'cancelled':'거부됨',
        'replaced':'대체됨',
    };

    var stateOptionText = {
        'try_destroy_src':'송신측의 파기 요청',
        'try_destroy_dest':'수신측의 파기 요청',
    }
    $letter.find('.letterStatus').text(stateText[letterObj.state]);

    if(letterObj.state_opt !== null){
        $letter.find('.letterStatusOpt').text('('+stateOptionText[letterObj.state_opt]+')');
    }
    if(letterObj.prev_no !== null){
        var $showPrev = $('<a href="#">#{0}</a>'.format(letterObj.prev_no));
        $showPrev.click(function(){
            $('#letter_'+letterObj.prev_no).toggle();
        })
        $letter.find('.letterPrevNo').html($showPrev);
    }
    else{
        $letter.find('.letterPrevNo').text('신규');
    }
    $letter.find('.letterBrief').html(nl2br(escapeHtml(letterObj.brief)));
    $letter.find('.letterDetail').html(nl2br(escapeHtml(letterObj.detail)));

    $letter.find('.letterSrc .signerImg img.generalIcon').attr('src', letterObj.src.generalIcon);
    $letter.find('.letterSrc .signerNation').text(letterObj.src.nationName).css(srcColorFormat);
    $letter.find('.letterSrc .signerName').text(letterObj.src.generalName).css(srcColorFormat);

    if(letterObj.dest.generalName){
        $letter.find('.letterDest .signerImg img.generalIcon').attr('src', letterObj.dest.generalIcon);
        $letter.find('.letterDest .signerNation').text(letterObj.dest.nationName).css(destColorFormat);
        $letter.find('.letterDest .signerName').text(letterObj.dest.generalName).css(destColorFormat);
    }

    if(letterObj.state == 'proposed' && letterObj.src.nationID != myNationID){
        $letter.find('.btnAgree').show().click(function(){
            if(!confirm('승인하시겠습니까?')){
                return false;
            }
            return repondLetter(letterObj.no, true, null);
        });
        $letter.find('.btnDisagree').show().click(function(){
            var reason = prompt('거부하시겠습니까? (이유 [최대 50자])');
            if(reason === null){
                return false;
            }
            reason = substr.substr(0, 50);
            return repondLetter(letterObj.no, false, reason);
        });
    }

    if(letterObj.state == 'proposed' && letterObj.src.nationID == myNationID){
        $letter.find('.btnRollback').show().click(function(){
            if(!confirm('회수하시겠습니까?')){
                return false;
            }
            return rollbackLetter(letterObj.no);
        });
    }

    if(letterObj.state == 'activated'){
        var $btnDestroy = $letter.find('.btnDestroy');
        if((letterObj.src.nationID==myNationID && letterObj.state_opt == 'try_destroy_src') || 
            (letterObj.dest.nationID==myNationID && letterObj.state_opt == 'try_destroy_dest')){
            $btnDestroy.show().prop('disabled', true);
        }
        else{
            $btnDestroy.show().click(function(){
                if(!confirm('본 문서를 파기하겠습니까? (상호 동의 필요)')){
                    return false;
                }
                return destroyLetter(letterObj.no);
            })
            
        }
    }
    

    $letter.find('.btnRenew').click(function(){
        var $inputPrevNo = $('#inputPrevNo');
        $inputPrevNo.val(letterObj.no);
        $inputPrevNo.trigger('change');
    })


    $('#letters').prepend($letter);
}

function initNewLetterForm(lettersObj){
    console.log(lettersObj);
    var nationList = [];
    $.each(lettersObj.nations, function(idx, nation){
        nation.id = nation.nation;
        nation.text = nation.name;
        nationList.push(nation);
    })

    var $destNation = $('#inputDestNation').select2({
        theme: 'bootstrap4',
        placeholder: "",
        language: "ko",
        width:300,
        containerCss: {
            display: "inline-block !important;",
            color: 'white !important'
        },
        data:nationList,
        templateResult: colorNation,
        containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });

    
    var prevNoList = [{
        id:0,
        text:'-새 문서-',
        nation:null,
    }];

    $.each(lettersObj.letters, function(idx, letterObj){
        if(letterObj.state == 'replaced' || letterObj.state == 'cancelled'){
            return true;
        }
        var targetNation = letterObj.src.nationID==myNationID?letterObj.dest:letterObj.src;
        prevNoList.push({
            id:letterObj.no,
            text:'#{0} <{1}>'.format(letterObj.no, targetNation.nationName),
            nation:targetNation.nationID
        });
    });

    var $inputPrevNo = $('#inputPrevNo').select2({
        theme: 'bootstrap4',
        placeholder: "",
        language: "ko",
        width:300,
        containerCss: {
            display: "inline-block !important;",
            color: 'white !important'
        },
        data:prevNoList,
        containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });
    $inputPrevNo.on('change', function(e){
        var data = $inputPrevNo.select2('data')[0];
        console.log(data);
        if(data.nation == null){
            $destNation.prop("disabled", false);
        }
        else{
            $destNation.val(data.nation).prop("disabled", true);
            var $targetLetter = $('#letter_'+data.id);
            resizeTextarea($('#inputBrief').val($targetLetter.data('brief')));
            resizeTextarea($('#inputDetail').val($targetLetter.data('detail')));
        }
    });

    $('#btnSend').click(submitLetter);

    $('#newLetter').show();
}

function drawLetters(lettersObj){
    var deferred = $.Deferred();
    if(!lettersObj){
        return quickReject('받아오는데 실패했습니다.');
    }

    if(!lettersObj.result){
        return quickReject('에러가 발생했습니다. : '+lettersObj.reason);
    }

    window.myNationID = lettersObj.myNationID;

    if(permissionLevel == 4){
        initNewLetterForm(lettersObj);
        $('.letterActionPlate').show();
    }

    $('.letterObj').detach();//첫 버전이니까 일괄 삭제 일괄 로드
    $.each(lettersObj.letters,  drawLetter);
    return true;
}

function loadLetters(){
    return $.post({
        url:'j_diplomacy_get_letter.php',
        dataType:'json', //첫 버전이니까 전체 다 불러오자
        data:{
        }
    });
}

function colorNation(nationInfo){
    if(!nationInfo.color){
        return nationInfo.text;
    }

    var fgColor = isBrightColor(nationInfo.color)?'#000000':'#ffffff';
    var $nationForm = $('<div>'+nationInfo.text+'</div>').css({
        'color':fgColor,
        'background-color':nationInfo.color
    });
    return $nationForm;
}

function resizeTextarea($obj){
    $obj.height(1).height($obj.prop('scrollHeight')+12 );
}

$(function(){

$('textarea.autosize').on('keydown keyup', function(){
    resizeTextarea($(this));
})

loadLetters()
.then(drawLetters, errUnknown)
.fail(function(reason){
    alert(reason);
});

});