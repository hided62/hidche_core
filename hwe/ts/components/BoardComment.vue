<template>
  <div class="row gx-0 comment s-border-b">
    <div class="authorName center d-grid">
      <div class="align-self-center">
        {{ comment.author }}
      </div>
    </div>
    <div class="col text">
      {{ comment.text }}
    </div>
    <div class="col-2 col-md-1 date center d-grid">
      <div class="align-self-center">
        {{ comment.date.slice(5, 16) }}
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import type { BoardCommentItem } from "@/PageBoard.vue";
import { defineComponent, type PropType } from "vue";

export default defineComponent({
  name: "BoardComment",
  props: {
    comment: {
      type: Object as PropType<BoardCommentItem>,
      required: true,
    },
  },
});
</script>
