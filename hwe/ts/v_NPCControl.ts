import "@scss/common/bootstrap5.scss";

import { createApp } from 'vue'
import NPCControl from '@/NPCControl.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';

setAxiosXMLHttpRequest();
createApp(NPCControl).use(BootstrapVue3).mount('#app')