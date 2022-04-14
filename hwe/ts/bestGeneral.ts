import "@scss/hallOfFame.scss";

import { auto500px } from "./util/auto500px";
import { insertCustomCSS } from "./util/customCSS";
import { htmlReady } from "./util/htmlReady";

auto500px();
htmlReady(() => {
  insertCustomCSS();
});