<template>
  <div class="articleFrame bg0">
    <div class="bg1 row gx-0">
      <div class="authorName center">{{ article.author }}</div>
      <div class="col articleTitle center">{{ article.title }}</div>
      <div class="col-2 col-md-1 date center">{{ article.date.slice(5, 16) }}</div>
    </div>
    <div class="row gx-0 s-border-b">
      <div class="col-2 col-md-1 authorIcon center">
        <img
          class="generalIcon"
          width="64"
          height="64"
          :src="article.author_icon"
        />
      </div>
      <div class="col text">{{ article.text }}</div>
    </div>
    <div class="commentList">
      <board-comment
        v-for="comment in article.comment"
        :key="comment.no"
        :comment="comment"
      />
    </div>
    <div class="row gx-0">
      <div class="bg2 inputCommentHeader center d-grid">
        <div class="align-self-center">댓글 달기</div>
      </div>
      <div class="col d-grid">
        <input
          class="commentText"
          type="text"
          maxlength="250"
          placeholder="새 댓글 내용"
          v-model.trim="newCommentText"
          @keyup.enter="submitComment"
        />
      </div>
      <div class="col-2 col-md-1 d-grid">
        <b-button class="submitComment" @click="submitComment" size="sm"
          >등록</b-button
        >
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import type { BoardArticleItem } from "@/PageBoard.vue";
import BoardComment from "@/components/BoardComment.vue";
import { defineComponent, type PropType } from "vue";
import axios from "axios";
import { convertFormData } from "@util/convertFormData";
import type { InvalidResponse } from "@/defs";
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