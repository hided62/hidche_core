import "@scss/history.scss";
import { createApp } from 'vue'
import PageGlobalDiplomacy from '@/PageGlobalDiplomacy.vue';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";
import { installVue3Components } from "./util/installVue3Components";

setAxiosXMLHttpRequest();
auto500px();

htmlReady(() => {
  insertCustomCSS();
});
installVue3Components(createApp(PageGlobalDiplomacy)).mount('#app');