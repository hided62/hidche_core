<template>
  <TopBackBar :title="commandName" />
  <div class="bg0">
    <div>
      병사를 모집합니다.
      <template v-if="commandName == '징병'">
        훈련과 사기치는 낮지만 가격이 저렴합니다.<br>
      </template>
      <template v-else-if="commandName == '모병'">
        훈련과 사기치는 높지만 자금이 많이 듭니다.<br>
      </template>
      가능한 수보다 많게 입력하면 가능한 최대 병사를 모집합니다.<br>
      이미 병사가 있는 경우 추가 {{ commandName }}되며, 병종이 다를경우는 기존의
      병사는 소집해제됩니다. <br>
      현재 {{ commandName }} 가능한 병종은
      <span style="color: green">녹색</span>으로 표시되며, 현재
      {{ commandName }} 가능한 특수병종은
      <span style="color: limegreen">초록색</span>으로 표시됩니다.
    </div>
    <div
      ref="defaultTarget"
      class="crewTypeList"
    >
      <div class="listFront">
        <div class="row gx-0 bg0">
          <div class="col-12 col-md-12 d-flex align-items-center">
            <div
              v-if="commandName == '모병'"
              class="text-center w-100"
            >
              모병은 가격 2배의 자금이 소요됩니다.<br>
            </div>
          </div>
        </div>
        <div class="row text-center bg2 gx-0">
          <div class="col-4 col-md-2">
            현재 기술력 : {{ techLevel }}등급
          </div>
          <div class="col-4 col-md-2">
            현재 통솔 :
            <span
              :style="{
                color: leadership < fullLeadership ? 'red' : undefined,
              }"
            >{{ leadership }}</span>
          </div>
          <div class="col-4 col-md-2">
            최대 통솔 : {{ fullLeadership }}
          </div>
          <div class="col-4 col-md-2">
            현재 병종 : {{ crewTypeMap?.get(currentCrewType)?.name }}
          </div>
          <div class="col-4 col-md-2">
            현재 병사 : {{ crew.toLocaleString() }}
          </div>
          <div class="col-4 col-md-2">
            현재 자금 : {{ gold.toLocaleString() }}
          </div>
        </div>
        <div class="miniCrewPanel center bg0">
          <div
            class="crewTypeImg"
            :style="{
              background: '#222222 no-repeat center',
              backgroundImage: `url('${destCrewType.img}')`,
              backgroundSize: '64px',
              outline: 'solid 1px gray',
              height: '64px',
            }"
          />
          <div
            :style="{
              backgroundColor:
                (destCrewType.notAvailable
                  ? 'red'
                  : destCrewType.reqTech == 0
                    ? 'green'
                    : 'limegreen') + ' !important',
              height: '100%',
            }"
            class="d-grid"
          >
            <div style="margin: auto">
              {{ destCrewType.name }}
            </div>
          </div>
          <div />
          <div class="crewTypePanel">
            <b-button-group>
              <b-button
                class="py-1"
                variant="dark"
                @click="beHalf"
              >
                절반
              </b-button><b-button
                class="py-1"
                variant="dark"
                @click="beFilled"
              >
                채우기
              </b-button><b-button
                class="py-1"
                variant="dark"
                @click="beFull"
              >
                가득
              </b-button>
            </b-button-group>
            <div class="row">
              <div class="col mx-2">
                <div class="input-group my-0">
                  <span class="input-group-text py-1">병력</span>
                  <input
                    v-model.number="amount"
                    type="number"
                    class="form-control py-1 f_tnum px-0 text-end"
                    min="1"
                  >
                  <span class="input-group-text py-1 f_tnum">00명</span>
                  <span
                    class="input-group-text py-1 f_tnum"
                    style="
                      text-align: right;
                      min-width: 10ch;
                      color: #303030;
                      background-color: #ddd;
                    "
                  ><div style="margin-left: auto">
                    {{
                      Math.ceil(
                        amount * destCrewType.baseCost * goldCoeff
                      ).toLocaleString()
                    }}금
                  </div></span>
                </div>
              </div>
            </div>
          </div>
          <div />
          <b-button
            variant="primary"
            @click="submit"
          >
            {{
              commandName
            }}
          </b-button>
        </div>
        <div class="listHeader crewTypeSubGrid text-center bg1">
          <div class="crewTypeImg">
            사진
          </div>
          <div class="crewTypeName">
            병종
          </div>
          <div>공격</div>
          <div>방어</div>
          <div>기동</div>
          <div>회피</div>
          <div>가격</div>
          <div>군량</div>
          <div class="crewTypePanel">
            병사 수
          </div>
          <div class="crewTypeBtn">
            행동
          </div>
          <div class="crewTypeInfo">
            특징
          </div>
        </div>
      </div>
      <div class="listMain">
        <template
          v-for="armCrewType in armCrewTypes"
          :key="armCrewType.armType"
        >
          <div class="s-border-b row gx-0">
            <div
              class="col-7 col-md-10 align-self-center px-3"
              style="font-size: 1.3em"
            >
              {{ armCrewType.armName }} 계열
            </div>
            <div class="col-5 col-md-2 d-grid">
              <b-button
                :variant="
                  showNotAvailable.get(armCrewType.armType) ? 'warning' : 'dark'
                "
                :pressed="showNotAvailable.get(armCrewType.armType)"
                class="btn-sm"
                @click="toggleShowNotAvailable(armCrewType.armType)"
              >
                {{
                  showNotAvailable.get(armCrewType.armType)
                    ? "선택 할 수 있는 병종만 보기"
                    : "선택 할 수 없는 병종도 보기"
                }}
              </b-button>
            </div>
          </div>
          <template
            v-for="crewType in armCrewType.values"
            :key="crewType.id"
          >
            <CrewTypeItem
              v-if="
                showNotAvailable.get(armCrewType.armType) ||
                  !crewType.notAvailable
              "
              :crewType="crewType"
              :leadership="fullLeadership"
              :commandName="commandName"
              :currentCrewType="currentCrewType"
              :crew="crew"
              :goldCoeff="goldCoeff"
              @submitOutput="trySubmit"
              @click="
                destCrewType = unwrap(crewTypeMap.get(crewType.id));
                beFilled();
              "
            />
          </template>
        </template>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
declare const staticValues: {
  commandName: string;
};

declare const procRes: {
  relYear: number;
  year: number;
  tech: number;
  techLevel: number;
  startYear: number;
  goldCoeff: number;
  leadership: number;
  fullLeadership: number;
  armCrewTypes: procArmTypeItem[];
  currentCrewType: number;
  crew: number;
  gold: number;
};
</script>

<script lang="ts" setup>
import CrewTypeItem from "@/processing/CrewTypeItem.vue";
import { ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import type { procArmTypeItem, procCrewTypeItem } from "../processingRes";

const commandName = staticValues.commandName;
const {
  techLevel,
  goldCoeff,
  leadership,
  fullLeadership,
  armCrewTypes,
  currentCrewType,
  crew,
  gold,
} = procRes;

const amount = ref(procRes.fullLeadership - Math.floor(procRes.crew / 100));

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          amount: amount.value * 100,
          crewType: destCrewType.value.id,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    const showNotAvailable = ref(new Map<number, boolean>());

    const crewTypeMap = new Map<number, procCrewTypeItem>();
    for (const armType of procRes.armCrewTypes) {
      showNotAvailable.value.set(armType.armType, false);
      for (const crewType of armType.values) {
        crewTypeMap.set(crewType.id, crewType);
      }
    }

    const destCrewType = ref(unwrap(crewTypeMap.get(procRes.currentCrewType)));

    function beHalf() {
      amount.value = Math.ceil(procRes.fullLeadership * 0.5);
    }

    function beFilled() {
      if (destCrewType.value.id == procRes.currentCrewType) {
        amount.value = Math.max(
          1,
          procRes.fullLeadership - Math.floor(procRes.crew / 100)
        );
      } else {
        amount.value = procRes.fullLeadership;
      }
    }

    function beFull() {
      amount.value = Math.floor(procRes.fullLeadership * 1.2);
    }

    function trySubmit(e: Event, inAmount: number, inCrewType: number) {
      e.preventDefault();
      amount.value = inAmount;
      destCrewType.value = unwrap(crewTypeMap.get(inCrewType));
      void submit(e);
    }

    function toggleShowNotAvailable(armType: number) {
      showNotAvailable.value.set(
        armType,
        !(showNotAvailable.value.get(armType) ?? 0)
      );
    }
</script>

<style lang="scss">
@import "@scss/common/break_500px.scss";

.crewTypeSubGrid {
  display: grid;
  align-items: center;
}
.crewTypeItem .crewTypeImg {
  height: 64px;
}

.crewTypeInfo {
  padding-left: 0.5em;
  padding-right: 0.5em;
}

@include media-1000px {
  .crewTypeSubGrid {
    grid-template-columns: 64px 1.5fr 1fr 1fr 1fr 1fr 1fr 1fr 250px 1.5fr 270px;
  }

  .miniCrewPanel {
    display: none;
  }

  .only500pxMode {
    display: none;
  }
}

@include media-500px {
  .only1000pxMode {
    display: none !important;
  }

  .listFront {
    position: sticky;
    top: 0px;
  }
  .crewTypeSubGrid {
    grid-template-columns: 64px 1.5fr 1fr 1fr 1fr 270px;
    grid-template-rows: 1fr 1fr;
  }
  .crewTypeImg {
    grid-column: 1 / 2;
    grid-row: 1 / 3;
  }
  .crewTypeName {
    grid-row: 1/ 3;
  }

  .crewTypeInfo {
    grid-column: -2 / -1;
    grid-row: 1 / 3;
  }

  .crewTypePanel {
    display: none;
  }
  .crewTypeBtn {
    display: none;
  }
  .crewTypeBtn > button {
    display: none;
  }

  .miniCrewPanel .crewTypePanel {
    display: block;
  }

  .miniCrewPanel {
    display: grid;
    grid-template-columns: 64px 1.5fr 0.5fr 270px 0.5fr 2fr;
    grid-template-rows: 64px;
    align-items: center;
  }
}
</style>
<style scoped>
</style>