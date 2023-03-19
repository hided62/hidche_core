import { BootstrapVueNext, BToastPlugin } from "bootstrap-vue-next";
import { Directives } from "bootstrap-vue-next";
import type { App } from "vue";
import Multiselect from "vue-multiselect";

export function installVue3Components<T>(app: App<T>): App<T> {

  app.use(BootstrapVueNext).use(BToastPlugin).component('v-multiselect', Multiselect);
  for (const [name, directive] of Object.entries(Directives)) {
    //BVN 0.7.3 directive 이름 hack
    if (!name.startsWith('v')) {
      continue;
    }
    app.directive(name.substring(1), directive);
  }
  return app;
}