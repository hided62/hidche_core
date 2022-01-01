<template>
  <div class="bg0" style="padding-top:20px;">
    <button type="button" class="btn btn-sammo-base2 back_btn" @click="back">
      돌아가기
    </button>
    <div></div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import "@scss/game_bg.scss";

export default defineComponent({
  methods: {
    back(){
      if(this.type === 'normal'){
        location.href = './';
      }
      else if(this.type == 'chief'){
        location.href = 'v_chiefCenter.php';
      }
      else{
        //TODO: window.close하려면 부모창이 있어야함!
        window.close();
      }
    }
  },
  props: {
    type: {
      type: String as PropType<"normal"|"chief"|"close">,
      default: "normal",
      required: false,
    },
  },
});
</script>


<style>
</style>