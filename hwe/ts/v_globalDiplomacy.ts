import "@scss/history.scss";
import { createApp } from 'vue'
import PageGlobalDiplomacy from '@/PageGlobalDiplomacy.vue';
import { BootstrapVueNext, BToastPlugin } from 'bootstrap-vue-next'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";

setAxiosXMLHttpRequest();
auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageGlobalDiplomacy).use(BootstrapVueNext).use(BToastPlugin).mount('#app');