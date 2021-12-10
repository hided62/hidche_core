import axios from 'axios';
import { errUnknown } from '@/common_legacy';
import { getIconPath } from "@util/getIconPath";
import { TemplateEngine } from "@util/TemplateEngine";
import { GeneralListResponse, InvalidResponse } from '@/defs';
import { convertFormData } from '@util/convertFormData';
import { unwrap_any } from '@util/unwrap_any';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { Tooltip } from 'bootstrap';
import { trim } from 'lodash';

setAxiosXMLHttpRequest();

declare const specialInfo: Record<string, string>;
declare const characterInfo: Record<string, string>;

declare global {
    interface Window {
        generalList: NPCPickPrintable[],
    }
}

type NPCPick = {
    no: number,
    name: string,
    leadership: number,
    strength: number,
    intel: number,
    nation: string,
    imgsvr: 0 | 1,
    picture: string,
    personal: string,
    special: string,
    special2: string,

    iconPath?: string,
    specialText?: string,
    special2Text?: string,
    personalText?: string,
}

type NPCPickPrintableR = {
    no: number,
    picture: string,
    imgsvr: 0 | 1,
    npc: number,
    age: number,
    nation: string,
    special: string,
    special2: string,
    personal: string,
    name: string,
    ownerName: string | null,
    injury: number,
    leadership: number,
    lbonus: number,
    strength: number,
    intel: number,
    explevel: number,
    experience: string,
    dedication: string,
    officerLevel: string,
    killturn: number,
    connect: number,
    reserved: number,

    userCSS?: string,
    nameAux?: string,
    total?: number,
    iconPath?: string,
    specialDomesticWithTooltip?: string,
    specialWarWithTooltip?: string,
    personalWithTooltip?: string,
}

type NPCPickPrintable = NPCPickPrintableR & {
    userCSS: string,
    nameAux: string,
    total: number,
    iconPath: string,
    specialDomesticWithTooltip: string,
    specialWarWithTooltip: string,
    personalWithTooltip: string,
}

type NPCToken = {
    result: true,
    pick: Record<number, NPCPick>,
    pickMoreFrom: string,
    pickMoreSeconds: number,
    validUntil: string,
}

const templateGeneralCard =
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

const templateSpecial =
    '<span class="obj_tooltip" data-bs-toggle="tooltip" data-placement="top"><%text%>\
    <span class="tooltiptext">\
        <%info%>\
    </span>\
</span>\
';

const templateGeneralRow =
    '<tr>\
    <td><img class="generalIcon" width="64" height="64" src="<%iconPath%>"></td>\
    <td style="<%userCSS%>"><%name%><%nameAux%></td>\
    <td><%age%>세</td>\
    <td><%personalWithTooltip%></td>\
    <td><%specialDomesticWithTooltip%> / <%specialWarWithTooltip%></td>\
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

async function pickGeneral(this: HTMLElement, e: JQuery.Event) {
    e.preventDefault();
    const $btn = $(this);

    if (!confirm(`빙의할까요? : ${$btn.data('name')}`)) {
        return;
    }

    let result: InvalidResponse;
    try {
        const response = await axios({
            url: 'j_select_npc.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                pick: unwrap_any<string>($btn.val()),
            })
        });
        result = response.data;
    }
    catch (e) {
        alert(`알 수 없는 에러: ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        location.reload();
        return;
    }

    alert('빙의에 성공했습니다.');
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
        const remainColor = 255 * (validUntil - now) / 30000;
        $validUntilText.css('color', `rgb(255, ${remainColor}, ${remainColor})`);
    }

    setTimeout(updateOutdateTimer, 1000);
}

function updatePickMoreTimer() {
    const $btn = $('#btn_pick_more');

    const now = Date.now();
    const remain = ($btn.data('available') - now) / 1000;
    if (remain <= 0) {
        $btn.prop('disabled', false)
        $btn.html('다른 장수 보기');
        return;
    }

    $btn.html(`다른 장수 보기(${Math.ceil(remain)}초)`);

    setTimeout(updatePickMoreTimer, 250);
}

function printGenerals(value: NPCToken) {
    $('.card_holder').empty();
    $('#valid_until').show();
    $('#valid_until_text').html(value.validUntil).data('until', (new Date(value.validUntil)).getTime()).css('color', 'white');
    $('#outdate_token').hide();
    const time = Date.now() + value.pickMoreSeconds * 1000;
    $('#btn_pick_more').data('available', time).prop('disabled', true);

    const pick = $.map(value.pick, function (value) {
        return value;
    });

    pick.sort(function (lhs, rhs) {
        const lsum = lhs.leadership + lhs.strength + lhs.intel;
        const rsum = rhs.leadership + rhs.strength + rhs.intel;
        return lsum - rsum;
    });

    $.each(pick, function (idx, cardData: NPCPick) {
        cardData.iconPath = getIconPath(cardData.imgsvr, cardData.picture);
        if (cardData.special in specialInfo) {
            cardData.specialText = TemplateEngine(templateSpecial, {
                text: cardData.special,
                info: specialInfo[cardData.special]
            });
        } else {
            cardData.specialText = cardData.special;
        }

        if (cardData.special2 in specialInfo) {
            cardData.special2Text = TemplateEngine(templateSpecial, {
                text: cardData.special2,
                info: specialInfo[cardData.special2]
            });
        } else {
            cardData.special2Text = cardData.special2;
        }

        if (cardData.personal in characterInfo) {
            cardData.personalText = TemplateEngine(templateSpecial, {
                text: cardData.personal,
                info: characterInfo[cardData.personal]
            });
        } else {
            cardData.personalText = cardData.personal;
        }


        const $card = $(TemplateEngine(templateGeneralCard, cardData));

        $('.card_holder').append($card);
        $card.find('.select_btn').on('click', pickGeneral);
        $card.find('.obj_tooltip').each(function(){
            new Tooltip(this, {
                title: function () {
                    return trim(this.querySelector('.tooltiptext')?.innerHTML);
                },
                html: true
            });
        });
    });

    updatePickMoreTimer();
    updateOutdateTimer();
}

function printGeneralList(value: GeneralListResponse) {
    const tokenList = value.token;
    const generalList:NPCPickPrintable[] = value.list.map((rawGeneral) => {
        const general: NPCPickPrintableR = {
            no: rawGeneral[0],
            picture: rawGeneral[1],
            imgsvr: rawGeneral[2],
            npc: rawGeneral[3],
            age: rawGeneral[4],
            nation: rawGeneral[5],
            special: rawGeneral[6],
            special2: rawGeneral[7],
            personal: rawGeneral[8],
            name: rawGeneral[9],
            ownerName: rawGeneral[10],
            injury: rawGeneral[11],
            leadership: rawGeneral[12],
            lbonus: rawGeneral[13],
            strength: rawGeneral[14],
            intel: rawGeneral[15],
            explevel: rawGeneral[16],
            experience: rawGeneral[17],
            dedication: rawGeneral[18],
            officerLevel: rawGeneral[19],
            killturn: rawGeneral[20],
            connect: rawGeneral[21],
            reserved: 0
        };
        if (general.npc < 2) {
            general.reserved = 2;
        }
        if (general.no in tokenList) {
            general.reserved = 1;
        }

        general.userCSS = "";
        general.nameAux = "";

        if (general.reserved == 1) {
            general.userCSS = 'color:violet';
        } else if (general.npc >= 2) {
            general.userCSS = 'color:cyan';
        } else if (general.npc == 1) {
            general.userCSS = 'color:skyblue';
        }

        if (general.ownerName) {
            general.nameAux += `<br><small>(${general.ownerName})</small>`;
        }

        if (general.reserved == 1) {
            general.nameAux +=  `<br><small>(${tokenList[general.no]}회)</small>`;
        }


        general.total = general.leadership + general.strength + general.intel;
        general.iconPath = getIconPath(general.imgsvr, general.picture);

        general.specialDomesticWithTooltip = TemplateEngine(templateSpecial, {
            text: general.special,
            info: specialInfo[general.special]
        });

        general.specialWarWithTooltip = TemplateEngine(templateSpecial, {
            text: general.special2,
            info: specialInfo[general.special2]
        });

        general.personalWithTooltip = TemplateEngine(templateSpecial, {
            text: general.personal,
            info: characterInfo[general.personal]
        });

        return general as NPCPickPrintable;
    });

    generalList.sort(function (lhs, rhs) {
        if (lhs.reserved > rhs.reserved) {
            return -1;
        }
        if (lhs.reserved < rhs.reserved) {
            return 1;
        }
        if (lhs.total != rhs.total) {
            return rhs.total - lhs.total;
        }
        if (lhs.leadership != rhs.leadership) {
            return rhs.leadership - lhs.total;
        }

        if (lhs.name < rhs.name) {
            return -1;
        }
        if (lhs.name > rhs.name) {
            return 1;
        }
        return 0;
    });

    window.generalList = generalList;
    _printGeneralList(true);
}

function _printGeneralList(clear?:boolean) {
    const $generalTable = $('#general_list');
    if (clear) {
        $generalTable.empty();
        $generalTable.data('lastIdx', 0);
        $('#row_print_more').show();
    }

    const generalList = window.generalList;

    const idxFrom = $generalTable.data('lastIdx');
    const idxTo = Math.min(idxFrom + 50, generalList.length);
    $generalTable.data('lastIdx', idxTo);

    for (let idx = idxFrom; idx < idxFrom + 50; idx++) {
        const general = generalList[idx];
        $generalTable.append(TemplateEngine(templateGeneralRow, general));
    }

    if (idxTo == generalList.length) {
        $('#row_print_more').hide();
    }

    $generalTable.find('.obj_tooltip').each(function(){
        new Tooltip(this, {
            title: function () {
                return trim(this.querySelector('.tooltiptext')?.innerHTML);
            },
            html: true
        })
    });
    $('#tb_general_list').show();
}

$(function ($) {
    window.generalList = [];

    $.post('j_get_select_npc_token.php').then(function (value) {
        if (!value.result) {
            alert(value.reason);
            return;
        }

        console.log(value);
        printGenerals(value);
    }, errUnknown);

    $('#btn_pick_more').click(function () {
        const generals = $<HTMLInputElement>('.keep_select:checked').map(function () {
            return unwrap_any<string>($(this).val());
        }).toArray();
        console.log(generals);
        $.post({
            url: 'j_get_select_npc_token.php',
            dataType: 'json',
            data: {
                refresh: true,
                keep: generals
            }
        }).then(function (result) {
            if (!result.result) {
                alert(result.reason);
                location.reload();
            }
            console.log(result);
            printGenerals(result);
        }, errUnknown);
    });

    $('#btn_load_general_list').click(function () {
        $.post({
            url: 'j_get_general_list.php',
            dataType: 'json',
            data: {
                with_token: true
            }
        }).then(function (result) {
            if (!result.result) {
                alert(result.reason);
                return false;
            }
            printGeneralList(result);
        }, errUnknown);
    });

    $('#btn_print_more').click(function () {
        _printGeneralList();
    })

});