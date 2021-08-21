(()=>{"use strict";var t,e={3777:(t,e,r)=>{var n=r(2297),o=r.n(n);function i(t){return(i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function u(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function c(t,e){if(e&&("object"===i(e)||"function"==typeof e))return e;if(void 0!==e)throw new TypeError("Derived constructors may only return object or undefined");return l(t)}function l(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}function a(t){var e="function"==typeof Map?new Map:void 0;return(a=function(t){if(null===t||(r=t,-1===Function.toString.call(r).indexOf("[native code]")))return t;var r;if("function"!=typeof t)throw new TypeError("Super expression must either be null or a function");if(void 0!==e){if(e.has(t))return e.get(t);e.set(t,n)}function n(){return f(t,arguments,y(this).constructor)}return n.prototype=Object.create(t.prototype,{constructor:{value:n,enumerable:!1,writable:!0,configurable:!0}}),s(n,t)})(t)}function f(t,e,r){return(f=p()?Reflect.construct:function(t,e,r){var n=[null];n.push.apply(n,e);var o=new(Function.bind.apply(t,n));return r&&s(o,r.prototype),o}).apply(null,arguments)}function p(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(t){return!1}}function s(t,e){return(s=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}function y(t){return(y=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)})(t)}function v(t,e,r){return e in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}r(2479),r(9720),r(2526),r(1817),r(1539),r(2165),r(6992),r(8783),r(3948),r(7042),r(1038),r(2222),r(8304),r(489),r(2772),r(9714),r(2419),r(1532);var h=function(t){!function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&s(t,e)}(o,t);var e,r,n=(e=o,r=p(),function(){var t,n=y(e);if(r){var o=y(this).constructor;t=Reflect.construct(n,arguments,o)}else t=n.apply(this,arguments);return c(this,t)});function o(){var t;u(this,o);for(var e=arguments.length,r=new Array(e),i=0;i<e;i++)r[i]=arguments[i];return v(l(t=n.call.apply(n,[this].concat(r))),"name","NotNullExpected"),t}return o}(a(TypeError));function d(t){if(null==t)throw new h;return t}function b(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,o,i=[],u=!0,c=!1;try{for(r=r.call(t);!(u=(n=r.next()).done)&&(i.push(n.value),!e||i.length!==e);u=!0);}catch(t){c=!0,o=t}finally{try{u||null==r.return||r.return()}finally{if(c)throw o}}return i}}(t,e)||function(t,e){if(t){if("string"==typeof t)return m(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?m(t,e):void 0}}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function m(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}!function(){var t=d(document.querySelector("#inherit_sum_value")),e=d(document.querySelector("#inherit_previous_value")),r=d(document.querySelector("#inherit_new_value")),n=Math.floor(o()(Object.values(window.items))),i=Math.floor(window.items.previous),u=n-i;t.value=n.toLocaleString(),e.value=i.toLocaleString(),r.value=u.toLocaleString();for(var c=0,l=Object.entries(window.items);c<l.length;c++){var a=b(l[c],2),f=a[0],p=a[1];d(document.querySelector("#inherit_".concat(f,"_value"))).value=Math.floor(p).toLocaleString()}for(var s=0,y=Object.entries(window.helpText);s<y.length;s++){var v=b(y[s],2),h=v[0],m=v[1];d(document.querySelector("#inherit_".concat(h," small.form-text"))).innerHTML=m}}()}},r={};function n(t){var o=r[t];if(void 0!==o)return o.exports;var i=r[t]={id:t,loaded:!1,exports:{}};return e[t].call(i.exports,i,i.exports,n),i.loaded=!0,i.exports}n.m=e,t=[],n.O=(e,r,o,i)=>{if(!r){var u=1/0;for(f=0;f<t.length;f++){for(var[r,o,i]=t[f],c=!0,l=0;l<r.length;l++)(!1&i||u>=i)&&Object.keys(n.O).every((t=>n.O[t](r[l])))?r.splice(l--,1):(c=!1,i<u&&(u=i));if(c){t.splice(f--,1);var a=o();void 0!==a&&(e=a)}}return e}i=i||0;for(var f=t.length;f>0&&t[f-1][2]>i;f--)t[f]=t[f-1];t[f]=[r,o,i]},n.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return n.d(e,{a:e}),e},n.d=(t,e)=>{for(var r in e)n.o(e,r)&&!n.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:e[r]})},n.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),n.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),n.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.nmd=t=>(t.paths=[],t.children||(t.children=[]),t),n.j=334,(()=>{var t={334:0};n.O.j=e=>0===t[e];var e=(e,r)=>{var o,i,[u,c,l]=r,a=0;if(u.some((e=>0!==t[e]))){for(o in c)n.o(c,o)&&(n.m[o]=c[o]);if(l)var f=l(n)}for(e&&e(r);a<u.length;a++)i=u[a],n.o(t,i)&&t[i]&&t[i][0](),t[u[a]]=0;return n.O(f)},r=self.webpackChunkhidche_lib=self.webpackChunkhidche_lib||[];r.forEach(e.bind(null,0)),r.push=e.bind(null,r.push.bind(r))})();var o=n.O(void 0,[216],(()=>n(3777)));o=n.O(o)})();
//# sourceMappingURL=inheritPoint.js.map