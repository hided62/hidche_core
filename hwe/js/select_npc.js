var templateGeneralCard = 
'<div class="general_card">\
    <h4 class="bg1 with_border"><%name%></h4>\
    <h4><img src="<%iconPath%>" height=64 width=64></h4>\
    <p><%leader%> / <%power%> / <%intel%><br>\
    <%special%> / <%special2%></p>\
    <button type="subject" class="with_skin with_border select_btn" value="<%no%>" name="pick">빙의하기</button>\
    <label><input <%keepCnt?"":disabled="disabled"%> type="checkbox" value="<%no%>" name="keep[]" class="keep_select">보관(<%keepCnt%>회)</label>\
</div>';

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
    $('#valid_until_text').html(value.validUntil);
    $('#btn_pick_more').data('available', new Date(value.pickMoreFrom).getTime()).prop('disabled',true);
    $.each(value.pick, function(idx, cardData){
        cardData.iconPath = getIconPath(cardData.imgsvr, cardData.picture);
        console.log(cardData);

        var $card = $(TemplateEngine(templateGeneralCard, cardData));
        console.log($card);

        $('.card_holder').append($card);
    });

    updatePickMoreTimer();
}

jQuery(function($){

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
        printGenerals(result);
    });
});

});