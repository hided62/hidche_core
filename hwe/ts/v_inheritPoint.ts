import { createApp } from 'vue'
import PageInheritPoint from '@/PageInheritPoint.vue';
import BootstrapVueNext from 'bootstrap-vue-next'
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";

auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PageInheritPoint).use(BootstrapVueNext).mount('#app');