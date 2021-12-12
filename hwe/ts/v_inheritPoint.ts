import "@scss/common/bootstrap5.scss";

import { createApp } from 'vue'
import PageInheritPoint from '@/PageInheritPoint.vue';
import BootstrapVue3 from 'bootstrap-vue-3'

createApp(PageInheritPoint).use(BootstrapVue3).mount('#app');