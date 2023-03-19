import { createApp } from 'vue'
import PageChiefCenter from '@/PageChiefCenter.vue';
import BootstrapVueNext, { BToastPlugin } from 'bootstrap-vue-next'
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import Multiselect from 'vue-multiselect';
import { auto500px } from './util/auto500px';
import { htmlReady } from './util/htmlReady';
import { insertCustomCSS } from './util/customCSS';

declare const maxChiefTurn: number;


setAxiosXMLHttpRequest();
auto500px();

htmlReady(() => {
    insertCustomCSS();
  });
createApp(PageChiefCenter, {
    maxChiefTurn,

}).use(BootstrapVueNext).use(BToastPlugin).component('v-multiselect', Multiselect).mount('#app')