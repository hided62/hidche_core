<template>
  <top-back-bar :title="title" />
  <BContainer
    id="container"
    class="tb_layout bg0"
    :toast="{ root: true }"
    :style="{ maxWidth: '1000px', margin: 'auto', border: 'solid 1px #888888', padding: '0' }"
  >
    <div class="bg1 section_bar">국가 정책</div>
    <div class="px-3" style="text-align: right">
      <small class="form-text text-muted">
        최근 설정:
        {{ lastSetters.policy.setter ?? "-없음-" }}
        ({{ lastSetters.policy.date ?? "설정 기록 없음" }})
      </small>
    </div>
    <div class="form_list row row-cols-md-2 row-cols-1">
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqNationGold" :step="100" title="국가 권장 금">
          이보다 많으면 포상, 적으면 몰수/헌납합니다.(긴급포상 제외)
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqNationRice" :step="100" title="국가 권장 쌀">
          이보다 많으면 포상, 적으면 몰수/헌납합니다.(긴급포상 제외)
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqHumanWarUrgentGold" :step="100" title="유저전투장 긴급포상 금">
          유저장긴급포상시 이보다 금이 적은 장수에게 포상합니다.
          <br />
          0이면 보병 6회 징병({{ (defaultStatMax * 100).toLocaleString() }} * 6) 가능한 금을 기준으로 하며, 그 수치는
          현재 {{ zeroPolicy.reqHumanWarUrgentGold.toLocaleString() }}입니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqHumanWarUrgentRice" :step="100" title="유저전투장 긴급포상 쌀">
          유저장긴급포상시 이보다 쌀이 적은 장수에게 포상합니다.
          <br />
          0이면 기본 병종으로 {{ (defaultStatMax * 100).toLocaleString() }} * 6명 사살 가능한 쌀을 기준으로 하며, 그
          수치는 현재 {{ zeroPolicy.reqHumanWarUrgentRice.toLocaleString() }}입니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqHumanWarRecommandGold" :step="100" title="유저전투장 권장 금">
          유저전투장에게 주는 금입니다. 이보다 적으면 포상합니다.
          <br />
          0이면 유저전투장 긴급포상 금의 2배를 기준으로 하며, 그 수치는 현재
          {{ (calcPolicyValue("reqHumanWarUrgentGold") * 2).toLocaleString() }}입니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqHumanWarRecommandRice" :step="100" title="유저전투장 권장 쌀">
          유저전투장에게 주는 쌀입니다. 이보다 적으면 포상합니다.
          <br />
          0이면 유저전투장 긴급포상 쌀의 2배를 기준으로 하며, 그 수치는 현재
          {{ (calcPolicyValue("reqHumanWarUrgentRice") * 2).toLocaleString() }}입니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqHumanDevelGold" :step="100" title="유저내정장 권장 금">
          유저내정장에게 주는 금입니다. 이보다 적으면 포상합니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqHumanDevelRice" :step="100" title="유저내정장 권장 쌀">
          유저내정장에게 주는 쌀입니다. 이보다 적으면 포상합니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqNPCWarGold" :step="100" title="NPC전투장 권장 금">
          NPC전투장에게 주는 금입니다. 이보다 적으면 포상합니다.
          <br />
          0이면 기본 병종 4회({{ (defaultStatNPCMax * 100).toLocaleString() }}
          * 4) 징병비를 기준으로 하며, 그 수치는 현재
          {{ zeroPolicy.reqNPCWarGold.toLocaleString() }}입니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqNPCWarRice" :step="100" title="NPC전투장 권장 쌀">
          NPC전투장에게 주는 쌀입니다. 이보다 적으면 포상합니다.
          <br />
          0이면 기본 병종으로
          {{ (defaultStatNPCMax * 100).toLocaleString() }} * 4명 사살 가능한 쌀을 기준으로 하며, 그 수치는 현재
          {{ zeroPolicy.reqNPCWarRice.toLocaleString() }}입니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqNPCDevelGold" :step="100" title="NPC내정장 권장 금">
          NPC내정장에게 주는 금입니다. 이보다 5배 더 많다면 헌납합니다.
          <br />
          0이면 30턴 내정 가능한 금을 기준으로 하며, 그 수치는 현재
          {{ zeroPolicy.reqNPCDevelGold }}입니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.reqNPCDevelRice" :step="100" title="NPC내정장 권장 쌀">
          NPC내정장에게 주는 쌀입니다. 이보다 5배 더 많다면 헌납합니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.minimumResourceActionAmount"
          :step="100"
          :min="100"
          title="포상/몰수/헌납/삼/팜 최소 단위"
        >
          연산결과가 이 단위보다 적다면 수행하지 않습니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.maximumResourceActionAmount"
          :step="100"
          :min="100"
          title="포상/몰수/헌납/삼/팜 최대 단위"
        >
          연산결과가 이 단위보다 크다면, 이 값에 맞춥니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.minWarCrew" :step="50" title="최소 전투 가능 병력 수">
          이보다 적을 때에는 징병을 시도합니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.minNPCRecruitCityPopulation"
          :step="100"
          title="NPC 최소 징병 가능 인구 수"
        >
          도시의 인구가 이보다 낮으면 NPC는 도시에서 징병하지 않고 후방 워프합니다.
          <br />NPC의 최대 병력수보다 낮게 설정하면 제자리에서 정착장려를 합니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          :modelValue="nationPolicy.safeRecruitCityPopulationRatio * 100"
          :step="0.5"
          :int="false"
          :min="0"
          :max="100"
          title="제자리 징병 허용 인구율(%)"
          @update:modelValue="nationPolicy.safeRecruitCityPopulationRatio = $event / 100"
        >
          전쟁 시 후방 발령, 후방 워프의 기준 인구입니다. 이보다 많다면 '충분하다'고 판단합니다.
          <br />NPC의 최대 병력수보다 낮게 설정하면 제자리에서 정착장려를 합니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo v-model="nationPolicy.minNPCWarLeadership" :step="5" title="NPC 전투 참여 통솔 기준">
          이 수치보다 같거나 높으면 NPC전투장으로 분류됩니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.properWarTrainAtmos"
          :step="5"
          :min="20"
          :max="100"
          title="훈련/사기진작 목표치"
        >
          훈련/사기진작 기준치입니다. 이보다 같거나 높으면 출병합니다.
        </NumberInputWithInfo>
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.cureThreshold"
          :step="5"
          :min="10"
          :max="100"
          title="요양 기준"
        >
          요양 기준 %입니다. 이보다 많이 부상을 입으면 요양합니다.
        </NumberInputWithInfo>
      </div>
      <!--allowNpcAttackCity는 현재 게임 내 비활성-->
    </div>
    <div style="padding: 0 8pt">
      <div class="alert alert-secondary">
        전투 부대는 작업중입니다(json양식: {부대번호:[시작도시번호(아국),도착도시번호(적군)],...})
        <br />후방 징병 부대는 작업중입니다(json양식: [부대번호,...]) <br />내정 부대는 작업중입니다(json양식:
        [부대번호,...])
        <input id="CombatForce" type="hidden" :value="JSON.stringify(nationPolicy.CombatForce)" data-type="json" />
        <input id="SupportForce" type="hidden" :value="JSON.stringify(nationPolicy.SupportForce)" data-type="json" />
        <input id="DevelopForce" type="hidden" :value="JSON.stringify(nationPolicy.DevelopForce)" data-type="json" />
      </div>
    </div>
    <div class="control_bar" data-type="nationPolicy">
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-dark reset_btn" @click="resetPolicy">초기값으로</button>
        <button type="button" class="btn btn-secondary revert_btn" @click="rollbackPolicy">이전값으로</button>
      </div>
      <button type="button" class="btn btn-primary submit_btn" @click="submitPolicy">설정</button>
    </div>
    <div class="row row-cols-md-2 row-cols-1 g-0">
      <div class="col half_section_left">
        <div class="bg1 section_bar">NPC 사령턴 우선순위</div>
        <div class="float-right px-3">
          <small class="form-text text-muted">
            최근 설정:
            {{ lastSetters.nation.setter ?? "-없음-" }}
            ({{ lastSetters.nation.date ?? "설정 기록 없음" }})
          </small>
        </div>
        <div class="text-left px-2">
          <small class="text-muted">
            예턴이 없거나, 지정되어 있더라도 실패하면
            <br />아래 순위에 따라 사령턴을 시도합니다.
          </small>
        </div>
        <div>
          <div class="row g-0">
            <div class="col">
              <div class="bg2 sub_bar">비활성</div>
              <draggable
                :list="chiefActionUnused"
                group="chiefAction"
                class="list-group col priority-list"
                itemKey="id"
              >
                <template #header>
                  <div class="list-group-item list-group-item-dark">&lt;비활성화 항목들&gt;</div>
                </template>
                <template #item="{ element }">
                  <div class="list-group-item">
                    <i class="bi bi-list" />
                    &nbsp;&nbsp;{{ element.id }}
                    <button
                      v-b-tooltip.hover
                      class="btn btn-sm float-right btn-secondary py-0 px-1"
                      :title="actionHelpText[element.id]"
                    >
                      <i class="bi bi-question-lg" />
                    </button>
                  </div>
                </template>
              </draggable>
            </div>
            <div class="col">
              <div class="bg2 sub_bar">활성</div>
              <draggable
                :list="chiefActionPriority"
                group="chiefAction"
                class="list-group col priority-list"
                itemKey="id"
              >
                <template #item="{ element }">
                  <div class="list-group-item">
                    <i class="bi bi-list" />
                    &nbsp;&nbsp;{{ element.id }}
                    <button
                      v-b-tooltip.hover
                      class="btn btn-sm float-right btn-secondary py-0 px-1"
                      :title="actionHelpText[element.id]"
                    >
                      <i class="bi bi-question-lg" />
                    </button>
                  </div>
                </template>
              </draggable>
            </div>
          </div>
          <div class="control_bar" data-type="nationPriority">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-dark reset_btn" @click="resetNationPriority">초기값으로</button>
              <button type="button" class="btn btn-secondary revert_btn" @click="rollbackNationPriority">
                이전값으로
              </button>
            </div>
            <button type="button" class="btn btn-primary submit_btn" @click="submitNationPriority">설정</button>
          </div>
        </div>
      </div>
      <div class="col half_section_right">
        <div class="bg1 section_bar">NPC 일반턴 우선순위</div>
        <div class="float-right px-3">
          <small class="form-text text-muted">
            최근 설정:
            {{ lastSetters.general.setter ?? "-없음-" }}
            ({{ lastSetters.general.date ?? "설정 기록 없음" }})
          </small>
        </div>
        <div class="text-left px-2">
          <small class="text-muted">
            순위가 높은 것부터 시도합니다.
            <br />아무것도 실행할 수 없으면 물자조달이나 인재탐색을 합니다.
          </small>
        </div>
        <div>
          <div class="row g-0">
            <div class="col">
              <div class="bg2 sub_bar">비활성</div>
              <draggable
                :list="generalActionUnused"
                group="generalAction"
                class="list-group col priority-list"
                itemKey="id"
              >
                <template #header>
                  <div class="list-group-item list-group-item-dark">&lt;비활성화 항목들&gt;</div>
                </template>
                <template #item="{ element }">
                  <div class="list-group-item">
                    <i class="bi bi-list" />
                    &nbsp;&nbsp;{{ element.id }}
                    <button
                      v-b-tooltip.hover
                      class="btn btn-sm float-right btn-secondary py-0 px-1"
                      :title="actionHelpText[element.id]"
                    >
                      <i class="bi bi-question-lg" />
                    </button>
                  </div>
                </template>
              </draggable>
            </div>
            <div class="col">
              <div class="bg2 sub_bar">활성</div>
              <draggable
                :list="generalActionPriority"
                group="generalAction"
                class="list-group col priority-list"
                itemKey="id"
              >
                <template #item="{ element }">
                  <div class="list-group-item">
                    <i class="bi bi-list" />
                    &nbsp;&nbsp;{{ element.id }}
                    <button
                      v-b-tooltip.hover
                      class="btn btn-sm float-right btn-secondary py-0 px-1"
                      :title="actionHelpText[element.id]"
                    >
                      <i class="bi bi-question-lg" />
                    </button>
                  </div>
                </template>
              </draggable>
            </div>
          </div>
          <div class="control_bar" data-type="generalPriority">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-dark reset_btn" @click="resetGeneralPriority">초기값으로</button>
              <button type="button" class="btn btn-secondary revert_btn" @click="rollbackGeneralPriority">
                이전값으로
              </button>
            </div>
            <button type="button" class="btn btn-primary submit_btn" @click="submitGeneralPriority">설정</button>
          </div>
        </div>
      </div>
    </div>
  </BContainer>
</template>
<script lang="ts">
type SetterInfo = {
  setter: string | null;
  date: string | null;
};

declare const staticValues: {
  nationID: number;

  defaultNationPolicy: NationPolicy;
  currentNationPolicy: NationPolicy;

  zeroPolicy: NationPolicy;
  currentNationPriority: NPCChiefActions[];
  availableNationPriorityItems: NPCChiefActions[];
  defaultNationPriority: NPCChiefActions[];

  currentGeneralActionPriority: NPCGeneralActions[];
  availableGeneralActionPriorityItems: NPCGeneralActions[];
  defaultGeneralActionPriority: NPCGeneralActions[];

  defaultStatNPCMax: number;
  defaultStatMax: number;

  lastSetters: {
    policy: SetterInfo;
    nation: SetterInfo;
    general: SetterInfo;
  };
};
</script>
<script setup lang="ts">
import "@scss/common/bootstrap5.scss";
import "@scss/game_bg.scss";

import { ref } from "vue";
import type { IDItem, InvalidResponse, NationPolicy, NPCChiefActions, NPCGeneralActions } from "@/defs";
import NumberInputWithInfo from "@/components/NumberInputWithInfo.vue";
import { cloneDeep, isEqual, isNumber, last } from "lodash";
import { unwrap } from "@util/unwrap";
import { convertFormData } from "@util/convertFormData";
import axios from "axios";
import { NPCPriorityBtnHelpMessage } from "@/helpTexts";
import draggable from "vuedraggable";
import TopBackBar from "@/components/TopBackBar.vue";
import { convertIDArray } from "@util/convertIDArray";
import { useToast, BContainer } from "bootstrap-vue-3";

const chiefActionPriority = ref<IDItem<NPCChiefActions>[]>([]);
const chiefActionKeys = ref(new Set(staticValues.availableNationPriorityItems));
for (const id of staticValues.currentNationPriority) {
  chiefActionPriority.value.push({ id });
  chiefActionKeys.value.delete(id);
}
const chiefActionUnused = ref<IDItem<NPCChiefActions>[]>(convertIDArray(chiefActionKeys.value));
const chiefActionStack = cloneDeep([chiefActionPriority.value]);

const generalActionPriority = ref<IDItem<NPCGeneralActions>[]>([]);
const generalActionKeys = ref(new Set(staticValues.availableGeneralActionPriorityItems));
for (const id of staticValues.currentGeneralActionPriority) {
  generalActionPriority.value.push({ id });
  generalActionKeys.value.delete(id);
}
const generalActionUnused = ref<IDItem<NPCGeneralActions>[]>(convertIDArray(generalActionKeys.value));
const generalActionStack = cloneDeep([generalActionPriority.value]);

const title = "NPC 정책";
const lastSetters = ref(staticValues.lastSetters);
const defaultStatMax = ref(staticValues.defaultStatMax);
const defaultStatNPCMax = ref(staticValues.defaultStatNPCMax);
const nationPolicy = ref(cloneDeep(staticValues.currentNationPolicy));
const nationPolicyStack = [staticValues.currentNationPolicy];
const zeroPolicy = ref(staticValues.zeroPolicy);
const actionHelpText = ref(NPCPriorityBtnHelpMessage);

const toasts = unwrap(useToast());

function resetPolicy() {
  if (!confirm("초기 설정으로 되돌릴까요?")) {
    return;
  }
  nationPolicy.value = cloneDeep(staticValues.defaultNationPolicy);
  toasts.info({
    title: "초기화 완료",
    body: "서버 초기값을 적용했습니다.설정 버튼을 누르면 반영됩니다.",
  });
}

function rollbackPolicy() {
  if (!confirm("이전 설정으로 되돌릴까요?")) {
    return;
  }
  let lastPolicy = unwrap(last(nationPolicyStack));
  while (nationPolicyStack.length > 1) {
    if (!isEqual(lastPolicy, nationPolicy.value)) {
      break;
    }
    nationPolicyStack.pop();
    lastPolicy = unwrap(last(nationPolicyStack));
  }
  nationPolicy.value = cloneDeep(lastPolicy);
  toasts.info({
    title: "되돌리기 완료",
    body: "이전 설정으로 되돌렸습니다.",
  });
}

async function submitPolicy() {
  if (!confirm("저장할까요?")) {
    return;
  }

  try {
    const response = await axios({
      url: "j_set_npc_control.php",
      responseType: "json",
      method: "post",
      data: convertFormData({
        type: "nationPolicy",
        data: JSON.stringify(nationPolicy.value),
      }),
    });
    const result: InvalidResponse = response.data;
    if (!result.result) {
      throw result.reason;
    }
  } catch (e) {
    console.error(e);
    toasts.danger({
      title: "에러",
      body: `설정하지 못했습니다: ${e}`,
    });
    return;
  }

  toasts.success({
    title: "적용 완료",
    body: "NPC 정책이 반영되었습니다.",
  });

  const lastPolicy = unwrap(last(nationPolicyStack));
  if (!isEqual(lastPolicy, nationPolicy.value)) {
    nationPolicyStack.push(cloneDeep(nationPolicy.value));
  }
}

function calcPolicyValue(title: keyof NationPolicy): number {
  if (!(title in nationPolicy.value)) {
    throw `${title}이 NationPolicy key가 아님`;
  }
  const policyValue = nationPolicy.value[title];
  if (!isNumber(policyValue)) {
    throw `${title}에 해당하는 값이 number가 아님`;
  }
  if (policyValue == 0) {
    return zeroPolicy.value[title] as number;
  }
  return policyValue;
}

function resetNationPriority() {
  if (!confirm("초기 설정으로 되돌릴까요?")) {
    return;
  }
  chiefActionUnused.value = [];
  chiefActionPriority.value = staticValues.defaultNationPriority.map((id) => {
    return { id };
  });
  toasts.info({
    title: "초기화 완료",
    body: "서버 초기값을 적용했습니다.설정 버튼을 누르면 반영됩니다.",
  });
}

function rollbackNationPriority() {
  if (!confirm("이전 설정으로 되돌릴까요?")) {
    return;
  }
  let lastActions = unwrap(last(chiefActionStack));
  while (chiefActionStack.length > 1) {
    if (!isEqual(lastActions, chiefActionPriority.value)) {
      break;
    }
    chiefActionStack.pop();
    lastActions = unwrap(last(chiefActionStack));
  }
  const chiefActionKeys = new Set(staticValues.availableNationPriorityItems);
  for (const { id } of lastActions) {
    if (chiefActionKeys.has(id)) {
      chiefActionKeys.delete(id);
    }
  }
  chiefActionPriority.value = cloneDeep(lastActions);
  chiefActionUnused.value = convertIDArray(chiefActionKeys);

  toasts.info({
    title: "되돌리기 완료",
    body: "이전 설정으로 되돌렸습니다.",
  });
}

async function submitNationPriority() {
  if (!confirm("저장할까요?")) {
    return;
  }

  try {
    const response = await axios({
      url: "j_set_npc_control.php",
      responseType: "json",
      method: "post",
      data: convertFormData({
        type: "nationPriority",
        data: JSON.stringify(chiefActionPriority.value.map(({ id }) => id)),
      }),
    });
    const result: InvalidResponse = response.data;
    if (!result.result) {
      throw result.reason;
    }
  } catch (e) {
    console.error(e);
    toasts.danger({
      title: "에러",
      body: `설정하지 못했습니다: ${e}`,
    });
    return;
  }

  toasts.success({
    title: "적용 완료",
    body: "NPC 정책이 반영되었습니다.",
  });

  const lastActions = unwrap(last(chiefActionStack));
  if (!isEqual(lastActions, chiefActionPriority.value)) {
    chiefActionStack.push(cloneDeep(chiefActionPriority.value));
  }
}

function resetGeneralPriority() {
  if (!confirm("초기 설정으로 되돌릴까요?")) {
    return;
  }
  generalActionUnused.value = [];
  generalActionPriority.value = staticValues.defaultGeneralActionPriority.map((id) => {
    return { id };
  });
  toasts.info({
    title: "초기화 완료",
    body: "서버 초기값을 적용했습니다.설정 버튼을 누르면 반영됩니다.",
  });
}

function rollbackGeneralPriority() {
  if (!confirm("이전 설정으로 되돌릴까요?")) {
    return;
  }
  let lastActions = unwrap(last(generalActionStack));
  while (generalActionStack.length > 1) {
    if (!isEqual(lastActions, generalActionPriority.value)) {
      break;
    }
    generalActionStack.pop();
    lastActions = unwrap(last(generalActionStack));
  }
  const generalActionKeys = new Set(staticValues.availableGeneralActionPriorityItems);
  for (const { id } of lastActions) {
    if (generalActionKeys.has(id)) {
      generalActionKeys.delete(id);
    }
  }
  generalActionPriority.value = cloneDeep(lastActions);
  generalActionUnused.value = convertIDArray(generalActionKeys);

  toasts.info({
    title: "되돌리기 완료",
    body: "이전 설정으로 되돌렸습니다.",
  });
}

async function submitGeneralPriority() {
  if (!confirm("저장할까요?")) {
    return;
  }

  try {
    const response = await axios({
      url: "j_set_npc_control.php",
      responseType: "json",
      method: "post",
      data: convertFormData({
        type: "generalPriority",
        data: JSON.stringify(generalActionPriority.value.map(({ id }) => id)),
      }),
    });
    const result: InvalidResponse = response.data;
    if (!result.result) {
      throw result.reason;
    }
  } catch (e) {
    console.error(e);
    toasts.danger({
      title: "에러",
      body: `설정하지 못했습니다: ${e}`,
    });
    return;
  }

  toasts.success({
    title: "적용 완료",
    body: "NPC 정책이 반영되었습니다.",
  });

  const lastActions = unwrap(last(generalActionStack));
  if (!isEqual(lastActions, generalActionPriority.value)) {
    generalActionStack.push(cloneDeep(generalActionPriority.value));
  }
}
</script>

<style>
.tooltip > .tooltip-inner {
  max-width: 350px;
  text-align: left;
}

.form_list {
  margin: 8px;
}

.sub_bar {
  text-align: center;
  border: 0.5px solid #aaa;
  margin-left: 5px;
  margin-right: 5px;
}

.priority-list {
  margin: 0 10px;
}

.control_bar {
  padding: 0 8pt 8pt 8pt;
  text-align: right;
}

.control_bar .btn {
  margin-top: 8pt;
}

.reset_btn {
  width: 15ch;
}

.revert_btn {
  width: 15ch;
}

.submit_btn {
  margin-left: 1em;
  width: 15ch;
}

.half_section_left {
  border-right: 0.5px solid #aaa;
}

.section_bar {
  text-align: center;
  border: 0.5px solid #aaa;
  padding-right: 0;
  padding-left: 0;
}
</style>
