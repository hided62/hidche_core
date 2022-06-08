import "@scss/auction.scss";
import { createApp } from 'vue'
import PageAuction from '@/PageAuction.vue';
import BootstrapVue3, { BToastPlugin } from 'bootstrap-vue-3'
import { auto500px } from "./util/auto500px";
import { insertCustomCSS } from "./util/customCSS";
import { htmlReady } from "./util/htmlReady";

auto500px();

htmlReady(() => {
    insertCustomCSS();
  });
createApp(PageAuction).use(BootstrapVue3).use(BToastPlugin).mount('#app')