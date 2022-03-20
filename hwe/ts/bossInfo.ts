import $ from 'jquery';
import axios from 'axios';
import { convertFormData } from '@util/convertFormData';
import type { InvalidResponse } from '@/defs';
import { unwrap_any } from '@util/unwrap_any';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import 'bootstrap';
import 'select2/dist/js/select2.full.js'

type GeneralSelectorItem = {
    id: string|number,
    text: string,
    selected: boolean,
}

declare const candidateAmbassadors: GeneralSelectorItem[];
declare const candidateAuditors: GeneralSelectorItem[];

async function changePermission(isAmbassador: boolean, rawGeneralList: GeneralSelectorItem[]) {
    console.log(isAmbassador);
    console.log(rawGeneralList);

    const generalList: number[] = [];
    for (const rawGen of rawGeneralList) {
        generalList.push(parseInt(rawGen.id as string));
    }

    let result: InvalidResponse;

    try {
        const response = await axios({
            url: 'j_general_set_permission.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                isAmbassador: isAmbassador,
                genlist: generalList
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
        alert(`변경하지 못했습니다 : ${result.reason}`);
        return;
    }

    alert('변경했습니다.');
    location.reload();
}

$(function () {
    setAxiosXMLHttpRequest();

    $('#selectAmbassador').select2({
        theme: 'bootstrap4',
        placeholder: "",
        allowClear: true,
        language: "ko",
        width: '300px',
        maximumSelectionLength: 2,
        containerCss: {
            display: "inline-block !important;",
            color: 'white !important'
        },
        data: candidateAmbassadors,
        //containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });

    $('#selectAuditor').select2({
        theme: 'bootstrap4',
        placeholder: "",
        allowClear: true,
        language: "ko",
        width: '300px',
        maximumSelectionLength: 2,
        containerCss: {
            display: "inline-block !important;",
            color: 'white !important'
        },
        data: candidateAuditors,
        //containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });

    $('#changeAmbassador').on('click', async function (e) {
        e.preventDefault();
        if (!confirm('외교권자를 변경할까요?')) {
            return;
        }

        await changePermission(true, $('#selectAmbassador').select2('data'));
        return;
    });

    $('#changeAuditor').on('click', async function (e) {
        e.preventDefault();
        if (!confirm('조언자를 변경할까요?')) {
            return;
        }

        await changePermission(false, $('#selectAuditor').select2('data'));
        return;
    });

    $('#btn_kick').on('click', async function (e) {
        e.preventDefault();
        const $kickSelect = $('#genlist_kick option:selected');
        const generalID = $kickSelect.val();
        if (!generalID) {
            alert('장수를 선택해주세요');
            return;
        }
        const generalName = $kickSelect.data('name');
        if (!confirm(`${generalName}를 추방하시겠습니까?`)) {
            return;
        }

        let result: InvalidResponse;

        try {
            const response = await axios({
                url: 'j_myBossInfo.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData({
                    action: '추방',
                    destGeneralID: generalID
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
            alert(`추방하지 못했습니다. : ${result.reason}`);
            return;
        }
        alert(`${generalName}를 추방했습니다.`);
        location.reload();
    });

    $('.btn_appoint').on('click', async function (e) {
        e.preventDefault();

        const $btn = $(this);
        const officerLevel = $btn.data('officer_level');
        const officerLevelText = $btn.data('officer_level_text');
        let cityID = 0;
        let cityName = '_';
        const $generalSelect = $(`#genlist_${officerLevel} option:selected`);
        const $citySelect = $(`#citylist_${officerLevel} option:selected`);

        const generalID = parseInt(unwrap_any<string>($generalSelect.val()));
        const generalName = $generalSelect.data('name');
        const generalOfficerLevel = $generalSelect.data('officer_level');


        if (officerLevel >= 5) {
            if (generalID == 0) {
                if (!confirm(`${officerLevelText}직을 비우시겠습니까?`)) {
                    return false;
                }
            } else if (generalOfficerLevel >= 5) {
                if (!confirm(`이미 수뇌인 ${generalName}을(를) ${officerLevelText}직에 임명하시겠습니까?`)) {
                    return false;
                }
            } else {
                if (!confirm(`${generalName}을(를) ${officerLevelText}직에 임명하시겠습니까?`)) {
                    return false;
                }
            }
        } else {
            cityID = parseInt(unwrap_any<string>($citySelect.val()));
            if (!cityID) {
                alert('도시를 선택해주세요');
                return false;
            }
            cityName = $citySelect.find('option:selected .name_field').text();

            if (generalID == 0) {
                if (!confirm(`${cityName} ${officerLevelText}직을 비우시겠습니까?`)) {
                    return false;
                }
            } else if (generalOfficerLevel >= 5) {
                if (!confirm(`수뇌인 ${generalName}을(를) ${cityName} ${officerLevelText}직에 임명하시겠습니까?`)) {
                    return false;
                }
            } else {
                if (!confirm(`${generalName}을(를) ${cityName} ${officerLevelText}직에 임명하시겠습니까?`)) {
                    return false;
                }
            }
        }

        let result: InvalidResponse;

        try {
            const response = await axios({
                url: 'j_myBossInfo.php',
                method: 'post',
                responseType: 'json',
                data: convertFormData({
                    action: '임명',
                    destGeneralID: generalID,
                    destCityID: cityID,
                    officerLevel: officerLevel
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
            alert(`임명하지 못했습니다. : ${result.reason}`);
            return false;
        }

        if (generalID) {
            alert(`${generalName}을(를) 임명했습니다.`);
        } else {
            alert('관직을 비웠습니다.');
        }

        location.reload();
    })
})