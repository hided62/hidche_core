import { createApp } from 'vue'
import NPCControl from '@/NPCControl.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import "@scss/common/bootstrap5.scss";
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';

setAxiosXMLHttpRequest();
createApp(NPCControl).use(BootstrapVue3).mount('#app')