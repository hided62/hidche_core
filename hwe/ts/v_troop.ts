import "@scss/troop.scss";

import { createApp } from 'vue'
import PageTroop from '@/PageTroop.vue';
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3'
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";

auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageTroop).use(BootstrapVue3).use(BToastPlugin).mount('#app')