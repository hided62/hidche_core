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
          />
        </td>
      </tr>
      <tr>
        <th class="bg1">내용</th>
        <td class="boardArticle">
          <textarea class="contentInput autosize" placeholder="내용"></textarea>
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">
          <button type="button" id="submitArticle">등록</button>
        </td>
      </tr>
    </tfoot>
  </table>

  <div id="board">
    <!--<suspense>
      <template #default>-->
        <board-article
          v-for="article in articles"
          :key="article.no"
          :article="article"
        />
      <!--</template>
      <template #fallback>
        <span>불러오는 중입니다...</span>
      </template>
    </suspense>-->
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType, reactive, toRef } from "vue";
import "../scss/bootstrap5.scss";
import "../scss/inheritPoint.scss";
import "../scss/game_bg.scss";
import TopBackBar from "./components/TopBackBar.vue";
import BoardArticle from "./components/BoardArticle.vue";
import _, { isArray } from "lodash";
import { ref } from "vue";
import axios from "axios";
import { convertFormData } from "./util/convertFormData";
import { InvalidResponse } from "./defs";
import { delay } from "./util/delay";
declare const isSecretBoard: boolean;

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
  async setup() {
    let boardResponse: BoardResponse;

    try {
      const response = await axios({
        url: "j_board_get_articles.php",
        responseType: "json",
        method: "post",
        data: convertFormData({
          isSecret: isSecretBoard,
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

    //const articles = reactive();
    console.log(boardResponse);
    const articles:BoardResponse['articles'] = isArray(boardResponse.articles)?{}:boardResponse;

    return {
      title: isSecretBoard ? "기밀실" : "회의실",
      articles,
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