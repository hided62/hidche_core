import { createApp } from 'vue'
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3'
import { auto500px } from './util/auto500px';
import { htmlReady } from './util/htmlReady';
import { insertCustomCSS } from './util/customCSS';
import PageFront from './PageFront.vue';
import Multiselect from 'vue-multiselect';
auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageFront).use(BootstrapVue3).use(BToastPlugin).component('v-multiselect', Multiselect).mount('#app');
