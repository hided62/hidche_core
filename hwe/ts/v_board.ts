import { createApp } from 'vue'
import Board from './Board.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import "../scss/bootstrap5.scss";
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';

declare const isSecretBoard: boolean;


setAxiosXMLHttpRequest();
createApp(Board, {
    isSecretBoard
}).use(BootstrapVue3).mount('#app')