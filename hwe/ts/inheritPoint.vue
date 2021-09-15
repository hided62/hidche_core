<template>
  <top-back-bar :title="title" />
  <div
    id="container"
    class="bg0 px-2"
    style="max-width: 1000px; margin: auto; border: solid 1px #888888; overflow:hidden;"
  >
    <div id="inheritance_list" class="row">
      <template v-for="(text, key) in inheritanceViewText" :key="key">
        <div
          :id="`inherit_${key}`"
          class="col col-sm-4 col-12 inherit_item inherit_template_item"
        >
          <div class="row">
            <label
              :id="`inherit_${key}_head`"
              class="inherit_head col col-md-6 col-sm-7 col-6 col-form-label"
              >{{ text.title }}</label
            >
            <div class="col col-md-6 col-sm-5 col-6">
              <input
                type="text"
                class="form-control inherit_value"
                readonly
                :id="`inherit_${key}_value`"
                :value="Math.floor(items[key]).toLocaleString()"
              />
            </div>
          </div>

          <div style="text-align: right">
            <small class="form-text text-muted" v-html="text.info"></small>
          </div>
        </div>
        <div v-if="key == 'new'" style="width: 100%; padding: 0 10px">
          <hr :style="{ opacity: 0.5 }" />
        </div>
      </template>
    </div>
    <div id="inheritance_store">
      <div class="row">
        <div class="col"><div class="bg1 a-center">유산 포인트 상점</div></div>
      </div>

      <div class="row">
        <div class="col offset-md-4 col-md-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">
              다음 전투 특기 선택
            </div>
            <div class="col-6">
              <select class="form-select col-6" v-model="nextSpecialWar">
                <option
                  v-for="(info, key) in availableSpecialWar"
                  :key="key"
                  :value="key"
                >
                  {{ info.title }}
                </option>
              </select>
            </div>
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              ><span
                style="color: white"
                v-html="availableSpecialWar[nextSpecialWar].info"
              /><br />다음에 얻을 전투 특기를 정합니다.<br /><span
                style="color: white"
                >필요 포인트: {{ inheritActionCost.nextSpecial }}</span
              ></small
            >
          </div>
          <div class="row px-4">
            <b-button
              class="col-6 offset-6"
              variant="primary"
              @click="setNextSpecialWar"
              >구입</b-button
            >
          </div>
        </div>
        <div class="col col-md-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">유니크 입찰</div>
            <div class="col-6">
              <select class="form-select col-6" v-model="specificUnique">
                <option
                  v-for="(info, key) in availableUnique"
                  :key="key"
                  :value="key"
                >
                  {{ info.title }}
                </option>
              </select>
            </div>
          </div>
          <div class="row px-4">
            <div class="col">
              <NumberInputWithInfo
                title="입찰 포인트"
                :min="inheritActionCost.minSpecificUnique"
                :max="this.items.previous"
                v-model="specificUniqueAmount"
              />
            </div>
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              >얻고자 하는 유니크 아이템을 포인트를 걸어 입찰합니다. 최고
              포인트인 경우 다음 턴에 유니크를 얻습니다.<br /><span
                style="color: white"
                v-html="availableUnique[specificUnique].info"
            /></small>
          </div>

          <div class="row px-4">
            <b-button
              class="col-6 offset-6"
              variant="primary"
              @click="buySpecificUnique"
              >구입</b-button
            >
          </div>
        </div>
      </div>
      <div style="width: 100%; padding: 0 10px">
        <hr :style="{ opacity: 0.5 }" />
      </div>
      <div class="row py-sm-2">
        <div class="col col-md-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">랜덤 턴 초기화</div>
            <b-button
              class="col-6"
              variant="primary"
              @click="buySimple('ResetTurnTime')"
              >구입</b-button
            >
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              >다음 턴이 랜덤으로 바뀝니다.<br /><span style="color: white"
                >필요 포인트: {{ inheritActionCost.resetTurnTime }}</span
              ></small
            >
          </div>
        </div>
        <div class="col col-md-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">랜덤 유니크 획득</div>
            <b-button
              class="col-6"
              variant="primary"
              @click="buySimple('BuyRandomUnique')"
              >구입</b-button
            >
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              >다음 턴에 랜덤 유니크를 얻습니다.<br /><span style="color: white"
                >필요 포인트: {{ inheritActionCost.randomUnique }}</span
              ></small
            >
          </div>
        </div>
        <div class="col col-md-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">
              즉시 전투 특기 초기화
            </div>
            <b-button
              class="col-6"
              variant="primary"
              @click="buySimple('ResetSpecialWar')"
              >구입</b-button
            >
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              >즉시 전투 특기를 초기화합니다.<br /><span style="color: white"
                >필요 포인트: {{ inheritActionCost.resetSpecialWar }}</span
              ></small
            >
          </div>
        </div>
      </div>
    </div>
    <div style="width: 100%; padding: 0 10px">
      <hr :style="{ opacity: 0.5 }" />
    </div>
    <div class="row">
      <div
        class="col col-md-4 col-sm-6 col-12"
        v-for="(info, buffKey) in inheritBuffHelpText"
        :key="buffKey"
      >
        <div class="row">
          <label class="col col-sm-6 col-form-label" :for="`buff-${buffKey}`">{{
            info.title
          }}</label>
          <div class="col col-sm-6">
            <b-form-input
              :id="`buff-${buffKey}`"
              type="number"
              v-model="inheritBuff[buffKey]"
              :min="prevInheritBuff[buffKey] ?? 0"
              :max="maxInheritBuff"
            ></b-form-input>
          </div>
        </div>
        <div style="text-align: right">
          <small class="form-text text-muted"
            >{{ info.info }}<br /><span style="color: white"
              >필요 포인트:
              {{
                inheritActionCost.buff[inheritBuff[buffKey]] -
                inheritActionCost.buff[prevInheritBuff[buffKey] ?? 0]
              }}</span
            ></small
          >
        </div>
        <div class="row px-4" style="margin-bottom: 1em">
          <b-button
            variant="secondary"
            @click="inheritBuff[buffKey] = prevInheritBuff[buffKey] ?? 0"
            class="col col-md-6 col-4 offset-md-0 offset-4"
            >리셋</b-button
          ><b-button
            variant="primary"
            class="col col-md-6 col-4"
            @click="buyInheritBuff(buffKey)"
            >구입</b-button
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import "../scss/bootstrap5.scss";
import "../scss/game_bg.scss";
import TopBackBar from "./components/TopBackBar.vue";
import _ from "lodash";
import { InvalidResponse } from "./defs";
import axios from "axios";
import NumberInputWithInfo from "./components/NumberInputWithInfo.vue";

type InheritanceType =
  | "previous"
  | "lived_month"
  | "max_belong"
  | "max_domestic_critical"
  | "snipe_combat"
  | "combat"
  | "sabotage"
  | "unifier"
  | "dex"
  | "tournament"
  | "betting";

type InheritanceViewType = InheritanceType | "sum" | "new";

declare const items: Record<InheritanceType, number>;

const inheritanceViewText: Record<
  InheritanceViewType,
  { title: string; info: string }
> = {
  sum: {
    title: "총 포인트",
    info: "다음 플레이에서 사용할 수 있는 총 포인트입니다.",
  },
  previous: {
    title: "기존 포인트",
    info: "이전에 물려받은 포인트입니다.",
  },
  new: {
    title: "신규 포인트",
    info: "이번 플레이에서 얻은 총 포인트입니다.",
  },
  lived_month: {
    title: "생존",
    info: "살아남은 기간입니다. (1개월 단위)",
  },
  max_belong: {
    title: "최대 임관년 수",
    info: "가장 오래 임관했던 국가의 연도입니다.",
  },
  max_domestic_critical: {
    title: "최대 연속 내정 성공",
    info: "성공한 내정 중 최대 연속값입니다.",
  },
  snipe_combat: {
    title: "병종 상성 우위 횟수",
    info: "유리한 상성을 가지고 전투했습니다.",
  },
  combat: {
    title: "전투 횟수",
    info: "전투 횟수입니다.",
  },
  sabotage: {
    title: "계략 성공 횟수",
    info: "계략 성공 횟수입니다.",
  },
  unifier: {
    title: "천통 기여",
    info: "천통에 기여한 포인트입니다. <br>각 국의 군주, 천통 수뇌, 천통 군주가 받습니다.",
  },
  dex: {
    title: "숙련도",
    info: "총 숙련도합입니다.",
  },
  tournament: {
    title: "토너먼트",
    info: "토너먼트 입상 포인트입니다.",
  },
  betting: {
    title: "베팅 당첨",
    info: "성공적인 베팅을 했습니다. <br>수익율과 베팅 성공 횟수를 따릅니다.",
  },
};

type inheritBuffType =
  | "warAvoidRatio"
  | "warCriticalRatio"
  | "warMagicTrialProb"
  | "domesticSuccessProb"
  | "domesticFailProb"
  | "warAvoidRatioOppose"
  | "warCriticalRatioOppose"
  | "warMagicTrialProbOppose";

declare const currentInheritBuff: {
  [v in inheritBuffType]: number | undefined;
};

const inheritBuffHelpText: Record<
  inheritBuffType,
  {
    title: string;
    info: string;
  }
> = {
  warAvoidRatio: {
    title: "회피 확률 증가",
    info: "전투 시 회피 확률이 1%p ~ 5%p 증가합니다.",
  },
  warCriticalRatio: {
    title: "필살 확률 증가",
    info: "전투 시 필살 확률이 1%p ~ 5%p 증가합니다.",
  },
  warMagicTrialProb: {
    title: "계략 시도 확률 증가",
    info: "전투 시 계략을 시도할 확률이 1%p ~ 5%p 증가합니다. 무장도 계략을 시도합니다.",
  },
  warAvoidRatioOppose: {
    title: "상대 회피 확률 감소",
    info: "전투 시 상대의 회피 확률이 1%p ~ 5%p 감소합니다.",
  },
  warCriticalRatioOppose: {
    title: "상대 필살 확률 감소",
    info: "전투 시 상대의 필살 확률이 1%p ~ 5%p 감소합니다.",
  },
  warMagicTrialProbOppose: {
    title: "상대 계략 시도 확률 감소",
    info: "전투 시 상대의 계략 시도 확률이 1%p ~ 5%p 감소합니다.",
  },
  domesticSuccessProb: {
    title: "내정 성공 확률 증가",
    info: "민심, 인구, 농업, 상업, 치안, 수비, 성벽, 기술 내정의 성공 확률이 1%p ~ 5%p 증가합니다.",
  },
  domesticFailProb: {
    title: "내정 실패 확률 감소",
    info: "민심, 인구, 농업, 상업, 치안, 수비, 성벽, 기술 내정의 실패 확률이 1%p ~ 5%p 감소합니다.",
  },
};

declare const maxInheritBuff: number;
declare const inheritActionCost: {
  buff: number[];
  resetTurnTime: number;
  resetSpecialWar: number;
  randomUnique: number;
  nextSpecial: number;
  minSpecificUnique: number;
};

declare const resetTurnTimeLevel: number;
declare const resetSpecialWarLevel: number;

declare const availableSpecialWar: Record<
  string,
  {
    title: string;
    info: string;
  }
>;

declare const availableUnique: Record<
  string,
  {
    title: string;
    info: string;
  }
>;

export default defineComponent({
  name: "InheritPoint",
  data() {
    const inheritBuff = {} as Record<inheritBuffType, number>;
    for (const buffKey of Object.keys(
      inheritBuffHelpText
    ) as inheritBuffType[]) {
      inheritBuff[buffKey] = currentInheritBuff[buffKey] ?? 0;
    }
    return {
      title: "유산 관리",
      inheritanceViewText,
      items: (() => {
        const totalPoint = Math.floor(_.sum(Object.values(items)));
        const previousPoint = Math.floor(items["previous"]);
        const newPoint = Math.floor(totalPoint - previousPoint);
        const result: Record<InheritanceViewType, number> = {
          ...items,
          sum: totalPoint,
          new: newPoint,
        };
        return result;
      })(),
      inheritBuffHelpText,
      inheritBuff,
      prevInheritBuff: currentInheritBuff,
      maxInheritBuff,
      inheritActionCost,
      resetTurnTimeLevel,
      resetSpecialWarLevel,
      nextSpecialWar: Object.keys(availableSpecialWar)[0],
      specificUnique: Object.keys(availableUnique)[0],
      availableSpecialWar,
      availableUnique,
      specificUniqueAmount: inheritActionCost.minSpecificUnique,
    };
  },
  methods: {
    async buyInheritBuff(buffKey: inheritBuffType) {
      const level = this.inheritBuff[buffKey];
      const prevLevel = this.prevInheritBuff[buffKey] ?? 0;
      if (level == prevLevel) {
        return;
      }
      if (level < prevLevel) {
        alert("낮출 수 없습니다.");
        return;
      }
      const cost =
        this.inheritActionCost.buff[level] -
        this.inheritActionCost.buff[prevLevel];
      if (this.items.previous < cost) {
        alert("유산 포인트가 부족합니다.");
        return;
      }

      const name = inheritBuffHelpText[buffKey].title;

      if (
        !confirm(
          `${name}를 ${level}등급으로 올릴까요? ${cost} 포인트가 소모됩니다.`
        )
      ) {
        return;
      }

      let result: InvalidResponse;
      try {
        const response = await axios({
          url: "api.php",
          method: "post",
          responseType: "json",
          data: {
            path: "InheritAction/BuyHiddenBuff",
            args: {
              type: buffKey,
              level,
            },
          },
        });
        result = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }

      alert("성공했습니다.");
      //TODO: 페이지 새로고침 필요없이 하도록
      location.reload();
    },
    async buySimple(
      type: "ResetTurnTime" | "BuyRandomUnique" | "ResetSpecialWar"
    ) {
      const costMap: Record<typeof type, number> = {
        ResetTurnTime: inheritActionCost.resetTurnTime,
        ResetSpecialWar: inheritActionCost.resetSpecialWar,
        BuyRandomUnique: inheritActionCost.randomUnique,
      };

      const cost = costMap[type];
      if (cost === undefined) {
        alert(`올바르지 않은 타입:${type}`);
        return;
      }

      const messageMap: Record<typeof type, string> = {
        ResetTurnTime: `${cost} 포인트로 턴을 초기화 하시겠습니까?`,
        ResetSpecialWar: `${cost} 포인트로 전투 특기를 초기화 하시겠습니까?`,
        BuyRandomUnique: `${cost} 포인트로 랜덤 유니크를 구입하시겠습니까?`,
      };
      if (this.items.previous < cost) {
        alert("유산 포인트가 부족합니다.");
        return;
      }
      if (!confirm(messageMap[type])) {
        return;
      }

      let result: InvalidResponse;
      try {
        const response = await axios({
          url: "api.php",
          method: "post",
          responseType: "json",
          data: {
            path: `InheritAction/${type}`,
            args: {},
          },
        });
        result = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }

      alert("성공했습니다.");
      //TODO: 페이지 새로고침 필요없이 하도록
      location.reload();
    },
    async setNextSpecialWar() {
      const specialWarName =
        this.availableSpecialWar[this.nextSpecialWar].title ?? undefined;
      if (specialWarName === undefined) {
        alert(`잘못된 타입: ${this.nextSpecialWar}`);
        return;
      }

      const cost = inheritActionCost.nextSpecial;
      if (this.items.previous < cost) {
        alert("유산 포인트가 부족합니다.");
        return;
      }
      //TODO: JosaUtil
      if (
        !confirm(
          `${cost} 포인트로 다음 전특을 ${specialWarName}(으)로 고정하겠습니까?`
        )
      ) {
        return;
      }

      let result: InvalidResponse;
      try {
        const response = await axios({
          url: "api.php",
          method: "post",
          responseType: "json",
          data: {
            path: `InheritAction/SetNextSpecialWar`,
            args: {
              type: this.nextSpecialWar,
            },
          },
        });
        result = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }

      alert("성공했습니다.");
      //TODO: 페이지 새로고침 필요없이 하도록
      location.reload();
    },
    async buySpecificUnique() {
      const uniqueName =
        this.availableUnique[this.specificUnique].title ?? undefined;
      if (uniqueName === undefined) {
        alert(`잘못된 타입: ${this.specificUnique}`);
        return;
      }

      const amount = this.specificUniqueAmount;
      if (this.items.previous < amount) {
        alert("유산 포인트가 부족합니다.");
        return;
      }
      //TODO: JosaUtil
      if (
        !confirm(
          `${amount} 포인트로 ${uniqueName}(을)를 입찰하겠습니까?`
        )
      ) {
        return;
      }

      let result: InvalidResponse;
      try {
        const response = await axios({
          url: "api.php",
          method: "post",
          responseType: "json",
          data: {
            path: `InheritAction/BuySpecificUnique`,
            args: {
              item: this.specificUnique,
              amount,
            },
          },
        });
        result = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }

      alert("성공했습니다.");
      //TODO: 페이지 새로고침 필요없이 하도록
      location.reload();
    },
  },
  components: {
    TopBackBar,
    NumberInputWithInfo,
  },
});
</script>


<style>
.col-form-label {
  text-align: right;
  padding-right: 2ch;
}

.inherit_value {
  text-align: right;
}
</style>