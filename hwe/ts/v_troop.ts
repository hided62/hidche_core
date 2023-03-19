import "@scss/troop.scss";

import { createApp } from 'vue'
import PageTroop from '@/PageTroop.vue';
import { BootstrapVueNext, BToastPlugin } from 'bootstrap-vue-next'
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";

auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageTroop).use(BootstrapVueNext).use(BToastPlugin).mount('#app')