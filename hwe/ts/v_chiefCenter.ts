import { createApp } from 'vue'
import PageChiefCenter from '@/PageChiefCenter.vue';
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { auto500px } from './util/auto500px';
import { htmlReady } from './util/htmlReady';
import { insertCustomCSS } from './util/customCSS';
import { installVue3Components } from './util/installVue3Components';

declare const maxChiefTurn: number;

setAxiosXMLHttpRequest();
auto500px();

htmlReady(() => {
  insertCustomCSS();
});
installVue3Components(createApp(PageChiefCenter, {
  maxChiefTurn,

})).mount('#app')