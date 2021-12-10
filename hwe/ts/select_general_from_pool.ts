import $ from 'jquery';
import axios from 'axios';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { InvalidResponse } from '@/defs';
import { initTooltip } from '@/common_legacy';
import { getIconPath } from "@util/getIconPath";
import { convertFormData } from '@util/convertFormData';
import { unwrap_any } from '@util/unwrap_any';
import { unwrap } from '@util/unwrap';
import { TemplateEngine } from '@util/TemplateEngine';
import { Tooltip } from 'bootstrap';
import { trim } from 'lodash';
type CardItem = {
    uniqueName: string,

    imgsvr: 0|1,
    picture: string,
    iconPath: string,
    specialDomestic?: string,
    specialDomesticName?: string,
    specialDomesticInfo?: string,
    specialDomesticText?: string,

    specialWar?: string,
    specialWarName?: string,
    specialWarInfo?: string,
    specialWarText?: string,

    personal?: string,
    personalText?: string,
}

type GeneralPoolResponse = {
    result: true,
    pick: CardItem[],
    validUntil: string,
}

declare const characterInfo: Record<string, { name: string, info: string }>;
declare const hasGeneralID: number;
declare let currentGeneralInfo: CardItem | undefined;
declare const cards: Record<string, CardItem>;
declare const validCustomOption: string[];

const templateGeneralCard = '<div class="general_card">\
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

const templateSpecial =
    '<span class="obj_tooltip" data-bs-toggle="tooltip" data-placement="top"><%text%>\
    <span class="tooltiptext">\
        <%info%>\
    </span>\
</span>\
';

function showPickedGeneral($btn: JQuery<HTMLElement>) {
    currentGeneralInfo = cards[unwrap_any<string>($btn.val())];
    const $card = $btn.closest('.general_card');

    const $leftPad = $('#left_pad');
    $leftPad.empty();
    $card.clone().appendTo($leftPad);
    initTooltip($leftPad);
}

async function pickGeneral(this: HTMLElement, e: JQuery.Event) {
    e.preventDefault();

    const $btn = $(this);

    if (!hasGeneralID) {
        showPickedGeneral($btn);
        return;
    }

    if (!confirm(`이 장수를 선택할까요? : ${$btn.data('name')}`)) {
        return false;
    }

    let result: InvalidResponse;
    try {
        const response = await axios({
            url: 'j_update_picked_general.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                pick: unwrap_any<string>($btn.val())
            })
        });
        result = response.data;
        if (!result.result) {
            throw result.reason;
        }
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        location.reload();
        return;
    }
    alert('선택한 장수로 변경했습니다.');
    location.href = './';
}

async function buildGeneral(e: JQuery.Event) {
    e.preventDefault();
    if (!currentGeneralInfo) {
        alert('장수를 선택해주세요!');
        return;
    }
    if (!confirm('이 장수로 생성할까요?')) {
        return;
    }

    let result: InvalidResponse;
    try {
        const response = await axios({
            url: 'j_select_picked_general.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                pick: unwrap(currentGeneralInfo).uniqueName,
                use_own_picture: $('#use_own_picture').is(':checked'),
                leadership: parseInt(unwrap_any<string>($('#leadership').val())),
                strength: parseInt(unwrap_any<string>($('#leadership').val())),
                intel: parseInt(unwrap_any<string>($('#leadership').val())),
                personal: unwrap_any<string>($('#selChar').val())
            })
        })
        result = response.data;
        if (!result.result) {
            throw result.reason;
        }
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        location.reload();
        return;
    }

    alert('선택한 장수로 생성했습니다.');
    location.href = './';
}

function updateOutdateTimer() {
    const $validUntilText = $('#valid_until_text');
    const now = Date.now();
    const validUntil = $validUntilText.data('until');
    if (validUntil <= 0) {
        return;
    } else if (validUntil < now) {
        $validUntilText.data('until', 0);
        $('#valid_until').hide();
        $('#outdate_token').show();
        return;
    } else if (validUntil - now <= 30000) {
        const colorVal = 255 * (validUntil - now) / 30000;
        $validUntilText.css('color', `rgb(255, ${colorVal}, ${colorVal})`);
    }

    setTimeout(updateOutdateTimer, 1000);
}

function printGenerals(value: GeneralPoolResponse) {
    $('.card_holder').empty();
    $('#valid_until').show();
    $('#valid_until_text').html(value.validUntil).data('until', (new Date(value.validUntil)).getTime()).css('color', 'white');
    $('#outdate_token').hide();

    const pick = value.pick.map(v => v);//XXX: 의도가 뭐였지? clone?

    const emptyCard = {
        'leadership': null,
        'strength': null,
        'intel': null,
        'personalText': null,
        'specialDomesticText': null,
        'specialWarText': null,
        'dex': null
    };

    for (const cardData of pick) {
        cardData.iconPath = getIconPath(cardData.imgsvr, cardData.picture);
        if (cardData.specialDomestic !== undefined) {
            cardData.specialDomesticText = TemplateEngine(templateSpecial, {
                text: unwrap(cardData.specialDomesticName),
                info: unwrap(cardData.specialDomesticInfo)
            });
            cardData.specialWarText = '-';
        }

        if (cardData.specialWar !== undefined) {
            cardData.specialWarText = TemplateEngine(templateSpecial, {
                text: unwrap(cardData.specialWarName),
                info: unwrap(cardData.specialWarInfo)
            });
            if (cardData.specialDomesticText === undefined) {
                cardData.specialDomesticText = '-';
            }
        }

        //FIXME: ego로 적었던것 같음!
        if (cardData.personal && cardData.personal in characterInfo) {
            cardData.personalText = TemplateEngine(templateSpecial, {
                text: unwrap(characterInfo[cardData.personal]).name,
                info: unwrap(characterInfo[cardData.personal]).info
            });
        } else {
            cardData.personalText = cardData.personal;
        }

        cards[cardData.uniqueName] = cardData;

        const $card = $(TemplateEngine(templateGeneralCard, $.extend({}, emptyCard, cardData)));

        $('.card_holder').append($card);
        $card.find('.select_btn').on('click', pickGeneral);

        $card.find('.obj_tooltip').each(function(){
            new Tooltip(this, {
                title: function () {
                    return trim(this.querySelector('.tooltiptext')?.innerHTML);
                },
                html: true
            })
        });
    }

    updateOutdateTimer();
}


$(async function ($) {
    setAxiosXMLHttpRequest();

    let result: InvalidResponse | GeneralPoolResponse;
    try {
        const response = await axios({
            url: 'j_get_select_pool.php',
            method: 'post',
            responseType: 'json',
        });
        result = response.data;
        if (!result.result) {
            throw result.reason;
        }
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
    }

    printGenerals(result);

    if (hasGeneralID) {
        $('#create_plate').hide();
    }

    $('#build_general').on('click', buildGeneral);

    for (const value of validCustomOption) {
        if (value == 'picture') {
            $('.custom_picture').show();
        } else if (value == 'ego') {
            $('.custom_personality').show();
        } else if (value == 'stat') {
            $('.custom_stat').show();
        }
    }
});