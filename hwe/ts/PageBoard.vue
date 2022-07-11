<template>
  <div id="container">
    <TopBackBar :title="title" />

    <div id="newArticle" class="bg0">
      <div class="newArticleHeader bg2 center">새 게시물 작성</div>
      <div class="row gx-0">
        <div class="col-2 col-md-1 articleTitle bg1 center">제목</div>
        <div class="col-10 col-md-11">
          <!-- eslint-disable-next-line vue/max-attributes-per-line -->
          <input v-model="newArticle.title" class="titleInput" type="text" maxlength="250" placeholder="제목" />
        </div>
      </div>
      <div class="row gx-0">
        <div class="col-2 col-md-1 bg1 center">내용</div>
        <div class="col-10 col-md-11">
          <textarea
            ref="newArticleTextForm"
            v-model="newArticle.text"
            class="contentInput autosize"
            placeholder="내용"
            @input="autoResizeTextarea"
          />
        </div>
      </div>
      <div class="row">
        <div class="col-8 col-md-10" />
        <div class="col-4 col-md-2 d-grid">
          <b-button id="submitArticle" @click="submitArticle"> 등록 </b-button>
        </div>
      </div>
    </div>
    <div id="board">
      <template v-if="articles && articles.length">
        <BoardArticle
          v-for="article in articles"
          :key="article.no"
          :article="article"
          @submitComment="reloadArticles"
        />
      </template>
      <template v-else> 게시물이 없습니다. </template>
    </div>

    <BottomBar />
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from "vue";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import BoardArticle from "@/components/BoardArticle.vue";
import { convertFormData } from "@util/convertFormData";
import axios from "axios";
import type { InvalidResponse } from "@/defs";
import { autoResizeTextarea } from "@util/autoResizeTextarea";
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

const props = defineProps({
  isSecretBoard: {
    type: Boolean,
    required: true,
  },
});

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

const title = ref(props.isSecretBoard ? "기밀실" : "회의실");
const newArticle = ref({
  title: "",
  text: "",
});

async function submitArticle() {
  const { title, text } = newArticle.value;
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
        isSecret: props.isSecretBoard,
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

  newArticle.value = { title: "", text: "" };
  if (newArticleTextForm.value !== undefined) {
    newArticleTextForm.value.style.height = "auto";
  }

  await reloadArticles();
}
</script>
