import $ from 'jquery';
import Popper from 'popper.js';
(window as unknown as { Popper: unknown }).Popper = Popper;//XXX: 왜 popper를 이렇게 불러야 하는가?
import 'bootstrap';
import { parseTime } from './util/parseTime';
import { addMilliseconds, addMinutes, differenceInMilliseconds } from 'date-fns';
import { formatTime } from './util/formatTime';
import { unwrap_any } from './util/unwrap_any';
import { errUnknown } from './common_legacy';
import { unwrap } from './util/unwrap';
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';
import './msg.ts';
import './map.ts';
import { exportWindow } from './util/exportWindow';
import {stringifyUrl} from 'query-string';
exportWindow($, '$');

type TurnArg = {
    //TODO: 채울것
}

type TurnItem = {
    action: string,
    brief: string,
    arg: TurnArg,
}

type ReservedTurnResponse = {
    result: true,
    turnTime: string,
    turnTerm: number,
    year: number,
    month: number,
    date: string,
    turn: TurnItem[],
}

$(function ($) {
    setAxiosXMLHttpRequest();

    $('#refreshPage').click(function () {
        document.location.reload();
        return false;
    });


    function reloadCommandList() {
        void $.get({
            url: 'j_get_reserved_command.php',
            dataType: 'json',
            cache: false,
        }).then(function (data: ReservedTurnResponse) {
            if (!data) {
                return;
            }
            if (!data.result) {
                return;
            }
            const game_clock = parseTime(data.date);
            const now_clock = new Date();
            const $clock = $('#clock');
            $clock.data('time-diff', differenceInMilliseconds(game_clock, now_clock));
            $clock.val(formatTime(game_clock));

            const turnTime = parseTime(data.turnTime);
            let nextTurnTime = new Date(turnTime);

            let year = data.year;
            let month = data.month;

            for (const [turnIdx, turnInfo] of Object.entries(data.turn)) {
                const $tr = $(`#command_${turnIdx}`);

                $tr.find('.time_pad').text(formatTime(nextTurnTime, 'HH:mm'));
                $tr.find('.month_pad').text(`${year}年 ${month}月`);
                const $turn_pad = $tr.find('.turn_pad');
                const $turn_text = $turn_pad.find('.turn_text');
                $turn_text.text(turnInfo.brief).css('font-size', '13px');

                const oWidth = unwrap($turn_pad.innerWidth());
                const iWidth = unwrap($turn_text.outerWidth());
                if (iWidth > oWidth * 0.95) {
                    const newFontSize = 13 * oWidth / iWidth * 0.9;
                    $turn_text.css('font-size', `${newFontSize}px`);
                }

                nextTurnTime = addMinutes(nextTurnTime, data.turnTerm);
                month += 1;
                if (month >= 13) {
                    year += 1;
                    month -= 12;
                }
            }
            console.log(data);


        });
    }

    function myclock() {

        const $clock = $('#clock');
        const now_clock = new Date();

        const rawTimeDiff = $clock.data('time-diff');
        if (rawTimeDiff === null || rawTimeDiff === undefined) {
            return;
        }
        const timeDiff = unwrap_any<number>(rawTimeDiff);

        const gameClock = addMilliseconds(now_clock, timeDiff);

        $('#clock').val(formatTime(gameClock));
    }

    function pushTurn(pushAmount: number) {
        $.post({
            url: 'j_general_turn.php',
            dataType: 'json',
            data: {
                amount: pushAmount
            }
        }).then(function (data) {
            if (!data.result) {
                alert(data.reason);
            }
            reloadCommandList();
        }, errUnknown);
    }

    function repeatTurn(repeatAmount: number) {
        $.post({
            url: 'j_general_turn.php',
            dataType: 'json',
            data: {
                amount: repeatAmount,
                is_repeat: true
            }
        }).then(function (data) {
            if (!data.result) {
                alert(data.reason);
            }
            reloadCommandList();
        }, errUnknown);
    }

    $('#pullTurn').click(function () {
        pushTurn(-parseInt(unwrap_any<string>($('#repeatAmount').val())));
    });

    $('#pushTurn').click(function () {
        pushTurn(parseInt(unwrap_any<string>($('#repeatAmount').val())));
    });

    $('#repeatTurn').click(function () {
        repeatTurn(parseInt(unwrap_any<string>($('#repeatAmount').val())));
    });


    function reserveTurn(turnList: number[], command: string) {
        console.log(turnList, command);
        $.post({
            url: 'j_set_general_command.php',
            dataType: 'json',
            data: {
                action: command,
                turnList: turnList
            }
        }).then(function (data) {
            if (!data.result) {
                alert(data.reason);
            }
            reloadCommandList();
        }, errUnknown);
    }

    $('#reserveTurn').click(function () {
        const turnList = unwrap_any<string[]>($('#generalTurnSelector').val()).map(v => parseInt(v));
        const $command = $('#generalCommandList option:selected');
        if ($command.data('reqarg')) {
            document.location.href = stringifyUrl({
                url: 'b_processing.php',
                query: {
                    command: unwrap_any<string>($command.val()),
                    turnList: turnList.join('_')
                }
            });
        }
        else {
            reserveTurn(turnList, unwrap_any<string>($command.val()));
        }
        return false;
    })

    setInterval(myclock, 500);
    reloadCommandList();
});