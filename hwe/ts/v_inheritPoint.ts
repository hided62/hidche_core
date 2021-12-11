import "@scss/common/bootstrap5.scss";

import { createApp } from 'vue'
import InheritPoint from '@/inheritPoint.vue';
import BootstrapVue3 from 'bootstrap-vue-3'

createApp(InheritPoint).use(BootstrapVue3).mount('#app');