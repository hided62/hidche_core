<template>
  <nav class="gameBottomBar navbar-expand navbar-dark bg-dark d-sm-block d-lg-none p-0">
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mx-auto">
        <li class="nav-item dropup">
          <div
            id="navbarGlobal"
            class="dropdown-toggle text-white btn btn-sammo-base2"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            외부 메뉴
          </div>
          <GlobalMenuBar
            id="navbarGlobalItems"
            aria-labelledby="navbarGlobal"
            :globalInfo="globalInfo"
            :modelValue="globalMenu"
            :columns="3"
          />
        </li>
        <li class="nav-item dropup">
          <div
            id="navbarNation"
            class="dropdown-toggle btn btn-sammo-nation controlBar"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            국가 메뉴
          </div>
          <MainControlDropdown
            id="navbarNationItems"
            aria-labelledby="navbarNation"
            :showSecret="showSecret"
            :permission="frontInfo.general.permission"
            :myLevel="frontInfo.general.officerLevel"
            :nationLevel="nationInfo.level"
          />
        </li>
        <li class="nav-item dropup">
          <div
            id="navbarQuick"
            class="dropdown-toggle text-white btn btn-dark"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            빠른 이동
          </div>
          <ul id="navbarQuickItems" class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item disabled">국가 정보</a></li>
            <hr class="dropdown-divider" />
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.nationNotice')">방침</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('#reservedCommandPanel')">명령</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.nationInfo')">국가</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.generalInfo')">장수</button>
            </li>
            <li><button type="button" class="dropdown-item" @click="scrollToSelector('.cityInfo')">도시</button></li>
            <li><a class="dropdown-item disabled">동향 정보</a></li>
            <hr class="dropdown-divider" />
            <li><button type="button" class="dropdown-item" @click="scrollToSelector('.mapView')">지도</button></li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.PublicRecord')">
                동향
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.GeneralLog')">개인</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.WorldHistory')">정세</button>
            </li>
            <li><span>&nbsp;</span></li>
            <li><a class="dropdown-item disabled">메시지</a></li>
            <hr class="dropdown-divider" />
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.PublicTalk > .stickyAnchor')">전체</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.NationalTalk > .stickyAnchor')">국가</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.PrivateTalk > .stickyAnchor')">개인</button>
            </li>
            <li>
              <button type="button" class="dropdown-item" @click="scrollToSelector('.DiplomacyTalk > .stickyAnchor')">외교</button>
            </li>
            <li>
              <button type="button" class="btn btn-sammo-base2" @click="moveLobby">로비로</button>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="refreshPage btn btn-sammo-base2 text-white" role="button" @click="emit('refresh')">갱신</a>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup lang="ts">
import type { GetFrontInfoResponse, GetMenuResponse } from "@/defs/API/Global";
import { scrollToSelector } from "@/util/scrollToSelector";
import { toRefs, watch, ref, computed } from "vue";
import GlobalMenuBar from "./GlobalMenuDropdown.vue";
import MainControlDropdown from "./MainControlDropdown.vue";

//FIXME: scrollToSelector

const props = defineProps<{
  frontInfo: GetFrontInfoResponse;
  globalMenu: GetMenuResponse["menu"];
}>();

const emit = defineEmits<{
  (event: "refresh"): void;
}>();

const { frontInfo, globalMenu } = toRefs(props);

const globalInfo = ref(frontInfo.value.global);
watch(frontInfo, (frontInfo) => {
  globalInfo.value = frontInfo.global;
});

const nationInfo = ref(frontInfo.value.nation);
watch(frontInfo, (frontInfo) => {
  nationInfo.value = frontInfo.nation;
});

function moveLobby() {
  location.replace("../");
}

const showSecret = computed(() => {
  if (!frontInfo.value) {
    return false;
  }
  if (frontInfo.value.general.permission >= 1) {
    return true;
  }
  if (frontInfo.value.general.officerLevel >= 2) {
    return true;
  }
  return false;
});
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";
@import "@scss/common/base.scss";

@include media-500px {
  .gameBottomBar {
    .nav-item ul.dropdown-menu {
      max-height: calc(100vh - 50px);
      overflow-y: auto;

      li {
        font-size: 16px;
      }
    }

    .nav-item > .btn {
      text-align: center;
      width: 125px;
      font-size: 16px;
    }

    box-shadow: 0 -1px 0 $dark;
    border-left: none !important;
    border-right: none !important;
  }

  #navbarNationItems {
    columns: 3;
  }

  #navbarQuickItems {
    columns: 3;
  }
}
</style>
