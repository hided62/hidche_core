import { createApp } from 'vue'
import NPCControl from './NPCControl.vue';
import { setAxiosXMLHttpRequest } from './util/setAxiosXMLHttpRequest';

setAxiosXMLHttpRequest();
createApp(NPCControl).mount('#app')