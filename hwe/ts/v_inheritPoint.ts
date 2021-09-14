import { createApp } from 'vue'
import InheritPoint from './inheritPoint.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import "../scss/bootstrap5.scss";
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'
createApp(InheritPoint).use(BootstrapVue3).mount('#app');