
import $ from 'jquery';
import type { InvalidResponse } from '@/defs';
import { getDateTimeNow } from '@util/getDateTimeNow';
import axios from 'axios';
import { convertFormData } from '@util/convertFormData';
import { isBrightColor } from "@util/isBrightColor";
import { unwrap } from '@util/unwrap';
import _, { isError, isString } from 'lodash-es';
import { addMinutes } from 'date-fns';
import { parseTime } from '@util/parseTime';
import { formatTime } from '@util/formatTime';
import { TemplateEngine } from '@util/TemplateEngine';
import { isNotNull } from '@util/isNotNull';
import { unwrap_any } from '@util/unwrap_any';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { exportWindow } from '@util/exportWindow';
import { SammoAPI } from './SammoAPI';
import type { MsgItem, MsgResponse } from './defs/API/Message';

const messageTemplate = `<div
    class="msg_plate msg_plate_<%msgType%> msg_plate_<%nationType%>"
    id="msg_<%id%>"
    data-id="<%id%>"
>
    <div class="msg_icon">
        <%if(src.icon){ %>
            <img class='generalIcon' width='64' height='64' src="<%encodeURI(src.icon)%>">
        <%} else {%>
            <img class='generalIcon' width='64' height='64' src="<%encodeURI(defaultIcon)%>">
        <%}%>
    </div>
    <div class="msg_body">
        <div class="msg_header">
            <%if(!this.option.action && src.id == myGeneralID && now <= last5min && invalidType == 'msg_valid' && !deletable){%>
                <button type="button" data-erase_until="<%last5min%>" class="btn btn btn-outline-warning btn-sm btn-delete-msg" style='float:right'>❌</button>
            <%}%>
            <%if(msgType == 'private') {%>
                <%if(src.name == generalName){%>
                <span class="msg_target msg_<%src.colorType%>" style="background-color:<%src.color%>;">나</span
                ><span class="msg_from_to">▶</span
                ><span class="msg_target msg_<%dest.colorType%>" style="background-color:<%dest.color%>;"><%e(dest.name)%>:<%dest.nation%></span>
                <%}else{%>
                <span class="msg_target msg_<%src.colorType%>" style="background-color:<%src.color%>;"><%e(src.name)%>:<%src.nation%></span
                ><span class="msg_from_to">▶</span
                ><span class="msg_target msg_<%dest.colorType%>" style="background-color:<%dest.color%>;">나</span>
                <%}%>
            <%} else if(msgType == 'national' && src.nation_id == dest.nation_id){%>
                <span class="msg_target msg_<%src.colorType%>" style="background-color:<%src.color%>;"><%e(src.name)%></span>
            <%} else if(msgType == 'national' || msgType == 'diplomacy'){%>
                <%if(src.nation_id == nationID){%>
                <span class="msg_target msg_<%src.colorType%>" style="background-color:<%src.color%>;"><%e(src.name)%></span
                ><span class="msg_from_to">▶</span
                ><span class="msg_target msg_<%dest.colorType%>" style="background-color:<%dest.color%>;"><%dest.nation%></span>
                <%}else{%>
                <span class="msg_target msg_<%src.colorType%>" style="background-color:<%src.color%>;"><%e(src.name)%>:<%src.nation%></span
                ><span class="msg_from_to"></span>
                <%}%>
            <%} else {%>
                <span class="msg_target msg_<%src.colorType%>" style="background-color:<%src.color%>;"><%e(src.name)%>:<%src.nation%></span>
            <%} %>
            <span class="msg_time">&lt;<%e(time)%>&gt;</span>
        </div>
        <div class="msg_content <%invalidType%>"><%linkifyStr(text)%></div>
        <%if(this.option && this.option.action) {%>
        <div class="msg_prompt">
            <button type="button" class="prompt_yes btn_prompt" <%allowButton?'':'disabled="disabled"'%>>수락</button> <button type="button" class="prompt_no btn_prompt" <%allowButton?'':'disabled="disabled"'%>>거절</button>
        </div><%} %></div></div>`;
let myGeneralID: number | undefined;
//let isChief: boolean | undefined;
let lastSequence: number | undefined;
let myNation: {
    id: number,
    mailbox: number,
    color: string,
    nation: string,
} | undefined;
//let myOfficerLevel: number | undefined;
let permissionLevel: number | undefined;

type MsgType = 'private' | 'public' | 'national' | 'diplomacy';
const minMsgSeq: Record<MsgType, number> = {
    'private': 0x7fffffff,
    'public': 0x7fffffff,
    'national': 0x7fffffff,
    'diplomacy': 0x7fffffff,
}

type MsgTarget = {
    id: number,
    name: string,
    nation_id: number, //XXX: 왜 이 값은 nationID가 아니고 nation_id인가?
    nation: string;
    color: string;
    icon: string;
}

type MsgPrintItem = MsgItem & {
    generalName: string;
    nationID: number;
    nationType: 'local' | 'src' | 'dest';
    myGeneralID: number;
    allowButton: boolean;
    last5min: string;
    now: string;
    invalidType: 'msg_invalid' | 'msg_valid';
    deletable: boolean;
    src: MsgTarget & { colorType: 'bright' | 'dark' },
    dest: MsgTarget & { colorType: 'bright' | 'dark' },
    defaultIcon: string,
}

type BasicGeneralTarget = {
    id: number,
    textName: string,
    name?: string,
    isRuler?: boolean,
    nation?: number,
    color?: string,
}

type MailboxItem = {
    id: number,
    mailbox: number,
    color: string,
    name: string,
    nationID: number,
    //nation: string,
    general: [number, string, number][]
}

type MabilboxListResponse = {
    result: true,
    nation: MailboxItem[]
}

type BasicInfoResponse = {
    generalID: number,
    myNationID: number,
    isChief: boolean,
    officerLevel: number,
    permission: number,
}


let generalList: Record<number, BasicGeneralTarget> = {};

async function responseMessage(msgID: number, responseAct: boolean): Promise<void> {
    try{
        await SammoAPI.Message.DecideMessageResponse({msgID, response: responseAct});
        location.reload();
    }
    catch(e){
        if(isString(e)){
            alert(e);
        }
        if(isError(e)){
            alert(e.message);
        }
        console.error(e);
    }
}

async function deleteMessage(msgID: number): Promise<MsgResponse> {
    try{
        await SammoAPI.Message.DeleteMessage({msgID});
    }
    catch(e){
        if(isString(e)){
            alert(e);
        }
        if(isError(e)){
            alert(e.message);
        }
        console.error(e);
    }
    return await refreshMsg();
}

export async function refreshMsg(): Promise<MsgResponse> {
    const value = await fetchRecentMsg();
    return redrawMsg(value, true);
}
exportWindow(refreshMsg, 'refreshMsg');

async function fetchRecentMsg(): Promise<MsgResponse> {
    try {
        console.log(lastSequence);
        return await SammoAPI.Message.GetRecentMessage({sequence: lastSequence ?? -1});
    }
    catch (e) {
        console.error(e);
        throw e;
    }
}

async function showOldMsg(msgType: MsgType): Promise<MsgResponse> {
    try{
        const response = await SammoAPI.Message.GetOldMessage({
            type: msgType,
            to: minMsgSeq[msgType],
        });
        return redrawMsg(response, false);
    }
    catch(e){
        if(isString(e)){
            alert(e);
        }
        if(isError(e)){
            alert(e.message);
        }
        console.error(e);
        throw e;
    }
}

function redrawMsg(msgResponse: MsgResponse, addFront: boolean): MsgResponse {
    function checkErasable(obj: MsgResponse) {

        const now = getDateTimeNow();
        $('.btn-delete-msg').each(function () {
            const $btn = $(this);
            const eraseUntil = $btn.data('erase_until');
            if (eraseUntil < now) {
                $btn.detach();
            }
        })
        return obj;
    }
    function registerSequence(obj: MsgResponse): MsgResponse {
        lastSequence = Math.max(lastSequence ?? 0, obj.sequence);
        for (const msgType of ['public', 'private', 'national', 'diplomacy'] as MsgType[]) {
            const msgList = obj[msgType];
            if (!msgList || msgList.length == 0) {
                continue;
            }
            const lastMsg = unwrap(_.last(msgList));
            minMsgSeq[msgType] = Math.min(minMsgSeq[msgType], lastMsg.id);
        }
        return obj;
    }

    function printTemplate(obj: MsgResponse) {
        const printList: [MsgItem[], JQuery<HTMLElement>, MsgType][] = [
            [obj.public, $('#message_board .public_message'), 'public'],
            [obj.private, $('#message_board .private_message'), 'private'],
            [obj.diplomacy, $('#message_board .diplomacy_message'), 'diplomacy'],
            [obj.national, $('#message_board .national_message'), 'national'],
        ];

        for (const [msgSource, $msgBoard, msgType] of printList) {
            if (!msgSource || $msgBoard.length == 0) {
                console.log('No Items', msgSource, $msgBoard);
                continue;
            }


            let needRefreshLastContact = (msgType == 'private');

            const now = getDateTimeNow();
            //list의 맨 앞이 가장 최신 메시지임.
            const $msgs: JQuery<HTMLElement>[] = msgSource.map(function (msg) {

                const contactTarget = (msg.src.id != myGeneralID || msg.msgType === 'public') ? msg.src.id : unwrap(msg.dest).id;
                if (needRefreshLastContact && contactTarget != myGeneralID && contactTarget in generalList) {
                    needRefreshLastContact = false;
                    $('#last_contact').val(contactTarget).html(generalList[contactTarget].textName).show();
                }

                const nationID = obj.nationID;
                const generalName = obj.generalName;
                let nationType: MsgPrintItem["nationType"];

                const src = {
                    ...msg.src,
                    colorType: isBrightColor(msg.src.color) ? 'bright' as const : 'dark' as const,
                };

                if (!src.nation) {
                    src.nation = '재야';
                    src.color = '#000000';
                }

                const dest = {
                    ...(msg.dest ?? msg.src),
                    colorType: isBrightColor((msg.dest ?? msg.src).color) ? 'bright' as const : 'dark' as const,
                };

                if (!dest.nation) {
                    dest.nation = '재야';
                    dest.color = '#000000';
                }

                if (src.nation_id == dest.nation_id) {
                    nationType = 'local';
                }
                else if (nationID == src.nation_id) {
                    nationType = 'src';
                }
                else {
                    nationType = 'dest';
                }


                const defaultIcon = `${window.pathConfig.sharedIcon}/default.jpg`;
                let allowButton: boolean;
                if (msgType == 'diplomacy') {
                    allowButton = unwrap(permissionLevel) >= 4;
                }
                else {
                    allowButton = true;
                }

                const last5min = formatTime(addMinutes(parseTime(msg.time), 5));
                let invalidType: MsgPrintItem['invalidType'];
                if (msg.option && msg.option.invalid) {
                    invalidType = 'msg_invalid';
                }
                else {
                    invalidType = 'msg_valid';
                }

                let deletable: boolean;
                if (msg.option && !msg.option.deletable) {
                    deletable = false;
                }
                else {
                    deletable = true;
                }

                const printMsg: MsgPrintItem = {
                    ...msg,
                    generalName,
                    nationID,
                    nationType,
                    myGeneralID: unwrap(myGeneralID),
                    src,
                    dest,
                    now,
                    allowButton,
                    last5min,
                    invalidType,
                    deletable,
                    defaultIcon,
                    msgType,
                };

                const msgHtml = TemplateEngine(unwrap(messageTemplate), printMsg);


                //만약 이전 메시지와 같은 id가 온 경우 덮어씌운다.
                //NOTE:현 프로세스 상에서는 같은 id가 와선 안된다.
                const $existMsg = $(`#msg_${msg.id}`);
                let $msg = $(msgHtml);
                if ($existMsg.length) {
                    console.log('메시지 충돌', $msg, $existMsg);
                    $existMsg.html($msg.html());
                    $msg = $existMsg;
                }

                let hideMsg = false;
                if (msg.option) {
                    if (msg.option.delete !== undefined) {
                        //delete는 삭제.
                        $(`#msg_${msg.option.delete}`).detach();
                    }
                    if (msg.option.overwrite !== undefined) {
                        //overwrite는 숨기기.
                        $.map(msg.option.overwrite, function (overwriteID) {
                            const $msg = $(`#msg_${overwriteID}`);
                            $msg.find('.btn-delete-msg').detach();
                            $msg.find('.msg_content').html('삭제된 메시지입니다.').removeClass('msg_valid').addClass('msg_invalid');
                        });

                    }
                    if (msg.option.hide) {
                        hideMsg = true;
                    }
                }

                if (hideMsg) {
                    return null;
                }

                $msg.find('.btn-delete-msg').on('click', async function () {
                    if (!confirm("삭제하시겠습니까?")) {
                        return false;
                    }
                    await deleteMessage(msg.id);
                });

                $msg.find('button.prompt_yes').on('click', async function () {
                    if (!confirm("수락하시겠습니까?")) {
                        return false;
                    }
                    await responseMessage(msg.id, true);

                });

                $msg.find('button.prompt_no').on('click', async function () {
                    if (!confirm("거절하시겠습니까?")) {
                        return false;
                    }
                    await responseMessage(msg.id, false);
                });

                if ($existMsg.length) {
                    return null;
                }
                else {
                    return $msg;
                }

            }).filter(isNotNull);

            if (addFront) {
                $msgBoard.prepend($msgs);
            }
            else {
                $msgBoard.find('.load_old_message').before($msgs);
            }

        }

    }


    msgResponse = checkErasable(msgResponse);
    msgResponse = registerSequence(msgResponse);
    printTemplate(msgResponse);
    return msgResponse;
}

function refreshMailboxList(obj: MabilboxListResponse) {
    const $mailboxList = $('#mailbox_list');

    $mailboxList.change(function () {
        console.log($(this).val());
    })

    const oldSelected = $mailboxList.val();

    $mailboxList.empty();

    let $lastContact = $('#last_contact');
    let lastContact: BasicGeneralTarget | undefined;
    if ($lastContact.length > 0 && parseInt(unwrap_any<string>($lastContact.val())) >= 0) {
        lastContact = {
            id: parseInt(unwrap_any<string>($lastContact.val())),
            textName: $lastContact.html()
        };
        //$lastContact = undefined;
    }

    generalList = {};


    obj.nation.sort(function (lhs, rhs) {
        if (lhs.mailbox == unwrap(myNation).mailbox) {
            return -1;
        }
        if (rhs.mailbox == unwrap(myNation).mailbox) {
            return 1;
        }
        return lhs.mailbox - rhs.mailbox;
    })

    for (const nation of obj.nation) {
        //console.log(nation);
        const $optgroup = $(`<optgroup label="${nation.name}"></optgroup>`);
        $optgroup.css('background-color', nation.color);

        if (unwrap(myNation).mailbox == nation.mailbox) {
            unwrap(myNation).color = nation.color;
        }

        if (isBrightColor(nation.color)) {
            $optgroup.css('color', 'black');
        }
        else {
            $optgroup.css('color', 'white');
        }

        nation.general.sort(function (lhs, rhs) {
            if (lhs[1] < rhs[1]) {
                return -1;
            }
            if (lhs[1] > rhs[1]) {
                return 1;
            }
            return 0;
        });

        $.each(nation.general, function () {
            const generalID = this[0];
            const generalName = this[1];
            //const isNPC = !!(this[2] & 0x2);
            const isRuler = !!(this[2] & 0x1);
            const isAmbassador = !!(this[2] & 0x4);



            if (generalID == myGeneralID) {
                return true;
            }

            let textName = generalName;
            if (isRuler) {
                textName = `*${textName}*`;
            }
            else if (isAmbassador) {
                textName = `#${textName}#`;
            }

            generalList[generalID] = {
                id: generalID,
                name: generalName,
                textName: textName,
                isRuler: isRuler,
                nation: nation.nationID,
                color: nation.color
            };

            const $item = $(`<option value="${generalID}">${textName}</option>`);

            if (unwrap(permissionLevel) == 4 && isAmbassador && unwrap(myNation).mailbox != nation.mailbox) {
                $item.prop('disabled', true);
            }
            $optgroup.append($item);
        });

        $mailboxList.append($optgroup);
    }

    const $favorite = $('<optgroup label="즐겨찾기"></optgroup>');

    //아국메시지, 전체메시지
    const $ourCountry = $(`<option value="${unwrap(myNation).mailbox}">【 아국 메세지 】</option>`)
        .css({ 'background-color': unwrap(myNation).color, 'color': isBrightColor(unwrap(myNation).color) ? 'black' : 'white' });
    const $toPublic = $('<option value="9999">【 전체 메세지 】</option>');
    $favorite.append($ourCountry);
    $favorite.append($toPublic);

    $lastContact = $('<option id="last_contact" value="-1"></option>').hide();
    if (lastContact) {
        $lastContact.show().val(lastContact.id).html(lastContact.textName);
    }
    $favorite.append($lastContact);
    //TODO:운영자를 추가하는 코드도 넣을 것.

    if (unwrap(permissionLevel) >= 4) {
        for (const nation of obj.nation) {
            //console.log(nation);
            const $nation = $(`<option value="${nation.mailbox}">${nation.name}</option>`);
            $nation.css('background-color', nation.color);

            if (isBrightColor(nation.color)) {
                $nation.css('color', 'black');
            }
            else {
                $nation.css('color', 'white');
            }
            $favorite.append($nation);
        }

    }


    $mailboxList.prepend($favorite);

    if (!oldSelected) {
        $mailboxList.val(unwrap(myNation).mailbox);
    }
    else {
        $mailboxList.val(oldSelected);
    }
}

function registerGlobal(basicInfo: BasicInfoResponse) {

    myNation = {
        'id': basicInfo.myNationID,
        'mailbox': basicInfo.myNationID + 9000,
        'color': '#000000',
        'nation': '재야'
    };
    myGeneralID = basicInfo.generalID;
    //isChief = basicInfo.isChief;
    //myOfficerLevel = basicInfo.officerLevel;
    permissionLevel = basicInfo.permission;
}

function activateMessageForm() {
    const $msgInput = $('#msg_input');
    const $msgSubmit = $('#msg_submit');
    const $mailboxList = $('#mailbox_list');

    $msgInput.keypress(function (e) {
        const code = (e.keyCode ? e.keyCode : e.which);
        if (code == 13) {
            $msgSubmit.trigger('click');
            return true;
        }
    });

    $msgSubmit.on('click', async function () {

        const text = _.trim(unwrap_any<string>($msgInput.val()));
        $msgInput.val('').trigger('focus');

        const targetMailbox = unwrap_any<string>($mailboxList.val());
        console.log(targetMailbox, text);

        try{
            await SammoAPI.Message.SendMessage({
                mailbox: parseInt(targetMailbox),
                text,
            });
            await refreshMsg();
        }
        catch(e){
            alert(e);
            await refreshMsg();
            return;
        }
    });
}

$(async function ($) {
    setAxiosXMLHttpRequest();

    //basic_info.json은 세션값에 따라 동적으로 바뀌는 데이터임.
    const basicInfoP = axios({
        url: 'j_basic_info.php',
        method: 'post',
        responseType: 'json'
    }).then((v) => registerGlobal(v.data));

    //sender_list.json 은 서버측에선 캐시 가능한 데이터임.
    const senderListP = SammoAPI.Message.GetContactList();

    const messageListP = fetchRecentMsg();

    await basicInfoP
    const senderList = await senderListP;
    refreshMailboxList(senderList);
    activateMessageForm();

    const messageList = await messageListP;
    redrawMsg(messageList, true);
    $('.load_old_message').click(function () {
        const $this = $(this);
        const msgType = $this.data('msg_type');
        void showOldMsg(msgType);
    })
});