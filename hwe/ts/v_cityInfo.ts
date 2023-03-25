import { createApp } from 'vue'
import { auto500px } from './util/auto500px';
import { htmlReady } from './util/htmlReady';
import { insertCustomCSS } from './util/customCSS';
import PageCityInfo from './PageCityInfo.vue';
import { installVue3Components } from './util/installVue3Components';
auto500px();

declare const query: {
  cityID?: number
};

htmlReady(() => {
  insertCustomCSS();
});
installVue3Components(createApp(PageCityInfo, query)).mount('#app');
