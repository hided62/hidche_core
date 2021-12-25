import { createApp } from 'vue'
import PageChiefCenter from '@/PageChiefCenter.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import Multiselect from 'vue-multiselect';

declare const maxChiefTurn: number;


setAxiosXMLHttpRequest();
createApp(PageChiefCenter, {
    maxChiefTurn,

}).use(BootstrapVue3).component('v-multiselect', Multiselect).mount('#app')