<template>
  <div class="MessagePanel">
    <div class="MessageInputForm bg0 row gx-0">
      <div id="mailbox_list-col" class="col-6 col-lg-2 d-grid">
        <BFormSelect v-model="targetMailbox" class="bg-dark text-white">
          <optgroup
            v-for="group of mailboxList"
            :key="group.label"
            :label="group.label"
            :style="{
              backgroundColor: group.color,
              color: !group.color ? undefined : isBrightColor(group.color) ? '#000000' : '#ffffff',
            }"
          >
            <BFormSelectOption
              v-for="target of group.options"
              :key="target.value"
              :disabled="target.disabled"
              :value="target.value"
              :style="{
                backgroundColor: target.color ?? '#000000',
                color: isBrightColor(target.color ?? '#000000') ? '#000000' : '#ffffff',
              }"
              >{{ target.text }}
            </BFormSelectOption>
          </optgroup>
        </BFormSelect>
      </div>
      <div id="msg_input-col" class="col-12 col-lg-8 d-grid">
        <input v-model="newMessageText" type="text" maxlength="99" class="form-control" @keydown.enter="sendMessage" />
      </div>
      <div id="msg_submit-col" class="col-6 col-lg-2 d-grid">
        <BButton variant="primary" @click="sendMessage">서신전달&amp;갱신</BButton>
      </div>
    </div>
    <div class="PublicTalk">
      <div class="stickyAnchor"></div>
      <div class="BoardHeader bg0 d-flex">
        <div class="flex-grow-1 align-self-center">전체 메시지</div>
        <div>
          <BButton size="sm" class="btn-more-small" variant="primary" @click="targetMailbox = 9999"
            ><i class="bi bi-reply-fill" />여기로</BButton
          >
        </div>
      </div>
      <template v-if="messagePublic.length == 0">
        <div>메시지가 없습니다.</div>
      </template>
      <div v-else class="MessageList">
        <template v-for="msg of messagePublic" :key="msg.id">
          <MessagePlate
            v-if="!msg.option.hide"
            :modelValue="msg"
            :generalID="generalID"
            :generalName="generalName"
            :nationID="nationID"
            :permissionLevel="permissionLevel"
            :deleted="deletedMessage.has(msg.id)"
            @setTarget="setTarget"
            @request-refresh="tryRefresh"
          ></MessagePlate>
        </template>
        <div class="d-grid Actions">
          <button type="button" class="btn btn-dark only-mobile" @click="foldMessage($event, 'public')">접기</button>
          <button type="button" class="btn btn-secondary" @click="loadOldMessage($event, 'public')">
            이전 메시지 불러오기
          </button>
        </div>
      </div>
    </div>
    <div class="NationalTalk">
      <div class="stickyAnchor"></div>
      <div class="BoardHeader bg0 d-flex">
        <div class="flex-grow-1 align-self-center">국가 메시지</div>
        <div>
          <BButton size="sm" class="btn-more-small" variant="primary" @click="targetMailbox = 9000 + props.nationID"
            ><i class="bi bi-reply-fill" />여기로</BButton
          >
        </div>
      </div>
      <template v-if="messageNational.length == 0">
        <div>메시지가 없습니다.</div>
      </template>
      <div v-else class="MessageList">
        <template v-for="msg of messageNational" :key="msg.id">
          <MessagePlate
            v-if="!msg.option.hide"
            :modelValue="msg"
            :generalID="generalID"
            :generalName="generalName"
            :nationID="nationID"
            :permissionLevel="permissionLevel"
            :deleted="deletedMessage.has(msg.id)"
            @setTarget="setTarget"
            @request-refresh="tryRefresh"
          ></MessagePlate>
        </template>
        <div class="d-grid Actions">
          <button type="button" class="btn btn-dark only-mobile" @click="foldMessage($event, 'national')">접기</button>
          <button type="button" class="btn btn-secondary" @click="loadOldMessage($event, 'national')">
            이전 메시지 불러오기
          </button>
        </div>
      </div>
    </div>
    <div class="PrivateTalk">
      <div class="stickyAnchor"></div>
      <div class="BoardHeader bg0 d-flex">
        <div class="flex-grow-1 align-self-center">개인 메시지</div>
        <div>
          <BButton
            size="sm"
            class="btn-more-small"
            variant="secondary"
            :disabled="!(messagePrivate.length > 0 && latestPrivateMsgToastInfo[2] > latestPrivateMsgToastInfo[1])"
            @click="readLatestMsg('private')"
            >모두 읽음</BButton
          >
        </div>
      </div>
      <template v-if="messagePrivate.length == 0">
        <div>메시지가 없습니다.</div>
      </template>
      <div v-else class="MessageList">
        <template v-for="msg of messagePrivate" :key="msg.id">
          <MessagePlate
            v-if="!msg.option.hide"
            :modelValue="msg"
            :generalID="generalID"
            :generalName="generalName"
            :nationID="nationID"
            :permissionLevel="permissionLevel"
            :deleted="deletedMessage.has(msg.id)"
            @setTarget="setTarget"
            @request-refresh="tryRefresh"
          ></MessagePlate>
        </template>
        <div class="d-grid Actions">
          <button type="button" class="btn btn-dark only-mobile" @click="foldMessage($event, 'private')">접기</button>
          <button type="button" class="btn btn-secondary" @click="loadOldMessage($event, 'private')">
            이전 메시지 불러오기
          </button>
        </div>
      </div>
    </div>
    <div class="DiplomacyTalk">
      <div class="stickyAnchor"></div>
      <div class="BoardHeader bg0 d-flex">
        <div class="flex-grow-1 align-self-center">외교 메시지</div>
        <div>
          <BButton
            size="sm"
            class="btn-more-small"
            variant="secondary"
            :disabled="!(messagePrivate.length > 0 && latestPrivateMsgToastInfo[2] > latestPrivateMsgToastInfo[1])"
            @click="readLatestMsg('diplomacy')"
            >모두 읽음</BButton
          >
        </div>
      </div>
      <template v-if="messageDiplomacy.length == 0">
        <div>메시지가 없습니다.</div>
      </template>
      <div v-else class="MessageList">
        <template v-for="msg of messageDiplomacy" :key="msg.id">
          <MessagePlate
            v-if="!msg.option.hide"
            :modelValue="msg"
            :generalID="generalID"
            :generalName="generalName"
            :nationID="nationID"
            :permissionLevel="permissionLevel"
            :deleted="deletedMessage.has(msg.id)"
            @setTarget="setTarget"
            @request-refresh="tryRefresh"
          ></MessagePlate>
        </template>
        <div class="d-grid Actions">
          <button type="button" class="btn btn-dark only-mobile" @click="foldMessage($event, 'diplomacy')">접기</button>
          <button type="button" class="btn btn-secondary" @click="loadOldMessage($event, 'diplomacy')">
            이전 메시지 불러오기
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
declare const staticValues: {
  serverName: string;
  serverNick: string;
  serverID: string;
  mapName: string;
  unitSet: string;
};
</script>
<script setup lang="ts">
import { onMounted, ref, toRef, watch, type Ref } from "vue";
import { delay } from "@/util/delay";
import { SammoAPI } from "@/SammoAPI";
import { isString } from "lodash-es";
import type { MabilboxListResponse, MsgItem, MsgResponse, MsgTarget, MsgType } from "@/defs/API/Message";
import MessagePlate from "@/components/MessagePlate.vue";
import { useToast, BFormSelect } from "bootstrap-vue-next";
import { unwrap } from "@/util/unwrap";
import { isBrightColor } from "@/util/isBrightColor";
import { getNewMsgToast } from "@/utilGame/getNewMsgToast";
import { scrollToSelector } from "@/util/scrollToSelector";

const serverID = staticValues.serverID;
const toasts = unwrap(useToast());

const emit = defineEmits<{
  (event: "request-refresh"): void;
}>();

const props = defineProps<{
  generalID: number;
  generalName: string;
  nationID: number;
  permissionLevel: number;
}>();

const generalID = toRef(props, "generalID");
const generalName = toRef(props, "generalName");
const nationID = toRef(props, "nationID");
const permissionLevel = toRef(props, "permissionLevel");

let nationMailbox = nationID.value + 9000;
watch(nationID, (newVal) => {
  nationMailbox = newVal + 9000;
});

const lastSequence = ref(-1);

const initRefreshLimit = 20;
let refreshLimit = initRefreshLimit;
const refreshP: Set<Promise<boolean>> = new Set();
let lastRefreshDone = 0;
let refreshTimer: null | number = null;

const messageStorage = new Map<number, MsgItem>();
const messagePublic = ref<MsgItem[]>([]);
const messageNational = ref<MsgItem[]>([]);
const messagePrivate = ref<MsgItem[]>([]);
const messageDiplomacy = ref<MsgItem[]>([]);

const deletedMessage = ref(new Set<number>());

const messageIndexedList: Record<MsgType, Ref<MsgItem[]>> = {
  public: messagePublic,
  national: messageNational,
  private: messagePrivate,
  diplomacy: messageDiplomacy,
};

const latestPrivateMsgToastInfo = ref<[string | undefined, number, number]>([undefined, 0, 0]);
const latestDiplomacyMsgToastInfo = ref<[string | undefined, number, number]>([undefined, 0, 0]);

function readLatestMsg(msgType: MsgType) {
  const targetMap = {
    private: latestPrivateMsgToastInfo,
    diplomacy: latestDiplomacyMsgToastInfo,
    public: undefined,
    national: undefined,
  };

  const target = targetMap[msgType];
  if (!target) {
    return;
  }

  const [toastID, , lastestReceivedID] = target.value;
  if (toastID) {
    toasts.remove(toastID);
  }

  if(msgType == "private" || msgType == "diplomacy"){
    void SammoAPI.Message.ReadLatestMessage({
      type: msgType,
      msgID: lastestReceivedID,
    });
  }
  target.value = [undefined, lastestReceivedID, lastestReceivedID];
}

function _updateLatestMsg(msg: MsgItem) {
  const msgType = msg.msgType;

  //TODO: 메시지함으로 바로 이동하는 기능이 나중에 필요할 것
  if (msgType == "private") {
    const [toastID, latestMsgID] = latestPrivateMsgToastInfo.value;
    if (msg.id <= latestMsgID) {
      return;
    }

    if (toastID) {
      toasts.remove(toastID);
    }
    const newToastID = toasts.show(
      getNewMsgToast("새로운 개인 메시지", "새로운 개인 메시지가 도착했습니다.", (type, e) => {
        if (type === "goto") {
          readLatestMsg("private");
          scrollToSelector(".PrivateTalk > .stickyAnchor");
          return;
        }
        if (type === "ignore") {
          readLatestMsg("private");
          return;
        }
      }),
      {
        delay: 1000 * 60 * 10,
        variant: "warning",
      }
    ).options.id;
    latestPrivateMsgToastInfo.value = [newToastID, latestMsgID, msg.id];
    return;
  }

  if (msgType == "diplomacy") {
    if (permissionLevel.value < 4) {
      return;
    }
    const [toastID, latestMsgID] = latestDiplomacyMsgToastInfo.value;

    if (msg.id <= latestMsgID) {
      return;
    }

    if (toastID) {
      toasts.remove(toastID);
    }
    const newToastID = toasts.show(
      getNewMsgToast("새로운 외교 메시지", "새로운 외교 메시지가 도착했습니다.", (type, e) => {
        if (type === "goto") {
          readLatestMsg("diplomacy");
          scrollToSelector(".DiplomacyTalk > .stickyAnchor");
          return;
        }
        if (type === "ignore") {
          readLatestMsg("diplomacy");
          return;
        }
      }),
      {
        delay: 1000 * 60 * 10,
        variant: "warning",
      }
    ).options.id;
    latestDiplomacyMsgToastInfo.value = [newToastID, latestMsgID, msg.id];
    return;
  }
}

function updateLatestMsg(msg: MsgItem[]) {
  for (const msgItem of msg) {
    if (msgItem.src.id == generalID.value) continue;
    _updateLatestMsg(msgItem);
    break;
  }
}

function processMsg(msg: MsgItem) {
  if (msg.option.delete) {
    (() => {
      const targetID = msg.option.delete;
      const targetMsg = messageStorage.get(targetID);
      if (!targetMsg) {
        return;
      }
      targetMsg.option.invalid = true;
    })();
  }
  if (msg.option.overwrite) {
    (() => {
      for(const targetID of msg.option.overwrite) {
        const targetMsg = messageStorage.get(targetID);
        if (!targetMsg) {
          continue;
        }
        deletedMessage.value.add(targetID);
        targetMsg.option.invalid = true;
      }
    })();
  }
}

function updateMsgResponse(response: MsgResponse) {
  if (response.generalName != generalName.value) {
    emit("request-refresh");
    return;
  }
  if (response.nationID != nationID.value) {
    emit("request-refresh");
    return;
  }

  if(response.latestRead){
    const newDiplomacyIdx = response.latestRead.diplomacy;
    const [diplomacyToastID, diplomacyLatestMsgID] = latestDiplomacyMsgToastInfo.value;
    if(diplomacyLatestMsgID < newDiplomacyIdx){
      if(diplomacyToastID){
        toasts.remove(diplomacyToastID);
      }
      latestDiplomacyMsgToastInfo.value = [undefined, newDiplomacyIdx, newDiplomacyIdx];
    }

    const newPrivateIdx = response.latestRead.private;
    const [privateToastID, privateLatestMsgID] = latestPrivateMsgToastInfo.value;
    if(privateLatestMsgID < newPrivateIdx){
      if(privateToastID){
        toasts.remove(privateToastID);
      }
      latestPrivateMsgToastInfo.value = [undefined, newPrivateIdx, newPrivateIdx];
    }
  }


  lastSequence.value = Math.max(lastSequence.value, response.sequence);

  for (const msgType of Object.keys(messageIndexedList) as (keyof typeof messageIndexedList)[]) {
    const msgList = messageIndexedList[msgType];

    const newMsgList = response[msgType];
    if (newMsgList.length == 0) {
      continue;
    }

    //순서가 어떤 순서인지 모르니, 여기서 내림차순으로 맞춘다.
    newMsgList.sort((a, b) => b.id - a.id);

    if (msgList.value.length == 0) {
      msgList.value = newMsgList;
      updateLatestMsg(newMsgList);
      continue;
    }

    //head test
    const filteredMsgList: MsgItem[] = [];

    for (const msg of newMsgList) {
      const oldMsg = messageStorage.get(msg.id);
      if (oldMsg !== undefined) {
        continue;
      }
      processMsg(msg);
      messageStorage.set(msg.id, msg);
      filteredMsgList.push(msg);
    }

    if (filteredMsgList.length == 0) {
      continue;
    }

    const msgHeadID = msgList.value[0].id;
    const newMsgTailID = filteredMsgList[filteredMsgList.length - 1].id;
    if (newMsgTailID > msgHeadID) {
      msgList.value = [...filteredMsgList, ...msgList.value];
      updateLatestMsg(filteredMsgList);
      continue;
    }

    const msgTailID = msgList.value[msgList.value.length - 1].id;
    const newMsgHeadID = filteredMsgList[0].id;

    if (msgTailID > newMsgHeadID) {
      msgList.value.push(...filteredMsgList);
      continue;
    }

    //중간에 삽입되는 경우는 에러이다.
    console.error("중간 삽입 있음", msgType, newMsgList);
  }
}

function beginRefreshTimer() {
  if (refreshTimer) {
    clearInterval(refreshTimer);
    refreshTimer = null;
  }
  refreshTimer = window.setInterval(function () {
    const now = Date.now();
    if (lastRefreshDone + 5000 < now) {
      //만약 서버 응답이 없다면?

      if (refreshP.size > refreshLimit && refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
        toasts.danger(
          {
            title: "메시지 자동 갱신 실패",
            body: "서버 응답이 없습니다. 새로고침을 해주세요.",
          },
          {
            delay: 1000 * 3600,
          }
        );
        return;
      }

      void tryRefresh();
    }
  }, 2500);
}

async function tryRefresh() {
  if (refreshP.size > 0) {
    await Promise.race([...refreshP, delay(500)]);
  }

  const waiterP = (async () => {
    let response: MsgResponse | undefined = undefined;
    try {
      response = await SammoAPI.Message.GetRecentMessage({
        sequence: lastSequence.value,
      });

      const now = Date.now();
      if (lastRefreshDone < now) {
        lastRefreshDone = now;
      }
    } catch (e) {
      if (isString(e)) {
        toasts.warning({
          title: "갱신 실패",
          body: e,
        });
      }
      console.error(e);
    }

    if (!response) {
      return false;
    }

    updateMsgResponse(response);

    return true;
  })();
  void waiterP.then((result) => {
    if (!result) {
      console.error("?! result");
      refreshLimit--;
    } else {
      refreshLimit = initRefreshLimit;
    }
    refreshP.delete(waiterP);
  });
  refreshP.add(waiterP);

  return waiterP;
}

const targetMailbox = ref(nationID.value + 9000);
type MailboxGroup = {
  label: string;
  color?: string;
  options: MailboxTarget[];
};
type MailboxTarget = {
  value: number;
  text: string;
  nationID: number;
  disabled?: true;
  color?: string;
};
const mailboxMap = ref(new Map<number, MailboxTarget>());
const mailboxList = ref<MailboxGroup[]>([]);
const newMessageText = ref<string>("");

function setTarget(type: MsgType, target: MsgTarget): void {
  const item = mailboxMap.value.get(
    (() => {
      if (type == "diplomacy" || type == "national") {
        if (target.nation_id == nationID.value) {
          return target.id;
        }
        return target.nation_id + 9000;
      }
      return target.id;
    })()
  );
  if (!item) {
    return;
  }
  targetMailbox.value = item.value;
}

function refreshMailboxList(obj: MabilboxListResponse) {
  const newMailboxMap = new Map<number, MailboxTarget>();

  let myNationColor = "#000000";
  const diplomacyMailboxList: MailboxGroup = {
    label: "외교메시지",
    color: "#000000",
    options: [],
  };
  const nationMailboxList: MailboxGroup[] = [];
  obj.nation.sort(function (lhs, rhs) {
    if (lhs.mailbox == nationMailbox) {
      return -1;
    }
    if (rhs.mailbox == nationMailbox) {
      return 1;
    }
    return lhs.mailbox - rhs.mailbox;
  });

  for (const nation of obj.nation) {
    if (nationMailbox == nation.mailbox) {
      myNationColor = nation.color;
      //nationColor저장하는 코드가 있었음
    }

    const nationBox: MailboxGroup = {
      label: nation.name,
      color: nation.color,
      options: [],
    };

    nation.general.sort(function (lhs, rhs) {
      if (lhs[1] < rhs[1]) {
        return -1;
      }
      if (lhs[1] > rhs[1]) {
        return 1;
      }
      return 0;
    });

    for (const [destGeneralID, destGeneralName, destGeneralFlag] of nation.general) {
      const isRuler = !!(destGeneralFlag & 0x1);
      const isAmbassador = !!(destGeneralFlag & 0x4);

      if (destGeneralID == generalID.value) {
        continue;
      }

      let textName = destGeneralName;
      if (isRuler) {
        textName = `*${textName}*`;
      } else if (isAmbassador) {
        textName = `#${textName}#`;
      }

      const target: MailboxTarget = {
        value: destGeneralID,
        text: textName,
        nationID: nation.nationID,
      };

      if (permissionLevel.value == 4 && isAmbassador && nationMailbox != nation.mailbox) {
        target.disabled = true;
      }
      nationBox.options.push(target);
      newMailboxMap.set(destGeneralID, target);
    }
    nationMailboxList.push(nationBox);

    if (permissionLevel.value < 4 || nationMailbox == nation.mailbox) {
      continue;
    }

    const target: MailboxTarget = {
      value: nation.mailbox,
      text: nation.name,
      nationID: nation.nationID,
      color: nation.color,
    };
    newMailboxMap.set(nation.mailbox, target);
    diplomacyMailboxList.options.push(target);
  }

  const favoriteBox: MailboxGroup = {
    label: "즐겨찾기",
    color: "#000000",
    options: [
      {
        value: nationMailbox,
        text: "【 아국 메세지 】",
        nationID: nationID.value,
        color: myNationColor,
      },
      {
        value: 9999,
        text: "【 전체 메세지 】",
        nationID: 0,
      },
    ],
  };

  mailboxList.value = [favoriteBox, diplomacyMailboxList, ...nationMailboxList];
  mailboxMap.value = newMailboxMap;
}

function foldMessage($event: MouseEvent, type: MsgType) {
  const target = messageIndexedList[type].value;
  if (target.length < 10) {
    return;
  }
  const remain = target.slice(10);
  target.length = 10;

  for (const msg of remain) {
    messageStorage.delete(msg.id);
  }
}

async function loadOldMessage($event: MouseEvent, type: MsgType) {
  const target = messageIndexedList[type].value;
  if (target.length == 0) {
    return;
  }
  const last = target[target.length - 1].id;

  try {
    const response = await SammoAPI.Message.GetOldMessage({
      to: last,
      type,
    });
    updateMsgResponse(response);
  } catch (e) {
    if (isString(e)) {
      toasts.warning({
        title: "이전 메시지 불러오기 실패",
        body: e,
      });
    }
    console.error(e);
  }
}

async function sendMessage() {
  const text = newMessageText.value;
  if (!text) {
    return tryRefresh();
  }
  const mailbox = targetMailbox.value;

  try {
    const waiter = SammoAPI.Message.SendMessage({
      mailbox,
      text,
    });
    newMessageText.value = "";
    await waiter;
    await tryRefresh();
  } catch (e) {
    if (isString(e)) {
      toasts.warning({
        title: "메시지 전송 실패",
        body: e,
      });
    }
    console.error(e);
  }
}

async function tryFullRefresh() {
  try {
    const refreshP = tryRefresh();
    const response = await SammoAPI.Message.GetContactList();
    refreshMailboxList(response);
    await refreshP;
  } catch (e) {
    if (isString(e)) {
      toasts.warning({
        title: " 실패했습니다.",
        body: e,
      });
    }
    console.error(e);
    return;
  }

  beginRefreshTimer();
}

defineExpose({
  tryRefresh,
  tryFullRefresh,
});

onMounted(async () => {
  await tryFullRefresh();
});
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

.btn-more-small {
  font-size: 0.8rem;
  padding: 0.15rem 0.4rem;
}

.BoardHeader {
  color: white;
  outline-style: solid;
  outline-width: 1px;
  outline-color: gray;
}

@include media-1000px {
  .MessagePanel {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }

  .MessageInputForm {
    grid-column: 1 / 3;
  }

  .PublicTalk {
    border-right: 1px solid gray;
  }

  .PrivateTalk {
    border-right: 1px solid gray;
  }

  .only-mobile {
    display: none;
  }

  .MessageList {
    overflow-y: auto;
    overflow-x: hidden;
    height: 650px;
  }
}

@include media-500px {
  #msg_submit-col {
    order: 2;
  }
  #msg_input-col {
    order: 3;
  }

  .MessageInputForm {
    position: sticky;
    top: 0px;
    z-index: 5;
  }

  .stickyAnchor {
    position: relative;
    top: -68px;
    visibility: hidden;
  }

  .d-grid.Actions {
    grid-template-columns: 1fr 1fr;
  }
}
</style>
