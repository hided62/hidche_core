import { setAxiosXMLHttpRequest } from "../util/setAxiosXMLHttpRequest";
import $ from 'jquery';
import 'bootstrap';
import axios from 'axios';
import { subDays } from 'date-fns';
import { getDateTimeNow } from "../util/getDateTimeNow";
import { sha512 } from "js-sha512";
import { convertFormData } from "../util/convertFormData";
import { InvalidResponse } from "../defs";
import { unwrap } from "../util/unwrap";
import { parseTime } from "../util/parseTime";
import { formatTime } from "../util/formatTime";

type ResultUserInfo = {
    result: true,
    id: number,
    name: string,
    grade: string,
    picture: string,
    global_salt: string,
    join_date: string,
    third_use: boolean,
    acl: string,
    oauth_type: 'NONE' | 'KAKAO',
    token_valid_until?: string
}

function fillUserInfo(result: ResultUserInfo | InvalidResponse) {
    if (!result.result) {
        alert(result.reason);
        location.href = 'entrance.php';
        return;
    }

    $('#slot_id').html(result.id.toString());
    $('#slot_nickname').html(result.name);
    $('#slot_grade').html(result.grade);
    $('#slot_acl').html(result.acl);
    $('#slot_icon').attr('src', result.picture);
    $('#global_salt').val(result.global_salt);
    $('#slot_join_date').html(result.join_date);
    $('#slot_third_use').html(result.third_use ? '○' : '×');
    if (result.third_use) {
        $('#third_use_disallow').show();
    }
    $('#slot_oauth_type').text(result.oauth_type);
    if (result.oauth_type != 'NONE') {
        $('#slot_token_valid_until').text(unwrap(result.token_valid_until));
    }
    else {
        $('#slot_token_valid_until').parent().html('');
    }



}

function changeIconPreview(this: HTMLFormElement) {
    const $preview = $(this) as JQuery<HTMLFormElement>;
    console.log($preview);

    const filename = $preview[0].files[0].name;
    const reader = new FileReader();
    reader.onload = function (e) {
        $('#slot_new_icon').attr('src', unwrap(unwrap(e.target).result).toString()).css('visibility', 'visible');
    }

    reader.readAsDataURL($preview[0].files[0]);

    $('#image_upload_filename').val(filename);
}

type IconResponse = {
    result: true,
    servers: [string, string][]
};

async function deleteIcon() {
    let result: InvalidResponse | IconResponse;
    try {
        const response = await axios({
            url: 'j_icon_delete.php',
            responseType: 'json',
            method: 'post'
        });
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`아이콘 삭제를 실패했습니다: ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        location.reload();
        return;
    }

    showAdjustServerModal(result.servers);
}

async function disallowThirdUse() {
    try {
        await axios({
            url: 'j_disallow_third_use.php',
            method: 'post',
            responseType: 'json'
        });
        alert('철회했습니다.');
    }
    catch (e) {
        alert('알 수 없는 이유로 철회를 실패했습니다.');
    }
    location.reload();
}

function showAdjustServerModal(serverList: [string, string][]) {

    const $form = $('#chooseServerForm');
    $form.empty();

    for (const [serverKey, serverKorName] of serverList) {
        const $item = $(`<div style="display:inline-block;margin-right:7px;" class="custom-control custom-checkbox">\
        <input type="checkbox" checked class="custom-control-input" name="${serverKey}" id="switch_${serverKey}">\
        <label class="custom-control-label" for="switch_${serverKey}">${serverKorName}</label>\
      </div>`);
        $form.append($item);
    }

    const $modal = $('#chooseServer');
    $modal.modal({
        backdrop: 'static'
    });
    $modal.on('hidden.bs.modal', function () {
        location.reload();
        return;
    });

    $("#modal-apply").off("click").on("click", async function () {
        const events: Promise<unknown>[] = [];
        $form.find('input:checked').each(function () {
            const $input = $(this);
            const server = $input.attr('name');

            console.log(server);

            events.push(axios({
                url: `../${server}/j_adjust_icon.php`,
                method: 'post',
                responseType: 'json',
            }));
        })

        for (const p of events) {
            try {
                await p;
            }
            catch (e) {
                //서버 폐쇄등으로 접근하지 못할 수도 있음.
                console.error(e, p);
            }
        }

        alert('적용되었습니다.');
        location.reload();
    });

}

async function changeIcon(this: HTMLFormElement) {
    const $icon = $('#image_upload') as JQuery<HTMLFormElement>;

    if ($icon[0].files.length == 0) {
        alert('파일을 선택해주세요');
        return false;
    }

    let result: InvalidResponse | IconResponse;
    try {
        const response = await axios({
            url: 'j_icon_change.php',
            method: 'post',
            responseType: 'json',
            data: new FormData(this)
        });
        result = response.data;
    }
    catch (e) {
        alert('알 수 없는 이유로 아이콘 업로드를 실패했습니다.');
        location.reload();
        return;
    }

    if (!result.result) {
        alert(result.reason);
        location.reload();
        return;
    }

    showAdjustServerModal(result.servers);
}

async function changePassword() {
    const old_pw = $('#current_pw').val() as string;
    const new_pw = $('#new_pw').val() as string;
    const new_pw_confirm = $('#new_pw_confirm').val() as string;

    if (!old_pw) {
        alert('이전 비밀번호를 입력해야 합니다.');
        return;
    }
    if (new_pw.length < 6) {
        alert('비밀번호 길이는 6글자 이상이어야 합니다.');
        return;
    }

    if (new_pw != new_pw_confirm) {
        alert('입력 값이 일치하지 않습니다.');
        return;
    }

    const global_salt = $('#global_salt').val();

    const old_password = sha512(global_salt + old_pw + global_salt);
    const new_password = sha512(global_salt + new_pw + global_salt);

    let result: InvalidResponse;

    try {
        const response = await axios({
            url: 'j_change_password.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                old_pw: old_password,
                new_pw: new_password
            })
        });
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`알 수 없는 이유로 비밀번호를 바꾸지 못했습니다.: ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        return;
    }

    alert('비밀번호를 바꾸었습니다');
    location.reload();
}

async function deleteMe() {
    const pw = $('#delete_pw').val() as string;

    if (!pw) {
        alert('비밀번호를 입력해야 합니다.');
        return;
    }

    const global_salt = $('#global_salt').val();

    const password = sha512(global_salt + pw + global_salt);

    let data: InvalidResponse;
    try {
        const response = await axios({
            url: 'j_delete_me.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                pw: password
            })
        });
        data = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`회원 탈퇴에 실패했습니다.: ${e}`);
        return;
    }

    if (!data.result) {
        alert(data.reason);
        return;
    }

    alert('탈퇴 처리되었습니다.');
    location.href = '../';
}

async function extendAuth() {
    const validUntil = $('#slot_token_valid_until').html();
    const availableAt = formatTime(subDays(parseTime(validUntil), 5));
    const now = getDateTimeNow();

    if (now < availableAt) {
        alert(`${availableAt}부터 초기화할 수 있습니다.`);
        return false;
    }

    if (!confirm('로그아웃됩니다. 진행할까요?')) {
        return;
    }

    let result: InvalidResponse;

    try {
        const response = await axios({
            url: '../oauth_kakao/j_reset_token.php',
            method: 'post',
            responseType: 'json'
        })
        result = response.data;
    } catch (e) {
        alert(`알 수 없는 이유로 로그인 토큰 연장에 실패했습니다. : ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        return;
    }

    alert('초기화했습니다. 다시 로그인해 주십시오.');
    location.href = '../';
}

$(function () {
    setAxiosXMLHttpRequest();
    $('#slot_icon, #slot_new_icon').attr('src', window.pathConfig.sharedIcon + '/default.jpg');

    axios({
        url: 'j_get_user_info.php',
        method: 'post',
        responseType: 'json'
    }).then(result => {
        fillUserInfo(result.data);
    }, () => {
        alert('알 수 없는 이유로, 회원 정보를 불러오지 못했습니다.');
        location.href = 'entrance.php';
    })

    $('#image_upload').on('change', changeIconPreview);

    $('#btn_remove_icon').on('click', function () {
        if (confirm('아이콘을 제거할까요?')) {
            void deleteIcon();
        }
        return false;
    });

    $('#third_use_disallow').on('click', function () {
        if (confirm('개인정보 3자 제공 동의를 철회할까요?')) {
            void disallowThirdUse();
        }
    });

    $('#change_pw_form').on('submit', function (e) {
        e.preventDefault();
        void changePassword();
    });

    $('#change_icon_form').on('submit', function (e) {
        e.preventDefault();
        void changeIcon.apply(this as HTMLFormElement);
    });

    $('#delete_me_form').on('submit', function (e) {
        e.preventDefault();
        if (confirm('한 달 동안 재 가입할 수 없습니다. 정말로 탈퇴할까요?')) {
            void deleteMe();
        }
    });

    $('#expand_login_token').on('click', extendAuth);
})
