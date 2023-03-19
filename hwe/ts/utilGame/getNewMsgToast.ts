import { BButton } from "bootstrap-vue-next";
import type { ToastContent } from "bootstrap-vue-next/dist/components/BToast/plugin";
import { h } from "vue";

type CallbackType = (type: "goto" | "ignore", e: MouseEvent) => void;

export function getNewMsgToast(title: string, body: string, callback: CallbackType): ToastContent{
  const bodyNode = h(
    "span",
    null,
    [
      body,
      h(BButton, { variant: "primary", size: "sm", onClick: (e)=>{ callback('goto', e) } }, "보러가기"),
      h(BButton, { variant: "secondary", size: "sm", onClick: (e)=>{ callback('ignore', e)} }, "이미읽음"),
    ]
  );
  return {
    title,
    body: bodyNode,
  }
}