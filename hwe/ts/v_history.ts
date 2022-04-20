import "@scss/history.scss";
import { createApp } from 'vue'
import PageHistory from '@/PageHistory.vue';
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";

setAxiosXMLHttpRequest();
auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageHistory).use(BootstrapVue3).use(BToastPlugin).mount('#app');