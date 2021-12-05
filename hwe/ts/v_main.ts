import { createApp } from 'vue'
import ReservedCommand from './ReservedCommand.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';
import Multiselect from 'vue-multiselect';

setAxiosXMLHttpRequest();

createApp(ReservedCommand).use(BootstrapVue3).component('v-multiselect', Multiselect).mount('#reservedCommandList')