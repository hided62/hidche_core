import '@scss/main.scss';
import "@scss/common_legacy.scss";

exportWindow(scrollHardTo, 'scrollHardTo');

import { exportWindow } from '@util/exportWindow';
import { scrollHardTo } from '@util/scrollHardTo';
import { createApp } from 'vue'
import PartialReservedCommand from '@/PartialReservedCommand.vue';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import "@/legacy/main";
import { auto500px } from './util/auto500px';
import { htmlReady } from './util/htmlReady';
import { insertCustomCSS } from './util/customCSS';
import { installVue3Components } from './util/installVue3Components';

setAxiosXMLHttpRequest();
auto500px();

htmlReady(() => {
  insertCustomCSS();
});
installVue3Components(createApp(PartialReservedCommand)).mount('#reservedCommandList');
