<template>
  <top-back-bar :title="title" />
  <div
    id="container"
    class="tb_layout bg0"
    style="width: 1000px; margin: auto; border: solid 1px #888888"
  >
    <div id="inheritance_list">
      <template v-for="(text, key) in inheritanceViewText" :key="key">
        <div :id="`inherit_${key}`" class="inherit_item inherit_template_item">
          <div class="row">
            <label
              :id="`inherit_${key}_head`"
              class="inherit_head col-sm-6 col-form-label"
              >{{ text.title }}</label
            >
            <div class="col-sm-6">
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
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import "../scss/bootstrap5.scss";
import "../scss/inheritPoint.scss";
import "../scss/game_bg.scss";
import TopBackBar from "./components/TopBackBar.vue";
import { sum } from "lodash";
import _ from "lodash";

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

export default defineComponent({
  name: "InheritPoint",
  data() {
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
    };
  },
  components: {
    TopBackBar,
  },
});
</script>


<style>
</style>