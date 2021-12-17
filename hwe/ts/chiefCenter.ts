import axios from 'axios';
import $ from 'jquery';
import { range } from 'lodash';
import { addMinutes } from 'date-fns';
import { errUnknown, getNpcColor } from '@/common_legacy';
import { InvalidResponse } from '@/defs';
import { unwrap } from "@util/unwrap";
import { convertFormData } from '@util/convertFormData';
import { unwrap_any } from "@util/unwrap_any";
import { parseTime } from '@util/parseTime';
import { formatTime } from '@util/formatTime';
import { stringifyUrl } from 'query-string';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';

setAxiosXMLHttpRequest();

declare const maxChiefTurn: number;

type TurnDOMObj = {
    turnTime: JQuery<HTMLElement>,
    turnPad: JQuery<HTMLElement>,
    turnText: JQuery<HTMLElement>
};

type TableObj = {
    btns: JQuery<HTMLElement>;
    [key: number]: {
        officerLevelText: JQuery<HTMLElement>,
        name: JQuery<HTMLElement>,
        turn: TurnDOMObj[],
    }
}

type ChiefResponse = InvalidResponse | {
    result: true,
    date: string,
    nationTurnBrief: {
        name: string | null,
        turnTime: string | null,
        officerLevelText: string,
        npcType: number,
        turn: string
    }[],
    isChief: boolean,
    turnTerm: number
}

let filledChiefList: Record<number, boolean> = {};
let chiefTableObj: TableObj = undefined as unknown as TableObj;//TODO: 매우 지저분하다. class 기반으로 고치던가 할 것

/*
function clearTable() {
    $('.chiefLevelText').html('-');
    $('.chiefTurnTime, .chiefTurnText, .chiefName').html('&nbsp;');
}
*/

function genChiefTableObj(): TableObj {
    const objTable: TableObj = {
        btns: $('#turnPush,#turnPull,#setCommand')
    };

    for (const chiefIdx of range(5, 13)) {
        const $plate = $(`#chief_${chiefIdx}`);
        const $officerLevelText = $plate.find('.chiefLevelText');
        const $name = $plate.find('.chiefName');
        const turn: TurnDOMObj[] = [];
        for (const turnIdx of range(maxChiefTurn)) {
            const $turn = $plate.find(`.turn${turnIdx}`);
            const $turnTime = $turn.find('.chiefTurnTime');
            const $turnPad = $turn.find('.chiefTurnPad');
            const $turnText = $turn.find('.chiefTurnText');
            turn.push({ turnTime: $turnTime, turnPad: $turnPad, turnText: $turnText });
        }
        objTable[chiefIdx] = {
            officerLevelText: $officerLevelText,
            name: $name,
            turn: turn
        };
    }

    return objTable;
}



function clearChief(chiefIdx: number): void {
    const $plate = $(`#chief_${chiefIdx}`);
    $plate.find('.chiefLevelText').html('-');
    $plate.find('.chiefTurnTime, .chiefTurnText, .chiefName').html('&nbsp;');
}

async function reloadTable() {

    const data: ChiefResponse = await (async () => {
        try {
            const response = await axios({
                url: 'j_getChiefTurn.php',
                responseType: 'json'
            });
            return response.data;
        }
        catch (e) {
            console.error(e);
            errUnknown();
        }
    })();

    if (!data.result) {
        alert(data.reason);
        return;
    }
    const turnTerm = data.turnTerm;
    const tmpFilledChiefList: Record<number, boolean> = {};

    if (data.isChief) {
        chiefTableObj.btns.css('visibility', 'visible');
    }
    else {
        chiefTableObj.btns.css('visibility', 'hidden');
    }
    $.each(data.nationTurnBrief, function (chiefIdx, chiefInfo) {
        tmpFilledChiefList[chiefIdx] = true;
        filledChiefList[chiefIdx] = true;

        const plateObj = chiefTableObj[chiefIdx];
        if (chiefInfo.name) {
            const $name = $(`<span>${chiefInfo.name}</span>`);
            const nameColor = getNpcColor(chiefInfo.npcType);
            if (nameColor) {
                $name.css('color', nameColor);
            }
            plateObj.name.empty().append($name);
        }
        else {
            plateObj.name.html('');
        }

        plateObj.officerLevelText.text(chiefInfo.officerLevelText);

        let turnTimeObj: Date | undefined;

        if (chiefInfo.turnTime) {
            turnTimeObj = parseTime(chiefInfo.turnTime);
        }

        const turnList = plateObj.turn;
        $.each(chiefInfo.turn, function (turnIdx, turnText) {
            if (turnTimeObj) {
                turnList[turnIdx].turnTime.text(formatTime(turnTimeObj, 'HH:mm'));
            }
            else {
                turnList[turnIdx].turnTime.text('');
            }

            turnList[turnIdx].turnText.html(turnText).css('font-size', '14px');
            const oWidth = unwrap(turnList[turnIdx].turnPad.innerWidth());
            const iWidth = unwrap(turnList[turnIdx].turnText.outerWidth());
            if (iWidth > oWidth * 0.95) {
                const newFontSize = 14 * oWidth / iWidth * 0.9;
                turnList[turnIdx].turnText.css('font-size', `${newFontSize}px`);
            }
            if (turnTimeObj) {
                turnTimeObj = addMinutes(turnTimeObj, turnTerm);
            }

        });
    });

    for (const idx of range(5, 13)) {
        if (idx in tmpFilledChiefList) {
            continue;
        }
        if (idx in filledChiefList) {
            clearChief(idx);
        }
    }
    filledChiefList = tmpFilledChiefList;
}

async function reserveTurn(turnList: number[], command: string) {
    console.log(turnList, command);
    try {

        const response = await axios({
            url: 'j_set_chief_command.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                turnList,
                action: command
            })
        });

        const data: InvalidResponse = response.data;

        if (!data.result) {
            alert(data.reason);
        }
        await reloadTable();
    }
    catch (e) {
        console.error(e);
        errUnknown();
    }
}

async function pushTurn(turnCnt: number) {
    try {
        const response = await axios({
            url: 'j_chief_turn.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                amount: turnCnt
            })
        });
        const data = response.data;
        if (!data.result) {
            alert(data.reason);
        }
        await reloadTable();
    }
    catch (e) {
        console.error(e);
        errUnknown();
    }
}

$(function ($) {

    chiefTableObj = genChiefTableObj();
    void reloadTable();
    $('#reloadTable').on('click', reloadTable);
    $('#setCommand').on('click', function () {
        const turnList = unwrap_any<string[]>($('#chiefTurnSelector').val()).map(function (v) { return parseInt(v); });
        const $command = $('#chiefCommandList option:selected');
        if ($command.data('reqarg')) {
            document.location.href = stringifyUrl({
                url: 'v_processing.php',
                query: {
                    command: unwrap_any<string>($command.val()),
                    turnList: turnList.join('_'),
                    is_chief: true
                }
            });
        }
        else {
            void reserveTurn(turnList, unwrap_any<string>($command.val()));
        }
        return false;
    });

    $('#turnPush').on('click', function () {
        void pushTurn(1);
        return false;
    });

    $('#turnPull').on('click', function () {
        void pushTurn(-1);
        return false;
    });

})