<template>
  <TopBackBar title="장수 생성" />

  <div v-if="gameConstStore && args" id="container" class="bg0">
    <div class="nation-list">
      <div class="nation-header nation-row bg1 center">
        <div>국가명</div>
        <div>임관권유문</div>
        <div class="display-toggle d-grid">
          <b-button
            v-model="displayTable"
            :pressed="displayTable"
            :variant="displayTable ? 'info' : 'secondary'"
            @click="displayTable = !displayTable"
          >
            {{ displayTable ? "숨기기" : "보이기" }}
          </b-button>
        </div>
        <div class="zoom-toggle d-grid">
          <b-button
            v-model="toggleZoom"
            :pressed="toggleZoom"
            :variant="toggleZoom ? 'info' : 'secondary'"
            :disabled="!displayTable"
            @click="toggleZoom = !toggleZoom"
          >
            {{ toggleZoom ? "작게 보기" : "크게 보기" }}
          </b-button>
        </div>
      </div>
      <template v-if="displayTable">
        <div
          v-for="nation in nationList"
          :key="nation.nation"
          :class="['nation-row', 's-border-b', toggleZoom ? 'on-zoom' : 'on-fit']"
        >
          <div
            :style="{
              backgroundColor: nation.color,
              color: isBrightColor(nation.color) ? 'black' : 'white',
              fontSize: '1.3em',
            }"
            class="d-grid"
          >
            <div class="align-self-center center">
              {{ nation.name }}
            </div>
          </div>
          <div class="nation-scout-plate align-self-center">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div class="nation-scout-msg" v-html="nation.scoutmsg ?? '-'" />
          </div>
        </div>
      </template>
    </div>

    <!-- 국가 설명 -->
    <div class="row bg1">
      <div class="col center">장수 생성</div>
    </div>
    <div class="forms">
      <div class="row">
        <div class="col col-md-4 col-3 a-right align-self-center">장수명</div>
        <div class="col col-md-3 col-9 align-self-center">
          <input v-model="args.name" class="form-control" />
        </div>
        <div class="col col-md-1 col-3 a-right align-self-center">전콘 사용</div>
        <div class="col col-md-4 col-9 align-self-center">
          <img style="height: 64px; width: 64px" :src="iconPath" />
          <label> <input v-model="args.pic" type="checkbox" /> 사용 </label>
        </div>
        <div class="col col-md-4 col-3 align-self-center a-right">성격</div>

        <div class="col col-md-8 col-9 align-self-center">
          <div class="row">
            <div class="col col-md-3 col-4 align-self-center">
              <select v-model="args.character" class="form-select form-inline" style="max-width: 20ch">
                <option v-for="(personalityObj, key) in availablePersonality" :key="key" :value="key">
                  {{ personalityObj.name }}
                </option>
              </select>
            </div>
            <div class="col col-md-9 col-8 align-self-center">
              <small class="text-muted">
                {{ availablePersonality[args.character].info }}
              </small>
            </div>
          </div>
        </div>
      </div>
      <!--<div class="row">
      <div class="col">
        계정관리에서 자신만을 표현할 수 있는 아이콘을 업로드 해보세요!
      </div>
      </div>-->
      <div class="row" style="margin-top: 1em">
        <div class="col col-md-4 col-3 a-right align-self-center">
          능력치
          <br />
          <small class="text-muted">통/무/지</small>
        </div>
        <div class="col col-md-2 col-3 align-self-center">
          <input v-model.number="args.leadership" type="number" class="form-control" />
        </div>
        <div class="col col-md-2 col-3 align-self-center">
          <input v-model.number="args.strength" type="number" class="form-control" />
        </div>
        <div class="col col-md-2 col-3 align-self-center">
          <input v-model.number="args.intel" type="number" class="form-control" />
        </div>
      </div>
      <div class="row" style="margin-top: 1em">
        <div class="col col-md-4 col-3 a-right align-self-center">능력치 조절</div>
        <div class="col col-md-8 col-9">
          <b-button variant="secondary" class="stat-btn" @click="randStatRandom"> 랜덤형 </b-button>
          <b-button variant="secondary" class="stat-btn" @click="randStatLeadPow"> 통솔무력형 </b-button>
          <b-button variant="secondary" class="stat-btn" @click="randStatLeadInt"> 통솔지력형 </b-button>
          <b-button variant="secondary" class="stat-btn" @click="randStatPowInt"> 무력지력형 </b-button>
        </div>
      </div>
    </div>
    <div class="row" style="border-top: solid 1px #aaa; margin-top: 0.5em">
      <div class="col a-center" style="color: orange">
        모든 능력치는 ( {{ stats.min }} &lt;= 능력치 &lt;= {{ stats.max }} ) 사이로 잡으셔야 합니다. <br />그 외의
        능력치는 가입되지 않습니다.
      </div>
    </div>
    <div class="row">
      <div class="col a-center">
        능력치의 총합은 {{ stats.total }} 입니다. 가입후 {{ stats.bonusMin }} ~ {{ stats.bonusMax }} 의 능력치 보너스를
        받게 됩니다. <br />임의의 도시에서 재야로 시작하며 건국과 임관은 게임 내에서 실행합니다.
      </div>
    </div>

    <div class="row bg1">
      <div class="col col-md-11 col-9 center align-self-center">유산 포인트 사용</div>
      <div class="col col-md-1 col-3">
        <label>
          <input v-model="displayInherit" type="checkbox" />
          {{ displayInherit ? "숨기기" : "보이기" }}
        </label>
      </div>
    </div>
    <div v-if="displayInherit" class="inherit-block">
      <div class="row">
        <div class="col">
          <NumberInputWithInfo v-model.number="inheritTotalPoint" title="보유한 유산 포인트" :readonly="true" />
        </div>
        <div class="col">
          <NumberInputWithInfo v-model.number="inheritRequiredPoint" title="필요 유산 포인트" :readonly="true" />
        </div>
      </div>
      <hr />
      <div class="row">
        <div class="col col-md-6 col-sm-6 col-12 p-2 align-self-center">
          <div class="row">
            <div class="col col-6 a-right align-self-center">천재로 생성</div>
            <div class="col col-6 align-self-center">
              <select v-model="args.inheritSpecial" class="form-select form-inline" style="max-width: 20ch">
                <option :value="undefined">사용안함</option>
                <option v-for="(inheritSpecial, key) in availableInheritSpecial" :key="key" :value="key">
                  {{ inheritSpecial.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="col align-self-center">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <small class="text-muted" v-html="availableInheritSpecial[args.inheritSpecial ?? '']?.info" />
          </div>
        </div>

        <div class="col col-md-6 col-sm-6 col-12 p-2 align-self-center">
          <div class="row">
            <div class="col col-6 a-right align-self-center">도시</div>
            <div class="col col-6 align-self-center">
              <select v-model.number="inheritCity" class="form-select form-inline" style="max-width: 20ch">
                <option :value="undefined">사용안함</option>
                <option v-for="city in availableInheritCity" :key="city.id" :value="city.id">
                  {{ `[${city.region}] ${city.name}` }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="col col-md-6 col-sm-6 col-12 p-2 align-self-center">
          <div class="a-center">
            <label> <input v-model="inheritTurnTimeSet" type="checkbox" />턴 시간 고정 </label>
          </div>
          <div class="row turn_time_pad">
            <div class="col col-md-4 offset-md-3 col-4 offset-3">
              <NumberInputWithInfo
                v-model="inheritTurnTimeMinute"
                :readonly="!inheritTurnTimeSet"
                :min="0"
                :max="1 - turnterm"
                :right="true"
                title="분"
              />
            </div>
            <div class="col col-md-4 col-4">
              <NumberInputWithInfo
                v-model="inheritTurnTimeSecond"
                :readonly="!inheritTurnTimeSet"
                :min="0"
                :max="60"
                :right="true"
                title="초"
              />
            </div>
          </div>
        </div>

        <div class="col col-md-6 col-12 p-2">
          <div class="a-center">추가 능력치 고정</div>
          <div class="row">
            <div class="col">
              <NumberInputWithInfo
                v-model="(args.inheritBonusStat ?? [0, 0, 0])[0]"
                :max="stats.bonusMax"
                title="통솔"
              />
            </div>
            <div class="col">
              <NumberInputWithInfo
                v-model="(args.inheritBonusStat ?? [0, 0, 0])[1]"
                :max="stats.bonusMax"
                title="무력"
              />
            </div>
            <div class="col">
              <NumberInputWithInfo
                v-model="(args.inheritBonusStat ?? [0, 0, 0])[2]"
                :max="stats.bonusMax"
                title="지력"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row" style="border-top: solid 1px #aaa">
      <div class="col a-center" style="margin: 0.5em">
        <b-button color="primary" @click="submitForm"> 장수 생성 </b-button>&nbsp;
        <b-button color="secondary" @click="resetArgs"> 다시 입력 </b-button>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
declare const staticValues: {
  nationList: {
    nation: number;
    name: string;
    color: string;
    scout: number;
    scoutmsg?: string;
  }[];
  turnterm: number;

  member: {
    name: string;
    grade: number;
    picture: string;
    imgsvr: 0 | 1;
  };

  serverID: string;
  inheritTotalPoint: number;
};
</script>
<script lang="ts" setup>
import "@scss/game_bg.scss";

import TopBackBar from "@/components/TopBackBar.vue";
import { getIconPath } from "@util/getIconPath";
import { isBrightColor } from "@util/isBrightColor";
import { abilityLeadint, abilityLeadpow, abilityPowint, abilityRand } from "@util/generalStats";
import { shuffle, sum } from "lodash";
import NumberInputWithInfo from "@/components/NumberInputWithInfo.vue";
import { SammoAPI } from "./SammoAPI";
import type { JoinArgs } from "./defs/API/General";
import { GameConstStore, getGameConstStore } from "@/GameConstStore";
import { computed, onMounted, ref, watch } from "vue";
import type { CityID, GameIActionInfo } from "./defs/GameObj";
import { unwrap } from "@/util/unwrap";

const gameConstStore = ref<GameConstStore>();

const { serverID, member, turnterm } = staticValues;

const nationList = ref(shuffle(staticValues.nationList));
const args = ref<JoinArgs>();

const stats = ref({
  min: 50,
  max: 50,
  total: 50,
  bonusMin: 1,
  bonusMax: 5,
});

const inheritTotalPoint = ref(staticValues.inheritTotalPoint);
const availableInheritCity = ref<{ id: CityID; name: string; region: string }[]>([]);
const availableInheritSpecial = ref<Record<string, GameIActionInfo>>({});
const availablePersonality = ref<Record<string, GameIActionInfo>>({});

watch(gameConstStore, (gameConst) => {
  if (gameConst === undefined) {
    console.error();
    return;
  }

  availableInheritCity.value = (() => {
    const cityList: { id: CityID; name: string; region: string }[] = [];
    for (const city of Object.values(gameConst.cityConst)) {
      cityList.push({
        id: city.id,
        name: city.name,
        region: gameConst.cityConstMap.region[city.region] as string,
      });
    }
    cityList.sort((lhs, rhs) => {
      const rC = lhs.region.localeCompare(rhs.region);
      if (rC != 0) return rC;
      return lhs.name.localeCompare(rhs.name);
    });
    return cityList;
  })();

  availableInheritSpecial.value = (() => {
    const specialList: typeof availableInheritSpecial.value = {};
    for (const specialWarID of Object.values(gameConst.gameConst.availableSpecialWar)) {
      specialList[specialWarID] = unwrap(gameConst.iActionInfo.specialWar[specialWarID]);
    }
    return specialList;
  })();

  availablePersonality.value = (() => {
    const personalityList: typeof availablePersonality.value = {};
    for (const personalityID of Object.values(gameConst.gameConst.availablePersonality)) {
      personalityList[personalityID] = unwrap(gameConst.iActionInfo.personality[personalityID]);
    }
    personalityList["Random"] = {
      value: "Random",
      name: "???",
      info: "무작위 성격을 선택합니다.",
    };
    return personalityList;
  })();

  stats.value = {
    min: gameConst.gameConst.defaultStatMin,
    max: gameConst.gameConst.defaultStatMax,
    total: gameConst.gameConst.defaultStatTotal,
    bonusMin: gameConst.gameConst.defaultStatMin,
    bonusMax: gameConst.gameConst.defaultStatMax,
  };

  args.value = {
    name: member.name,
    leadership: stats.value.total - 2 * Math.floor(stats.value.total / 3),
    strength: Math.floor(stats.value.total / 3),
    intel: Math.floor(stats.value.total / 3),
    pic: true,
    character: "Random",

    inheritCity: undefined,
    inheritBonusStat: [0, 0, 0],
    inheritSpecial: undefined,
    inheritTurntime: undefined,
  };
});

onMounted(async () => {
  gameConstStore.value = await getGameConstStore();
});

function randStatRandom() {
  if (args.value === undefined) throw "nyc";
  [args.value.leadership, args.value.strength, args.value.intel] = abilityRand(stats.value);
}
function randStatLeadPow() {
  if (args.value === undefined) throw "nyc";
  [args.value.leadership, args.value.strength, args.value.intel] = abilityLeadpow(stats.value);
}
function randStatLeadInt() {
  if (args.value === undefined) throw "nyc";
  [args.value.leadership, args.value.strength, args.value.intel] = abilityLeadint(stats.value);
}
function randStatPowInt() {
  if (args.value === undefined) throw "nyc";
  [args.value.leadership, args.value.strength, args.value.intel] = abilityPowint(stats.value);
}
function resetArgs() {
  if (!args.value) throw "nyc";
  const defaultStatTotal = stats.value.total;
  args.value.name = member.name;
  args.value.pic = true;
  args.value.character = "Random";
  args.value.leadership = defaultStatTotal - 2 * Math.floor(defaultStatTotal / 3);
  args.value.strength = Math.floor(defaultStatTotal / 3);
  args.value.intel = Math.floor(defaultStatTotal / 3);
}

async function submitForm() {
  if (!args.value) throw "nyc";
  //검증은 언제 되어야 하는가?
  const subbmitArgs = args.value;
  const totalStat = subbmitArgs.leadership + subbmitArgs.strength + subbmitArgs.intel;
  const defaultStatTotal = stats.value.total;

  if (totalStat < defaultStatTotal) {
    if (
      !confirm(
        `설정한 능력치가 ${totalStat}으로, 실제 최대치인 ${defaultStatTotal}보다 적습니다.\r\n그래도 진행할까요?`
      )
    ) {
      return false;
    }
  }
  try {
    await SammoAPI.General.Join(subbmitArgs);
  } catch (e) {
    console.error(e);
    alert(`실패했습니다: ${e}`);
    return;
  }

  alert("정상적으로 생성되었습니다. \n위키와 팁/강좌 게시판을 꼭 읽어보세요!");
  location.href = "./";
}

const iconPath = computed(() => {
  if (!args.value) throw "nyc";

  if (args.value.pic) {
    return getIconPath(member.imgsvr, member.picture);
  }
  return getIconPath(0, "default.jpg");
});

const inheritRequiredPoint = computed(() => {
  if (!args.value || !gameConstStore.value) throw "nyc";

  let inheritRequiredPoint = 0;
  const gameConst = gameConstStore.value.gameConst;

  if (args.value.inheritCity !== undefined) {
    inheritRequiredPoint += gameConst.inheritBornCityPoint;
  }
  if (args.value.inheritSpecial !== undefined) {
    inheritRequiredPoint += gameConst.inheritBornSpecialPoint;
  }
  if (args.value.inheritTurntime !== undefined) {
    inheritRequiredPoint += gameConst.inheritBornTurntimePoint;
  }
  if (args.value.inheritBonusStat !== undefined && sum(args.value.inheritBonusStat) != 0) {
    inheritRequiredPoint += gameConst.inheritBornStatPoint;
  }
  return inheritRequiredPoint;
});

const toggleZoom = ref(true);

const displayTable = ref<boolean>(JSON.parse(localStorage.getItem(`conf.${serverID}.join.displayTable`) ?? "true"));
watch(displayTable, (newValue: boolean) => {
  localStorage.setItem(`conf.${serverID}.join.displayTable`, JSON.stringify(newValue));
});

const displayInherit = ref<boolean>(JSON.parse(localStorage.getItem(`conf.${serverID}.join.displayInherit`) ?? "true"));
watch(displayInherit, (newValue: boolean) => {
  localStorage.setItem(`conf.${serverID}.join.displayInherit`, JSON.stringify(newValue));
});

const inheritCity = ref<number>();
watch(inheritCity, (newValue: undefined | number) => {
  if (!args.value) throw "nyc";

  if (!newValue) {
    args.value.inheritCity = undefined;
    return;
  }
  args.value.inheritCity = inheritCity.value;
});

const inheritTurnTimeSet = ref(false);
watch(inheritTurnTimeSet, (newValue: boolean) => {
  if (!args.value) throw "nyc";

  if (!newValue) {
    args.value.inheritTurntime = undefined;
    return;
  }
  args.value.inheritTurntime = inheritTurnTimeMinute.value * 60 + inheritTurnTimeSecond.value;
});

watch(
  [inheritCity, inheritTurnTimeSet],
  ([newInheritCity, newInheritTurnTimeSet], [oldInheritCity, oldInheritTurnTimeSet]) => {
    if (newInheritCity === undefined || newInheritTurnTimeSet === false) {
      return;
    }
    alert("도시와 턴 시간을 동시에 설정할 수 없습니다.");

    if (newInheritCity !== oldInheritCity) {
      inheritCity.value = undefined;
    }
    if (newInheritTurnTimeSet !== oldInheritTurnTimeSet) {
      inheritTurnTimeSet.value = false;
    }
  },
  { immediate: true }
);

const inheritTurnTimeMinute = ref(0);
watch(inheritTurnTimeMinute, (newValue: number) => {
  if (!args.value) throw "nyc";
  if (!inheritTurnTimeSet.value) {
    args.value.inheritTurntime = undefined;
    return;
  }
  args.value.inheritTurntime = newValue * 60 + inheritTurnTimeSecond.value;
});

const inheritTurnTimeSecond = ref(0);
watch(inheritTurnTimeSecond, (newValue: number) => {
  if (!args.value) throw "nyc";

  if (!inheritTurnTimeSet.value) {
    args.value.inheritTurntime = undefined;
    return;
  }
  args.value.inheritTurntime = inheritTurnTimeMinute.value * 60 + newValue;
});
</script>
<style lang="scss">
@import "@scss/common/base.scss";
@import "@scss/editor_component.scss";

#container {
  width: 100%;
  max-width: 1000px;
  margin: auto;
  border: solid 1px #888888;
  overflow: hidden;
}

.forms {
  padding: 0 6px;
}

.stat-btn {
  width: 8em;
  margin-right: 1px;
  margin-bottom: 1px;
}

.col-form-label {
  text-align: right;
}

.turn_time_pad .col-form-label {
  text-align: left;
}
</style>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

@include media-1000px {
  .nation-list .nation-row {
    display: grid;
    grid-template-columns: 130px 870px;
  }

  .zoom-toggle {
    display: none;
  }

  .zoom-toggle > * {
    display: none;
  }
}

@include media-500px {
  .nation-list .nation-row.nation-header {
    display: grid;
    grid-template-columns: 3fr 1fr 1fr;
    grid-template-rows: 1fr 1fr;

    .display-toggle {
      grid-column: 2/3;
      grid-row: 1/3;
    }
    .zoom-toggle {
      grid-column: 3/4;
      grid-row: 1/3;
    }
  }

  .nation-list .nation-row {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: 1fr minmax(1fr, calc(200px * 500 / 870));
  }

  .on-fit {
    .nation-scout-plate {
      max-height: calc(200px * 500 / 870);
      overflow: hidden;
    }

    .nation-scout-msg {
      width: 870px;
      transform-origin: 0px 0px;
      transform: scale(calc(500 / 870));
    }
  }

  .on-zoom {
    .nation-scout-plate {
      max-height: 200px;
      overflow-y: hidden;
      overflow-x: auto;
    }

    .nation-scout-msg {
      max-width: 870px;
    }
  }
}
</style>
