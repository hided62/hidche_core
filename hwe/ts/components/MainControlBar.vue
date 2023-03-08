<template>
  <div class="controlBar">
    <a href="v_board.php" :class="`commandButton btn btn-sammo-nation ${myLevel >= 1 ? '' : 'disabled'}`">회 의 실</a>
    <a
      href="v_board.php?isSecret=true"
      :class="`commandButton ${permission >= 2 ? '' : 'disabled'} btn btn-sammo-nation`"
      >기 밀 실</a
    >
    <a
      href="v_troop.php"
      :class="`commandButton ${myLevel >= 1 && nationLevel >= 1 ? '' : 'disabled'} btn btn-sammo-nation`"
      >부대 편성</a
    >
    <a href="t_diplomacy.php" :class="`commandButton ${showSecret ? '' : 'disabled'} btn btn-sammo-nation`">외 교 부</a>
    <a href="b_myBossInfo.php" :class="`commandButton ${myLevel >= 1 ? '' : 'disabled'} btn btn-sammo-nation`"
      >인 사 부</a
    >
    <a href="v_nationStratFinan.php" :class="`commandButton ${showSecret ? '' : 'disabled'} btn btn-sammo-nation`"
      >내 무 부</a
    >
    <a href="v_chiefCenter.php" :class="`commandButton ${showSecret ? '' : 'disabled'} btn btn-sammo-nation`"
      >사 령 부</a
    >
    <a href="v_NPCControl.php" :class="`commandButton ${showSecret ? '' : 'disabled'} btn btn-sammo-nation`"
      >NPC 정책</a
    >
    <a
      href="b_genList.php"
      target="_blank"
      :class="`open-window commandButton btn btn-sammo-nation ${showSecret ? '' : 'disabled'}`"
      >암 행 부</a
    >
    <a href="b_tournament.php" target="_blank" class="commandButton btn btn-sammo-nation">토 너 먼 트</a>
    <a href="b_myKingdomInfo.php" :class="`commandButton btn btn-sammo-nation ${myLevel >= 1 ? '' : 'disabled'}`"
      >세력 정보</a
    >
    <a
      href="b_myCityInfo.php"
      :class="`commandButton btn btn-sammo-nation ${myLevel >= 1 && nationLevel >= 1 ? '' : 'disabled'}`"
      >세력 도시</a
    >
    <a href="v_nationGeneral.php" :class="`commandButton btn btn-sammo-nation ${myLevel >= 1 ? '' : 'disabled'}`"
      >세력 장수</a
    >
    <a href="v_globalDiplomacy.php" class="commandButton btn btn-sammo-nation">중원 정보</a>
    <a href="b_currentCity.php" class="commandButton btn btn-sammo-nation">현재 도시</a>
    <a
      href="v_battleCenter.php"
      target="_blank"
      :class="`open-window commandButton btn btn-sammo-nation ${showSecret ? '' : 'disabled'}`"
      >감 찰 부</a
    >
    <a href="v_inheritPoint.php" class="commandButton btn btn-sammo-nation">유산 관리</a>
    <a href="b_myPage.php" class="commandButton btn btn-sammo-nation">내 정보&amp;설정</a>
    <div class="btn-group">
      <a href="v_auction.php" target="_blank" class="open-window commandButton btn btn-sammo-nation">경 매 장</a>
      <button
        type="button"
        class="btn btn-sammo-nation dropdown-toggle dropdown-toggle-split"
        data-bs-toggle="dropdown"
        aria-expanded="false"
      >
        <span class="visually-hidden">Toggle Dropdown</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a href="v_auction.php" target="_blank" class="open-window commandButton dropdown-item">금/쌀 경매장</a>
        </li>
        <li>
          <a href="v_auction.php?type=unique" target="_blank" class="open-window commandButton dropdown-item"
            >유니크 경매장</a
          >
        </li>
      </ul>
    </div>
    <a href="b_betting.php" target="_blank" class="commandButton btn btn-sammo-nation">베 팅 장</a>
  </div>
</template>
<script setup lang="ts">
import { toRefs } from "vue";

const props = defineProps<{
  showSecret: boolean;
  permission: number;
  myLevel: number;
  nationLevel: number;
}>();

const { showSecret, permission, myLevel, nationLevel } = toRefs(props);
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

@include media-500px {
  .controlBar {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.1rem;
  }
}

@include media-1000px {
  .controlBar {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.1rem;
  }
}
</style>
