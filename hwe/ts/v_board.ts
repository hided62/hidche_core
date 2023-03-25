import "@scss/board.scss";
import { createApp } from 'vue'
import PageBoard from '@/PageBoard.vue';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { auto500px } from "./util/auto500px";
import { insertCustomCSS } from "./util/customCSS";
import { htmlReady } from "./util/htmlReady";
import { installVue3Components } from "./util/installVue3Components";

declare const isSecretBoard: boolean;



setAxiosXMLHttpRequest();
auto500px();

htmlReady(() => {
    insertCustomCSS();
  });
installVue3Components(createApp(PageBoard, {
    isSecretBoard
})).mount('#app')