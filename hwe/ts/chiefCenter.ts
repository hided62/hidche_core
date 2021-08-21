import axios from 'axios';
import $ from 'jquery';
import { range } from 'lodash';
import { DateTime } from 'luxon';
import { errUnknown, getNpcColor } from './common_legacy';
import { InvalidResponse } from './defs';
import { unwrap } from "./util/unwrap";
import { convertFormData } from './util/convertFormData';
import { unwrap_any } from "./util/unwrap_any";

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
        name:string|null,
        turnTime:string|null,
        officerLevelText:string,
        npcType:number,
        turn:string
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
        const $plate = $('#chief_{0}'.format(chiefIdx));
        const $officerLevelText = $plate.find('.chiefLevelText');
        const $name = $plate.find('.chiefName');
        const turn: TurnDOMObj[] = [];
        for (const turnIdx of range(maxChiefTurn)) {
            const $turn = $plate.find('.turn{0}'.format(turnIdx));
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
    const $plate = $('#chief_{0}'.format(chiefIdx));
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
    const tmpFilledChiefList:Record<number, boolean> = {};

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
            const $name = $('<span>{0}</span>'.format(chiefInfo.name));
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

        let turnTimeObj: DateTime|undefined;

        if (chiefInfo.turnTime) {
            turnTimeObj = DateTime.fromSQL(chiefInfo.turnTime);
        }

        const turnList = plateObj.turn;
        $.each(chiefInfo.turn, function (turnIdx, turnText) {
            if (turnTimeObj) {
                turnList[turnIdx].turnTime.text(turnTimeObj.toFormat('HH:mm'));
            }
            else {
                turnList[turnIdx].turnTime.text('');
            }

            turnList[turnIdx].turnText.html(turnText).css('font-size', '13px');
            const oWidth = unwrap(turnList[turnIdx].turnPad.innerWidth());
            const iWidth = unwrap(turnList[turnIdx].turnText.outerWidth());
            if (iWidth > oWidth * 0.95) {
                const newFontSize = 13 * oWidth / iWidth * 0.9;
                turnList[turnIdx].turnText.css('font-size', '{0}px'.format(newFontSize));
            }
            if (turnTimeObj) {
                turnTimeObj = turnTimeObj.plus({'minutes': turnTerm});
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
    try{

        const response = await axios({
            url: 'j_set_chief_command.php',
            responseType:'json',
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
    catch(e){
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

jQuery(function ($) {

    chiefTableObj = genChiefTableObj();
    void reloadTable();
    $('#reloadTable').on('click', reloadTable);
    $('#setCommand').on('click', function () {
        const turnList = unwrap_any<string[]>($('#chiefTurnSelector').val()).map(function (v) { return parseInt(v); });
        const $command = $('#chiefCommandList option:selected');
        if ($command.data('reqarg')) {
            $.redirect(
                "b_processing.php", {
                command: unwrap($command.val()),
                turnList: turnList.join('_'),
                is_chief: true
            }, "GET");
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