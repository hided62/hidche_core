<template>
  <table style="width: 1000px; margin: auto" class="tb_layout bg0">
    <tr>
      <td style="text-align: left">
        {{title}}<br /><button
          type="button"
          class="btn btn-primary"
          @click="back"
        >돌아가기</button><br />
      </td>
    </tr>
  </table>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import "../../scss/game_bg.scss";

export default defineComponent({
  name: "TopBackBar",
  methods: {
    back(){
      if(this.type === 'normal'){
        location.href = './';
      }
      else if(this.type == 'chief'){
        location.href = 'b_chiefcenter.php';
      }
      else{
        //TODO: window.close하려면 부모창이 있어야함!
        window.close();
      }
    }
  },
  props: {
    title: {
      type: String,
      required: true,
    },
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