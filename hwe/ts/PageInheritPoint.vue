<template>
  <TopBackBar :title="title" />
  <div
    id="container"
    class="bg0 px-2"
    style="max-width: 1000px; margin: auto; border: solid 1px #888888; overflow: hidden"
  >
    <div id="inheritance_list" class="row">
      <template v-for="(text, key) in inheritanceViewText" :key="key">
        <div :id="`inherit_${key}`" class="col col-sm-4 col-12 inherit_item inherit_template_item">
          <div class="row">
            <label :id="`inherit_${key}_head`" class="inherit_head col col-lg-6 col-sm-7 col-6 col-form-label">{{
              text.title
            }}</label>
            <div class="col col-lg-6 col-sm-5 col-6">
              <input
                :id="`inherit_${key}_value`"
                type="text"
                class="form-control inherit_value f_tnum"
                readonly
                :value="Math.floor(items[key]).toLocaleString()"
              />
            </div>
          </div>

          <div style="text-align: right">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <small class="form-text text-muted" v-html="text.info" />
          </div>
        </div>
        <div v-if="key == 'new'" style="width: 100%; padding: 0 10px">
          <hr :style="{ opacity: 0.5 }" />
        </div>
      </template>
    </div>
    <div id="inheritance_store">
      <div class="row">
        <div class="col">
          <div class="bg1 a-center">유산 포인트 상점</div>
        </div>
      </div>

      <div class="row">
        <div class="col offset-lg-4 col-lg-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">다음 전투 특기 선택</div>
            <div class="col-6">
              <select v-model="nextSpecialWar" class="form-select col-6">
                <option v-for="(info, key) in availableSpecialWar" :key="key" :value="key">
                  {{ info.title }}
                </option>
              </select>
            </div>
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              ><!-- eslint-disable-next-line vue/no-v-html -->
              <span style="color: white" v-html="availableSpecialWar[nextSpecialWar].info" /><br />다음에 얻을 전투
              특기를 정합니다.<br /><span style="color: white"
                >필요 포인트: {{ inheritActionCost.nextSpecial }}</span
              ></small
            >
          </div>
          <div class="row px-4">
            <BButton class="col-6 offset-6" variant="primary" @click="setNextSpecialWar"> 구입 </BButton>
          </div>
        </div>
        <div class="col col-lg-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">유니크 경매</div>
            <div class="col-6">
              <select v-model="specificUnique" class="form-select col-6">
                <option disabled selected :value="null">유니크 선택</option>
                <option v-for="(info, key) in availableUnique" :key="key" :value="key">
                  {{ info.title }}
                </option>
              </select>
            </div>
          </div>
          <div class="row px-4">
            <div class="col f_tnum">
              <NumberInputWithInfo
                v-model="specificUniqueAmount"
                title="입찰 포인트"
                :min="inheritActionCost.minSpecificUnique"
                :max="items.previous"
              />
            </div>
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              >얻고자 하는 유니크 아이템으로 경매를 시작합니다. 24턴 동안 진행됩니다.<br />
              <!-- eslint-disable-next-line vue/no-v-html -->
              <span style="color: white" v-html="specificUnique == null ? '' : availableUnique[specificUnique].info" />
            </small>
          </div>

          <div class="row px-4">
            <BButton class="col-6 offset-6" variant="primary" @click="openUniqueItemAuction"> 경매 시작 </BButton>
          </div>
        </div>
      </div>
      <div style="width: 100%; padding: 0 10px">
        <hr :style="{ opacity: 0.5 }" />
      </div>
      <div class="row py-sm-2">
        <div class="col col-lg-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">랜덤 턴 초기화</div>
            <BButton class="col-6" variant="primary" @click="tryResestTurnTime()"> 구입 </BButton>
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              >다음 턴 시간이 앞, 뒤 랜덤하게 바뀝니다. (필요 포인트가 피보나치식으로 증가합니다)<br /><span style="color: white"
                >필요 포인트: {{ inheritActionCost.resetTurnTime }}</span
              ></small
            >
          </div>
        </div>
        <div class="col col-lg-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">랜덤 유니크 획득</div>
            <BButton class="col-6" variant="primary" @click="buySimple('BuyRandomUnique')"> 구입 </BButton>
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              >다음 턴에 랜덤 유니크를 얻습니다.<br /><span style="color: white"
                >필요 포인트: {{ inheritActionCost.randomUnique }}</span
              ></small
            >
          </div>
        </div>
        <div class="col col-lg-4 col-sm-6 col-12 py-2">
          <div class="row px-4">
            <div class="a-right col-6 align-self-center">즉시 전투 특기 초기화</div>
            <BButton class="col-6" variant="primary" @click="buySimple('ResetSpecialWar')"> 구입 </BButton>
          </div>
          <div class="a-right">
            <small class="form-text text-muted"
              >즉시 전투 특기를 초기화합니다. (필요 포인트가 피보나치식으로 증가합니다)<br /><span style="color: white"
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
      <div v-for="(info, buffKey) in inheritBuffHelpText" :key="buffKey" class="col col-lg-4 col-sm-6 col-12">
        <div class="row">
          <label class="col col-sm-6 col-form-label" :for="`buff-${buffKey}`">{{ info.title }}</label>
          <div class="col col-sm-6 f_tnum">
            <b-form-input
              :id="`buff-${buffKey}`"
              v-model.number="inheritBuff[buffKey]"
              type="number"
              :min="prevInheritBuff[buffKey] ?? 0"
              :max="maxInheritBuff"
            />
          </div>
        </div>
        <div style="text-align: right">
          <small class="form-text text-muted f_tnum"
            >{{ info.info }}<br /><span style="color: white"
              >필요 포인트:
              {{
                inheritActionCost.buff[inheritBuff[buffKey]] - inheritActionCost.buff[prevInheritBuff[buffKey] ?? 0]
              }}</span
            ></small
          >
        </div>
        <div class="row px-4" style="margin-bottom: 1em">
          <BButton
            variant="secondary"
            class="col col-lg-6 col-4 offset-lg-0 offset-4"
            @click="inheritBuff[buffKey] = prevInheritBuff[buffKey] ?? 0"
          >
            리셋 </BButton
          ><BButton variant="primary" class="col col-lg-6 col-4" @click="buyInheritBuff(buffKey)"> 구입 </BButton>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="bg1 a-center">유산 포인트 변경 내역</div>
      </div>
    </div>
    <div v-for="[idx, log] of inheritPointLogs" :key="idx" class="row">
      <div class="col a-right" style="max-width: 20ch">
        <small class="text-muted tnum">[{{ log.date }}]</small>
      </div>
      <div class="col a-left">
        {{ log.text }}
      </div>
    </div>
    <div class="d-grid"><BButton @click="getMoreLog()">더 가져오기</BButton></div>
  </div>
</template>

<script lang="ts">
type InheritanceType =
  | "previous"
  | "lived_month"
  | "max_belong"
  | "max_domestic_critical"
  | "active_action"
  //  | "snipe_combat"
  | "combat"
  | "sabotage"
  | "unifier"
  | "dex"
  | "tournament"
  | "betting";

type InheritanceViewType = InheritanceType | "sum" | "new";

declare const staticValues: {
  lastInheritPointLogs: InheritPointLogItem[];
  items: Record<InheritanceType, number>;
  currentInheritBuff: {
    [v in inheritBuffType]: number | undefined;
  };
  maxInheritBuff: number;
  inheritActionCost: {
    buff: number[];
    resetTurnTime: number;
    resetSpecialWar: number;
    randomUnique: number;
    nextSpecial: number;
    minSpecificUnique: number;
  };
  resetTurnTimeLevel: number;
  resetSpecialWarLevel: number;
  ternTurm: number;
  turnTime: string;

  availableSpecialWar: Record<
    string,
    {
      title: string;
      info: string;
    }
  >;
  availableUnique: Record<
    string,
    {
      title: string;
      rawName: string;
      info: string;
    }
  >;
};
</script>
<script lang="ts" setup>
import { reactive, ref } from "vue";
import "@scss/game_bg.scss";
import TopBackBar from "@/components/TopBackBar.vue";
import _ from "lodash-es";
import NumberInputWithInfo from "@/components/NumberInputWithInfo.vue";
import { SammoAPI } from "./SammoAPI";
import type { inheritBuffType, InheritPointLogItem } from "./defs/API/InheritAction";
import * as JosaUtil from "@/util/JosaUtil";
import { BButton } from "bootstrap-vue-next";
import { unwrap } from "./util/unwrap";
import { add as dateAdd } from 'date-fns';

const inheritanceViewText: Record<InheritanceViewType, { title: string; info: string }> = {
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
  active_action: {
    title: "능동 행동 수",
    info: "장수 동향에 본인의 이름이 직접 나타난 수입니다.<br>일부 사령턴은 제외됩니다.",
  },
  /*  snipe_combat: {
    title: "병종 상성 우위 횟수",
    info: "유리한 상성을 가지고 전투했습니다.",
  },*/
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
    info: "총 숙련도합입니다. <br>최대 숙련 이후에는 상승량이 1/3로 감소합니다.",
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

const inheritBuff = reactive({} as Record<inheritBuffType, number>);
for (const buffKey of Object.keys(inheritBuffHelpText) as inheritBuffType[]) {
  inheritBuff[buffKey] = staticValues.currentInheritBuff[buffKey] ?? 0;
}

const title = "유산 관리";

const items = ref(
  (() => {
    const totalPoint = Math.floor(_.sum(Object.values(staticValues.items)));
    const previousPoint = Math.floor(staticValues.items["previous"]);
    const newPoint = Math.floor(totalPoint - previousPoint);
    const result: Record<InheritanceViewType, number> = {
      ...staticValues.items,
      sum: totalPoint,
      new: newPoint,
    };
    return result;
  })()
);

const {
  maxInheritBuff,
  inheritActionCost,
  currentInheritBuff: prevInheritBuff,
  availableSpecialWar,
  availableUnique,
} = staticValues;

const nextSpecialWar = ref(Object.keys(availableSpecialWar)[0]);
const specificUnique = ref<string | null>(null);
const specificUniqueAmount = ref(inheritActionCost.minSpecificUnique);

const lastLogID = ref(Math.min(...staticValues.lastInheritPointLogs.map((v) => v.id)));
const inheritPointLogs = ref(
  (() => {
    const logs = new Map<number, InheritPointLogItem>();
    for (const log of staticValues.lastInheritPointLogs) {
      logs.set(log.id, log);
    }
    return logs;
  })()
);

staticValues.lastInheritPointLogs;
async function buyInheritBuff(buffKey: inheritBuffType) {
  const level = Math.floor(unwrap(inheritBuff[buffKey]));
  const prevLevel = prevInheritBuff[buffKey] ?? 0;
  if (level == prevLevel) {
    return;
  }
  if (level < prevLevel) {
    alert("낮출 수 없습니다.");
    return;
  }
  const cost = inheritActionCost.buff[level] - inheritActionCost.buff[prevLevel];
  if (items.value.previous < cost) {
    alert("유산 포인트가 부족합니다.");
    return;
  }

  const name = inheritBuffHelpText[buffKey].title;
  const josaUl = JosaUtil.pick(name, "을");
  if (!confirm(`${name}${josaUl} ${level}등급으로 올릴까요? ${cost} 포인트가 소모됩니다.`)) {
    return;
  }

  try {
    await SammoAPI.InheritAction.BuyHiddenBuff({
      type: buffKey,
      level,
    });
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }

  alert("성공했습니다.");
  //TODO: 페이지 새로고침 필요없이 하도록
  location.reload();
}

async function tryResestTurnTime(){
  const cost = inheritActionCost.resetTurnTime;

  if (items.value.previous < cost) {
    alert("유산 포인트가 부족합니다.");
    return;
  }


  const { minTurnTime, maxTurnTime } = await SammoAPI.InheritAction.CalcResetTurnTimeRange();

  //YYYY-MM-DD hh:mm:ss 에서 hh:mm:ss 까지만 보여줄 예정
  const textMinTurnTime = minTurnTime.substring(11, 19);
  const textMaxTurnTime = maxTurnTime.substring(11, 19);

  const msg = `${cost} 포인트로 턴을 초기화 하시겠습니까?\n${textMinTurnTime} ~ ${textMaxTurnTime} 사이의 시간으로 초기화 됩니다.`;
  if (!confirm(msg)) {
    return;
  }

  try {
    await SammoAPI.InheritAction.ResetTurnTime();
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }

  alert("성공했습니다.");
  //TODO: 페이지 새로고침 필요없이 하도록
  location.reload();
}

async function buySimple(type: "BuyRandomUnique" | "ResetSpecialWar") {
  const costMap: Record<typeof type, number> = {
    ResetSpecialWar: inheritActionCost.resetSpecialWar,
    BuyRandomUnique: inheritActionCost.randomUnique,
  };

  const cost = costMap[type];
  if (cost === undefined) {
    alert(`올바르지 않은 타입:${type}`);
    return;
  }

  if (items.value.previous < cost) {
    alert("유산 포인트가 부족합니다.");
    return;
  }

  const messageMap: Record<typeof type, string> = {
    ResetSpecialWar: `${cost} 포인트로 전투 특기를 초기화 하시겠습니까?`,
    BuyRandomUnique: `${cost} 포인트로 랜덤 유니크를 구입하시겠습니까?`,
  };

  if (!confirm(messageMap[type])) {
    return;
  }

  try {
    await SammoAPI.InheritAction[type]();
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }

  alert("성공했습니다.");
  //TODO: 페이지 새로고침 필요없이 하도록
  location.reload();
}
async function setNextSpecialWar() {
  const specialWarName = availableSpecialWar[nextSpecialWar.value].title ?? undefined;
  if (specialWarName === undefined) {
    alert(`잘못된 타입: ${nextSpecialWar.value}`);
    return;
  }

  const cost = inheritActionCost.nextSpecial;
  if (items.value.previous < cost) {
    alert("유산 포인트가 부족합니다.");
    return;
  }

  const josaRo = JosaUtil.pick(specialWarName, "로");
  if (!confirm(`${cost} 포인트로 다음 전특을 ${specialWarName}${josaRo} 고정하겠습니까?`)) {
    return;
  }

  try {
    await SammoAPI.InheritAction.SetNextSpecialWar({
      type: nextSpecialWar.value,
    });
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }

  alert("성공했습니다.");
  //TODO: 페이지 새로고침 필요없이 하도록
  location.reload();
}
async function openUniqueItemAuction() {
  if (specificUnique.value === null) {
    alert("유니크를 선택해주세요.");
    return;
  }

  const uniqueName = availableUnique[specificUnique.value].title ?? undefined;
  if (uniqueName === undefined) {
    alert(`잘못된 타입: ${specificUnique.value}`);
    return;
  }
  const uniqueRawName = availableUnique[specificUnique.value].rawName ?? undefined;

  const amount = specificUniqueAmount.value;
  if (items.value.previous < amount) {
    alert("유산 포인트가 부족합니다.");
    return;
  }

  const josaUl = JosaUtil.pick(uniqueRawName, "을");
  if (!confirm(`${amount} 포인트로 ${uniqueName}${josaUl} 입찰하겠습니까?`)) {
    return;
  }

  try {
    await SammoAPI.Auction.OpenUniqueAuction({
      itemID: specificUnique.value,
      amount,
    });
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }

  alert("성공했습니다. 경매장을 확인해주세요.");
  //TODO: 페이지 새로고침 필요없이 하도록
  location.reload();
}

async function getMoreLog(): Promise<void>{
  try{
    const result = await SammoAPI.InheritAction.GetMoreLog({
      lastID: lastLogID.value
    });
    for(const log of result.log){
      inheritPointLogs.value.set(log.id, log);
      lastLogID.value = Math.min(lastLogID.value, log.id);
    }
  }catch(e){
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }
}
</script>

<style>
.col-form-label {
  text-align: right;
  padding-right: 2ch;
}

.inherit_value {
  text-align: right;
}

.tnum {
  font-feature-settings: "tnum";
}
</style>
