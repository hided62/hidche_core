<template>
  <top-back-bar :title="title" />

  <table id="newArticle" class="bg0">
    <thead>
      <tr>
        <td colspan="2" class="newArticleHeader bg2">새 게시물 작성</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th class="bg1" style="width: 66px">
          <span class="articleTitle">제목</span>
        </th>
        <td>
          <input
            class="titleInput"
            type="text"
            maxlength="250"
            placeholder="제목"
            v-model="newArticle.title"
          />
        </td>
      </tr>
      <tr>
        <th class="bg1">내용</th>
        <td class="boardArticle">
          <textarea
            class="contentInput autosize"
            ref="newArticleTextForm"
            placeholder="내용"
            v-model="newArticle.text"
            @input="autoResizeTextarea"
          />
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">
          <button type="button" id="submitArticle" @click="submitArticle">
            등록
          </button>
        </td>
      </tr>
    </tfoot>
  </table>

  <div id="board">
    <template v-if="articles && articles.length">
      <board-article
        v-for="article in articles"
        :key="article.no"
        :article="article"
        @submit-comment="reloadArticles"
      />
    </template>
    <template v-else> 게시물이 없습니다. </template>
  </div>
</template>

<script lang="ts">
import "../scss/bootstrap5.scss";
import "../scss/game_bg.scss";
import "../../css/config.css";

import { defineComponent, onMounted, reactive, ref } from "vue";
import TopBackBar from "./components/TopBackBar.vue";
import BoardArticle from "./components/BoardArticle.vue";
import { convertFormData } from "./util/convertFormData";
import axios from "axios";
import { InvalidResponse } from "./defs";
import { autoResizeTextarea } from "./util/autoResizeTextarea";
import { unwrap } from "./util/unwrap";
export type BoardResponse = {
  result: true;
  articles: Record<number, BoardArticleItem>;
};

export type BoardArticleItem = {
  no: number;
  nation_no: number;
  is_secret?: boolean;
  date: string;
  general_no: number;
  author: string;
  author_icon: string;
  title: string;
  text: string;
  comment: BoardCommentItem[];
};

export type BoardCommentItem = {
  no: number;
  nation_no: number;
  is_secret?: boolean;
  date: string;
  document_no: number;
  general_no: number;
  author: string;
  text: string;
};

export default defineComponent({
  name: "Board",
  components: {
    TopBackBar,
    BoardArticle,
  },
  props: {
    isSecretBoard: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      title: this.isSecretBoard ? "기밀실" : "회의실",
      newArticle: {
        title: "",
        text: "",
      },
    };
  },
  methods: {
    autoResizeTextarea,
    async submitArticle() {
      const { title, text } = this.newArticle;
      if (!title && !text) {
        return;
      }

      let result: InvalidResponse;

      try {
        const response = await axios({
          url: "j_board_article_add.php",
          method: "post",
          responseType: "json",
          data: convertFormData({
            isSecret: this.isSecretBoard,
            title,
            text,
          }),
        });
        result = response.data;
        if (!result.result) {
          throw result.reason;
        }
      } catch (e) {
        console.error(e);
        alert(`실패했습니다. :${e}`);
        return;
      }

      this.newArticle = { title: "", text: "" };
      const newArticleTextForm = unwrap(this.newArticleTextForm);
      newArticleTextForm.style.height = 'auto';

      await this.reloadArticles();
    },
  },

  setup(props) {
    const newArticleTextForm = ref<HTMLInputElement>();
    const articles = reactive<BoardArticleItem[]>([]);

    const reloadArticles = async () => {
      let boardResponse: BoardResponse;

      try {
        const response = await axios({
          url: "j_board_get_articles.php",
          responseType: "json",
          method: "post",
          data: convertFormData({
            isSecret: props.isSecretBoard,
          }),
        });
        const result: InvalidResponse | BoardResponse = response.data;
        if (!result.result) {
          throw result.reason;
        }
        boardResponse = result;
      } catch (e) {
        console.error(e);
        alert(`에러: ${e}`);
        return;
      }


      articles.length = 0;
      articles.push(...Object.values(boardResponse.articles));
      articles.reverse();
    };

    onMounted(async () => {
      await reloadArticles();
    });

    return {
      newArticleTextForm,
      articles,
      reloadArticles,
    };
  },
});
</script>


<style>
#newArticle {
  width: 1000px;
  margin: 0 auto;
}

.titleInput {
  width: 100%;
  color: white;
  background-color: transparent;
  border: none;
  margin: 1px 5px;
}

.contentInput {
  width: 100%;
  min-height: 3em;
  color: white;
  background-color: transparent;
  border: none;
  padding: 1px 5px;
}

.articleFrame {
  width: 1000px;
  margin: 20px auto;
}

.commentText {
  width: 100%;
}

.authorName,
.comment .author {
  width: 110px;
  font-size: 85%;
}

.date {
  width: 125px;
  font-size: 85%;
}

.text {
  text-align: left;
  padding: 1px 5px;
}

.submitComment {
  width: 100%;
}

.commentText {
  color: white;
  background-color: transparent;
  border: none;
  padding: 1px 5px;
}
</style>