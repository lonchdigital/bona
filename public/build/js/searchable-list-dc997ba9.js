import{$ as t}from"./app-bbb14717.js";import"./jquery-5c3435d1.js";function f(){t(".filter-item--brands").each(function(){const e=t(this).find(".search-input"),n=t(this).find(".option-letter"),s=t(this).find(".custom-control");l(e,n,s),e.keyup(function(){l(t(this),n,s)})})}function l(o,e,n){const s=o.val();let i=!1;s?(i||(e.attr("style","display: none;"),i=!0),n.each(function(){t(this).find(".custom-control-label").text().toLowerCase().indexOf(s.toLowerCase())===-1?t(this).attr("style","display: none;"):t(this).attr("style")&&t(this).removeAttr("style")})):(e.removeAttr("style"),n.removeAttr("style"),i=!1)}export{f as init};
