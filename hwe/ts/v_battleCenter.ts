import "@scss/nationGeneral.scss";
import "ag-grid-community/dist/styles/ag-grid.css";
import "ag-grid-community/dist/styles/ag-theme-balham-dark.css";
import "@scss/battleLog.scss";
import { createApp } from 'vue'
import PageBattleCenter from '@/PageBattleCenter.vue';
import { auto500px } from './util/auto500px';
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";
import { installVue3Components } from "./util/installVue3Components";




auto500px();

htmlReady(() => {
  insertCustomCSS();
});
installVue3Components(createApp(PageBattleCenter)).mount('#app');