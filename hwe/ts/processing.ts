//import $ from 'jquery';
//import 'bootstrap';
import axios from 'axios';
//import 'select2';
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';
import { convertFormData } from './util/convertFormData';
import { InvalidResponse } from './defs';
import { unwrap_any } from './util/unwrap_any';
import { DataFormat, IdTextPair, OptionData } from 'select2';
import { unwrap } from "./util/unwrap";

declare const isChiefTurn: boolean;
declare global {
    interface Window {
        submitAction: () => Promise<void>;
        turnList: number[],
        command: string,
    }
}

async function reserveTurn(turnList: number[], command: string, arg: Record<string, unknown>) {
    const target = isChiefTurn ? 'j_set_chief_command.php' : 'j_set_general_command.php';

    let data: InvalidResponse;
    try {
        const response = await axios({
            url: target,
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                action: command,
                turnList: turnList,
                arg: JSON.stringify(arg)
            })
        });
        data = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`에러가 발생했습니다: ${e}`);
        return;
    }

    if (!data.result) {
        alert(data.reason);
        return;
    }

    if (!isChiefTurn) {
        window.location.href = './';
    } else {
        window.location.href = 'b_chiefcenter.php';
    }
}

$(function ($) {
    setAxiosXMLHttpRequest();
    //checkCommandArg 참고
    const availableArgumentList = {
        'string': [
            'nationName', 'optionText', 'itemType', 'nationType', 'itemCode', 'commandType',
        ],
        'int': [
            'crewType', 'destGeneralID', 'destCityID', 'destNationID',
            'amount', 'colorType',
            'year', 'month',
            'srcArmType', 'destArmType', //숙련전환 전용
        ],
        'boolean': [
            'isGold', 'buyRice',
        ],
        'integerArray': [
            'destNationIDList', 'destGeneralIDList', 'amountList'
        ]
    }

    type argTypes = keyof typeof availableArgumentList;
    type argValues = string | number | boolean | number[];

    const handlerList: Record<argTypes, ($obj: JQuery<HTMLInputElement>) => argValues> = {
        'string': function ($obj: JQuery<HTMLInputElement>) {
            return $.trim(unwrap_any<string>($obj.eq(0).val()));
        },
        'int': function ($obj: JQuery<HTMLInputElement>) {
            return parseInt(unwrap_any<string>($obj.eq(0).val()));
        },
        'boolean': function ($obj: JQuery<HTMLInputElement>) {
            switch (unwrap_any<string>($obj.eq(0).val()).toLowerCase()) {
                case "true":
                case "yes":
                case "1":
                    return true;
                case "false":
                case "no":
                case "0":
                    return false;
                default:
                    throw new Error("Boolean.parse: Cannot convert string to boolean.");
            }
        },
        'integerArray': function ($obj: JQuery<HTMLInputElement>) {
            const result: number[] = [];
            $obj.each(function () {
                result.push(parseInt(unwrap_any<string>($(this).val())));
            });
            return result;
        }
    }

    window.submitAction = async function (): Promise<void> {
        const argument: Record<string, argValues> = {};
        for (const typeName of Object.keys(availableArgumentList) as argTypes[]) {
            const typeKeys = availableArgumentList[typeName];
            for (const typeKey of typeKeys) {
                let $obj = $('#' + typeKey) as JQuery<HTMLInputElement>;
                if ($obj.length == 0) {
                    $obj = $('.' + typeKey);
                    if ($obj.length == 0) {
                        continue;
                    }
                }

                argument[typeKey] = handlerList[typeName]($obj);
            }
        }

        console.log(argument);
        await reserveTurn(window.turnList, window.command, argument);
    };

    $('#commonSubmit').on('click', window.submitAction);

    const $colorType = $('#colorType');
    if ($colorType.length) {
        $colorType.select2({
            theme: 'bootstrap4',
            placeholder: "색상을 선택해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            templateSelection: function (item) {
                if ((item as DataFormat).disabled) {
                    return item.text;
                }
                const element = (item as OptionData).element;
                if(!element){
                    throw 'invalid type';
                }
                const bgcolor = unwrap(element.dataset.color);
                const fgcolor = unwrap(element.dataset.fontColor);
                return $("<span><span style='background-color:{0};color:{1};'>　</span>&nbsp;{2}</span>".format(
                    bgcolor, fgcolor, item.text
                ));
            },
            templateResult: function (item) {
                if ((item as DataFormat).disabled) {
                    return item.text;
                }
                const element = (item as OptionData).element;
                if(!element){
                    throw 'invalid type';
                }
                const bgcolor = unwrap(element.dataset.color);
                const fgcolor = unwrap(element.dataset.fontColor);
                return $("<div style='padding: 0.75rem 0.375rem; background-color:{0};color:{1};'>{2}</div>".format(
                    bgcolor, fgcolor, item.text
                ));
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'no-padding simple-select2-align-center bg-secondary text-secondary',
        });
    }

    const $nationType = $('#nationType');
    if ($nationType.length) {
        $nationType.select2({
            theme: 'bootstrap4',
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    const $destCityID = $('#destCityID');
    if ($destCityID.length) {
        $destCityID.select2({
            theme: 'bootstrap4',
            placeholder: "도시를 선택해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    const $destNationID = $('#destNationID');
    if ($destNationID.length) {
        $destNationID.select2({
            theme: 'bootstrap4',
            placeholder: "국가를 선택해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    const $destGeneralID = $('#destGeneralID');
    if ($destGeneralID.length) {
        $destGeneralID.select2({
            theme: 'bootstrap4',
            placeholder: "장수를 선택해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    const $isGold = $('#isGold');
    if ($isGold.length) {
        $isGold.select2({
            theme: 'bootstrap4',
            placeholder: "분량을 지정해 주세요.",
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            minimumResultsForSearch: -1,
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        });
    }

    const $amount = $('#amount:not([type=hidden])');
    if ($amount.length) {
        $amount.select2({
            theme: 'bootstrap4',
            placeholder: "분량을 지정해 주세요.",
            allowClear: false,
            language: "ko",
            containerCss: {
                display: "inline-block !important",
                color: 'white !important',
            },
            tags: true,
            sorter: function (items) {
                (items as IdTextPair[]).sort(function (lhs, rhs) {
                    return parseInt(lhs.id) - parseInt(rhs.id);
                })
                return items;
            },
            containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
            dropdownCssClass: 'select2-only-number simple-select2-align-center bg-secondary text-secondary',
        })
    }

    $(document).on('keypress', '.select2-only-number .select2-search__field', function (e) {
        $(this).val(unwrap_any<string>($(this).val()).replace(/[^\d].+/, ""));
        if ((e.which < 48 || e.which > 57)) {
            e.preventDefault();
        }
    });

});