import $ from 'jquery';

exportWindow(scrollHardTo, 'scrollHardTo');

import { exportWindow } from '@util/exportWindow';
import { scrollHardTo } from '@util/scrollHardTo';
import { createApp } from 'vue'
import ReservedCommand from '@/ReservedCommand.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import Multiselect from 'vue-multiselect';

import "@/legacy/main";
import { auto500px } from './util/auto500px';

import '@scss/main.scss';

setAxiosXMLHttpRequest();

createApp(ReservedCommand).use(BootstrapVue3).component('v-multiselect', Multiselect).mount('#reservedCommandList');

auto500px();