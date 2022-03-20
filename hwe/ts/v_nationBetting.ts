import "@scss/nationBetting.scss";

import { createApp } from 'vue'
import PageNationBetting from '@/PageNationBetting.vue';
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3';
import { auto500px } from './util/auto500px';




auto500px();
createApp(PageNationBetting).use(BootstrapVue3).use(BToastPlugin).mount('#app');