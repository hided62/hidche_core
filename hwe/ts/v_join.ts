import { createApp } from 'vue'
import Join from './Join.vue';
import BootstrapVue3 from 'bootstrap-vue-3'
import "../scss/bootstrap5.scss";
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'

createApp(Join).use(BootstrapVue3).mount('#app')