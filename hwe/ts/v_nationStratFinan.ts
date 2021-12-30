import { createApp } from 'vue'
import PageNationStratFinan from '@/PageNationStratFinan.vue';
import BootstrapVue3 from 'bootstrap-vue-3';
import { auto500px } from './util/auto500px';




auto500px();
createApp(PageNationStratFinan).use(BootstrapVue3).mount('#app');