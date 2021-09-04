import { createApp } from 'vue'
import Board from './Board.vue';
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';

setAxiosXMLHttpRequest();
createApp(Board).mount('#app')