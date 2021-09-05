<template>
  <table class="articleFrame bg0">
    <thead>
      <tr class="bg1">
        <th class="authorName">{{ article.author }}</th>
        <th class="articleTitle">{{ article.title }}</th>
        <th class="date">{{ article.date }}</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <img
            class="authorIcon generalIcon"
            width="64"
            height="64"
            :src="article.author_icon"
          />
        </td>
        <td class="text" colspan="2">{{ article.text }}</td>
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
            v-model.trim="newCommentText"
            @keyup.enter="submitComment"
          />
        </td>
        <td>
          <button type="button" class="submitComment" @click="submitComment">
            등록
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</template>
<script lang="ts">
import { BoardArticleItem } from "../Board.vue";
import BoardComment from "./BoardComment.vue";
import { defineComponent, PropType } from "vue";
import axios from "axios";
import { convertFormData } from "../util/convertFormData";
import { InvalidResponse } from "../defs";
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
  emits: ["submit-comment"],
  methods: {
    async submitComment() {
      const comment = this.newCommentText;
      if (!comment) {
        return;
      }
      const articleNo = this.article.no;

      let result: InvalidResponse;

      try {
        const response = await axios({
          url: "j_board_comment_add.php",
          method: "post",
          responseType: "json",
          data: convertFormData({
            articleNo: articleNo,
            text: comment,
          }),
        });
        result = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }

      this.newCommentText = "";

      this.$emit("submit-comment");
    },
  },
});
</script>

<style>
td.text {
  white-space: pre;
}
</style>