import '@scss/main.scss';
import "@scss/common_legacy.scss";

exportWindow(scrollHardTo, 'scrollHardTo');

import { exportWindow } from '@util/exportWindow';
import { scrollHardTo } from '@util/scrollHardTo';
import { createApp } from 'vue'
import PartialReservedCommand from '@/PartialReservedCommand.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import Multiselect from 'vue-multiselect';

import "@/legacy/main";
import { auto500px } from './util/auto500px';

setAxiosXMLHttpRequest();
auto500px();
createApp(PartialReservedCommand).use(BootstrapVue3).component('v-multiselect', Multiselect).mount('#reservedCommandList');
