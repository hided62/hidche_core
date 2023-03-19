import { createApp } from 'vue'
import PageCachedMap from '@/PageCachedMap.vue';
import { auto500px } from "./util/auto500px";
import { installVue3Components } from './util/installVue3Components';

auto500px();

installVue3Components(createApp(PageCachedMap)).mount('#app');