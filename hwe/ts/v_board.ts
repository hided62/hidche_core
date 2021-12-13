import { createApp } from 'vue'
import PageBoard from '@/PageBoard.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import "@scss/common/bootstrap5.scss";
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';

declare const isSecretBoard: boolean;


setAxiosXMLHttpRequest();
createApp(PageBoard, {
    isSecretBoard
}).use(BootstrapVue3).mount('#app')