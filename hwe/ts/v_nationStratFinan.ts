import "@scss/nationStratFinan.scss";

import { createApp } from 'vue'
import PageNationStratFinan from '@/PageNationStratFinan.vue';
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3';
import { auto500px } from './util/auto500px';




auto500px();
createApp(PageNationStratFinan).use(BootstrapVue3).use(BToastPlugin).mount('#app');