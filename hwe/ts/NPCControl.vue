<template>
  <!-- backbutton -->
  <div
    id="container"
    class="tb_layout bg0"
    :style="{ width: '1000px', margin: 'auto', border: 'solid 1px #888888' }"
  >
    <div class="bg1 section_bar">국가 정책</div>
    <div class="text-right px-3">
      <small class="form-text text-muted">
        최근 설정:
        {{ nationPolicy.valueSetter ?? "-없음-" }}
        ({{ nationPolicy.valueSetTime ?? "설정 기록 없음" }})
      </small>
    </div>
    <div class="form_list">
      <div class="form-group row">
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.reqNationGold"
            :step="100"
            title="국가 권장 금"
            >이보다 많으면 포상, 적으면 몰수/헌납합니다.(긴급포상
            제외)</NumberInputWithInfo
          >
        </div>
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.reqNationRice"
            :step="100"
            title="국가 권장 쌀"
            >이보다 많으면 포상, 적으면 몰수/헌납합니다.(긴급포상
            제외)</NumberInputWithInfo
          >
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-6">
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
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.reqHumanWarUrgentRice"
            :step="100"
            title="유저전투장 긴급포상 금"
            >유저장긴급포상시 이보다 쌀이 적은 장수에게 포상합니다.<br />0이면
            기본 병종으로 {{ (defaultStatMax * 100).toLocaleString() }} * 6명
            사살 가능한 쌀을 기준으로 하며, 그 수치는 현재
            {{
              zeroPolicy.reqHumanWarUrgentRice.toLocaleString()
            }}입니다.</NumberInputWithInfo
          >
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-6">
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
        <div class="col-sm-6">
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
      </div>
      <div class="form-group row">
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.reqHumanDevelGold"
            :step="100"
            title="유저내정장 권장 금"
            >유저내정장에게 주는 금입니다. 이보다 적으면
            포상합니다.</NumberInputWithInfo
          >
        </div>
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.reqHumanDevelRice"
            :step="100"
            title="유저내정장 권장 쌀"
            >유저내정장에게 주는 쌀입니다. 이보다 적으면
            포상합니다.</NumberInputWithInfo
          >
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-6">
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
        <div class="col-sm-6">
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
      </div>
      <div class="form-group row">
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.reqNPCDevelGold"
            :step="100"
            title="NPC내정장 권장 금"
            >NPC내정장에게 주는 금입니다. 이보다 5배 더 많다면 헌납합니다.<br />0이면
            30턴 내정 가능한 금을 기준으로 하며, 그 수치는 현재
            {{ zeroPolicy.reqNPCDevelGold }}입니다.</NumberInputWithInfo
          >
        </div>
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.reqNPCDevelRice"
            :step="100"
            title="NPC내정장 권장 쌀"
            >NPC내정장에게 주는 쌀입니다. 이보다 5배 더 많다면
            헌납합니다.</NumberInputWithInfo
          >
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.minimumResourceActionAmount"
            :step="100"
            title="포상/몰수/헌납/삼/팜 최소 단위"
            >연산결과가 이 단위보다 적다면 수행하지
            않습니다.</NumberInputWithInfo
          >
        </div>
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.minWarCrew"
            :step="50"
            title="최소 전투 가능 병력 수"
            >이보다 적을 때에는 징병을 시도합니다.</NumberInputWithInfo
          >
        </div>
      </div>
      <div class="form-group row">
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.minNPCRecruitCityPopulation"
            :step="100"
            title="NPC 최소 징병 가능 인구 수"
            >도시의 인구가 이보다 낮으면 NPC는 도시에서 징병하지 않고 후방
            워프합니다.<br />NPC의 최대 병력수보다 낮게 설정하면 제자리에서
            정착장려를 합니다.</NumberInputWithInfo
          >
        </div>
        <div class="col-sm-6">
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
      </div>
      <div class="form-group row">
        <div class="col-sm-6">
          <NumberInputWithInfo
            v-model="nationPolicy.minNPCWarLeadership"
            :step="5"
            title="NPC 전투 참여 통솔 기준"
            >이 수치보다 같거나 높으면 NPC전투장으로
            분류됩니다.</NumberInputWithInfo
          >
        </div>
        <div class="col-sm-6">
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
      </div>
      <!--allowNpcAttackCity는 현재 게임 내 비활성-->

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
    <div class="row">
      <div class="col-sm-6 half_section_left">
        <div class="bg1 section_bar">NPC 사령턴 우선순위</div>
        <div class="float-right px-3">
          <small class="form-text text-muted">
            최근 설정:
            {{ chiefActionPriority.prioritySetter ?? "-없음-" }}
            ({{ chiefActionPriority.prioritySetTime ?? "설정 기록 없음" }})
          </small>
        </div>
        <div class="text-left px-2">
          <small class="text-muted"
            >예턴이 없거나, 지정되어 있더라도 실패할경우<br />아래 순위에 따라
            사령턴을 시도합니다.</small
          >
        </div>
        <div class="form_list">
          <div class="row">
            <div class="col-sm-6">
              <div class="bg2 sub_bar">비활성</div>
              <div
                id="nationPriorityDisabled"
                class="list-group col"
                data-type="list"
              ></div>
            </div>
            <div class="col-sm-6">
              <div class="bg2 sub_bar">활성</div>
              <div
                id="nationPriority"
                class="list-group col"
                data-type="list"
              ></div>
            </div>
          </div>
          <div class="control_bar" data-type="nationPriority">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-dark reset_btn">
                초기값으로
              </button>
              <button type="button" class="btn btn-secondary revert_btn">
                이전값으로
              </button>
            </div>
            <button type="button" class="btn btn-primary submit_btn">
              설정
            </button>
          </div>
        </div>
      </div>
      <div class="col-sm-6 half_section_right">
        <div class="bg1 section_bar">NPC 일반턴 우선순위</div>
        <div class="float-right px-3">
          <small class="form-text text-muted">
            최근 설정:
            {{ nationPolicy.prioritySetter ?? "-없음-" }}
            ({{ nationPolicy.prioritySetTime ?? "설정 기록 없음" }})
          </small>
        </div>
        <div class="text-left px-2">
          <small class="text-muted"
            >순위가 높은 것부터 시도합니다. <br />아무것도 실행할 수 없으면
            물자조달이나 인재탐색을 합니다.</small
          >
        </div>
        <div class="form_list">
          <div class="row">
            <div class="col-sm-6">
              <div class="bg2 sub_bar">비활성</div>
              <div
                id="generalPriorityDisabled"
                class="list-group col"
                data-type="list"
              ></div>
            </div>
            <div class="col-sm-6">
              <div class="bg2 sub_bar">활성</div>
              <div
                id="generalPriority"
                class="list-group col"
                data-type="list"
              ></div>
            </div>
          </div>
          <div class="control_bar" data-type="generalPriority">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-dark reset_btn">
                초기값으로
              </button>
              <button type="button" class="btn btn-secondary revert_btn">
                이전값으로
              </button>
            </div>
            <button type="button" class="btn btn-primary submit_btn">
              설정
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import { defineComponent } from "vue";
import "../scss/bootstrap5.scss";
import "../scss/game_bg.scss";
import {
  InvalidResponse,
  NationPolicy,
  NPCChiefActions,
  NPCGeneralActions,
} from "./defs";
import NumberInputWithInfo from "./components/NumberInputWithInfo.vue";
import { cloneDeep, isEqual, last } from "lodash";
import { unwrap } from "./util/unwrap";
import { convertFormData } from "./util/convertFormData";
import axios from "axios";

declare const nationID: number;

declare const defaultNationPolicy: NationPolicy;
declare const currentNationPolicy: NationPolicy;
declare const defaultNationPriority: NationPolicy;

declare const zeroPolicy: NationPolicy;
declare const autoPolicy: NationPolicy;

declare const currentNationPriority: NPCChiefActions[];
declare const availableNationPriorityItems: NPCChiefActions[];
declare const defaultGeneralActionPriority: NPCChiefActions[];

declare const currentGeneralActionPriority: NPCGeneralActions[];
declare const availableGeneralActionPriorityItems: NPCGeneralActions[];

declare const defaultStatNPCMax: number;
declare const defaultStatMax: number;

export default defineComponent({
  name: "NPCControl",
  components: {
    NumberInputWithInfo,
  },
  methods: {
    resetPolicy() {
      this.nationPolicy = cloneDeep(defaultNationPolicy);
      //TODO: toast
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
      //TODO: toast
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
        //TODO: toast
        return;
      }

      //TODO: submit
      //TODO: toast
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
  },
  data() {
    return {
      reqNationGold: 110,

      nationID,
      defaultStatMax,
      defaultStatNPCMax,
      nationPolicy: cloneDeep(currentNationPolicy),
      nationPolicyStack: [currentNationPolicy],
      zeroPolicy,
      autoPolicy,

      chiefActionPriority: cloneDeep(currentNationPriority),
      //chiefActionUnused: [],
      generalActionPriority: cloneDeep(currentGeneralActionPriority),
      //generalActionUnsed: [],

      displayOrder: [],
    };
  },
});
</script>
