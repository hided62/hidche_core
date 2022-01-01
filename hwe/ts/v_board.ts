import "@scss/board.scss";
import { createApp } from 'vue'
import PageBoard from '@/PageBoard.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { auto500px } from "./util/auto500px";

declare const isSecretBoard: boolean;



setAxiosXMLHttpRequest();
auto500px();

createApp(PageBoard, {
    isSecretBoard
}).use(BootstrapVue3).mount('#app')