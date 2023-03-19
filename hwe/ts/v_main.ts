import '@scss/main.scss';
import "@scss/common_legacy.scss";

exportWindow(scrollHardTo, 'scrollHardTo');

import { exportWindow } from '@util/exportWindow';
import { scrollHardTo } from '@util/scrollHardTo';
import { createApp } from 'vue'
import PartialReservedCommand from '@/PartialReservedCommand.vue';
import BootstrapVueNext from 'bootstrap-vue-next'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import Multiselect from 'vue-multiselect';
import "@/legacy/main";
import { auto500px } from './util/auto500px';
import { htmlReady } from './util/htmlReady';
import { insertCustomCSS } from './util/customCSS';

setAxiosXMLHttpRequest();
auto500px();

htmlReady(() => {
  insertCustomCSS();
});
createApp(PartialReservedCommand).use(BootstrapVueNext).component('v-multiselect', Multiselect).mount('#reservedCommandList');
