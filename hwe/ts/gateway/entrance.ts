import { exportWindow } from '@util/exportWindow';
import $ from 'jquery';
exportWindow($, '$');
import axios from 'axios';
import { initTooltip } from "@/legacy/initTooltip";
import { TemplateEngine } from '@util/TemplateEngine';
import type { InvalidResponse } from '@/defs';
import { getDateTimeNow } from '@util/getDateTimeNow';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { loadPlugin as loadAdminPlugin } from '@/gateway/admin_server';
import '@/gateway/common';

declare const isAdmin: boolean;

const serverListTemplate = "\
<tr class='server_item bg0 server_name_<%name%>' data-server='<%name%>'>\
    <td class='server_name obj_tooltip' data-bs-toggle='tooltip' data-bs-placement='bottom'>\
        <span style='font-weight:bold;font-size:1.4em;color:<%color%>'><%korName%>섭</span><br>\
        <span class='n_country'></span>\
        <span class='tooltiptext server_date'></span>\
    </td>\
    <td colspan='4' class='server_down'>- 폐 쇄 중 -</td>\
</tr>\
";

const serverTextInfo = "\
<td>\
서기 <%year%>년 <%month%>월 (<span style='color:orange;'><%scenario%></span>)<br>\
유저 : <%userCnt%> / <%maxUserCnt%>명 <span style='color:cyan;'>NPC : <%npcCnt%>명</span> (<span style='color:limegreen;'><%turnTerm%>분 턴 서버</span>)<br>\
(상성 설정:<%fictionMode%>), (기타 설정:<%otherTextInfo%>)\
</td>\
";

const serverProvisionalInfo = "\
<td>\
- 오픈 일시 : <%opentime%> -<br>\
서기 <%year%>년 <%month%>월 (<span style='color:orange;'><%scenario%></span>)<br>\
유저 : <%userCnt%> / <%maxUserCnt%>명 <span style='color:cyan;'>NPC : <%npcCnt%>명</span> (<span style='color:limegreen;'><%turnTerm%>분 턴 서버</span>)<br>\
(상성 설정:<%fictionMode%>), (기타 설정:<%otherTextInfo%>)\
</td>\
";

const serverFullTemplate = "\
<td colspan='4' class='server_full'>- 장수 등록 마감 -</td>\
";

const serverCreateTemplate = "\
<td colspan='2' class='not_registered'>- 미 등 록 -\
<td class='ignore_border'>\
<div class='d-grid'>\
<%if(canCreate) {%>\
<a class='btn btn-secondary server-action' href='<%serverPath%>/v_join.php'>장수생성</a>\
<%}%>\
<%if(canSelectNPC) {%>\
<a class='btn btn-secondary server-action' href='<%serverPath%>/select_npc.php'>장수빙의</a>\
<%}%>\
<%if(canSelectPool) {%>\
<a class='btn btn-secondary server-action' href='<%serverPath%>/select_general_from_pool.php'>장수선택</a>\
<%}%>\
</div></td>";

const serverLoginTemplate = "\
<td style='background:url(\"<%picture%>\");background-size: 64px 64px;background-repeat: no-repeat;background-position: center center;'></td>\
<td><%name%></td>\
<td class='ignore_border'>\
<div class='d-grid'>\
<a href='<%serverPath%>/' class='btn btn-secondary server-action'>입장</a>\
</div></td>\
";

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const serverReservedTemplate = "\
<td colspan='4' class='server_reserved'>\
<%openDatetime==starttime?'':'- 가오픈 일시 : '+openDatetime+ '- <br>'%>\
- 오픈 일시 : <%starttime%> - <br>\
<span style='color:orange;'><%scenarioName%></span> <span style='color:limegreen;'><%turnterm%>분 턴 서버</span><br>\
(상성 설정:<%fictionMode%>), (빙의 여부:<%npcMode%>), (최대 스탯:<%gameConf.defaultStatTotal%>), (기타 설정:<%otherTextInfo%>)\
</td>\
";

type ServerResponseItem = {
    color: string,
    korName: string,
    name: string,
    exists: boolean,
    enable: boolean,
};

type ServerResponse = {
    result: true;
    reason: 'success';
    server: ServerResponseItem[];
}

type RawServerResponse = InvalidResponse | ServerResponse;

type ReservedGameInfo = {
    scenarioName: string,
    turnterm: number,
    fictionMode: '가상' | '사실',
    block_general_create: number,
    npcMode: '불가' | '가능' | '선택 생성',
    openDatetime: string,
    starttime: string,
    gameConf: Record<string, string | number>,
    otherTextInfo: string,
}

type GameInfo = {
    isUnited: number,
    npcMode: '불가' | '가능' | '선택 생성',
    year: number,
    month: number,
    scenario: string,
    maxUserCnt: number,
    turnTerm: number,
    opentime: string,
    starttime: string,
    turntime: string,
    join_mode: string,
    fictionMode: '가상' | '사실',
    block_general_create: number,
    autorun_user: string,
    userCnt: number,
    npcCnt: number,
    nationCnt: number,
    otherTextInfo: string,
    defaultStatTotal: number,
}

type MyInfo = {
    name: string,
    picture: string,
    serverPath?: string,
}

type ServerDetailResponse = {
    reserved?: ReservedGameInfo;
    game: GameInfo;
    me: MyInfo | null | undefined;
}

$(function ($) {
    setAxiosXMLHttpRequest();
    $("#btn_logout").on('click', Entrance_Logout);
    void Entrance_UpdateServer();
    if(isAdmin){
        void loadAdminPlugin();
    }
});

async function Entrance_UpdateServer() {
    let data: RawServerResponse;

    try {
        const response = await axios({
            url: 'j_server_get_status.php',
            responseType: 'json',
            method: 'post'
        });
        data = response.data;
    }
    catch (e) {
        alert(e);
        return;
    }

    if (!data.result) {
        alert(data.reason);
        return;
    }

    await Entrance_drawServerList(data.server);
}

async function Entrance_drawServerList(serverInfos: ServerResponseItem[]) {
    const $serverList = $('#server_list');
    const now = getDateTimeNow();

    const serverDetailInfoP: Record<string, Promise<ServerDetailResponse>> = {};

    for (const serverInfo of serverInfos) {
        if(!serverInfo.exists){
            continue;
        }
        const responseP = axios({
            url: `../${serverInfo.name}/j_server_basic_info.php`,
            method: 'get',
            responseType: 'json'
        }).then(v=>{
            return v.data as ServerDetailResponse;
        });
        serverDetailInfoP[serverInfo.name] = responseP;
    }
    for (const serverInfo of serverInfos) {
        const $serverHtml = $(TemplateEngine(serverListTemplate, serverInfo));
        $serverList.append($serverHtml);
        if (!serverInfo.exists) {
            continue;
        }

        const serverPath = `../${serverInfo.name}`;

        let response: ServerDetailResponse;
        try{
            if(!(serverInfo.name in serverDetailInfoP)){
                continue;
            }
            response = await serverDetailInfoP[serverInfo.name];
        }
        catch(e){
            console.error(e);
            continue;
        }

        if(response.reserved){
            $serverHtml.find('.server_down').detach();
            $serverHtml.append(
                TemplateEngine(serverReservedTemplate, response.reserved)
            );
            initTooltip($serverHtml);
        }

        if(!response.game){
            continue;
        }

        const game = response.game;

        //TODO: 서버 폐쇄 방식을 새롭게 변경
        $serverHtml.find('.server_down').detach();

        if (game.isUnited == 3) {
            $serverHtml.find('.n_country').html('§이벤트 종료§');
            $serverHtml.find('.server_date').html(`${game.starttime} <br>~ ${game.turntime}`);
        } else if (game.isUnited == 1) {
            $serverHtml.find('.n_country').html('§이벤트 진행중§');
            $serverHtml.find('.server_date').html(`${game.starttime} ~`);
        } else if (game.isUnited == 2) {
            $serverHtml.find('.n_country').html('§천하통일§');
            $serverHtml.find('.server_date').html(`${game.starttime} <br>~ ${game.turntime}`);
        } else if (game.opentime <= now) {
            $serverHtml.find('.n_country').html(`<${game.nationCnt}국 경쟁중>`);
            $serverHtml.find('.server_date').html(`${game.starttime} ~`);
        } else {
            $serverHtml.find('.n_country').html('-가오픈 중-');
            $serverHtml.find('.server_date').html(`${game.starttime} ~`);
        }

        if (game.opentime <= now) {
            $serverHtml.append(
                TemplateEngine(serverTextInfo, game)
            );
        } else {
            $serverHtml.append(
                TemplateEngine(serverProvisionalInfo, game)
            );
        }

        if (response.me && response.me.name) {
            const me = response.me;
            me.serverPath = serverPath;
            $serverHtml.append(
                TemplateEngine(serverLoginTemplate, me)
            );
        } else if (game.userCnt >= game.maxUserCnt) {
            $serverHtml.append(
                TemplateEngine(serverFullTemplate, {})
            );
        } else {
            $serverHtml.append(
                TemplateEngine(serverCreateTemplate, {
                    serverPath: serverPath,
                    canCreate: !(game.block_general_create & 1),
                    canSelectNPC: game.npcMode == '가능',
                    canSelectPool: game.npcMode == '선택 생성'
                })
            )
        }
        initTooltip($serverHtml);
    }
}

async function Entrance_Logout() {
    const response = await axios({
        url: 'j_logout.php',
        method: 'post',
        responseType: 'json'
    });

    const data: InvalidResponse = response.data;
    if(!data.result){
        alert(`로그아웃 실패: ${data.reason}`);
        return;
    }
    location.href = "../";
}