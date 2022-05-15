import "@scss/vote.scss";

import { createApp } from 'vue'
import PageVote from '@/PageVote.vue';
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3'
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";

auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageVote).use(BootstrapVue3).use(BToastPlugin).mount('#app')