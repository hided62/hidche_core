import { createApp } from 'vue'
import PageJoin from '@/PageJoin.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import "@scss/common/bootstrap5.scss";
import "@scss/editor_component.scss";

createApp(PageJoin).use(BootstrapVue3).mount('#app')