var templateGeneralCard = 
'<div class="general_card">\
    <h4 class="bg1 with_border"><%name%></h4>\
    <h4><img src="<%iconPath%>" height=64 width=64></h4>\
    <p><%leadership%> / <%strength%> / <%intel%><br>\
    <%nation%><br>\
    <%personalText%><br>\
    <%specialText%> / <%special2Text%></p>\
    <button type="subject" class="with_skin with_border select_btn" data-name="<%name%>" value="<%no%>" name="pick">빙의하기</button>\
    <label><input <%keepCnt?"":disabled="disabled"%> type="checkbox" value="<%no%>" name="keep[]" class="keep_select">보관(<%keepCnt%>회)</label>\
</div>';

var templateSpecial = 
'<span class="obj_tooltip" data-toggle="tooltip" data-placement="top"><%text%>\
    <span class="tooltiptext">\
        <%info%>\
    </span>\
</span>\
';

var templateGeneralRow = 
'<tr>\
    <td><img class="generalIcon" width="64" height="64" src="<%iconPath%>"></td>\
    <td style="<%userCSS%>"><%name%><%nameAux%></td>\
    <td><%age%>세</td>\
    <td><%personalWithTooltip%></td>\
    <td><%specialDomesticWithTooltip%> / <%speicalWarWithTooltip%></td>\
    <td>Lv <%explevel%></td>\
    <td><%nation%></td>\
    <td><%experience%></td>\
    <td><%dedication%></td>\
    <td><%officerLevel%></td>\
    <td><%total%></td>\
    <td><%leadership%></td>\
    <td><%strength%></td>\
    <td><%intel%></td>\
    <td><%killturn%></td>\
</tr>';

function pickGeneral(){
    $btn = $(this);

    if (!confirm('빙의할까요? : {0}'.format($btn.data('name')))) {
        return false;
    }

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
        var lsum = lhs.leadership+lhs.strength+lhs.intel;
        var rsum = rhs.leadership+rhs.strength+rhs.intel;
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

        if(cardData.personal in characterInfo){
            cardData.personalText = TemplateEngine(templateSpecial, {
                text:cardData.personal,
                info:characterInfo[cardData.personal]
            });
        }
        else{
            cardData.personalText = cardData.personal;
        }
        

        var $card = $(TemplateEngine(templateGeneralCard, cardData));

        $('.card_holder').append($card);
        $card.find('.select_btn').click(pickGeneral);
        $card.find('.obj_tooltip').tooltip({
            title:function(){
                return $.trim($(this).find('.tooltiptext').html());
            },
            html:true
        });
    });

    updatePickMoreTimer();
    updateOutdateTimer();
}

function printGeneralList(value){
    var tokenList = value.token;
    var generalList = $.map(value.list, function(general){
        general = {
            no:general[0],
            picture:general[1],
            imgsvr:general[2],
            npc:general[3],
            age:general[4],
            nation:general[5],
            special:general[6],
            special2:general[7],
            personal:general[8],
            name:general[9],
            ownerName:general[10],
            injury:general[11],
            leadership:general[12],
            lbonus:general[13],
            strength:general[14],
            intel:general[15],
            explevel:general[16],
            experience:general[17],
            dedication:general[18],
            officerLevel:general[19],
            killturn:general[20],
            connect:general[21],
            reserved:0
        };
        if(general.npc < 2){
            general.reserved = 2;
        }
        if(general.no in tokenList){
            general.reserved = 1;
        }

        general.userCSS = "";
        general.nameAux = "";

        if(general.reserved == 1){
            general.userCSS = 'color:violet';
        }
        else if(general.npc >= 2){
            general.userCSS = 'color:cyan';
        }
        else if(general.npc == 1){
            general.userCSS = 'color:skyblue';
        }

        if(general.ownerName){
            general.nameAux += '<br><small>({0})</small>'.format(general.ownerName);
        }

        if(general.reserved == 1){
            general.nameAux += '<br><small>({0}회)</small>'.format(tokenList[general.no]);
        }


        general.total = general.leadership + general.strength + general.intel;
        general.iconPath = getIconPath(general.imgsvr, general.picture);

        general.specialDomesticWithTooltip = TemplateEngine(templateSpecial, {
            text:general.special,
            info:specialInfo[general.special]
        });

        general.speicalWarWithTooltip = TemplateEngine(templateSpecial, {
            text:general.special2,
            info:specialInfo[general.special2]
        });

        general.personalWithTooltip = TemplateEngine(templateSpecial, {
            text:general.personal,
            info:characterInfo[general.personal]
        });

        return general;
    });

    generalList.sort(function(lhs, rhs){
        if(lhs.reserved > rhs.reserved){
            return -1;
        }
        if(lhs.reserved < rhs.reserved){
            return 1;
        }
        if(lhs.total != rhs.total){
            return rhs.total - lhs.total;
        }
        if(lhs.leadership != rhs.leadership){
            return rhs.leadership - lhs.total;
        }
        
        if(lhs.name < rhs.name){
            return -1;
        }
        if(lhs.name > rhs.name){
            return 1;
        }
        return 0;
    });

    window.generalList = generalList;
    _printGeneralList(true);
}

function _printGeneralList(clear){
    var $generalTable = $('#general_list');
    if(clear){
        $generalTable.empty();
        $generalTable.data('lastIdx', 0);
        $('#row_print_more').show();
    }

    generalList = window.generalList;

    var idxFrom = $generalTable.data('lastIdx');
    var idxTo = Math.min(idxFrom + 50, generalList.length);
    $generalTable.data('lastIdx', idxTo);

    for(var idx = idxFrom; idx < idxFrom + 50; idx++){
        var general = generalList[idx];
        $generalTable.append(TemplateEngine(templateGeneralRow, general));
    }

    if(idxTo == generalList.length){
        $('#row_print_more').hide();
    }

    $generalTable.find('.obj_tooltip').tooltip({
        title:function(){
            return $.trim($(this).find('.tooltiptext').html());
        },
        html:true
    });
    $('#tb_general_list').show();
}

$(function($){
window.generalList = [];

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

$('#btn_load_general_list').click(function(){
    $.post({
        url:'j_get_general_list.php',
        dataType:'json',
        data:{
            with_token:true
        }
    }).then(function(result){
        if(!result.result){
            alert(result.reason);
            return false;
        }
        printGeneralList(result);
    });
});

$('#btn_print_more').click(function(){
    _printGeneralList();
})

});