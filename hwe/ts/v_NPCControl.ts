import "@scss/common/bootstrap5.scss";

import { createApp } from 'vue'
import NPCControl from '@/PageNPCControl.vue';
import { BootstrapVue3, BToastPlugin } from 'bootstrap-vue-3'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';

setAxiosXMLHttpRequest();
createApp(NPCControl).use(BootstrapVue3).use(BToastPlugin).mount('#app')