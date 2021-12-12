<template>
  <b-button-toolbar key-nav v-if="editable && editor" class="bg-dark">
    <!-- 뒤로, 앞으로 -->
    <b-button-group class="mx-1">
      <b-button
        @click="editor.chain().focus().toggleBold().run()"
        :class="{ 'is-active': editor.isActive('bold') }"
        ><i class="bi bi-type-bold" v-b-tooltip.hover title="진하게"></i
      ></b-button>
      <b-button
        @click="editor.chain().focus().toggleItalic().run()"
        :class="{ 'is-active': editor.isActive('italic') }"
        ><i class="bi bi-type-italic" v-b-tooltip.hover title="기울이기"></i
      ></b-button>
      <!-- 밑줄, 효과 지우기 -->
    </b-button-group>

    <b-button-group class="mx-1">
      <!-- 글꼴, 크기 -->
    </b-button-group>

    <b-button-group class="mx-1">
      <b-button
        @click="editor.chain().focus().toggleStrike().run()"
        :class="{ 'is-active': editor.isActive('strike') }"
        ><i
          class="bi bi-type-strikethrough"
          v-b-tooltip.hover
          title="가로선"
        ></i
      ></b-button>
      <!-- 윗첨자, 아랫첨자 -->
    </b-button-group>

    <b-button-group class="mx-1">
      <!-- 마지막으로 사용한 색 -->
      <!-- 배경색, 글자색 -->
    </b-button-group>

    <b-button-group class="mx-1">
      <!-- 이미지추가 -->
      <!-- 링크 -->
      <!-- 영상링크 -->
      <!-- 표 -->
      <!-- 구분선 삽입 -->
    </b-button-group>


    <b-button-group class="mx-1">
      <!-- 글머리 기호 -->
      <!-- 번호 매기기 -->
      <!-- 문단정렬(왼, 가, 오, 양)(내어, 들여) -->
    </b-button-group>


    <b-button-group class="mx-1">
      <!-- 줄간격 (1.0, 1.2, 1.4, 1.5, 1.6, 1.8, 2.0, 3.0) -->
    </b-button-group>


    <b-button-group class="mx-1">
      <!-- 원본 코드 -->
    </b-button-group>
  </b-button-toolbar>
  <editor-content :editor="editor" />
</template>

<script lang="ts">
//import "@scss/common/bootstrap5.scss";
import { defineComponent } from "vue";
import { Editor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import {BButtonGroup, BButtonToolbar, BButton} from "bootstrap-vue-3";

const compoment = defineComponent({
  components: {
    EditorContent,
    BButtonGroup,
    BButtonToolbar,
    BButton,
  },

  props: {
    modelValue: {
      type: String,
      default: "",
    },
    editable: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      editor: null as unknown as InstanceType<typeof Editor>,
    };
  },

  watch: {
    modelValue(value: string) {
      const isSame = this.editor.getHTML() === value;

      if (isSame) {
        return;
      }

      this.editor.commands.setContent(value, false);
    },
    editable(value: boolean) {
      this.editor.options.editable = value;
      if(value == true){
        this.editor.commands.focus();
      }
    },
  },

  mounted() {
    this.editor = new Editor({
      extensions: [StarterKit],
      editable: this.editable,
      content: this.modelValue,
      onUpdate: () => {
        this.$emit("update:modelValue", this.editor.getHTML());
      },
    });
  },

  beforeUnmount() {
    this.editor.destroy();
  },
});
export default compoment;
</script>