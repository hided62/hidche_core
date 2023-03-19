import "@scss/nationBetting.scss";

import { createApp } from 'vue'
import PageNationBetting from '@/PageNationBetting.vue';
import { BootstrapVueNext, BToastPlugin } from 'bootstrap-vue-next';
import { auto500px } from './util/auto500px';
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";




auto500px();
htmlReady(() => {
  insertCustomCSS();
});
createApp(PageNationBetting).use(BootstrapVueNext).use(BToastPlugin).mount('#app');