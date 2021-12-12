<template>
  <b-button-toolbar key-nav v-if="editable && editor" class="bg-dark">
    <b-button-group class="mx-1">
      <b-button
        @click="editor.commands.undo()"
        v-b-tooltip.hover
        title="되돌리기"
        ><i class="bi bi-arrow-90deg-left"></i
      ></b-button>
      <b-button @click="editor.commands.redo()" v-b-tooltip.hover title="재실행"
        ><i class="bi bi-arrow-90deg-right"></i
      ></b-button>
    </b-button-group>
    <b-button-group class="mx-1">
      <b-button
        @click="editor.chain().focus().toggleBold().run()"
        :class="{ 'is-active': editor.isActive('bold') }"
        v-b-tooltip.hover
        title="진하게"
        ><i class="bi bi-type-bold"></i
      ></b-button>
      <b-button
        @click="editor.chain().focus().toggleItalic().run()"
        :class="{ 'is-active': editor.isActive('italic') }"
        v-b-tooltip.hover
        title="기울이기"
        ><i class="bi bi-type-italic"></i
      ></b-button>
      <b-button
        @click="editor.chain().focus().toggleUnderline().run()"
        :class="{ 'is-active': editor.isActive('underline') }"
        v-b-tooltip.hover
        title="밑줄"
        ><i class="bi bi-type-underline"></i
      ></b-button>
      <!-- 효과 지우기 -->
    </b-button-group>

    <b-button-group class="mx-1">
      <b-dropdown>
        <template #button-content> 크기 </template>
        <b-dropdown-item @click="editor.chain().focus().unsetFontSize().run()"
          ><span>기본</span></b-dropdown-item
        >
        <b-dropdown-divider />
        <b-dropdown-item
          v-for="sizeItem in fontSize"
          :key="sizeItem"
          @click="editor.chain().focus().setFontSize(sizeItem).run()"
          ><span
            :style="{
              'font-size': sizeItem,
              'text-decoration': editor.isActive('textStyle', {
                fontSize: sizeItem,
              })
                ? 'underline'
                : undefined,
            }"
            >{{ sizeItem }}</span
          ></b-dropdown-item
        >
      </b-dropdown>
      <!-- 글꼴 -->
    </b-button-group>

    <b-button-group class="mx-1">
      <b-button
        @click="editor.chain().focus().toggleStrike().run()"
        :class="{ 'is-active': editor.isActive('strike') }"
        v-b-tooltip.hover
        title="가로선"
        ><i class="bi bi-type-strikethrough"></i
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
      <b-button
        @click="editor.chain().focus().setTextAlign('left').run()"
        :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }"
        v-b-tooltip.hover
        title="왼쪽 정렬"
        ><i class="bi bi-text-left"></i
      ></b-button>
      <b-button
        @click="editor.chain().focus().setTextAlign('center').run()"
        :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }"
        v-b-tooltip.hover
        title="가운데 정렬"
        ><i class="bi bi-text-center"></i
      ></b-button>
      <b-button
        @click="editor.chain().focus().setTextAlign('right').run()"
        :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }"
        v-b-tooltip.hover
        title="오른쪽 정렬"
        ><i class="bi bi-text-right"></i
      ></b-button>
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
import { FontSize } from "@/tiptap-ext/FontSize";
import StarterKit from "@tiptap/starter-kit";
import Underline from "@tiptap/extension-underline";
import TextStyle from "@tiptap/extension-text-style";
import TextAlign from "@tiptap/extension-text-align";
import {
  BButtonGroup,
  BButtonToolbar,
  BButton,
  BDropdown,
  BDropdownItem,
  BDropdownDivider,
} from "bootstrap-vue-3";

const compoment = defineComponent({
  components: {
    EditorContent,
    BButtonGroup,
    BButtonToolbar,
    BButton,
    BDropdown,
    BDropdownItem,
    BDropdownDivider,
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
      fontList: ["Pretendard", "맑은 고딕", "궁서", "돋움"],
      fontSize: [
        "8px",
        "10px",
        "12px",
        "14px",
        "18px",
        "22px",
        "28px",
        "36px",
        "48px",
        "72px",
      ],
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
      if (value == true) {
        this.editor.commands.focus();
      }
    },
  },

  mounted() {
    const editor = new Editor({
      extensions: [
        StarterKit,
        Underline,
        FontSize,
        TextStyle,
        TextAlign.configure({
          types: ["heading", "paragraph"],
        }),
      ],
      editable: this.editable,
      content: this.modelValue,
      onUpdate: () => {
        this.$emit("update:modelValue", this.editor.getHTML());
      },
    });
    this.editor = editor;
  },

  beforeUnmount() {
    this.editor.destroy();
  },
});
export default compoment;
</script>