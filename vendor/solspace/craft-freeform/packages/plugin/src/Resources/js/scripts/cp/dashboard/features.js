!function(e){var t={};function r(n){if(t[n])return t[n].exports;var a=t[n]={i:n,l:!1,exports:{}};return e[n].call(a.exports,a,a.exports,r),a.l=!0,a.exports}r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var a in e)r.d(n,a,function(t){return e[t]}.bind(null,a));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=154)}({154:function(e,t){function r(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}!function(){"use strict";tippy(".info-popup",{theme:"light-border",allowHTML:!0,placement:"right",maxWidth:600,animation:"scale"}),$(".banner .action-buttons .mark-as-read").on("click",(function(){var e=$(this).parents("tr[data-id]:first"),t=e.data("id");$.ajax({type:"post",url:Craft.getCpUrl("freeform/feeds/dismiss-message"),dataType:"json",data:r({id:t},Craft.csrfTokenName,Craft.csrfTokenValue),success:function(t){!0===t.success&&(e.parents("table").find("tr").length<=1&&e.parents("div.banner:first").remove(),e.remove())}})})),$(".banner .button.dismiss-type").on("click",(function(){var e=$(this).parents("div[data-type]:first"),t=e.data("type");$.ajax({type:"post",url:Craft.getCpUrl("freeform/feeds/dismiss-type"),dataType:"json",data:r({type:t},Craft.csrfTokenName,Craft.csrfTokenValue),success:function(t){!0===t.success&&e.remove()}})}))}()}});