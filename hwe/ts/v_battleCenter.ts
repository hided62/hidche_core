import "@scss/nationGeneral.scss";
import "ag-grid-community/dist/styles/ag-grid.css";
import "ag-grid-community/dist/styles/ag-theme-balham-dark.css";
import "@scss/battleLog.scss";
import { createApp } from 'vue'
import PageBattleCenter from '@/PageBattleCenter.vue';
import { BootstrapVueNext, BToastPlugin } from 'bootstrap-vue-next';
import { auto500px } from './util/auto500px';
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";




auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageBattleCenter).use(BootstrapVueNext).use(BToastPlugin).mount('#app');