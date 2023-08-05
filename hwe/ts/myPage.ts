import "@scss/myPage.scss";

import axios from 'axios';
import $ from 'jquery';
import { type InvalidResponse, keyScreenMode, type ScreenModeType, type ItemTypeKey } from '@/defs';
import { convertFormData } from '@util/convertFormData';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { unwrap } from '@util/unwrap';
import { unwrap_any } from '@util/unwrap_any';
import { auto500px } from './util/auto500px';
import { initTooltip } from "./legacy/initTooltip";
import { insertCustomCSS } from "./util/customCSS";
import * as JosaUtil from '@util/JosaUtil';
import { SammoAPI } from "./SammoAPI";

type LogResponse = {
    result: true;
    log: Record<string, string>;
};

declare const staticValues: {
    items: Record<ItemTypeKey, {
        name: string;
        rawName: string;
        className: string;
        cost: number;
        isBuyable: boolean;
    }>
}

$(function ($) {
    setAxiosXMLHttpRequest();

    const initCustomCSSForm = function () {
        let lastTimeOut: NodeJS.Timeout | undefined = undefined;
        const $obj = $('#custom_css');
        const key = 'sam_customCSS';

        let text = localStorage.getItem(key);
        if (text) {
            $obj.val(text);
            console.log(text);
        }

        $obj.on('change keyup paste', function () {
            const newText = $obj.val();
            if (text == newText) {
                return;
            }
            if (lastTimeOut) {
                clearTimeout(unwrap(lastTimeOut));
            }
            $obj.css('background-color', '#222222');
            lastTimeOut = setTimeout(function () {
                text = unwrap_any<string>($obj.val());
                localStorage.setItem(key, text);
                $obj.css('background-color', 'black');
            }, 500);
        })
            ;
    };

    $('.load_old_log').on('click', async function (e) {
        e.preventDefault();

        const $thisBtn = $(this);
        const logType = $thisBtn.data('log_type');
        const $last = $(`.log_${logType}:last`);
        let reqTo: number | null = null;
        if ($last.length) {
            reqTo = $last.data('seq');
        }

        let result: InvalidResponse | LogResponse;

        try {
            const response = await axios({
                url: 'j_general_log_old.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData({
                    to: reqTo,
                    type: logType
                })
            });
            result = response.data;
        }
        catch (e) {
            console.log(e);
            alert(`실패했습니다: ${e}`);
            return;
        }

        if (!result.result) {
            alert(`로그를 받아오지 못했습니다. : ${result.reason}`);
            location.reload();
            return;
        }

        const keys: string[] = Object.keys(result.log);
        if (keys.length > 1 && parseInt(keys[0]) < parseInt(keys[1])) {
            keys.reverse();
        }

        if (keys.length == 0) {
            $thisBtn.hide();
            return;
        }

        const html: string[] = [];
        for (const key of keys) {
            if ($(`#log_${logType}_${key}`).length) {
                return true;
            }
            const item = result.log[key];
            html.push(`<div class='log_${logType}' id='log_${logType}_${key}' data-seq='${key}'>${item}</div>`);
        }
        $(`#${logType}Plate`).append(html.join(''));
    })

    initCustomCSSForm();


    const $screenModeRadios = $('input:radio[name=screenMode]');
    $screenModeRadios.prop('checked', false).filter(`[value="${localStorage.getItem(keyScreenMode) ?? 'auto'}"]`).prop('checked', true);
    $screenModeRadios.on('click', function (e) {
        const mode = (e.target as HTMLInputElement).value as ScreenModeType;
        localStorage.setItem(keyScreenMode, mode);
        document.dispatchEvent(new CustomEvent('tryChangeScreenMode'));
    });


    $('#dieOnPrestart').on('click', async function (e) {
        e.preventDefault();

        if (!confirm('정말로 삭제하시겠습니까?')) {
            return false;
        }

        try {
            await SammoAPI.General.DieOnPrestart();
        }
        catch (e) {
            console.log(e);
            alert(`실패했습니다: ${e}`);
            location.reload();
            return;
        }

        location.replace('..');
    });

    $('#buildNationCandidate').on('click', async function (e) {
        e.preventDefault();

        if(!confirm('거병 이후 장수를 삭제할 수 없게됩니다. 거병하시겠습니까?')){
            return false;
        }

        try {
            await SammoAPI.General.BuildNationCandidate();
        }
        catch (e) {
            console.log(e);
            alert(`실패했습니다: ${e}`);
            location.reload();
            return;
        }

        location.reload();
    });

    $('#instantRetreat').on('click', async function (e) {
        e.preventDefault();

        if(!confirm('아군 접경으로 이동할까요?')){
            return false;
        }

        try {
            await SammoAPI.General.InstantRetreat();
        }
        catch (e) {
            console.log(e);
            alert(`실패했습니다: ${e}`);
            location.reload();
            return;
        }

        location.reload();
    });

    $('#vacation').on('click', async function (e) {
        e.preventDefault();
        if (!confirm('휴가 기능을 신청할까요?')) {
            return false;
        }

        let result: InvalidResponse;

        try {
            const response = await axios({
                url: 'j_vacation.php',
                method: 'post',
                responseType: 'json',
            });
            result = response.data;
        }
        catch (e) {
            console.log(e);
            alert(`실패했습니다: ${e}`);
            location.reload();
            return;
        }

        if (!result.result) {
            alert(`실패했습니다: ${result.reason}`);
            location.reload();
            return;
        }

        location.reload();
    });

    $('#set_my_setting').on('click', async function (e) {
        e.preventDefault();
        let result: InvalidResponse;

        try {
            const response = await axios({
                url: 'j_set_my_setting.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData({
                    tnmt: unwrap_any<string>($('.tnmt:checked').val()),
                    defence_train: parseInt(unwrap_any<string>($('#defence_train').val())),
                    use_treatment: unwrap_any<string>($('#use_treatment').val()),
                    use_auto_nation_turn: unwrap_any<string>($('#use_auto_nation_turn').val()),
                })
            });
            result = response.data;
            if (!result.result) {
                throw result.reason;
            }
        }
        catch (e) {
            console.log(e);
            alert(`실패했습니다: ${e}`);
            location.reload();
            return;
        }

        location.reload();

    });

    $('.drop-item-btn').on('click', async function (e) {
        e.preventDefault();
        const $this = $(this);
        const type = $this.data('item-type') as ItemTypeKey | undefined;
        if (!type) {
            return;
        }

        console.log(`${type} 판매 시도`);
        const item = staticValues.items[type];
        console.log(item);

        const josaUl = JosaUtil.pick(item.rawName, '을');
        if (!confirm(`${item.name}${josaUl} 버리시겠습니까? (판매시 가치: ${item.cost / 2})`)) {
            return;
        }

        if (!item.isBuyable && !confirm(`이 아이템은 유니크 아이템입니다. 진짜로 ${item.name}${josaUl} 버리시겠습니까?`)) {
            return;
        }


        try {
            await SammoAPI.General.DropItem({
                itemType: type,
            });
            alert(`${item.name}${josaUl} 버렸습니다.`);
            location.reload();
        }
        catch (e) {
            console.error(e);
            alert(e);
        }

    });

    initTooltip();
    insertCustomCSS();
});

auto500px();