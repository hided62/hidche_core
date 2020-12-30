var templateGeneralCard = '<div class="general_card">\
    <h4 class="bg1 with_border"><%generalName%></h4>\
    <h4><img src="<%iconPath%>" height=64 width=64></h4><p>\
    <%if(leadership){%>\
    <%leadership%> / <%strength%> / <%intel%><br>\
    <%}%>\
    <%if(personalText){%><%personalText%><br><%}%>\
    <%if(specialDomesticText||specialWarText){%>\
        <%specialDomesticText%> / <%specialWarText%><br>\
    <%}%>\
    <%if(dex){%><br>\
        보병: <%parseInt(dex[0]/1000)%>K<br>\
        궁병: <%parseInt(dex[1]/1000)%>K<br>\
        기병: <%parseInt(dex[2]/1000)%>K<br>\
        귀병: <%parseInt(dex[3]/1000)%>K<br>\
        차병: <%parseInt(dex[4]/1000)%>K<br>\
    <%}%>\
    </p>\
    <button type="subject" class="with_skin with_border select_btn" data-name="<%generalName%>" value="<%uniqueName%>" name="pick">선택하기</button>\
</div>';

var templateSpecial =
    '<span class="obj_tooltip" data-toggle="tooltip" data-placement="top"><%text%>\
    <span class="tooltiptext">\
        <%info%>\
    </span>\
</span>\
';

function pickGeneral() {
    $btn = $(this);


    if (hasGeneralID) {
        if (!confirm('이 장수를 선택할까요? : {0}'.format($btn.data('name')))) {
            return false;
        }
        $.post({
            url: 'j_update_picked_general.php',
            dataType: 'json',
            data: {
                pick: $btn.val()
            }
        }).then(function(result) {
            if (!result.result) {
                alert(result.reason);
                location.refresh();
            }

            alert('선택한 장수로 변경했습니다.');
            location.href = './';
        });
        return false;
    }

    currentGeneralInfo = cards[$btn.val()];
    var $card = $btn.closest('.general_card');

    var $leftPad = $('#left_pad');
    $leftPad.empty();
    $card.clone().appendTo($leftPad);
    initTooltip($leftPad);

    return false;
}

function buildGeneral() {
    if (!currentGeneralInfo) {
        alert('장수를 선택해주세요!');
        return false;
    }
    if (!confirm('이 장수로 생성할까요?')) {
        return false;
    }
    $.post({
        url: 'j_select_picked_general.php',
        dataType: 'json',
        data: {
            pick: currentGeneralInfo.uniqueName,
            use_own_picture: $('#use_own_picture').is(':checked'),
            leadership: parseInt($('#leadership').val()),
            strength: parseInt($('#leadership').val()),
            intel: parseInt($('#leadership').val()),
            personal: $('#selChar').val()
        }
    }).then(function(result) {
        if (!result.result) {
            alert(result.reason);
            location.refresh();
        }

        alert('선택한 장수로 생성했습니다.');
        location.href = './';
    });
    return false;
}

function updateOutdateTimer() {
    var $validUntilText = $('#valid_until_text');
    var now = Date.now();
    var validUntil = $validUntilText.data('until');
    if (validUntil <= 0) {
        return;
    } else if (validUntil < now) {
        $validUntilText.data('until', 0);
        $('#valid_until').hide();
        $('#outdate_token').show();
        return;
    } else if (validUntil - now <= 30000) {
        $validUntilText.css('color', "rgb(255, {0}, {0})".format(255 * (validUntil - now) / 30000));
    }

    setTimeout(updateOutdateTimer, 1000);
}

function printGenerals(value) {
    $('.card_holder').empty();
    $('#valid_until').show();
    $('#valid_until_text').html(value.validUntil).data('until', (new Date(value.validUntil)).getTime()).css('color', 'white');
    $('#outdate_token').hide();

    var pick = $.map(value.pick, function(value, key) {
        return value;
    });

    var emptyCard = {
        'leadership': null,
        'strength': null,
        'intel': null,
        'personalText': null,
        'specialDomesticText': null,
        'specialWarText': null,
        'dex': null
    };

    $.each(pick, function(idx, cardData) {
        cardData.iconPath = getIconPath(cardData.imgsvr, cardData.picture);
        if (cardData.specialDomestic !== undefined) {
            cardData.specialDomesticText = TemplateEngine(templateSpecial, {
                text: cardData.specialDomesticName,
                info: cardData.specialDomesticInfo
            });
            cardData.specialWarText = '-';
        }

        if (cardData.specialWar !== undefined) {
            cardData.specialWarText = TemplateEngine(templateSpecial, {
                text: cardData.specialWarName,
                info: cardData.specialWarInfo
            });
            if (cardData.specialDomesticText === undefined) {
                cardData.specialDomesticText = '-';
            }
        }

        //FIXME: ego로 적었던것 같음!
        if (cardData.personal in characterInfo) {
            cardData.personalText = TemplateEngine(templateSpecial, {
                text: characterInfo[cardData.personal].name,
                info: characterInfo[cardData.personal].info
            });
        } else {
            cardData.personalText = cardData.personal;
        }

        cards[cardData.uniqueName] = cardData;

        var $card = $(TemplateEngine(templateGeneralCard, $.extend({}, emptyCard, cardData)));

        $('.card_holder').append($card);
        $card.find('.select_btn').click(pickGeneral);
        $card.find('.obj_tooltip').tooltip({
            title: function() {
                return $.trim($(this).find('.tooltiptext').html());
            },
            html: true
        });
    });

    updateOutdateTimer();
}


$(function($) {
    $.post('j_get_select_pool.php').then(function(value) {
        if (!value.result) {
            alert(value.reason);
            return;
        }

        console.log(value);
        printGenerals(value);
    });

    $('#build_general').on('click', buildGeneral);

    $.each(validCustomOption, function(idx, value) {
        if (value == 'picture') {
            $('.custom_picture').show();
        } else if (value == 'ego') {
            $('.custom_personality').show();
        } else if (value == 'stat') {
            $('.custom_stat').show();
        }
    });
});