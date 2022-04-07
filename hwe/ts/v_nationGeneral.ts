import "@scss/nationGeneral.scss";
import "ag-grid-community/dist/styles/ag-grid.css";
import "ag-grid-community/dist/styles/ag-theme-balham-dark.css";

import { createApp } from 'vue'
import PageNationGeneral from '@/PageNationGeneral.vue';
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3';
import { auto500px } from './util/auto500px';




auto500px();
createApp(PageNationGeneral).use(BootstrapVue3).use(BToastPlugin).mount('#app');