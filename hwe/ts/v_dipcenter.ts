import { createApp } from 'vue'
import PartialDipcenter from '@/PartialDipcenter.vue';
import BootstrapVue3 from 'bootstrap-vue-3';
import { htmlReady } from './util/htmlReady';
//import { activateFlip } from './legacy/activateFlip';
//import { activateFlip } from "@/legacy/activateFlip";
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
//import { htmlReady } from './util/htmlReady';

createApp(PartialDipcenter).use(BootstrapVue3).mount('#editorForm');
setAxiosXMLHttpRequest();


htmlReady(function(){
    //activateFlip();
})
