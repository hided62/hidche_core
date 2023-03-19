import "@scss/vote.scss";

import { createApp } from 'vue'
import PageVote from '@/PageVote.vue';
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";
import { installVue3Components } from "./util/installVue3Components";

auto500px();

htmlReady(() => {
  insertCustomCSS();
});
installVue3Components(createApp(PageVote)).mount('#app')