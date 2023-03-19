import { createApp } from 'vue'
import NPCControl from '@/PageNPCControl.vue';
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
createApp(NPCControl).use(BootstrapVueNext).use(BToastPlugin).mount('#app')