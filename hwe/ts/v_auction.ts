import "@scss/auction.scss";
import { createApp } from 'vue'
import PageAuction from '@/PageAuction.vue';
import BootstrapVueNext, { BToastPlugin } from 'bootstrap-vue-next'
import { auto500px } from "./util/auto500px";
import { insertCustomCSS } from "./util/customCSS";
import { htmlReady } from "./util/htmlReady";

declare const staticValues: {
  serverID: string,
  turnterm: number,
  serverNick: string,
  isResAuction: boolean,
};

auto500px();

htmlReady(() => {
    insertCustomCSS();
  });
createApp(PageAuction, {
  isResAuction: staticValues.isResAuction,
}).use(BootstrapVueNext).use(BToastPlugin).mount('#app')