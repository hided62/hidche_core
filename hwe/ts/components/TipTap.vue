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
      <b-button
        @click="
          editor.chain().focus().unsetColor().unsetBackgroundColor().run()
        "
        v-b-tooltip.hover
        title="색상 취소"
        ><i class="bi bi-droplet"></i
      ></b-button>
      <input
        type="color"
        class="form-control form-control-color"
        :value="
          colorConvert(editor.getAttributes('textStyle').color, '#ffffff')
        "
        @input="editor.chain().focus().setColor(($event.target as HTMLInputElement).value).run()"
        v-b-tooltip.hover
        title="글자색"
      />
      <input
        type="color"
        class="form-control form-control-color"
        :value="
          colorConvert(
            editor.getAttributes('textStyle').backgroundColor,
            '#000000'
          )
        "
        @input="
          editor.chain().focus().setBackgroundColor(($event.target as HTMLInputElement).value).run()
        "
        v-b-tooltip.hover
        title="배경색"
      />
    </b-button-group>

    <b-button-group class="mx-1">
      <b-button
        v-b-tooltip.hover
        @click="showImageModal = true"
        title="이미지 추가"
        ><i class="bi bi-image"></i
      ></b-button>
      <!-- 이미지추가 -->
      <!-- 링크 -->
      <!-- 영상링크 -->
      <!-- 표 -->
      <!-- 구분선 삽입 -->
      <b-button
        @click="editor.chain().focus().setHorizontalRule().run()"
        v-b-tooltip.hover
        title="구분선"
        ><i class="bi bi-hr"></i
      ></b-button>
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

    <b-button-group class="mx-1"> </b-button-group>

    <b-button-group class="mx-1">
      <!-- 줄간격 (1.0, 1.2, 1.4, 1.5, 1.6, 1.8, 2.0, 3.0) -->
    </b-button-group>

    <b-button-group class="mx-1">
      <!-- 원본 코드 -->
    </b-button-group>
  </b-button-toolbar>
  <bubble-menu
    :tippy-options="{ animation: false, maxWidth: 600 }"
    :editor="editor"
    v-if="editable && editor"
    v-show="editor.isActive('custom-image')"
  >
    <b-button-toolbar>
      <b-button-group class="mx-1">
        <b-button
          @click="editor.chain().focus().setImageEx({ size: 'small' }).run()"
          :class="{
            'is-active': editor.isActive('custom-image', {
              size: 'small',
            }),
            f_frac: true,
          }"
          v-b-tooltip.hover
          title="1/4 너비로 채우기"
          >1/4</b-button
        >
        <b-button
          @click="editor.chain().focus().setImageEx({ size: 'medium' }).run()"
          :class="{
            'is-active': editor.isActive('custom-image', {
              size: 'medium',
            }),
            f_frac: true,
          }"
          v-b-tooltip.hover
          title="1/2 너비로 채우기"
          >1/2</b-button
        >
        <b-button
          @click="editor.chain().focus().setImageEx({ size: 'large' }).run()"
          :class="{
            'is-active': editor.isActive('custom-image', {
              size: 'large',
            }),
            f_frac: true,
          }"
          v-b-tooltip.hover
          title="가득 채우기"
          >1</b-button
        >
        <b-button
          @click="editor.chain().focus().setImageEx({ size: 'original' }).run()"
          :class="{
            'is-active': editor.isActive('custom-image', {
              size: 'original',
            }),
          }"
          >원본</b-button
        >
      </b-button-group>
      <b-button-group class="mx-1">
        <b-button
          @click="
            editor.chain().focus().setImageEx({ align: 'float-left' }).run()
          "
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'float-left',
            }),
          }"
          v-b-tooltip.hover
          title="왼쪽으로 붙이기"
          ><i class="bi bi-chevron-bar-left"></i
        ></b-button>
        <b-button
          @click="editor.chain().focus().setImageEx({ align: 'left' }).run()"
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'left',
            }),
          }"
          v-b-tooltip.hover
          title="왼쪽으로"
          ><i class="bi bi-align-start"></i
        ></b-button>
        <b-button
          @click="editor.chain().focus().setImageEx({ align: 'center' }).run()"
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'center',
            }),
          }"
          v-b-tooltip.hover
          title="가운데로"
          ><i class="bi bi-align-center"></i
        ></b-button>
        <b-button
          @click="editor.chain().focus().setImageEx({ align: 'right' }).run()"
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'right',
            }),
          }"
          v-b-tooltip.hover
          title="오른쪽으로 붙이기"
          ><i class="bi bi-align-end"></i
        ></b-button>
        <b-button
          @click="
            editor.chain().focus().setImageEx({ align: 'float-right' }).run()
          "
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'float-right',
            }),
          }"
          v-b-tooltip.hover
          title="오른쪽으로 붙이기"
          ><i class="bi bi-chevron-bar-right"></i
        ></b-button>
      </b-button-group>
    </b-button-toolbar>
  </bubble-menu>
  <editor-content :editor="editor" class="tiptap-editor" />
  <b-modal
    v-model="showImageModal"
    title="이미지 추가"
    okTitle="추가"
    cancelTitle="취소"
    @ok="tryAddImage"
    @show="resetModal"
    @hidden="resetModal"
  >
    <div class="bg-light text-dark">
      <b-form-group
        label-cols-sm="4"
        label-cols-lg="3"
        content-cols-sm
        content-cols-lg="7"
        description="업로드할 파일을 선택해주세요. (jpg, png, gif, webp)"
        label="이미지 업로드"
        label-align="right"
        :label-for="`${uuid}_image_upload`"
      >
        <input
          class="form-control"
          type="file"
          :id="`${uuid}_image_upload`"
          @change="chooseImage"
          accept=".jpg,.jpeg,.png,.gif,.webp"
        />
      </b-form-group>
      <b-form-group
        label-cols-sm="4"
        label-cols-lg="3"
        content-cols-sm
        content-cols-lg="7"
        description="링크할 이미지 주소를 입력해주세요."
        label="이미지 링크"
        label-align="right"
        :label-for="`${uuid}_image_link`"
      >
        <b-form-input v-model="imageLink"></b-form-input>
      </b-form-group>
    </div>
  </b-modal>
</template>

<script lang="ts">
//import "@scss/common/bootstrap5.scss";
import { defineComponent } from "vue";
import { Editor, EditorContent, BubbleMenu } from "@tiptap/vue-3";
import { FontSize } from "@/tiptap-ext/FontSize";
import StarterKit from "@tiptap/starter-kit";
import Underline from "@tiptap/extension-underline";
import TextStyle from "@tiptap/extension-text-style";
import TextAlign from "@tiptap/extension-text-align";
import Color from "@tiptap/extension-color";
//import Image from "@tiptap/extension-image";
import CustomImage from "@/tiptap-ext/CustomImage";
import Link from "@tiptap/extension-link";
import { BackgroundColor } from "@/tiptap-ext/BackgroundColor";
import {
  BButtonGroup,
  BButtonToolbar,
  BButton,
  BDropdown,
  BDropdownItem,
  BDropdownDivider,
  BModal,
} from "bootstrap-vue-3";
import { v4 as uuidv4 } from "uuid";
import { unwrap } from "@/util/unwrap";
import { getBase64FromFileObject } from "@/util/getBase64FromFileObject";
import { isObject, isString } from "lodash";
import type { AxiosError } from "axios";
import { SammoAPI } from "@/SammoAPI";

const compoment = defineComponent({
  components: {
    EditorContent,
    BubbleMenu,
    BModal,
    BButtonGroup,
    BButtonToolbar,
    BButton,
    BDropdown,
    BDropdownItem,
    BDropdownDivider,
  },
  emits: ["ready", "update:modelValue"],
  methods: {
    unwrap,
    chooseImage(e: Event) {
      const target = unwrap(e.target) as HTMLInputElement;
      this.imageUploadFiles = target.files;
    },
    colorConvert(val: string | undefined, defaultVal: string) {
      if (!val) {
        return defaultVal;
      }
      if (val.startsWith("rgb")) {
        const rgb = val.split("(")[1].split(")")[0].split(",");
        const vals: string[] = [];
        for (const subColor of rgb) {
          const hexSubColor = parseInt(subColor).toString(16);
          if (hexSubColor.length == 1) {
            vals.push("0");
          }
          vals.push(hexSubColor);
        }
        return `#${vals.join("")}`;
      }
      return val;
    },
    async tryAddImage(bvModalEvt: Event) {
      if (this.imageUploadFiles === null || this.imageUploadFiles.length == 0) {
        this.addImageLink(bvModalEvt);
        return;
      }

      const targetImage = unwrap(this.imageUploadFiles.item(0));
      let imageResult: {
        result: true;
        path: string;
      };
      try {
        const base64Binary = await getBase64FromFileObject(targetImage);
        imageResult = await SammoAPI.Misc.UploadImage({
          imageData: base64Binary,
        });
      } catch (e) {
        if (isString(e)) {
          alert(e);
          bvModalEvt.preventDefault();
        }

        if (isObject(e) && "response" in e) {
          const axiosErr = e as AxiosError;
          if (axiosErr.response?.status === 413) {
            alert("허용 용량을 초과했습니다.");
            bvModalEvt.preventDefault();
          }
        }
        console.error(e);
        return false;
      }

      const imagePath = imageResult.path;
      this.editor.chain().focus().setImage({ src: imagePath }).run();
    },
    addImageLink(bvModalEvt: Event) {
      if (!this.imageLink) {
        alert("업로드할 이미지를 선택하거나, 이미지 주소를 입력해주세요.");
        bvModalEvt.preventDefault();
        return false;
      }
      this.editor.chain().focus().setImage({ src: this.imageLink }).run();
    },
    resetModal() {
      this.imageLink = "";
      this.imageUploadFiles = null;
    },
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
      uuid: uuidv4(),
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
      imageUploadFiles: null as FileList | null,
      imageLink: "",
      showImageModal: false,
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
        Color.configure({
          types: ["textStyle"],
        }),
        BackgroundColor.configure({
          types: ["textStyle"],
        }),
        CustomImage,
        Link,
      ],
      editable: this.editable,
      content: this.modelValue,
      onUpdate: () => {
        this.$emit("update:modelValue", this.editor.getHTML());
      },
      onCreate: () => {
        this.$emit("ready");
      }
    });
    this.editor = editor;
  },

  beforeUnmount() {
    this.editor.destroy();
  },
});
export default compoment;
</script>