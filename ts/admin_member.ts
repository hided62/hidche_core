import axios from 'axios';
import $ from 'jquery';
import { isNumber } from 'lodash';
import { TemplateEngine } from '../hwe/ts/util/TemplateEngine';
import { InvalidResponse } from '../hwe/ts/defs';
import { setAxiosXMLHttpRequest } from '../hwe/ts/util/setAxiosXMLHttpRequest';
import { unwrap_any } from '../hwe/ts/util/unwrap_any';
import { convertFormData } from '../hwe/ts/util/convertFormData';
import { exportWindow } from './util/exportWindow';

type UserEntry = {
    userID: string,
    userName: string,
    email: string,
    authType: string | null,
    userGrade: number,
    blockUntil: string | null,
    nickname: string,
    icon: string,
    joinDate: string,
    loginDate: string,
    deleteAfter: string | null,
}

type UserListResponse = {
    result: true,
    users: UserEntry[],
    servers: string[],
    allowJoin: boolean,
    allowLogin: boolean,
}

const userFrame = '\
<tr id="userinfo_<%userID%>" data-username="<%userName%>" data-id="<%userID%>">\
    <th scope="row"><%userID%></th>\
    <td><%userName%></td>\
    <td class="small"><%emailFunc(email)%><br>(<%authType%>)</td>\
    <td><%userGradeText%><p class="small hide_text user_grade_<%userGrade%>" style="margin:0;"><%shortDate(blockUntil)%></p></td>\
    <td><%nickname%></td>\
    <td><img class="generalIcon" src="<%icon%>" width="64" height="64"></td>\
    <td class="small"><%slotGeneralList%></td>\
    <td class="small"><%shortDate(joinDate)%></td>\
    <td class="small"><%shortDate(loginDate)%></td>\
    <td class="small"><%shortDate(deleteAfter)%></td>\
    <td>\
        <div class="btn-group" role="group">\
            <button type="button" onclick="changeUserStatus(\'delete\', this);" class="btn btn-danger btn-sm">강제<br>탈퇴</button>\
            <button type="button" onclick="changeUserStatus(\'reset_pw\', this);" class="btn btn-info btn-sm">암호<br>변경</button>\
            <button type="button" onclick="changeUserStatus(\'block\', this);" class="btn btn-warning btn-sm">유저<br>차단</button>\
            <button type="button" onclick="changeUserStatus(\'unblock\', this);" class="btn btn-secondary btn-sm">차단<br>해제</button>\
            <button type="button" onclick="changeUserStatus(\'set_userlevel\', this);" class="btn btn-primary btn-sm">별도<br>권한</button>\
        </div>\
    </td>\
</tr>';

function convUserGrade(grade: number): string {
    const userGradeMap = {
        0: '차단',
        1: '일반',
        4: '특별',
        5: '부운영자',
        6: '운영자'
    };

    if (grade in userGradeMap) {
        return userGradeMap[grade as (keyof typeof userGradeMap)];
    }
    return grade.toString();
}

function fillAllowJoinLogin(result: UserListResponse) {
    if (result.allowJoin) {
        $('#allow_join_y').trigger('click');
    }
    else {
        $('#allow_join_n').trigger('click');
    }

    if (result.allowLogin) {
        $('#allow_login_y').trigger('click');
    }
    else {
        $('#allow_login_n').trigger('click');
    }
}

function fillUserList(result: UserListResponse) {
    const $user_list = $('#user_list');


    $user_list.empty();

    const slotGeneralList = $.map(result.servers, function (value) {
        return `<span class="server_generalName_${value}"></span>`;
    }).join('<br>');

    const emailFunc = function (text: string) {
        return String(text).replace('@', '@<br>');
    }
    const brFunc = function (text: string) {
        return String(text).split(' ').join('<br>');
    };

    const shortDateFunc = function (date: string | null) {
        if (!date) {
            return '-';
        }
        return brFunc(date.substr(2));
    }

    $.each(result.users, function (idx, user) {
        const templateItem = {
            ...user,
            email: user.email ?? '-',
            br: brFunc,
            shortDate: shortDateFunc,
            emailFunc: emailFunc,
            slotGeneralList: slotGeneralList,
            userGradeText: convUserGrade(user.userGrade),
        }

        $user_list.append(
            TemplateEngine(userFrame, templateItem)
        )
    });

    //TODO: slotGeneralList에 값을 채워야함. ajax로 받아올 필요 있음
}

async function changeSystem(action: string, param?: string): Promise<void> {
    const text = `${action}${param ? (', ' + param) : ''}을 진행합니다.`;
    if (!confirm(text)) {
        return;
    }

    let result: InvalidResponse | {
        result: true,
        affected?: number,
    };

    try {
        const response = await axios({
            url: 'j_set_userlist.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                'action': action,
                'param': param ?? null
            })
        })
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        return;
    }

    if (result.affected) {
        alert(`${result.affected}건이 처리되었습니다.`);
        await refreshAll();
    }
    else {
        alert('완료되었습니다.');
    }
}

async function changeUserStatus(action: string, userID: Element | number, param?: number): Promise<void> {
    if (userID instanceof Element) {
        userID = parseInt($(userID).parents('tr').data('id'));
    }
    if (!isNumber(userID)) {
        alert('userID가 올바르게 지정되지 않았습니다!');
        return;
    }


    if (action == 'set_userlevel') {
        if (!isNumber(param)) {
            param = parseInt(prompt('원하는 등급을 입력해주세요.(1:일반, 4:특별, 5:부운영자, 6:운영자)', '1') ?? '0');
        }

        if (param < 1 || param > 6) {
            alert('올바르지 않습니다.');
            return;
        }
    }

    if (action == 'block') {
        if (!isNumber(param)) {
            param = parseInt(prompt('블록 기간을 입력해주세요. <= 0은 반영구(50년)입니다.', '7') ?? '7');
        }
    }

    const userName = unwrap_any<string>($('#userinfo_' + userID).data('username'));

    const text = `${userName}에 대해서 ${action}${param ? (', ' + param) : ''}을 진행합니다.`;
    if (!confirm(text)) {
        return;
    }

    let result: InvalidResponse | {
        result: true,
        detail?: string,
    };

    try {
        const response = await axios({
            url: 'j_set_userlist.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                'action': action,
                'user_id': userID,
                'param': param ?? null
            })
        })
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        return;
    }

    if (result.detail) {
        alert(`완료되었습니다: ${result.detail}`);
    }
    else {
        alert('완료되었습니다.');
    }

    await refreshAll();
}

async function refreshAll() {
    let result: InvalidResponse | UserListResponse;

    try {
        const response = await axios({
            url: 'j_get_userlist.php',
            method: 'post',
            responseType: 'json',
        });
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        return;
    }

    fillAllowJoinLogin(result);
    fillUserList(result);
}

$(async function () {
    setAxiosXMLHttpRequest();
    await refreshAll();

    $('input[name=allow_join], input[name=allow_login]').on('change', async function () {
        const $this = $(this);
        await changeSystem(unwrap_any<string>($this.attr('name')), unwrap_any<string>($this.val()));
    })
});

exportWindow(changeSystem, 'changeSystem');
exportWindow(changeUserStatus, 'changeUserStatus');