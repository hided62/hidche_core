

function submitLetter(){
    var $letter = $('#newLetter');
    var $brief = $letter.find('input.briefInput');
    var $detail = $letter.find('input.detailInput');
    var $prevNo = $letter.find('input.prevNo');
    var $destNation = $letter.find('input.destNation');
    var brief = $.trim($brief.val());
    var detail = $.trim($detail.val());
    var prevNo = parseInt($prevNo.val());
    var destNation = parseInt($destNation.val());
    
    if(prevNo !== null && prevNo < 1){
        prevNo = null;
    }


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
            $title.val(title);
            $text.val(text);
            alert()
            return quickReject('외교 서신을 보내는데 실패했습니다.');
        }
        if(!data.result){
            $title.val(title);
            $text.val(text);
            return quickReject('외교 서신을 보내는데 실패했습니다. : '+data.reason);
        }

        return loadLetters().done(drawLetters);

    }, errUnknown)
    .fail(function(reason){
        alert(reason);
    });

    return false;
}

function repondLetter(isAgree){
    var $this = $(this);
    var $letter = $this.parents('.letter').eq(0);
    var letterNo = $letter.data('no');
    
    $.post({
        url:'j_diplomacy_respond_letter.php',
        dataType:'json',
        data:{
            letterNo:letterNo,
            isAgree:isAgree,
            reason:'', //TODO: reason 받기
        }
    }).then(function(data){
        if(!data){
            $text.val(text);
            alert()
            return quickReject('응답을 실패했습니다.');
        }
        if(!data.result){
            $text.val(text);
            return quickReject('응답을 실패했습니다. : '+data.reason);
        }

        return loadArticles().done(drawLetters);

    }, errUnknown)
    .fail(function(reason){
        alert(reason);
    });

    return false;
}

function drawLetter(letterObj){
    var $letterFrame = $('#letterTemplate > .letterFrame');

    var $letter = $letterFrame.clone();
    $letter.addClass('letterObj')
        .data('no', letterObj.no)

    //TODO: 국가명, 수뇌명 입력
    $letter.find('.date').text(letterObj.date);
    $letter.find('.letterNo').text('#'+letterObj.no);
    $letter.find('.srcNation').text(letterObj.aux[''])
    $letter.find('.brief').html(nl2br(escapeHtml(letterObj.brief)));
    $letter.find('.detail').html(nl2br(escapeHtml(letterObj.detail)));
    //TODO: 바꿀 것

    $('#board').append($letter);
}

function drawLetters(lettersObj){
    var deferred = $.Deferred();
    if(!lettersObj){
        return quickReject('받아오는데 실패했습니다.');
    }

    //TODO: 국가 리스트 출력

    if(!lettersObj.result){
        return quickReject('에러가 발생했습니다. : '+lettersObj.reason);
    }

    $('.letterObj').detach();//첫 버전이니까 일괄 삭제 일괄 로드
    $.each(lettersObj.letters,  drawLetter);
    return true;
}

function loadLetters(){
    return $.post({
        url:'j_diplomacy_get_letters.php',
        dataType:'json', //첫 버전이니까 전체 다 불러오자
        data:{
        }
    });
}

$(function(){

$('#submitLetter').click(submitLetter);
$('.respondAgree').click(function(){
    return repondLetter(true);
});
$('.respondDisagree').click(function(){
    return repondLetter(false);
});

loadLetters()
.then(drawLetters, errUnknown)
.fail(function(reason){
    alert(reason);
});

});