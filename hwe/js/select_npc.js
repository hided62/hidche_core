var templateGeneralCard = 
'<div class="general_card">\
    <h4 class="bg1 with_border"><%name%></h4>\
    <h4><img src="<%iconPath%>" height=64 width=64></h4>\
    <p><%leader%> / <%power%> / <%intel%><br>\
    <%specialText%> / <%special2Text%></p>\
    <button type="subject" class="with_skin with_border select_btn" value="<%no%>" name="pick">빙의하기</button>\
    <label><input <%keepCnt?"":disabled="disabled"%> type="checkbox" value="<%no%>" name="keep[]" class="keep_select">보관(<%keepCnt%>회)</label>\
</div>';

var templateSpecial = 
'<span class="obj_tooltip" data-toggle="tooltip" data-placement="top"><%text%>\
    <span class="tooltiptext">\
        <%info%>\
    </span>\
</span>\
';

function pickGeneral(){
    $btn = $(this);

    $.post({
        url:'j_select_npc.php',
        dataType:'json',
        data:{
            pick:$btn.val()
        }
    }).then(function(result){
        if(!result.result){
            alert(result.reason);
            location.refresh();
        }

        alert('빙의에 성공했습니다.');
        location.href = './';
    });
    return false;
}

function updateOutdateTimer(){
    var $validUntilText = $('#valid_until_text');
    var now = Date.now();
    var validUntil = $validUntilText.data('until');
    if(validUntil <= 0){
        return;
    }
    else if(validUntil < now){
        $validUntilText.data('until',0);
        $('#valid_until').hide();
        $('#outdate_token').show();
        return;
    }
    else if(validUntil - now <= 30000){
        $validUntilText.css('color', "rgb(255, {0}, {0})".format(255*(validUntil - now)/30000)); 
    }

    setTimeout(updateOutdateTimer, 1000);
}

function updatePickMoreTimer(){
    var $btn = $('#btn_pick_more');
    
    var now = Date.now();
    var remain = ($btn.data('available') - now) / 1000;
    if(remain <= 0){
        $btn.prop('disabled', false)
        $btn.html('다른 장수 보기');
        return;
    }

    $btn.html('다른 장수 보기({0}초)'.format(Math.ceil(remain)));

    setTimeout(updatePickMoreTimer, 250);
}

function printGenerals(value){
    $('.card_holder').empty();
    $('#valid_until').show();
    $('#valid_until_text').html(value.validUntil).data('until', (new Date(value.validUntil)).getTime()).css('color','white');
    $('#outdate_token').hide();
    var time = Date.now() + value.pickMoreSeconds*1000;
    $('#btn_pick_more').data('available', time).prop('disabled',true);

    var pick = $.map(value.pick, function(value, key) {
        return value;
    });

    pick.sort(function(lhs, rhs){
        var lsum = lhs.leader+lhs.power+lhs.intel;
        var rsum = rhs.leader+rhs.power+rhs.intel;
        return lsum - rsum;
    });
    
    $.each(pick, function(idx, cardData){
        cardData.iconPath = getIconPath(cardData.imgsvr, cardData.picture);
        if(cardData.special in specialInfo){
            cardData.specialText = TemplateEngine(templateSpecial, {
                text:cardData.special,
                info:specialInfo[cardData.special]
            });
        }
        else{
            cardData.specialText = cardData.special;
        }

        if(cardData.special2 in specialInfo){
            cardData.special2Text = TemplateEngine(templateSpecial, {
                text:cardData.special2,
                info:specialInfo[cardData.special2]
            });
        }
        else{
            cardData.special2Text = cardData.special2;
        }
        

        var $card = $(TemplateEngine(templateGeneralCard, cardData));
        console.log($card);

        $('.card_holder').append($card);
        $card.find('.select_btn').click(pickGeneral);
        $card.find('.obj_tooltip').tooltip({
            title:function(){
                return $(this).find('.tooltiptext').html();
            },
            html:true
        });
    });

    updatePickMoreTimer();
    updateOutdateTimer();
}

$(function($){

$.post('j_get_select_npc_token.php').then(function(value){
    if(!value.result){
        alert(value.reason);
        return;
    }

    console.log(value);
    printGenerals(value);
});

$('#btn_pick_more').click(function(){
    var generals = $.map($('.keep_select:checked'), function(value){
        return $(value).val();
    });
    console.log(generals);
    $.post({
        url:'j_get_select_npc_token.php',
        dataType:'json',
        data:{
            refresh:true,
            keep:generals
        }
    }).then(function(result){
        if(!result.result){
            alert(result.reason);
            location.refresh();
        }
        console.log(result);
        printGenerals(result);
    });
});

});