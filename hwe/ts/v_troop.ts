import "@scss/troop.scss";

import { createApp } from 'vue'
import PageTroop from '@/PageTroop.vue';
import { auto500px } from "./util/auto500px";
import { htmlReady } from "./util/htmlReady";
import { insertCustomCSS } from "./util/customCSS";
import { installVue3Components } from "./util/installVue3Components";

auto500px();

htmlReady(() => {
  insertCustomCSS();
});
installVue3Components(createApp(PageTroop)).mount('#app')