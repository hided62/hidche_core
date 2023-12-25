<template>
  <ul class="dropdown-menu dromdown-menu-start">
    <li><a href="v_board.php" :class="`dropdown-item ${myLevel >= 1 ? '' : 'disabled'}`">회의실</a></li>
    <li>
      <a href="v_board.php?isSecret=true" :class="`dropdown-item ${permission >= 2 ? '' : 'disabled'} `">기밀실</a>
    </li>
    <li>
      <a href="v_troop.php" :class="`dropdown-item ${myLevel >= 1 && nationLevel >= 1 ? '' : 'disabled'} `">부대 편성</a>
    </li>
    <li><a href="t_diplomacy.php" :class="`dropdown-item ${showSecret ? '' : 'disabled'} `">외교부</a></li>
    <li><a href="b_myBossInfo.php" :class="`dropdown-item ${myLevel >= 1 ? '' : 'disabled'} `">인사부</a></li>
    <li><a href="v_nationStratFinan.php" :class="`dropdown-item ${showSecret ? '' : 'disabled'} `">내무부</a></li>
    <li><a href="v_chiefCenter.php" :class="`dropdown-item ${showSecret ? '' : 'disabled'} `">사령부</a></li>
    <li><a href="v_NPCControl.php" :class="`dropdown-item ${showSecret ? '' : 'disabled'} `">NPC 정책</a></li>
    <li><a href="v_userAction.php" class="dropdown-item">개인 전략</a></li>
    <li>
      <a href="b_genList.php" target="_blank" :class="`dropdown-item open-window ${showSecret ? '' : 'disabled'}`"
        >암행부</a
      >
    </li>
    <li><a href="b_tournament.php" class="dropdown-item" target="_blank">토너먼트</a></li>
    <li><a href="b_myKingdomInfo.php" :class="`dropdown-item ${myLevel >= 1 ? '' : 'disabled'}`">세력 정보</a></li>
    <li>
      <a href="b_myCityInfo.php" :class="`dropdown-item ${myLevel >= 1 && nationLevel >= 1 ? '' : 'disabled'}`"
        >세력도시</a
      >
    </li>
    <li><a href="v_nationGeneral.php" :class="`dropdown-item ${myLevel >= 1 ? '' : 'disabled'}`">세력 장수</a></li>
    <li><a href="v_globalDiplomacy.php" class="dropdown-item">중원 정보</a></li>
    <li><a href="b_currentCity.php" class="dropdown-item">현재 도시</a></li>
    <li>
      <a href="v_battleCenter.php" target="_blank" :class="`dropdown-item open-window ${showSecret ? '' : 'disabled'}`"
        >감찰부</a
      >
    </li>
    <li><a href="v_inheritPoint.php" class="dropdown-item">유산 관리</a></li>
    <li><a href="b_myPage.php" class="dropdown-item">내 정보&amp;설정</a></li>
    <li style="orphans: 2">
      <a href="v_auction.php" target="_blank" class="dropdown-item open-window">금/쌀 경매장</a>
    </li>
    <li><a href="v_auction.php?type=unique" target="_blank" class="dropdown-item open-window">유니크 경매장</a></li>
    <li><a href="b_betting.php" class="dropdown-item" target="_blank">베팅장</a></li>
  </ul>
</template>
<script setup lang="ts">
import { computed, toRefs } from "vue";

const props = defineProps<{
  showSecret: boolean;
  permission: number;
  myLevel: number;
  nationLevel: number;
  columns?: number;
}>();

const columns = computed(() => {
  return props.columns ?? 4;
});
const { showSecret, permission, myLevel, nationLevel } = toRefs(props);
</script>

<style lang="scss" scoped>
.subItem {
  padding-left: 1.5rem;
}

.dropdown-menu {
  columns: v-bind(columns);
}
</style>
