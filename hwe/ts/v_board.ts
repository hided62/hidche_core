import { createApp } from 'vue'
import Board from './Board.vue';
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';

declare const isSecretBoard: boolean;


setAxiosXMLHttpRequest();
createApp(Board, {
    isSecretBoard
}).mount('#app')