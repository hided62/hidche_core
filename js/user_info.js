(()=>{"use strict";var t,e={1016:(t,e,r)=>{r(5666),r(1539),r(9714),r(9826),r(2222),r(8674),r(7042),r(1038),r(8783),r(2526),r(1817),r(2165),r(6992),r(3948);var n=r(9669),o=r.n(n),a=r(9755),u=r.n(a),i=(r(3734),r(9490)),c="yyyy-MM-dd HH:mm:ss",l="yyyy-MM-dd HH:mm:ss.SSS";function s(){var t=arguments.length>0&&void 0!==arguments[0]&&arguments[0];return t?i.ou.now().toFormat(l):i.ou.now().toFormat(c)}var f=r(3434),p=r(1584),h=r.n(p),d=r(1763),y=r.n(d),v=r(7037),m=r.n(v),b=r(1469),_=r.n(b);function w(t,e){var r="undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!r){if(Array.isArray(t)||(r=g(t))||e&&t&&"number"==typeof t.length){r&&(t=r);var n=0,o=function(){};return{s:o,n:function(){return n>=t.length?{done:!0}:{done:!1,value:t[n++]}},e:function(t){throw t},f:o}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var a,u=!0,i=!1;return{s:function(){r=r.call(t)},n:function(){var t=r.next();return u=t.done,t},e:function(t){i=!0,a=t},f:function(){try{u||null==r.return||r.return()}finally{if(i)throw a}}}}function g(t,e){if(t){if("string"==typeof t)return x(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?x(t,e):void 0}}function x(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}function k(t){for(var e=new FormData,r=function(t){if(m()(t))return t;if(y()(t))return t.toString();if(h()(t))return t?"true":"false";throw new TypeError("지원하지 않는 formData Type")},n=0,o=Object.entries(t);n<o.length;n++){var a=(p=o[n],d=2,function(t){if(Array.isArray(t))return t}(p)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,o,a=[],u=!0,i=!1;try{for(r=r.call(t);!(u=(n=r.next()).done)&&(a.push(n.value),!e||a.length!==e);u=!0);}catch(t){i=!0,o=t}finally{try{u||null==r.return||r.return()}finally{if(i)throw o}}return a}}(p,d)||g(p,d)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()),u=a[0],i=a[1];if(_()(i)){var c,l="".concat(u,"[]"),s=w(i);try{for(s.s();!(c=s.n()).done;){var f=c.value;e.append(l,r(f))}}catch(t){s.e(t)}finally{s.f()}}else e.append(u,r(i))}var p,d;return e}function j(t){return(j="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function S(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function O(t,e){return!e||"object"!==j(e)&&"function"!=typeof e?R(t):e}function R(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}function T(t){var e="function"==typeof Map?new Map:void 0;return(T=function(t){if(null===t||(r=t,-1===Function.toString.call(r).indexOf("[native code]")))return t;var r;if("function"!=typeof t)throw new TypeError("Super expression must either be null or a function");if(void 0!==e){if(e.has(t))return e.get(t);e.set(t,n)}function n(){return A(t,arguments,P(this).constructor)}return n.prototype=Object.create(t.prototype,{constructor:{value:n,enumerable:!1,writable:!0,configurable:!0}}),M(n,t)})(t)}function A(t,e,r){return(A=E()?Reflect.construct:function(t,e,r){var n=[null];n.push.apply(n,e);var o=new(Function.bind.apply(t,n));return r&&M(o,r.prototype),o}).apply(null,arguments)}function E(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(t){return!1}}function M(t,e){return(M=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}function P(t){return(P=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)})(t)}function F(t,e,r){return e in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}r(9720),r(8304),r(489),r(2772),r(2419),r(1532);var I=function(t){!function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&M(t,e)}(o,t);var e,r,n=(e=o,r=E(),function(){var t,n=P(e);if(r){var o=P(this).constructor;t=Reflect.construct(n,arguments,o)}else t=n.apply(this,arguments);return O(this,t)});function o(){var t;S(this,o);for(var e=arguments.length,r=new Array(e),a=0;a<e;a++)r[a]=arguments[a];return F(R(t=n.call.apply(n,[this].concat(r))),"name","NotNullExpected"),t}return o}(T(TypeError));function D(t){if(null==t)throw new I;return t}function C(t,e){if(t){if("string"==typeof t)return H(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?H(t,e):void 0}}function H(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}function N(t,e,r,n,o,a,u){try{var i=t[a](u),c=i.value}catch(t){return void r(t)}i.done?e(c):Promise.resolve(c).then(n,o)}function U(t){return function(){var e=this,r=arguments;return new Promise((function(n,o){var a=t.apply(e,r);function u(t){N(a,n,o,u,i,"next",t)}function i(t){N(a,n,o,u,i,"throw",t)}u(void 0)}))}}function q(){var t=u()(this);console.log(t);var e=t[0].files[0].name,r=new FileReader;r.onload=function(t){u()("#slot_new_icon").attr("src",D(D(t.target).result).toString()).css("visibility","visible")},r.readAsDataURL(t[0].files[0]),u()("#image_upload_filename").val(e)}function B(){return(B=U(regeneratorRuntime.mark((function t(){var e,r;return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.prev=0,t.next=3,o()({url:"j_icon_delete.php",responseType:"json",method:"post"});case 3:r=t.sent,e=r.data,t.next=12;break;case 7:return t.prev=7,t.t0=t.catch(0),console.error(t.t0),alert("아이콘 삭제를 실패했습니다: ".concat(t.t0)),t.abrupt("return");case 12:if(e.result){t.next=16;break}return alert(e.reason),location.reload(),t.abrupt("return");case 16:X(e.servers);case 17:case"end":return t.stop()}}),t,null,[[0,7]])})))).apply(this,arguments)}function L(){return(L=U(regeneratorRuntime.mark((function t(){return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.prev=0,t.next=3,o()({url:"j_disallow_third_use.php",method:"post",responseType:"json"});case 3:alert("철회했습니다."),t.next=9;break;case 6:t.prev=6,t.t0=t.catch(0),alert("알 수 없는 이유로 철회를 실패했습니다.");case 9:location.reload();case 10:case"end":return t.stop()}}),t,null,[[0,6]])})))).apply(this,arguments)}function X(t){var e=u()("#chooseServerForm");e.empty();var r,n,a,i=function(t,e){var r="undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!r){if(Array.isArray(t)||(r=C(t))){r&&(t=r);var n=0,o=function(){};return{s:o,n:function(){return n>=t.length?{done:!0}:{done:!1,value:t[n++]}},e:function(t){throw t},f:o}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var a,u=!0,i=!1;return{s:function(){r=r.call(t)},n:function(){var t=r.next();return u=t.done,t},e:function(t){i=!0,a=t},f:function(){try{u||null==r.return||r.return()}finally{if(i)throw a}}}}(t);try{for(i.s();!(r=i.n()).done;){var c=(n=r.value,a=2,function(t){if(Array.isArray(t))return t}(n)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,o,a=[],u=!0,i=!1;try{for(r=r.call(t);!(u=(n=r.next()).done)&&(a.push(n.value),!e||a.length!==e);u=!0);}catch(t){i=!0,o=t}finally{try{u||null==r.return||r.return()}finally{if(i)throw o}}return a}}(n,a)||C(n,a)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()),l=c[0],s=c[1],f=u()('<div style="display:inline-block;margin-right:7px;" class="custom-control custom-checkbox">        <input type="checkbox" checked class="custom-control-input" name="'.concat(l,'" id="switch_').concat(l,'">        <label class="custom-control-label" for="switch_').concat(l,'">').concat(s,"</label>      </div>"));e.append(f)}}catch(t){i.e(t)}finally{i.f()}var p=u()("#chooseServer");p.modal({backdrop:"static"}),p.on("hidden.bs.modal",(function(){location.reload()})),u()("#modal-apply").off("click").on("click",U(regeneratorRuntime.mark((function t(){var r,n,a,i;return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:r=[],e.find("input:checked").each((function(){var t=u()(this).attr("name");console.log(t),r.push(o()({url:"../".concat(t,"/j_adjust_icon.php"),method:"post",responseType:"json"}))})),n=0,a=r;case 3:if(!(n<a.length)){t.next=16;break}return i=a[n],t.prev=5,t.next=8,i;case 8:t.next=13;break;case 10:t.prev=10,t.t0=t.catch(5),console.error(t.t0,i);case 13:n++,t.next=3;break;case 16:alert("적용되었습니다."),location.reload();case 18:case"end":return t.stop()}}),t,null,[[5,10]])}))))}function $(){return W.apply(this,arguments)}function W(){return(W=U(regeneratorRuntime.mark((function t(){var e,r;return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(0!=u()("#image_upload")[0].files.length){t.next=4;break}return alert("파일을 선택해주세요"),t.abrupt("return",!1);case 4:return t.prev=4,t.next=7,o()({url:"j_icon_change.php",method:"post",responseType:"json",data:new FormData(this)});case 7:r=t.sent,e=r.data,t.next=16;break;case 11:return t.prev=11,t.t0=t.catch(4),alert("알 수 없는 이유로 아이콘 업로드를 실패했습니다."),location.reload(),t.abrupt("return");case 16:if(e.result){t.next=20;break}return alert(e.reason),location.reload(),t.abrupt("return");case 20:X(e.servers);case 21:case"end":return t.stop()}}),t,this,[[4,11]])})))).apply(this,arguments)}function z(){return(z=U(regeneratorRuntime.mark((function t(){var e,r,n,a,i,c,l,s;return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(e=u()("#current_pw").val(),r=u()("#new_pw").val(),n=u()("#new_pw_confirm").val(),e){t.next=6;break}return alert("이전 비밀번호를 입력해야 합니다."),t.abrupt("return");case 6:if(!(r.length<6)){t.next=9;break}return alert("비밀번호 길이는 6글자 이상이어야 합니다."),t.abrupt("return");case 9:if(r==n){t.next=12;break}return alert("입력 값이 일치하지 않습니다."),t.abrupt("return");case 12:return a=u()("#global_salt").val(),i=(0,f.sha512)(a+e+a),c=(0,f.sha512)(a+r+a),t.prev=15,t.next=18,o()({url:"j_change_password.php",method:"post",responseType:"json",data:k({old_pw:i,new_pw:c})});case 18:s=t.sent,l=s.data,t.next=27;break;case 22:return t.prev=22,t.t0=t.catch(15),console.error(t.t0),alert("알 수 없는 이유로 비밀번호를 바꾸지 못했습니다.: ".concat(t.t0)),t.abrupt("return");case 27:if(l.result){t.next=30;break}return alert(l.reason),t.abrupt("return");case 30:alert("비밀번호를 바꾸었습니다"),location.reload();case 32:case"end":return t.stop()}}),t,null,[[15,22]])})))).apply(this,arguments)}function G(){return(G=U(regeneratorRuntime.mark((function t(){var e,r,n,a,i;return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(e=u()("#delete_pw").val()){t.next=4;break}return alert("비밀번호를 입력해야 합니다."),t.abrupt("return");case 4:return r=u()("#global_salt").val(),n=(0,f.sha512)(r+e+r),t.prev=6,t.next=9,o()({url:"j_delete_me.php",responseType:"json",method:"post",data:k({pw:n})});case 9:i=t.sent,a=i.data,t.next=18;break;case 13:return t.prev=13,t.t0=t.catch(6),console.error(t.t0),alert("회원 탈퇴에 실패했습니다.: ".concat(t.t0)),t.abrupt("return");case 18:if(a.result){t.next=21;break}return alert(a.reason),t.abrupt("return");case 21:alert("탈퇴 처리되었습니다."),location.href="../";case 23:case"end":return t.stop()}}),t,null,[[6,13]])})))).apply(this,arguments)}function J(){return K.apply(this,arguments)}function K(){return(K=U(regeneratorRuntime.mark((function t(){var e,r,n,a;return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(e=u()("#slot_token_valid_until").html(),r=i.ou.fromFormat(e,c).minus({days:5}).toFormat(c),!(s()<r)){t.next=6;break}return alert("".concat(r,"부터 초기화할 수 있습니다.")),t.abrupt("return",!1);case 6:if(confirm("로그아웃됩니다. 진행할까요?")){t.next=8;break}return t.abrupt("return");case 8:return t.prev=8,t.next=11,o()({url:"../oauth_kakao/j_reset_token.php",method:"post",responseType:"json"});case 11:a=t.sent,n=a.data,t.next=19;break;case 15:return t.prev=15,t.t0=t.catch(8),alert("알 수 없는 이유로 로그인 토큰 연장에 실패했습니다. : ".concat(t.t0)),t.abrupt("return");case 19:if(n.result){t.next=22;break}return alert(n.reason),t.abrupt("return");case 22:alert("초기화했습니다. 다시 로그인해 주십시오."),location.href="../";case 24:case"end":return t.stop()}}),t,null,[[8,15]])})))).apply(this,arguments)}u()((function(){o().defaults.headers.common["X-Requested-With"]="XMLHttpRequest",u()("#slot_icon, #slot_new_icon").attr("src",window.pathConfig.sharedIcon+"/default.jpg"),o()({url:"j_get_user_info.php",method:"post",responseType:"json"}).then((function(t){!function(t){if(!t.result)return alert(t.reason),void(location.href="entrance.php");u()("#slot_id").html(t.id.toString()),u()("#slot_nickname").html(t.name),u()("#slot_grade").html(t.grade),u()("#slot_acl").html(t.acl),u()("#slot_icon").attr("src",t.picture),u()("#global_salt").val(t.global_salt),u()("#slot_join_date").html(t.join_date),u()("#slot_third_use").html(t.third_use?"○":"×"),t.third_use&&u()("#third_use_disallow").show(),u()("#slot_oauth_type").text(t.oauth_type),"NONE"!=t.oauth_type?u()("#slot_token_valid_until").text(D(t.token_valid_until)):u()("#slot_token_valid_until").parent().html("")}(t.data)}),(function(){alert("알 수 없는 이유로, 회원 정보를 불러오지 못했습니다."),location.href="entrance.php"})),u()("#image_upload").on("change",q),u()("#btn_remove_icon").on("click",(function(){return confirm("아이콘을 제거할까요?")&&function(){B.apply(this,arguments)}(),!1})),u()("#third_use_disallow").on("click",(function(){confirm("개인정보 3자 제공 동의를 철회할까요?")&&function(){L.apply(this,arguments)}()})),u()("#change_pw_form").on("submit",(function(t){t.preventDefault(),function(){z.apply(this,arguments)}()})),u()("#change_icon_form").on("submit",(function(t){t.preventDefault(),$.apply(this)})),u()("#delete_me_form").on("submit",(function(t){t.preventDefault(),confirm("한 달 동안 재 가입할 수 없습니다. 정말로 탈퇴할까요?")&&function(){G.apply(this,arguments)}()})),u()("#expand_login_token").on("click",J)}))}},r={};function n(t){var o=r[t];if(void 0!==o)return o.exports;var a=r[t]={exports:{}};return e[t].call(a.exports,a,a.exports,n),a.exports}n.m=e,n.amdO={},t=[],n.O=(e,r,o,a)=>{if(!r){var u=1/0;for(s=0;s<t.length;s++){for(var[r,o,a]=t[s],i=!0,c=0;c<r.length;c++)(!1&a||u>=a)&&Object.keys(n.O).every((t=>n.O[t](r[c])))?r.splice(c--,1):(i=!1,a<u&&(u=a));if(i){t.splice(s--,1);var l=o();void 0!==l&&(e=l)}}return e}a=a||0;for(var s=t.length;s>0&&t[s-1][2]>a;s--)t[s]=t[s-1];t[s]=[r,o,a]},n.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return n.d(e,{a:e}),e},n.d=(t,e)=>{for(var r in e)n.o(e,r)&&!n.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:e[r]})},n.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),n.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),n.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},(()=>{var t={87:0};n.O.j=e=>0===t[e];var e=(e,r)=>{var o,a,[u,i,c]=r,l=0;if(u.some((e=>0!==t[e]))){for(o in i)n.o(i,o)&&(n.m[o]=i[o]);if(c)var s=c(n)}for(e&&e(r);l<u.length;l++)a=u[l],n.o(t,a)&&t[a]&&t[a][0](),t[u[l]]=0;return n.O(s)},r=self.webpackChunkhidche_lib=self.webpackChunkhidche_lib||[];r.forEach(e.bind(null,0)),r.push=e.bind(null,r.push.bind(r))})();var o=n.O(void 0,[216],(()=>n(1016)));o=n.O(o)})();
//# sourceMappingURL=user_info.js.map