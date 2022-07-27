import { createApp } from 'vue'
import PageInheritPoint from '@/PageInheritPoint.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";

auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageInheritPoint).use(BootstrapVue3).mount('#app');