import { createApp } from 'vue'
import PageJoin from '@/PageJoin.vue';
import BootstrapVueNext from 'bootstrap-vue-next'
import { auto500px } from './util/auto500px';

auto500px();

createApp(PageJoin).use(BootstrapVueNext).mount('#app')