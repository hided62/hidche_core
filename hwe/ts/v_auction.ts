import "@scss/auction.scss";
import { createApp } from 'vue'
import PageAuction from '@/PageAuction.vue';
import { auto500px } from "./util/auto500px";
import { insertCustomCSS } from "./util/customCSS";
import { htmlReady } from "./util/htmlReady";
import { installVue3Components } from "./util/installVue3Components";

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
installVue3Components(createApp(PageAuction, {
  isResAuction: staticValues.isResAuction,
})).mount('#app')