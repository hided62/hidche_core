<template>
  <div
    :id="`msg_${msg.id}`"
    :class="['msg_plate', `msg_plate_${msg.msgType}`, `msg_plate_${nationType}`]"
    :data-id="msg.id"
  >
    <div class="msg_icon">
      <img v-if="src.icon" class="generalIcon" width="64" height="64" :src="encodeURI(src.icon)" />
      <img v-else class="generalIcon" width="64" height="64" :src="encodeURI(defaultIcon)" />
    </div>
    <div class="msg_body">
      <div class="msg_header">
        <template v-if="deletable">
          <button
            type="button"
            class="btn btn btn-outline-warning btn-sm btn-delete-msg"
            style="float: right"
            @click="tryDelete"
          >
            ❌
          </button>
        </template>

        <template v-if="msg.msgType == 'private'">
          <template v-if="src.name == generalName">
            <span :class="`msg_target msg_${srcColorType}`" :style="{ backgroundColor: src.color }">나</span
            ><span class="msg_from_to">▶</span
            ><span :class="`msg_target msg_${destColorType}`" :style="{ backgroundColor: dest.color }"
              >{{ dest.name }}:{{ dest.nation }}</span
            >
          </template>
          <template v-else>
            <span :class="`msg_target msg_${srcColorType}`" :style="{ backgroundColor: src.color }"
              >{{ src.name }}:{{ src.nation }}</span
            ><span class="msg_from_to">▶</span
            ><span :class="`msg_target msg_${destColorType}`" :style="{ backgroundColor: dest.color }">나</span>
          </template>
        </template>
        <template v-else-if="msg.msgType == 'national' && src.nation_id === dest.nation_id">
          <span :class="`msg_target msg_${srcColorType}`" :style="{ backgroundColor: src.color }">{{ src.name }}</span>
        </template>
        <template v-else-if="msg.msgType == 'national' || msg.msgType == 'diplomacy'">
          <template v-if="src.nation_id == nationID">
            <span :class="`msg_target msg_${srcColorType}`" :style="{ backgroundColor: src.color }">{{ src.name }}</span
            ><span class="msg_from_to">▶</span
            ><span :class="`msg_target msg_${destColorType}`" :style="{ backgroundColor: dest.color }">{{
              dest.nation
            }}</span>
          </template>
          <template v-else>
            <span :class="`msg_target msg_${srcColorType}`" :style="{ backgroundColor: src.color }"
              >{{ src.name }}:{{ src.nation }}</span
            ><span class="msg_from_to"></span>
          </template>
        </template>
        <template v-else>
          <span :class="`msg_target msg_${srcColorType}`" :style="{ backgroundColor: dest.color }"
            >{{ src.name }}:{{ src.nation }}</span
          >
        </template>
        <span class="msg_time">&lt;{{ msg.time }}&gt;</span>
      </div>
      <!-- eslint-disable-next-line vue/no-v-html vue/max-attributes-per-line -->
      <div :class="['msg_content', isValidMsg ? 'msg_valid' : 'msg_invalid']" v-html="isValidMsg ? linkifyStr(msg.text) : '삭제된 메시지입니다'"
      ></div>
      <div v-if="msg.option.action" class="msg_prompt">
        <button
          type="button"
          class="prompt_yes btn_prompt"
          :disabled="allowButton ? undefined : true"
          @click="tryAccept"
        >
          수락
        </button>
        <button
          type="button"
          class="prompt_no btn_prompt"
          :disabled="allowButton ? undefined : true"
          @click="tryDecline"
        >
          거절
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { MsgItem, MsgTarget } from "@/defs/API/Message";
import { parseTime } from "@/util/parseTime";
import { differenceInMilliseconds, addMinutes } from "date-fns/esm";
import { computed, ref, toRef, watch, type ComputedRef, type Ref } from "vue";
import linkifyStr from "linkifyjs/string";
import { SammoAPI } from "@/SammoAPI";
import { isError, isString } from "lodash";
import { isBrightColor } from "@/util/isBrightColor";

const props = defineProps<{
  modelValue: MsgItem;
  generalID: number;
  generalName: string;
  nationID: number;
  permissionLevel: number;
  deleted?: boolean;
}>();

const emit = defineEmits<{
  (event: "request-refresh"): void;
}>();

const src: Ref<MsgTarget> = ref(props.modelValue.src);
const dest: Ref<MsgTarget> = ref(props.modelValue.dest ?? props.modelValue.src);
const srcColorType = computed(() => isBrightColor(src.value.color) ? "bright" : "dark");
const destColorType = computed(() => isBrightColor(dest.value.color) ? "bright" : "dark");

const msg = toRef(props, "modelValue");
const defaultIcon = `${window.pathConfig.sharedIcon}/default.jpg`;

const isValidMsg = ref(true);
const deletable = ref(false);

watch(
  () => props.deleted,
  () => {
    isValidMsg.value = testValidMsg(msg.value);
    deletable.value = testDeletable(msg.value);
  }
);

watch(
  msg,
  (msg) => {
    isValidMsg.value = testValidMsg(msg);
    deletable.value = testDeletable(msg);

    src.value = msg.src;
    dest.value = msg.dest ?? {
      id: 0,
      name: "",
      nation: "재야",
      nation_id: 0,
      color: "#000000",
      icon: defaultIcon,
    };
  },
);

const deletableTimer: Ref<number | undefined> = ref();
const allowButton = computed(() => {
  if (msg.value.msgType != "diplomacy") {
    return true;
  }
  if (props.permissionLevel >= 4) {
    return true;
  }
  return false;
});

function testDeletable(msg: MsgItem): boolean {
  if (deletableTimer.value) {
    clearTimeout(deletableTimer.value);
  }

  if (props.deleted) return false;
  if (msg.option.action) return false;
  if (msg.src.id != props.generalID) return false;
  if (msg.option.invalid) return false;
  if (!msg.option.deletable) return false;

  const now = new Date();
  const last5min = addMinutes(parseTime(msg.time), 5);

  const timeDiff = differenceInMilliseconds(last5min, now);

  if (timeDiff <= 0) return false;

  deletableTimer.value = window.setTimeout(() => {
    deletable.value = testDeletable(msg);
  }, timeDiff);

  return true;
}

const nationType: ComputedRef<"local" | "src" | "dest"> = computed(() => {
  if (msg.value.src.nation_id === msg.value.dest?.nation_id) {
    return "local";
  }

  if (msg.value.src.nation_id === props.nationID) {
    return "src";
  }
  return "dest";
});

function testValidMsg(msg: MsgItem): boolean {
  if (props.deleted) {
    return false;
  }
  if (msg.option.invalid) {
    return false;
  }

  return true;
}

async function tryDelete() {
  if (!confirm("삭제하시겠습니까?")) {
    return false;
  }
  try {
    await SammoAPI.Message.DeleteMessage({ msgID: msg.value.id });
  } catch (e) {
    if (isString(e)) {
      alert(e);
    }
    if (isError(e)) {
      alert(e.message);
    }
    console.error(e);
  }
  emit("request-refresh");
}

async function tryAccept() {
  if (!confirm("수락하시겠습니까?")) {
    return false;
  }
  try {
    await SammoAPI.Message.DecideMessageResponse({ msgID: msg.value.id, response: true });
  } catch (e) {
    if (isString(e)) {
      alert(e);
    }
    if (isError(e)) {
      alert(e.message);
    }
    console.error(e);
  }
  emit("request-refresh");
}

async function tryDecline() {
  if (!confirm("거절하시겠습니까?")) {
    return false;
  }
  try {
    await SammoAPI.Message.DecideMessageResponse({ msgID: msg.value.id, response: false });
  } catch (e) {
    if (isString(e)) {
      alert(e);
    }
    if (isError(e)) {
      alert(e.message);
    }
    console.error(e);
  }
  emit("request-refresh");
}
</script>

<style lang="scss" scoped>
.msg_plate {
    width: 100%;
    display: grid;
    grid-template-columns: 64px 1fr;
    border-bottom: solid 1px gray;
    min-height: 64px;
    font-size: 12.5px;
    word-break: break-all;
    color: white;
}

.msg_plate_private {
    background-color: #5d1e1a;
}

.msg_plate_private.msg_plate_dest {
    background-color: #5d461a;
}

.msg_plate_public {
    background-color: #141c65;
}

.msg_plate_national,
.msg_plate_diplomacy {
    background-color: #00582c;
}

.msg_plate_national.msg_plate_dest,
.msg_plate_diplomacy.msg_plate_dest {
    background-color: #704615;
}

.msg_plate_national.msg_plate_src,
.msg_plate_diplomacy.msg_plate_src {
    background-color: #70153b;
}

.msg_icon {
    width: 64px;
    height: 64px;
    border-right: solid 1px gray;
}

.msg_time {
    font-size: 0.75em;
    font-weight: normal;
}

.msg_header {
    font-weight: bold;
    margin-bottom: 3px;
    color: white;
    position: relative;
}

.msg_invalid {
    color: rgba(255, 255, 255, 0.5);
}

.msg_content {
    margin-left: 10px;
    margin-right: 5px;
    overflow: hidden;
}

.msg_target {
    margin: 2px 2px 0 2px;
    padding: 2px 3px;
    display: inline-block;
    box-shadow: 2px 2px black;
    border-radius: 3px;
}
.msg_target.msg_bright {
    color: black;
}

.msg_target.msg_dark {
    color: white;
}

.msg_from_to {
    display: inline-block;
}

.msg_prompt {
    text-align: right;
    margin-top: 5px;
    margin-right: 5px;
}
.btn-delete-msg {
    position: absolute;
    right: 0;
    top: 0;
    margin: 2px 2px 0 2px;
    font-size: 8px;
}

</style>