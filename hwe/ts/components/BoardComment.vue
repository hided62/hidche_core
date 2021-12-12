<template>
  <tr class="comment">
    <th class="author">{{ comment.author }}</th>
    <td class="text">{{ comment.text }}</td>
    <td class="date">{{ comment.date }}</td>
    <!-- 삭제도 나중에 추가? -->
  </tr>
</template>
<script lang="ts">
import { BoardCommentItem } from "@/PageBoard.vue";
import { defineComponent, PropType } from "vue";

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
