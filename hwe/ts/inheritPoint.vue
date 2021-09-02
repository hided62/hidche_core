<template>
  <top-back-bar :title="title" />

  <div
    id="container"
    class="tb_layout bg0"
    style="width: 1000px; margin: auto; border: solid 1px #888888"
  >
    <div id="inheritance_list">
      <div id="inherit_sum" class="inherit_item">
        <div class="row">
          <label
            id="inherit_sum_head"
            class="inherit_head col-sm-6 col-form-label"
            >총 포인트</label
          >
          <div class="col-sm-6">
            <input
              type="text"
              class="form-control inherit_value"
              readonly
              id="inherit_sum_value"
              value=""
            />
          </div>
        </div>

        <div style="text-align: right">
          <small class="form-text text-muted"></small>
        </div>
      </div>

      <div id="inherit_previous" class="inherit_item">
        <div class="row">
          <label
            id="inherit_sum_head"
            class="inherit_head col-sm-6 col-form-label"
            >기존 포인트</label
          >
          <div class="col-sm-6">
            <input
              type="text"
              class="form-control inherit_value"
              readonly
              id="inherit_previous_value"
              value=""
            />
          </div>
        </div>

        <div style="text-align: right">
          <small class="form-text text-muted"></small>
        </div>
      </div>

      <div id="inherit_new" class="inherit_item">
        <div class="row">
          <label
            id="inherit_sum_head"
            class="inherit_head col-sm-6 col-form-label"
            >신규 포인트</label
          >
          <div class="col-sm-6">
            <input
              type="text"
              class="form-control inherit_value"
              readonly
              id="inherit_new_value"
              value=""
            />
          </div>
        </div>

        <div style="text-align: right">
          <small class="form-text text-muted"></small>
        </div>
      </div>
      <div style="width: 100%; padding: 0 10px">
        <hr style="border-top: 1px solid #888888" />
      </div>
      <!--<?php if ($key == 'previous') {
                    continue;
                } ?>-->
      <div
        v-for="(vals, key) in inheritanceKey"
        :key="key"
        :id="`inherit_${key}`"
        class="inherit_item inherit_template_item"
      >
        <div class="row">
          <label
            id="inherit_<?= $key ?>_head"
            class="inherit_head col-sm-6 col-form-label"
            >{{ vals[2] }}</label
          >
          <div class="col-sm-6">
            <input
              type="text"
              class="form-control inherit_value"
              readonly
              :id="`inherit_${key}_value`"
              value=""
            />
          </div>
        </div>

        <div style="text-align: right">
          <small class="form-text text-muted"></small>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import "../scss/inheritPoint.scss";
import "../scss/game_bg.scss";
import TopBackBar from "./components/TopBackBar.vue";

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

const inheritanceKey: Record<
  InheritanceType,
  [boolean | [string, string], number, string]
> = {
  previous: [true, 1, "기존 포인트"],
  lived_month: [true, 1, "생존"],
  max_belong: [false, 10, "최대 임관년 수"],
  max_domestic_critical: [true, 1, "최대 연속 내정 성공"],
  snipe_combat: [true, 10, "병종 상성 우위 횟수"],
  combat: [["rank", "warnum"], 5, "전투 횟수"],
  sabotage: [["rank", "firenum"], 20, "계략 성공 횟수"],
  unifier: [true, 1, "천통 기여"],
  dex: [false, 0.001, "숙련도"],
  tournament: [true, 1, "토너먼트"],
  betting: [false, 10, "베팅 당첨"],
};
const pointHelpText: Record<InheritanceViewType, string> = {
  sum: "다음 플레이에서 사용할 수 있는 총 포인트입니다.",
  new: "이번 플레이에서 얻은 총 포인트입니다.",
  previous: "이전에 물려받은 포인트입니다.",
  lived_month: "살아남은 기간입니다. (1개월 단위)",
  max_belong: "가장 오래 임관했던 국가의 연도입니다.",
  max_domestic_critical: "성공한 내정 중 최대 연속값입니다.",
  snipe_combat: "유리한 상성을 가지고 전투했습니다.",
  combat: "전투 횟수입니다.",
  sabotage: "계략 성공 횟수입니다.",
  unifier:
    "천통에 기여한 포인트입니다. <br>각 국의 군주, 천통 수뇌, 천통 군주가 받습니다.",
  dex: "총 숙련도합입니다.",
  tournament: "토너먼트 입상 포인트입니다.",
  betting: "성공적인 베팅을 했습니다. <br>수익율과 베팅 성공 횟수를 따릅니다.",
};

export default defineComponent({
  name: "InheritPoint",
  data() {
    return {
      title: "유산 관리",
      pointHelpText,
      inheritanceKey,
    };
  },
  components: {
    TopBackBar,
  },
});
</script>


<style>
</style>