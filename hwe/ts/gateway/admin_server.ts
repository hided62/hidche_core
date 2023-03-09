import { exportWindow } from '@util/exportWindow';
import axios from 'axios';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import type { InvalidResponse } from '@/defs';
import { convertFormData } from '@util/convertFormData';
import { TemplateEngine } from "@util/TemplateEngine";
import { unwrap_any } from '@util/unwrap_any';
import { unwrap } from '@util/unwrap';
import '@/gateway/common';

type ServerUpdateResponse = {
    result: true,
    version: string,
    server: string,
    imgResult: false,
}

type ServerRootUpdateResponse = {
    result: true,
    version: string,
    server: string,
    imgResult: true,
    imgDetail: string,
}

type ServerStateItem = {
    name: string,
    korName: string,
    color: string,
    isRoot: boolean,
    lastGitPath: string,
    valid: boolean,
    run: boolean,
    installed: boolean,
    version: string,
    reason: string,
    status?: string,
}

type ServerStateResponse = {
    acl: Record<string, string[]>,
    server: ServerStateItem[],
    grade: number,
}

type ServerChangeResponse = {
    result: true,
    installURL?: string,
}

const serverAdminTemplate = '\
<tr class="bg0 server_admin_<%name%>" data-server_name="<%name%>" data-is_root="<%isRoot%>" data-git-path="<%lastGitPath%>">\
    <th style="color:<%color%>;"><%korName%>(<%name%>)</th>\
    <td><%status%></td>\
    <td><%version%></td>\
    <td><button type="button" class="serv_act_close with_skin valid_if_set with_border obj_fill" onclick="modifyServerStatus(this, \'close\');">폐쇄</button></td>\
    <td><button type="button" class="serv_act_open with_skin valid_if_set with_border obj_fill" onclick="modifyServerStatus(this, \'open\');">오픈</button></td>\
    <td><a class="just_link" href="../<%name%>/install.php"><button type="button" class="serv_act_reset with_skin valid_if_set with_border obj_fill">리셋</button></a></td>\
    <td><a class="just_link" href="../<%name%>/install_db.php"><button type="button" class="serv_act_hard_reset with_skin valid_if_installed only_admin with_border obj_fill">하드리셋</button></a></td>\
    <td><a class="just_link" href="../<%name%>/_119.php"><button type="button" class="serv_act_119 with_skin valid_if_set with_border obj_fill">서버119</button></a></td>\
    <td><button type="button" class="serv_act_update with_skin with_border obj_fill" onclick="serverUpdate(this);">업데이트</button></td>\
</tr>\
';//TODO: npm install 관련 기능 추가, js/css output 경로 변경

declare global {
    interface Window {
        adminGrade: number;
        aclList: Record<string, string[]>;
    }
}

async function serverUpdate(caller: HTMLElement) {
    const $caller = $(caller);
    const $tr = $caller.parents('tr');
    const server = unwrap_any<string>($tr.data('server_name'));
    let isRoot: string | boolean = unwrap_any<string>($tr.data('is_root'));

    let target = $tr.data('gitPath');

    if (typeof isRoot !== 'boolean') {
        isRoot = (isRoot != 'false');
    }

    let allowFullUpdate = (server in window.aclList && window.aclList[server].indexOf('fullUpdate') >= 0);
    allowFullUpdate = allowFullUpdate || window.adminGrade > 5;

    let allowUpdate = (server in window.aclList && window.aclList[server].indexOf('update') >= 0);
    allowUpdate = allowUpdate || window.adminGrade >= 5;
    allowUpdate = allowUpdate || allowFullUpdate;

    if (!allowUpdate) {
        alert('권한이 없습니다!');
        return;
    }


    if (allowFullUpdate) {
        target = prompt('가져올 git tree-ish 명을 입력해주세요.', target)
        if (!target) {
            return;
        }
    }
    else if (isRoot) {
        if (!confirm('서버 라이브러리, 루트 서버에 대해 git pull을 실행합니다.')) {
            return;
        }
    }
    else if (!confirm('다음 git tree-ish 주소로 업데이트를 시도합니다 : ' + target)) {
        return;
    }

    let result: InvalidResponse | ServerUpdateResponse | ServerRootUpdateResponse;

    try {
        const response = await axios({
            url: '../j_updateServer.php',
            responseType: 'json',
            method: 'post',
            data: convertFormData({
                server: server,
                target: target
            })
        });
        result = response.data;
    } catch (e) {
        console.error(e);
        alert(`에러: ${e}`);
        location.reload();
        return;
    }

    if (!result.result) {
        alert(`실패했습니다: ${result.reason}`);
        return;
    }

    let aux = '';
    if (result.imgResult) {
        aux = ` (이미지 서버 갱신:${result.imgResult}, ${result.imgDetail})`;
    }
    alert(`${result.server} 서버가 ${result.version} 버전으로 업데이트 되었습니다.${aux}`);
}

function drawServerAdminList(serverList: ServerStateResponse) {
    const $table = $('#server_admin_list');
    const $showErrorLog = $('#showErrorLog');

    if (serverList.grade >= 5) {
        $showErrorLog.show();
    }
    $.each(serverList.server, function (idx, server) {
        console.log(server);
        let status: string;
        if (!server.valid) {
            status = `에러, ${server.reason}`;
        }
        else if (!server.run) {
            status = '폐쇄됨';
        }
        else {
            status = '운영 중';
        }
        server.status = status;

        const $tr = $(TemplateEngine(serverAdminTemplate, server));
        $table.append($tr);
        if (serverList.grade < 4) {
            $tr.find('button').prop('disabled', true);
        }
        if (!server.valid) {
            $tr.find('.valid_if_set').prop('disabled', true);
        }
        if (!server.installed) {
            $tr.find('.valid_if_installed').prop('disabled', true);
        }



        const aclByServer = serverList.acl[server.name];

        $.each(aclByServer, function (idx, action) {
            console.log(action);
            if (action == 'update' || action == 'fullUpdate') {
                if (!server.installed) {
                    return true;
                }
                $tr.find('.serv_act_update').prop('disabled', false);
                $showErrorLog.show();
            }
            else if (action == 'openClose') {
                if (!server.valid) {
                    return true;
                }
                $tr.find('.serv_act_open, .serv_act_close').prop('disabled', false);
            }
            else if (action == 'reset') {
                if (!server.installed) {
                    return true;
                }
                $tr.find('.serv_act_reset, .serv_act_close').prop('disabled', false);
            }
        });
    });
    window.adminGrade = serverList.grade;
    window.aclList = serverList.acl;
    if (serverList.grade <= 5) {
        $table.find('.only_admin').prop('disabled', true);
    }
}

export async function loadPlugin(): Promise<void> {
    setAxiosXMLHttpRequest();

    Entrance_AdminInit();

    const response = await axios({
        url: 'j_server_get_admin_status.php',
        method: 'post',
        responseType: 'json'
    });

    drawServerAdminList(response.data);
}

function Entrance_AdminInit() {
    console.log('adminInit');
    $("#Entrance_000202").on('click', Entrance_Member);
    $("#notice_change_btn").on('click', Entrance_AdminChangeNotice);
}

function Entrance_Member(e: JQuery.Event) {
    e.preventDefault();
    $("#Entrance_00").hide();
    $("#EntranceMember_00").show();
}

async function Entrance_AdminChangeNotice(e: JQuery.Event) {
    e.preventDefault();
    const notice = unwrap_any<string>($("#notice_edit").val());

    if (!confirm('정말 실행하시겠습니까?')) {
        return;
    }

    let result: InvalidResponse | ServerChangeResponse;
    try {
        const response = await axios({
            url: 'j_server_change_status.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                action: 'notice',
                notice: notice
            })
        });
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
    }

    if (!result.result) {
        alert(`실패했습니다: ${result.reason}`);
        return;
    }

    location.reload();
}

async function modifyServerStatus(caller: HTMLElement, action: string) {
    const $caller = $(caller);
    const server = unwrap_any<string>($caller.parents('tr').data('server_name'));

    if (!confirm('정말 실행하시겠습니까?')) {
        return;
    }

    let result: InvalidResponse | ServerChangeResponse;

    try {
        const response = await axios({
            url: 'j_server_change_status.php',
            method: 'post',
            responseType: 'json',
            data: convertFormData({
                server: server,
                action: action
            })
        });
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
    }

    if (!result.result) {
        alert(`실패했습니다: ${result.reason}`);
        return;
    }

    if (action == 'reset') {
        location.href = unwrap(result.installURL);
    } else {
        location.reload();
    }
}

function Entrance_AdminOpen119(caller: HTMLElement) {
    const $caller = $(caller);
    const serverDir = $caller.parents('tr').data('server_name');
    location.href = `../${serverDir}/_119.php`;
}

exportWindow(modifyServerStatus, 'modifyServerStatus');
exportWindow(Entrance_AdminOpen119, 'Entrance_AdminOpen119');
exportWindow(serverUpdate, 'serverUpdate');