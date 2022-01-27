<template>
  <my-toast v-model="toasts" />
  <top-back-bar :title="title" />
  <div
    id="container"
    class="tb_layout bg0"
    :style="{ maxWidth: '1000px', margin: 'auto', border: 'solid 1px #888888' }"
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
        <NumberInputWithInfo
          v-model="nationPolicy.reqNationGold"
          :step="100"
          title="국가 권장 금"
          >이보다 많으면 포상, 적으면 몰수/헌납합니다.(긴급포상
          제외)</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqNationRice"
          :step="100"
          title="국가 권장 쌀"
          >이보다 많으면 포상, 적으면 몰수/헌납합니다.(긴급포상
          제외)</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqHumanWarUrgentGold"
          :step="100"
          title="유저전투장 긴급포상 금"
          >유저장긴급포상시 이보다 금이 적은 장수에게 포상합니다.<br />0이면
          보병 6회 징병({{ (defaultStatMax * 100).toLocaleString() }} * 6)
          가능한 금을 기준으로 하며, 그 수치는 현재
          {{
            zeroPolicy.reqHumanWarUrgentGold.toLocaleString()
          }}입니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqHumanWarUrgentRice"
          :step="100"
          title="유저전투장 긴급포상 쌀"
          >유저장긴급포상시 이보다 쌀이 적은 장수에게 포상합니다.<br />0이면
          기본 병종으로 {{ (defaultStatMax * 100).toLocaleString() }} * 6명 사살
          가능한 쌀을 기준으로 하며, 그 수치는 현재
          {{
            zeroPolicy.reqHumanWarUrgentRice.toLocaleString()
          }}입니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqHumanWarRecommandGold"
          :step="100"
          title="유저전투장 권장 금"
          >유저전투장에게 주는 금입니다. 이보다 적으면 포상합니다. <br />
          0이면 유저전투장 긴급포상 금의 2배를 기준으로 하며, 그 수치는 현재
          {{
            (calcPolicyValue("reqHumanWarUrgentGold") * 2).toLocaleString()
          }}입니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqHumanWarRecommandRice"
          :step="100"
          title="유저전투장 권장 쌀"
          >유저전투장에게 주는 쌀입니다. 이보다 적으면 포상합니다. <br />
          0이면 유저전투장 긴급포상 쌀의 2배를 기준으로 하며, 그 수치는 현재
          {{
            (calcPolicyValue("reqHumanWarUrgentRice") * 2).toLocaleString()
          }}입니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqHumanDevelGold"
          :step="100"
          title="유저내정장 권장 금"
          >유저내정장에게 주는 금입니다. 이보다 적으면
          포상합니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqHumanDevelRice"
          :step="100"
          title="유저내정장 권장 쌀"
          >유저내정장에게 주는 쌀입니다. 이보다 적으면
          포상합니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqNPCWarGold"
          :step="100"
          title="NPC전투장 권장 금"
          >NPC전투장에게 주는 금입니다. 이보다 적으면 포상합니다. <br />
          0이면 기본 병종 4회({{ (defaultStatNPCMax * 100).toLocaleString() }}
          * 4) 징병비를 기준으로 하며, 그 수치는 현재
          {{
            zeroPolicy.reqNPCWarGold.toLocaleString()
          }}입니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqNPCWarRice"
          :step="100"
          title="NPC전투장 권장 쌀"
          >NPC전투장에게 주는 쌀입니다. 이보다 적으면 포상합니다. <br />
          0이면 기본 병종으로
          {{ (defaultStatNPCMax * 100).toLocaleString() }} * 4명 사살 가능한
          쌀을 기준으로 하며, 그 수치는 현재
          {{
            zeroPolicy.reqNPCWarRice.toLocaleString()
          }}입니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqNPCDevelGold"
          :step="100"
          title="NPC내정장 권장 금"
          >NPC내정장에게 주는 금입니다. 이보다 5배 더 많다면 헌납합니다.<br />0이면
          30턴 내정 가능한 금을 기준으로 하며, 그 수치는 현재
          {{ zeroPolicy.reqNPCDevelGold }}입니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.reqNPCDevelRice"
          :step="100"
          title="NPC내정장 권장 쌀"
          >NPC내정장에게 주는 쌀입니다. 이보다 5배 더 많다면
          헌납합니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.minimumResourceActionAmount"
          :step="100"
          title="포상/몰수/헌납/삼/팜 최소 단위"
          >연산결과가 이 단위보다 적다면 수행하지 않습니다.</NumberInputWithInfo
        >
      </div>
        <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.maximumResourceActionAmount"
          :step="100"
          title="포상/몰수/헌납/삼/팜 최대 단위"
          >연산결과가 이 단위보다 크다면, 이 값에 맞춥니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.minWarCrew"
          :step="50"
          title="최소 전투 가능 병력 수"
          >이보다 적을 때에는 징병을 시도합니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.minNPCRecruitCityPopulation"
          :step="100"
          title="NPC 최소 징병 가능 인구 수"
          >도시의 인구가 이보다 낮으면 NPC는 도시에서 징병하지 않고 후방
          워프합니다.<br />NPC의 최대 병력수보다 낮게 설정하면 제자리에서
          정착장려를 합니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          :modelValue="nationPolicy.safeRecruitCityPopulationRatio * 100"
          @update:modelValue="
            nationPolicy.safeRecruitCityPopulationRatio = $event / 100
          "
          :step="0.5"
          :int="false"
          :min="0"
          :max="100"
          title="제자리 징병 허용 인구율(%)"
          >전쟁 시 후방 발령, 후방 워프의 기준 인구입니다. 이보다 많다면
          '충분하다'고 판단합니다.<br />NPC의 최대 병력수보다 낮게 설정하면
          제자리에서 정착장려를 합니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.minNPCWarLeadership"
          :step="5"
          title="NPC 전투 참여 통솔 기준"
          >이 수치보다 같거나 높으면 NPC전투장으로
          분류됩니다.</NumberInputWithInfo
        >
      </div>
      <div class="col">
        <NumberInputWithInfo
          v-model="nationPolicy.properWarTrainAtmos"
          :step="5"
          :min="20"
          :max="100"
          title="훈련/사기진작 목표치"
          >훈련/사기진작 기준치입니다. 이보다 같거나 높으면
          출병합니다.</NumberInputWithInfo
        >
      </div>
      <!--allowNpcAttackCity는 현재 게임 내 비활성-->
    </div>
    <div style="padding: 0 8pt">
      <div class="alert alert-secondary">
        전투 부대는 작업중입니다(json양식:
        {부대번호:[시작도시번호(아국),도착도시번호(적군)],...})<br />
        후방 징병 부대는 작업중입니다(json양식: [부대번호,...])<br />
        내정 부대는 작업중입니다(json양식: [부대번호,...])
        <input
          type="hidden"
          :value="JSON.stringify(nationPolicy.CombatForce)"
          data-type="json"
          id="CombatForce"
        />
        <input
          type="hidden"
          :value="JSON.stringify(nationPolicy.SupportForce)"
          data-type="json"
          id="SupportForce"
        />
        <input
          type="hidden"
          :value="JSON.stringify(nationPolicy.DevelopForce)"
          data-type="json"
          id="DevelopForce"
        />
      </div>
    </div>
    <div class="control_bar" data-type="nationPolicy">
      <div class="btn-group" role="group">
        <button
          type="button"
          @click="resetPolicy"
          class="btn btn-dark reset_btn"
        >
          초기값으로
        </button>
        <button
          type="button"
          @click="rollbackPolicy"
          class="btn btn-secondary revert_btn"
        >
          이전값으로
        </button>
      </div>
      <button
        type="button"
        @click="submitPolicy"
        class="btn btn-primary submit_btn"
      >
        설정
      </button>
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
          <small class="text-muted"
            >예턴이 없거나, 지정되어 있더라도 실패하면<br />아래 순위에 따라
            사령턴을 시도합니다.</small
          >
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
                  <div class="list-group-item list-group-item-dark">
                    &lt;비활성화 항목들&gt;
                  </div>
                </template>
                <template #item="{ element }">
                  <div class="list-group-item">
                    <i class="bi bi-list"></i>&nbsp;&nbsp;{{ element.id
                    }}<button
                      class="btn btn-sm float-right btn-secondary py-0 px-1"
                      v-b-tooltip.hover :title="actionHelpText[element.id]"
                    >
                      <i class="bi bi-question-lg"></i>
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
                    <i class="bi bi-list"></i>&nbsp;&nbsp;{{ element.id
                    }}<button
                      class="btn btn-sm float-right btn-secondary py-0 px-1"
                      v-b-tooltip.hover :title="actionHelpText[element.id]"
                    >
                      <i class="bi bi-question-lg"></i>
                    </button>
                  </div>
                </template>
              </draggable>
            </div>
          </div>
          <div class="control_bar" data-type="nationPriority">
            <div class="btn-group" role="group">
              <button
                type="button"
                class="btn btn-dark reset_btn"
                @click="resetNationPriority"
              >
                초기값으로
              </button>
              <button
                type="button"
                class="btn btn-secondary revert_btn"
                @click="rollbackNationPriority"
              >
                이전값으로
              </button>
            </div>
            <button
              type="button"
              class="btn btn-primary submit_btn"
              @click="submitNationPriority"
            >
              설정
            </button>
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
          <small class="text-muted"
            >순위가 높은 것부터 시도합니다. <br />아무것도 실행할 수 없으면
            물자조달이나 인재탐색을 합니다.</small
          >
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
                  <div class="list-group-item list-group-item-dark">
                    &lt;비활성화 항목들&gt;
                  </div>
                </template>
                <template #item="{ element }">
                  <div class="list-group-item">
                    <i class="bi bi-list"></i>&nbsp;&nbsp;{{ element.id
                    }}<button
                      class="btn btn-sm float-right btn-secondary py-0 px-1"
                      v-b-tooltip.hover :title="actionHelpText[element.id]"
                    >
                      <i class="bi bi-question-lg"></i>
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
                    <i class="bi bi-list"></i>&nbsp;&nbsp;{{ element.id
                    }}<button
                      class="btn btn-sm float-right btn-secondary py-0 px-1"
                      v-b-tooltip.hover :title="actionHelpText[element.id]"
                    >
                      <i class="bi bi-question-lg"></i>
                    </button>
                  </div>
                </template>
              </draggable>
            </div>
          </div>
          <div class="control_bar" data-type="generalPriority">
            <div class="btn-group" role="group">
              <button
                type="button"
                class="btn btn-dark reset_btn"
                @click="resetGeneralPriority"
              >
                초기값으로
              </button>
              <button
                type="button"
                class="btn btn-secondary revert_btn"
                @click="rollbackGeneralPriority"
              >
                이전값으로
              </button>
            </div>
            <button
              type="button"
              class="btn btn-primary submit_btn"
              @click="submitGeneralPriority"
            >
              설정
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import "@scss/common/bootstrap5.scss";
import "@scss/game_bg.scss";

import { defineComponent } from "vue";
import {
  IDItem,
  InvalidResponse,
  NationPolicy,
  NPCChiefActions,
  NPCGeneralActions,
  ToastType,
} from "@/defs";
import NumberInputWithInfo from "@/components/NumberInputWithInfo.vue";
import { cloneDeep, isEqual, last } from "lodash";
import { unwrap } from "@util/unwrap";
import { convertFormData } from "@util/convertFormData";
import axios from "axios";
import { NPCPriorityBtnHelpMessage } from "@/helpTexts";
import draggable from "vuedraggable";
import MyToast from "@/components/MyToast.vue";
import TopBackBar from "@/components/TopBackBar.vue";
import { convertIDArray } from "@util/convertIDArray";

declare const nationID: number;

declare const defaultNationPolicy: NationPolicy;
declare const currentNationPolicy: NationPolicy;

declare const zeroPolicy: NationPolicy;
declare const currentNationPriority: NPCChiefActions[];
declare const availableNationPriorityItems: NPCChiefActions[];
declare const defaultNationPriority: NPCChiefActions[];

declare const currentGeneralActionPriority: NPCGeneralActions[];
declare const availableGeneralActionPriorityItems: NPCGeneralActions[];
declare const defaultGeneralActionPriority: NPCGeneralActions[];

declare const defaultStatNPCMax: number;
declare const defaultStatMax: number;

type SetterInfo = {
  setter: string | null;
  date: string | null;
};

declare const lastSetters: {
  policy: SetterInfo;
  nation: SetterInfo;
  general: SetterInfo;
};

export default defineComponent({
  name: "PageNPCControl",
  components: {
    TopBackBar,
    NumberInputWithInfo,
    draggable,
    MyToast,
  },
  methods: {
    resetPolicy() {
      if (!confirm("초기 설정으로 되돌릴까요?")) {
        return;
      }
      this.nationPolicy = cloneDeep(defaultNationPolicy);
      this.toasts.push({
        title: "초기화 완료",
        content: "서버 초기값을 적용했습니다.설정 버튼을 누르면 반영됩니다.",
        type: "info",
      });
    },
    rollbackPolicy() {
      if (!confirm("이전 설정으로 되돌릴까요?")) {
        return;
      }
      let lastPolicy = unwrap(last(this.nationPolicyStack));
      while (this.nationPolicyStack.length > 1) {
        if (!isEqual(lastPolicy, this.nationPolicy)) {
          break;
        }
        this.nationPolicyStack.pop();
        lastPolicy = unwrap(last(this.nationPolicyStack));
      }
      this.nationPolicy = cloneDeep(lastPolicy);
      this.toasts.push({
        title: "되돌리기 완료",
        content: "이전 설정으로 되돌렸습니다.",
        type: "info",
      });
    },
    async submitPolicy() {
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
            data: JSON.stringify(this.nationPolicy),
          }),
        });
        const result: InvalidResponse = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        this.toasts.push({
          title: "에러",
          content: `설정하지 못했습니다: ${e}`,
          type: "danger",
        });
        return;
      }

      this.toasts.push({
        title: "적용 완료",
        content: "NPC 정책이 반영되었습니다.",
        type: "success",
      });

      const lastPolicy = unwrap(last(this.nationPolicyStack));
      if (!isEqual(lastPolicy, this.nationPolicy)) {
        const { nationPolicy } = this;
        this.nationPolicyStack.push(cloneDeep(nationPolicy));
      }
    },
    calcPolicyValue(
      title: keyof NationPolicy
    ): NationPolicy[keyof NationPolicy] {
      if (!(title in this.nationPolicy)) {
        throw `${title}이 NationPolicy key가 아님`;
      }
      if (this.nationPolicy[title] == 0) {
        return this.zeroPolicy[title];
      }
      return this.nationPolicy[title];
    },
    resetNationPriority() {
      if (!confirm("초기 설정으로 되돌릴까요?")) {
        return;
      }
      this.chiefActionUnused = [];
      this.chiefActionPriority = defaultNationPriority.map((id) => {
        return { id };
      });
      this.toasts.push({
        title: "초기화 완료",
        content: "서버 초기값을 적용했습니다.설정 버튼을 누르면 반영됩니다.",
        type: "info",
      });
    },
    rollbackNationPriority() {
      if (!confirm("이전 설정으로 되돌릴까요?")) {
        return;
      }
      let lastActions = unwrap(last(this.chiefActionStack));
      while (this.chiefActionStack.length > 1) {
        if (!isEqual(lastActions, this.chiefActionPriority)) {
          break;
        }
        this.chiefActionStack.pop();
        lastActions = unwrap(last(this.chiefActionStack));
      }
      const chiefActionKeys = new Set(availableNationPriorityItems);
      for (const { id } of lastActions) {
        if (chiefActionKeys.has(id)) {
          chiefActionKeys.delete(id);
        }
      }
      this.chiefActionPriority = cloneDeep(lastActions);
      this.chiefActionUnused = convertIDArray(chiefActionKeys);

      this.toasts.push({
        title: "되돌리기 완료",
        content: "이전 설정으로 되돌렸습니다.",
        type: "info",
      });
    },
    async submitNationPriority() {
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
            data: JSON.stringify(this.chiefActionPriority.map(({ id }) => id)),
          }),
        });
        const result: InvalidResponse = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        this.toasts.push({
          title: "에러",
          content: `설정하지 못했습니다: ${e}`,
          type: "danger",
        });
        return;
      }

      this.toasts.push({
        title: "적용 완료",
        content: "NPC 정책이 반영되었습니다.",
        type: "success",
      });

      const lastActions = unwrap(last(this.chiefActionStack));
      if (!isEqual(lastActions, this.chiefActionPriority)) {
        const { chiefActionPriority } = this;
        this.chiefActionStack.push(cloneDeep(chiefActionPriority));
      }
    },
    resetGeneralPriority() {
      if (!confirm("초기 설정으로 되돌릴까요?")) {
        return;
      }
      this.generalActionUnused = [];
      this.generalActionPriority = defaultGeneralActionPriority.map((id) => {
        return { id };
      });
      this.toasts.push({
        title: "초기화 완료",
        content: "서버 초기값을 적용했습니다.설정 버튼을 누르면 반영됩니다.",
        type: "info",
      });
    },
    rollbackGeneralPriority() {
      if (!confirm("이전 설정으로 되돌릴까요?")) {
        return;
      }
      let lastActions = unwrap(last(this.generalActionStack));
      while (this.generalActionStack.length > 1) {
        if (!isEqual(lastActions, this.generalActionPriority)) {
          break;
        }
        this.generalActionStack.pop();
        lastActions = unwrap(last(this.generalActionStack));
      }
      const generalActionKeys = new Set(availableGeneralActionPriorityItems);
      for (const { id } of lastActions) {
        if (generalActionKeys.has(id)) {
          generalActionKeys.delete(id);
        }
      }
      this.generalActionPriority = cloneDeep(lastActions);
      this.generalActionUnused = convertIDArray(generalActionKeys);

      this.toasts.push({
        title: "되돌리기 완료",
        content: "이전 설정으로 되돌렸습니다.",
        type: "info",
      });
    },
    async submitGeneralPriority() {
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
            data: JSON.stringify(
              this.generalActionPriority.map(({ id }) => id)
            ),
          }),
        });
        const result: InvalidResponse = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        this.toasts.push({
          title: "에러",
          content: `설정하지 못했습니다: ${e}`,
          type: "danger",
        });
        return;
      }

      this.toasts.push({
        title: "적용 완료",
        content: "NPC 정책이 반영되었습니다.",
        type: "success",
      });

      const lastActions = unwrap(last(this.generalActionStack));
      if (!isEqual(lastActions, this.generalActionPriority)) {
        const { generalActionPriority } = this;
        this.generalActionStack.push(cloneDeep(generalActionPriority));
      }
    },
  },
  data() {
    const chiefActionPriority: IDItem<NPCChiefActions>[] = [];
    const chiefActionKeys = new Set(availableNationPriorityItems);
    for (const id of currentNationPriority) {
      chiefActionPriority.push({ id });
      chiefActionKeys.delete(id);
    }
    const chiefActionUnused: IDItem<NPCChiefActions>[] =
      convertIDArray(chiefActionKeys);

    const generalActionPriority: IDItem<NPCGeneralActions>[] = [];
    const generalActionKeys = new Set(availableGeneralActionPriorityItems);
    for (const id of currentGeneralActionPriority) {
      generalActionPriority.push({ id });
      generalActionKeys.delete(id);
    }
    const generalActionUnused: IDItem<NPCGeneralActions>[] =
      convertIDArray(generalActionKeys);

    return {
      title: "NPC 정책",
      toasts: <ToastType[]>[],
      lastSetters,

      nationID,
      defaultStatMax,
      defaultStatNPCMax,
      nationPolicy: cloneDeep(currentNationPolicy),
      nationPolicyStack: [currentNationPolicy],
      zeroPolicy,

      actionHelpText: NPCPriorityBtnHelpMessage,

      chiefActionUnused,
      chiefActionPriority,
      chiefActionStack: [cloneDeep(chiefActionPriority)],

      generalActionUnused,
      generalActionPriority,
      generalActionStack: [cloneDeep(generalActionPriority)],
    };
  },
});
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