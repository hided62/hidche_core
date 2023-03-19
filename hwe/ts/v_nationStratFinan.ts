import "@scss/nationStratFinan.scss";

import { createApp } from 'vue'
import PageNationStratFinan from '@/PageNationStratFinan.vue';
import { BootstrapVueNext, BToastPlugin } from 'bootstrap-vue-next';
import { auto500px } from './util/auto500px';
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";




auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageNationStratFinan).use(BootstrapVueNext).use(BToastPlugin).mount('#app');