import $ from 'jquery';
import { unwrap_any } from '@util/unwrap_any';
import axios from 'axios';
import { isBrightColor } from "@util/isBrightColor";
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { isString } from 'lodash-es';
import { convertFormData } from '@util/convertFormData';
import type { InvalidResponse, NationStaticItem } from '@/defs';
import { escapeHtml } from '@/legacy/escapeHtml';
import { nl2br } from '@util/nl2br';
import { unwrap } from '@util/unwrap';
import 'bootstrap';
import 'select2/dist/js/select2.full.js'
import type { LoadingData } from 'select2';

declare const permissionLevel: number;
let myNationID: number | undefined;

type LetterNationTarget = {
    nationID: number,
    nationName: string,
    nationColor: string,
}
type LetterFullTarget = LetterNationTarget & {
    nationID: number,
    nationName: string,
    nationColor: string,
    generalName: string,
    generalIcon: string,
}

type LetterState = 'proposed' | 'activated' | 'cancelled' | 'replaced';

type LetterItem = {
    no: number,

    src: LetterFullTarget,
    dest: LetterNationTarget | LetterFullTarget,
    prev_no: number | null,
    state: LetterState,
    state_opt: 'try_destroy_src' | 'try_destroy_dest' | null,
    brief: string,
    detail: string,
    date: string,
}



type LetterResponse = {
    result: true,
    nations: Record<number, NationStaticItem>,
    letters: Record<number, LetterItem>,
    myNationID: number,
}

const stateText: Record<LetterState, string> = {
    'proposed': '제안됨',
    'activated': '승인됨',
    'cancelled': '거부됨',
    'replaced': '대체됨',
};

const stateOptionText: Record<NonNullable<LetterItem['state_opt']>, string> = {
    'try_destroy_src': '송신측의 파기 요청',
    'try_destroy_dest': '수신측의 파기 요청',
}

async function submitLetter(e: JQuery.Event): Promise<void> {
    e.preventDefault();

    const $brief = $('#inputBrief');
    const $detail = $('#inputDetail');
    const $prevNo = $('#inputPrevNo');
    const $destNation = $('#inputDestNation');
    const brief = $.trim(unwrap_any<string>($brief.val()));
    const detail = $.trim(unwrap_any<string>($detail.val()));
    let prevNo = $prevNo.val() as number | undefined;
    const destNation = unwrap_any<number>($destNation.val());

    if (prevNo !== undefined && prevNo < 1) {
        prevNo = undefined;
    }

    console.log(brief);
    if (!brief) {
        return;
    }

    $brief.val('');
    $detail.val('');

    let result: InvalidResponse;

    try {
        const response = await axios({
            url: 'j_diplomacy_send_letter.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                brief: brief,
                detail: detail,
                destNation: destNation,
                prevNo: prevNo ?? null,
            })
        });
        result = response.data;
        if (!result.result) {
            throw result.reason;
        }
    }
    catch (e) {
        console.error(e);
        alert(`외교 서신을 보내는데 실패했습니다: ${e}`);
        return;
    }

    alert('전송했습니다.');
    location.reload();
}

async function repondLetter(letterNo: number, isAgree: boolean, reason: string | null): Promise<void> {

    let result: InvalidResponse;
    try {
        const response = await axios({
            url: 'j_diplomacy_respond_letter.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                letterNo: letterNo,
                isAgree: isAgree,
                reason: reason,
            })
        });
        result = response.data;
        if (!result.result) {
            throw result.reason;
        }
    }
    catch (e) {
        console.error(e);
        alert(`응답을 실패했습니다: ${e}`);
        return;
    }

    if (isAgree) {
        alert('승인했습니다.');
    }
    else {
        alert('거부했습니다.');
    }
    location.reload();
}

async function rollbackLetter(letterNo: number) {
    let result: InvalidResponse;
    try {
        const response = await axios({
            url: 'j_diplomacy_rollback_letter.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                letterNo: letterNo,
            })
        });
        result = response.data;
        if (!result.result) {
            throw result.reason;
        }
    }
    catch (e) {
        console.error(e);
        alert(`회수를 실패했습니다: ${e}`);
        return;
    }

    alert('회수 했습니다.');
    location.reload();
}

async function destroyLetter(letterNo: number) {
    let result: InvalidResponse | {
        result: true,
        state: LetterState
    };
    try {
        const response = await axios({
            url: 'j_diplomacy_destroy_letter.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                letterNo: letterNo,
            })
        });
        result = response.data;
        if (!result.result) {
            throw result.reason;
        }
    }
    catch (e) {
        console.error(e);
        alert(`회수를 실패했습니다: ${e}`);
        return;
    }

    if (result.state == 'activated') {
        alert('파기를 요청 했습니다.');
    }
    else {
        alert('파기 되었습니다.');
    }

    location.reload();
}

function drawLetter(letterObj: LetterItem) {

    if (letterObj.state == 'cancelled') {
        //TODO: 취소되거나, 대체된 문서도 보여줄 방법을 찾아볼 것
        return;
    }

    console.log(letterObj);

    const $letterFrame = $('#letterTemplate > .letterFrame');

    const srcColorFormat = {
        'background-color': letterObj.src.nationColor,
        'color': isBrightColor(letterObj.src.nationColor) ? '#000000' : '#ffffff'
    };

    const destColorFormat = {
        'background-color': letterObj.dest.nationColor,
        'color': isBrightColor(letterObj.dest.nationColor) ? '#000000' : '#ffffff'
    };

    const targetNation = letterObj.src.nationID == myNationID ? letterObj.dest : letterObj.src;
    const targetColor = letterObj.src.nationID == myNationID ? destColorFormat : srcColorFormat;

    const $letter = $letterFrame.clone();

    if (letterObj.state == 'replaced') {
        $letter.hide();
    }

    $letter.addClass('letterObj')
        .data('no', letterObj.no)
        .data('brief', letterObj.brief)
        .data('detail', letterObj.detail)
        .attr('id', 'letter_' + letterObj.no);

    $letter.find('.letterHeader').css(targetColor);
    $letter.find('.letterNationName').text(targetNation.nationName);
    $letter.find('.letterDate').text(letterObj.date);
    $letter.find('.letterNo').text('#' + letterObj.no);

    $letter.find('.letterStatus').text(stateText[letterObj.state]);

    if (letterObj.state_opt !== null) {
        $letter.find('.letterStatusOpt').text(`(${unwrap(stateOptionText[letterObj.state_opt])})`);
    }
    if (letterObj.prev_no !== null) {
        const $showPrev = $(`<a href="#">#${letterObj.prev_no}</a>`);
        $showPrev.click(function () {
            $('#letter_' + letterObj.prev_no).toggle();
        })
        $letter.find('.letterPrevNo').empty().append($showPrev);
    }
    else {
        $letter.find('.letterPrevNo').text('신규');
    }
    $letter.find('.letterBrief').html(nl2br(escapeHtml(letterObj.brief)));
    $letter.find('.letterDetail').html(nl2br(escapeHtml(letterObj.detail)));

    $letter.find('.letterSrc .signerImg img.generalIcon').attr('src', letterObj.src.generalIcon);
    $letter.find('.letterSrc .signerNation').text(letterObj.src.nationName).css(srcColorFormat);
    $letter.find('.letterSrc .signerName').text(letterObj.src.generalName).css(srcColorFormat);

    if ('generalName' in letterObj.dest) {
        $letter.find('.letterDest .signerImg img.generalIcon').attr('src', letterObj.dest.generalIcon);
        $letter.find('.letterDest .signerNation').text(letterObj.dest.nationName).css(destColorFormat);
        $letter.find('.letterDest .signerName').text(letterObj.dest.generalName).css(destColorFormat);
    }

    if (letterObj.state == 'proposed' && letterObj.src.nationID != myNationID) {
        $letter.find('.btnAgree').show().click(async function (e) {
            e.preventDefault();
            if (!confirm('승인하시겠습니까?')) {
                return;
            }
            await repondLetter(letterObj.no, true, null);
        });
        $letter.find('.btnDisagree').show().click(async function (e) {
            e.preventDefault();
            let reason = prompt('거부하시겠습니까? (이유 [최대 50자])');
            if (reason === null) {
                return;
            }
            reason = reason.substr(0, 50);
            await repondLetter(letterObj.no, false, reason);
        });
    }

    if (letterObj.state == 'proposed' && letterObj.src.nationID == myNationID) {
        $letter.find('.btnRollback').show().click(async function (e) {
            e.preventDefault();
            if (!confirm('회수하시겠습니까?')) {
                return false;
            }
            await rollbackLetter(letterObj.no);
        });
    }

    if (letterObj.state == 'activated') {
        const $btnDestroy = $letter.find('.btnDestroy');
        if ((letterObj.src.nationID == myNationID && letterObj.state_opt == 'try_destroy_src') ||
            (letterObj.dest.nationID == myNationID && letterObj.state_opt == 'try_destroy_dest')) {
            $btnDestroy.show().prop('disabled', true);
        }
        else {
            $btnDestroy.show().click(async function (e) {
                e.preventDefault();
                if (!confirm('본 문서를 파기하겠습니까? (상호 동의 필요)')) {
                    return false;
                }
                await destroyLetter(letterObj.no);
            })

        }
    }


    $letter.find('.btnRenew').click(function () {
        const $inputPrevNo = $('#inputPrevNo');
        $inputPrevNo.val(letterObj.no);
        $inputPrevNo.trigger('change');
    })


    $('#letters').prepend($letter);
}

function initNewLetterForm(lettersObj: LetterResponse) {
    console.log(lettersObj);
    const nationList: (NationStaticItem & { id: number, text: string })[] = [];
    for (const nation of Object.values(lettersObj.nations)) {
        nationList.push({
            ...nation,
            id: nation.nation,
            text: nation.name,
        });
    }

    const $destNation = $('#inputDestNation').select2({
        theme: 'bootstrap4',
        placeholder: "",
        language: "ko",
        width: '300px',
        containerCss: {
            display: "inline-block !important;",
            color: 'white !important'
        },
        data: nationList,
        templateResult: colorNation,
        containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });


    const prevNoList: {
        id: number,
        text: string,
        nation: null | number,
    }[] = [{
        id: 0,
        text: '-새 문서-',
        nation: null,
    }];

    for(const letterObj of Object.values(lettersObj.letters)){
        if (letterObj.state == 'replaced' || letterObj.state == 'cancelled') {
            continue;
        }
        const targetNation = letterObj.src.nationID == myNationID ? letterObj.dest : letterObj.src;
        prevNoList.push({
            id: letterObj.no,
            text: `#${letterObj.no} <${targetNation.nationName}>`,
            nation: targetNation.nationID
        });
    }

    const $inputPrevNo = $('#inputPrevNo').select2({
        theme: 'bootstrap4',
        placeholder: "",
        language: "ko",
        width: '300px',
        containerCss: {
            display: "inline-block !important;",
            color: 'white !important'
        },
        data: prevNoList,
        containerCssClass: 'simple-select2-align-center bg-secondary text-secondary',
        dropdownCssClass: 'simple-select2-align-center bg-secondary text-secondary',
    });
    $inputPrevNo.on('change', function () {
        const data = $inputPrevNo.select2('data')[0] as unknown as typeof prevNoList[0];
        console.log(data);
        if (data.nation == null) {
            $destNation.prop("disabled", false);
        }
        else {
            $destNation.val(data.nation).prop("disabled", true);
            const $targetLetter = $('#letter_' + data.id);
            resizeTextarea($('#inputBrief').val($targetLetter.data('brief')));
            resizeTextarea($('#inputDetail').val($targetLetter.data('detail')));
        }
    });

    $('#btnSend').click(submitLetter);

    $('#newLetter').show();
}

function drawLetters(lettersObj: LetterResponse) {
    myNationID = lettersObj.myNationID;

    if (permissionLevel == 4) {
        initNewLetterForm(lettersObj);
        $('.letterActionPlate').show();
    }

    $('.letterObj').detach();//첫 버전이니까 일괄 삭제 일괄 로드
    for (const letter of Object.values(lettersObj.letters)) {
        drawLetter(letter);
    }
}

async function loadLetters(): Promise<LetterResponse> {
    const response = await axios({
        url: 'j_diplomacy_get_letter.php',
        responseType: 'json',
        method: 'post',
        data: convertFormData({
        })
    });
    const result: LetterResponse|InvalidResponse = response.data;
    if(!result.result){
        throw result.reason;
    }
    return result;
}

function colorNation(nationInfo: { id: number, text: string, color?: string } | LoadingData) {
    if (!('color' in nationInfo)) {
        return nationInfo.text;
    }
    if (!nationInfo.color) {
        return nationInfo.text;
    }

    const fgColor = isBrightColor(nationInfo.color) ? '#000000' : '#ffffff';
    const $nationForm = $('<div>' + nationInfo.text + '</div>').css({
        'color': fgColor,
        'background-color': nationInfo.color
    });
    return $nationForm;
}

function resizeTextarea($obj: JQuery<HTMLElement>) {
    $obj.height(1).height($obj.prop('scrollHeight') + 12);
}

$(async function () {
    setAxiosXMLHttpRequest();
    $('textarea.autosize').on('keydown keyup', function () {
        resizeTextarea($(this));
    })

    try {
        const letters = await loadLetters();
        drawLetters(letters);
    }
    catch (e) {
        console.error(e);
        if (isString(e)) {
            alert(e);
        }
        else {
            alert(`실패했습니다.`);
        }
        return;
    }
});