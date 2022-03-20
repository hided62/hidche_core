<template>
  <div
    ref="container"
    style="
      position: relative;
      user-select: none;
      overflow: hidden;
      touch-action: none;
    "
  >
    <slot :selected="intersected" />
  </div>
</template>

<script lang="ts">
/// https://github.com/andi23rosca/drag-select-vue/blob/master/src/DragSelect.vue
import {
  defineComponent,
  ref,
  watch,
  onMounted,
  onBeforeUnmount,
  type PropType,
} from "vue";
import VueTypes from "vue-types";

function getDimensions(p1: coord, p2: coord): rect {
  return {
    width: Math.abs(p1.x - p2.x),
    height: Math.abs(p1.y - p2.y),
  };
}
function collisionCheck(node1: DOMRect, node2: DOMRect): boolean {
  return (
    node1.left < node2.left + node2.width &&
    node1.left + node1.width > node2.left &&
    node1.top < node2.top + node2.height &&
    node1.top + node1.height > node2.top
  );
}

type coord = { x: number; y: number };
type rect = { width: number; height: number };

export default defineComponent({
  props: {
    attribute: VueTypes.string.isRequired,
    color: {
      ...VueTypes.string.def("#4299E1"),
      required: false,
    },
    opacity: {
      ...VueTypes.number.def(0.7),
      required: false,
    },
    modelValue: {
      type: Object as PropType<Set<string>>,
      required: false,
      default: () => ref(new Set()),
    },
  },
  emits: ["update:modelValue", "dragDone", "dragStart"],
  setup(props, { emit }) {
    const intersected = ref<Set<string>>(props.modelValue);
    const container = ref<HTMLElement>();

    watch(intersected, (val) => {
      emit("update:modelValue", val);
    });
    watch(props.modelValue, (val) => {
      if (intersected.value === val) {
        return;
      }
      intersected.value = val;
    });

    onMounted(() => {
      if (!container.value) {
        console.error(`Container is not referenced.`);
        return;
      }
      const uContainer = container.value;
      let containerRect = uContainer.getBoundingClientRect();

      function getCoords(e: MouseEvent | Touch): coord {
        return {
          x: e.clientX - containerRect.left,
          y: e.clientY - containerRect.top,
        };
      }
      let children: HTMLCollection;
      let box = document.createElement("div");
      box.setAttribute("data-drag-box-component", "");
      box.style.position = "absolute";
      box.style.backgroundColor = props.color;
      box.style.opacity = `${props.opacity}`;
      let start = { x: 0, y: 0 };
      let end = { x: 0, y: 0 };
      function intersection() {
        const rect = box.getBoundingClientRect();
        const localIntersected = new Set<string>();
        for (let i = 0; i < children.length; i++) {
          if (collisionCheck(rect, children[i].getBoundingClientRect())) {
            const attr = children[i].getAttribute(props.attribute);
            if (children[i].hasAttribute(props.attribute)) {
              localIntersected.add(attr as string);
            }
          }
        }

        let dismatch = false;
        for (const oldVal of intersected.value) {
          if (!localIntersected.has(oldVal)) {
            dismatch = true;
            break;
          }
        }
        if (!dismatch) {
          for (const newVal of localIntersected) {
            if (!intersected.value.has(newVal)) {
              dismatch = true;
              break;
            }
          }
        }
        if (dismatch) {
          intersected.value = localIntersected;
        }
      }
      function touchStart(e: TouchEvent) {
        e.preventDefault();
        startDrag(e.touches[0]);
      }
      function touchMove(e: TouchEvent) {
        e.preventDefault();
        drag(e.touches[0]);
      }

      let isMine = false;
      function startDrag(e: MouseEvent | Touch) {
        containerRect = uContainer.getBoundingClientRect();
        children = uContainer.children;
        start = getCoords(e);
        end = start;
        document.addEventListener("mousemove", drag);
        document.addEventListener("touchmove", touchMove);
        box.style.top = start.y + "px";
        box.style.left = start.x + "px";
        uContainer.prepend(box);
        intersection();
        isMine = true;
        emit("dragStart");
      }
      function drag(e: MouseEvent | Touch) {
        end = getCoords(e);
        const dimensions = getDimensions(start, end);
        if (end.x < start.x) {
          box.style.left = end.x + "px";
        }
        if (end.y < start.y) {
          box.style.top = end.y + "px";
        }
        box.style.width = dimensions.width + "px";
        box.style.height = dimensions.height + "px";
        intersection();
      }
      function endDrag() {
        start = { x: 0, y: 0 };
        end = { x: 0, y: 0 };
        box.style.width = "0";
        box.style.height = "0";
        document.removeEventListener("mousemove", drag);
        document.removeEventListener("touchmove", touchMove);
        box.remove();
        if(isMine){
            emit("dragDone", intersected.value);
        }
        isMine = false;
      }

      uContainer.addEventListener("mousedown", startDrag);
      uContainer.addEventListener("touchstart", touchStart);
      document.addEventListener("mouseup", endDrag);
      document.addEventListener("touchend", endDrag);

      onBeforeUnmount(() => {
        uContainer.removeEventListener("mousedown", startDrag);
        uContainer.removeEventListener("touchstart", touchStart);
        document.removeEventListener("mouseup", endDrag);
        document.removeEventListener("touchend", endDrag);
      });
    });

    return {
      intersected,
      container,
    };
  },
});
</script>