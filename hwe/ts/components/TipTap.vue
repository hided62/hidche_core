<template>
  <BButtonToolbar v-if="editable && editor" key-nav class="bg-dark">
    <BButtonGroup class="mx-1">
      <BButton v-b-tooltip.hover title="되돌리기" @click="editor?.commands.undo()">
        <i class="bi bi-arrow-90deg-left" />
      </BButton>
      <BButton v-b-tooltip.hover title="재실행" @click="editor?.commands.redo()">
        <i class="bi bi-arrow-90deg-right" />
      </BButton>
    </BButtonGroup>
    <BButtonGroup class="mx-1">
      <BButton
        v-b-tooltip.hover
        :class="{ 'is-active': editor.isActive('bold') }"
        title="진하게"
        @click="editor?.chain().focus().toggleBold().run()"
      >
        <i class="bi bi-type-bold" />
      </BButton>
      <BButton
        v-b-tooltip.hover
        :class="{ 'is-active': editor.isActive('italic') }"
        title="기울이기"
        @click="editor?.chain().focus().toggleItalic().run()"
      >
        <i class="bi bi-type-italic" />
      </BButton>
      <BButton
        v-b-tooltip.hover
        :class="{ 'is-active': editor.isActive('underline') }"
        title="밑줄"
        @click="editor?.chain().focus().toggleUnderline().run()"
      >
        <i class="bi bi-type-underline" />
      </BButton>
      <!-- 효과 지우기 -->
    </BButtonGroup>

    <BButtonGroup class="mx-1">
      <BDropdown>
        <template #button-content> 크기 </template>
        <BDropdownItem @click="editor?.chain().focus().unsetFontSize().run()">
          <span>기본</span>
        </BDropdownItem>
        <BDropdownDivider />
        <BDropdownItem
          v-for="sizeItem in fontSize"
          :key="sizeItem"
          @click="editor?.chain().focus().setFontSize(sizeItem).run()"
        >
          <span
            :style="{
              'font-size': sizeItem,
              'text-decoration': editor.isActive('textStyle', {
                fontSize: sizeItem,
              })
                ? 'underline'
                : undefined,
            }"
            >{{ sizeItem }}</span
          >
        </BDropdownItem>
      </BDropdown>
      <!-- 글꼴 -->
    </BButtonGroup>

    <BButtonGroup class="mx-1">
      <BButton
        v-b-tooltip.hover
        :class="{ 'is-active': editor.isActive('strike') }"
        title="가로선"
        @click="editor?.chain().focus().toggleStrike().run()"
      >
        <i class="bi bi-type-strikethrough" />
      </BButton>
      <!-- 윗첨자, 아랫첨자 -->
    </BButtonGroup>

    <BButtonGroup class="mx-1">
      <BButton
        v-b-tooltip.hover
        title="색상 취소"
        @click="editor?.chain().focus().unsetColor().unsetBackgroundColor().run()"
      >
        <i class="bi bi-droplet" />
      </BButton>
      <input
        v-b-tooltip.hover
        type="color"
        class="form-control form-control-color"
        :value="colorConvert(editor.getAttributes('textStyle').color, '#ffffff')"
        title="글자색"
        @input="editor?.chain().focus().setColor(($event.target as HTMLInputElement).value).run()"
      />
      <input
        v-b-tooltip.hover
        type="color"
        class="form-control form-control-color"
        :value="colorConvert(editor.getAttributes('textStyle').backgroundColor, '#000000')"
        title="배경색"
        @input="
          editor?.chain().focus().setBackgroundColor(($event.target as HTMLInputElement).value).run()
        "
      />
    </BButtonGroup>

    <BButtonGroup class="mx-1">
      <BButton v-b-tooltip.hover title="이미지 추가" @click="showImageModal = true">
        <i class="bi bi-image" />
      </BButton>
      <!-- 이미지추가 -->
      <!-- 링크 -->
      <!-- 영상링크 -->
      <!-- 표 -->
      <!-- 구분선 삽입 -->
      <BButton v-b-tooltip.hover title="구분선" @click="editor?.chain().focus().setHorizontalRule().run()">
        <i class="bi bi-hr" />
      </BButton>
    </BButtonGroup>

    <BButtonGroup class="mx-1">
      <!-- 글머리 기호 -->
      <!-- 번호 매기기 -->
      <BButton
        v-b-tooltip.hover
        :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }"
        title="왼쪽 정렬"
        @click="editor?.chain().focus().setTextAlign('left').run()"
      >
        <i class="bi bi-text-left" />
      </BButton>
      <BButton
        v-b-tooltip.hover
        :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }"
        title="가운데 정렬"
        @click="editor?.chain().focus().setTextAlign('center').run()"
      >
        <i class="bi bi-text-center" />
      </BButton>
      <BButton
        v-b-tooltip.hover
        :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }"
        title="오른쪽 정렬"
        @click="editor?.chain().focus().setTextAlign('right').run()"
      >
        <i class="bi bi-text-right" />
      </BButton>
      <!-- 문단정렬(왼, 가, 오, 양)(내어, 들여) -->
    </BButtonGroup>

    <BButtonGroup class="mx-1" />

    <BButtonGroup class="mx-1">
      <!-- 줄간격 (1.0, 1.2, 1.4, 1.5, 1.6, 1.8, 2.0, 3.0) -->
    </BButtonGroup>

    <BButtonGroup class="mx-1">
      <!-- 원본 코드 -->
    </BButtonGroup>
  </BButtonToolbar>
  <BubbleMenu
    v-if="editable && editor"
    v-show="editor.isActive('custom-image')"
    :tippyOptions="{ animation: false, maxWidth: 600 }"
    :editor="editor"
  >
    <BButtonToolbar>
      <BButtonGroup class="mx-1">
        <BButton
          v-b-tooltip.hover
          :class="{
            'is-active': editor.isActive('custom-image', {
              size: 'small',
            }),
            f_frac: true,
          }"
          title="1/4 너비로 채우기"
          @click="editor?.chain().focus().setImageEx({ size: 'small' }).run()"
        >
          1/4
        </BButton>
        <BButton
          v-b-tooltip.hover
          :class="{
            'is-active': editor.isActive('custom-image', {
              size: 'medium',
            }),
            f_frac: true,
          }"
          title="1/2 너비로 채우기"
          @click="editor?.chain().focus().setImageEx({ size: 'medium' }).run()"
        >
          1/2
        </BButton>
        <BButton
          v-b-tooltip.hover
          :class="{
            'is-active': editor.isActive('custom-image', {
              size: 'large',
            }),
            f_frac: true,
          }"
          title="가득 채우기"
          @click="editor?.chain().focus().setImageEx({ size: 'large' }).run()"
        >
          1
        </BButton>
        <BButton
          :class="{
            'is-active': editor.isActive('custom-image', {
              size: 'original',
            }),
          }"
          @click="editor?.chain().focus().setImageEx({ size: 'original' }).run()"
        >
          원본
        </BButton>
      </BButtonGroup>
      <BButtonGroup class="mx-1">
        <BButton
          v-b-tooltip.hover
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'float-left',
            }),
          }"
          title="왼쪽으로 붙이기"
          @click="editor?.chain().focus().setImageEx({ align: 'float-left' }).run()"
        >
          <i class="bi bi-chevron-bar-left" />
        </BButton>
        <BButton
          v-b-tooltip.hover
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'left',
            }),
          }"
          title="왼쪽으로"
          @click="editor?.chain().focus().setImageEx({ align: 'left' }).run()"
        >
          <i class="bi bi-align-start" />
        </BButton>
        <BButton
          v-b-tooltip.hover
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'center',
            }),
          }"
          title="가운데로"
          @click="editor?.chain().focus().setImageEx({ align: 'center' }).run()"
        >
          <i class="bi bi-align-center" />
        </BButton>
        <BButton
          v-b-tooltip.hover
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'right',
            }),
          }"
          title="오른쪽으로 붙이기"
          @click="editor?.chain().focus().setImageEx({ align: 'right' }).run()"
        >
          <i class="bi bi-align-end" />
        </BButton>
        <BButton
          v-b-tooltip.hover
          :class="{
            'is-active': editor.isActive('custom-image', {
              float: 'float-right',
            }),
          }"
          title="오른쪽으로 붙이기"
          @click="editor?.chain().focus().setImageEx({ align: 'float-right' }).run()"
        >
          <i class="bi bi-chevron-bar-right" />
        </BButton>
      </BButtonGroup>
    </BButtonToolbar>
  </BubbleMenu>
  <EditorContent :editor="editor" class="tiptap-editor" />
  <BModal
    v-model="showImageModal"
    title="이미지 추가"
    okTitle="추가"
    cancelTitle="취소"
    @ok="tryAddImage"
    @show="resetModal"
    @hidden="resetModal"
  >
    <div class="bg-light text-dark">
      <BFormGroup
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
          :id="`${uuid}_image_upload`"
          class="form-control"
          type="file"
          accept=".jpg,.jpeg,.png,.gif,.webp"
          @change="chooseImage"
        />
      </BFormGroup>
      <BFormGroup
        label-cols-sm="4"
        label-cols-lg="3"
        content-cols-sm
        content-cols-lg="7"
        description="링크할 이미지 주소를 입력해주세요."
        label="이미지 링크"
        label-align="right"
        :label-for="`${uuid}_image_link`"
      >
        <BFormInput v-model="imageLink" />
      </BFormGroup>
    </div>
  </BModal>
</template>

<script lang="ts" setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
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
  BFormGroup,
  BFormInput,
} from "bootstrap-vue-3";
import { v4 as uuidv4 } from "uuid";
import { unwrap } from "@/util/unwrap";
import { getBase64FromFileObject } from "@/util/getBase64FromFileObject";
import { isObject, isString } from "lodash";
import type { AxiosError } from "axios";
import { SammoAPI } from "@/SammoAPI";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  editable: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["ready", "update:modelValue"]);

const uuid = ref(uuidv4());
const editor = ref<InstanceType<typeof Editor>>();
//const fontList = ref(["Pretendard", "맑은 고딕", "궁서", "돋움"]);
const fontSize = ref(["8px", "10px", "12px", "14px", "18px", "22px", "28px", "36px", "48px", "72px"]);
const imageUploadFiles = ref(null as FileList | null);
const imageLink = ref("");
const showImageModal = ref(false);

watch(
  () => props.modelValue,
  (value: string) => {
    const isSame = editor.value?.getHTML() === value;

    if (isSame) {
      return;
    }

    editor.value?.commands.setContent(value, false);
  }
);

watch(
  () => props.editable,
  (value: boolean) => {
    if (!editor.value) {
      return;
    }
    editor.value.options.editable = value;
    if (value == true) {
      editor.value.commands.focus();
    }
  }
);

onMounted(() => {
  const vEditor = new Editor({
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
    editable: props.editable,
    content: props.modelValue,
    onUpdate: () => {
      emit("update:modelValue", editor.value?.getHTML());
    },
    onCreate: () => {
      emit("ready");
    },
  });
  editor.value = vEditor;
});

onBeforeUnmount(() => {
  editor.value?.destroy();
});

function chooseImage(e: Event) {
  const target = unwrap(e.target) as HTMLInputElement;
  imageUploadFiles.value = target.files;
}

function colorConvert(val: string | undefined, defaultVal: string) {
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
}

async function tryAddImage(bvModalEvt: Event) {
  if (imageUploadFiles.value === null || imageUploadFiles.value.length == 0) {
    addImageLink(bvModalEvt);
    return;
  }

  const targetImage = unwrap(unwrap(imageUploadFiles.value).item(0));
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
  editor.value?.chain().focus().setImageEx({ src: imagePath }).run();
}

function addImageLink(bvModalEvt: Event) {
  if (!imageLink.value) {
    alert("업로드할 이미지를 선택하거나, 이미지 주소를 입력해주세요.");
    bvModalEvt.preventDefault();
    return false;
  }
  editor.value?.chain().focus().setImageEx({ src: imageLink.value }).run();
}

function resetModal() {
  imageLink.value = "";
  imageUploadFiles.value = null;
}
</script>
