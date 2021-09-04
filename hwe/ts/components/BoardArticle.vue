<template>
  <table class="articleFrame bg0">
    <thead>
      <tr class="bg1">
        <th class="authorName"></th>
        <th class="articleTitle"></th>
        <th class="date"></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><img class="authorIcon generalIcon" width="64" height="64" /></td>
        <td class="text" colspan="2"></td>
      </tr>
    </tbody>
    <tbody class="commentList">
      <board-comment
        v-for="comment in article.comment"
        :key="comment.no"
        :comment="comment"
      />
    </tbody>
    <tfoot>
      <tr>
        <td class="bg2 inputCommentHeader">댓글 달기</td>
        <td>
          <input
            class="commentText"
            type="text"
            maxlength="250"
            placeholder="새 댓글 내용"
            :value="newCommentText"
          />
        </td>
        <td><button type="button" class="submitComment">등록</button></td>
      </tr>
    </tfoot>
  </table>
</template>
<script lang="ts">
import { BoardArticleItem } from "board.vue";
import BoardComment from "./BoardComment.vue";
import { defineComponent, PropType } from "vue";

export default defineComponent({
  name: "BoardArticle",
  components: {
    BoardComment,
  },
  data() {
    return {
      newCommentText: "",
    };
  },
  props: {
    article: {
      type: Object as PropType<BoardArticleItem>,
      required: true,
    },
  },
  methods: {
    submitComment() {
      console.log(this.newCommentText);
    },
  },
  /*
  setup(props) {
    toRef(props, "article");
  },
  */
});
</script>
