import{$ as c}from"./app-e4c09039.js";import"./jquery-5c3435d1.js";function p(){window.dataLayer=window.dataLayer||[];const e=c("#order-count-form");e.submit(function(n){n.preventDefault();let t=new FormData(this);e.find(".field-error").remove();let r={};for(var o of t.entries())r[o[0]]=o[1];t.get("agree")!==null&&(r.agree=!0),r.title=e.find(".title.h2").text();let a=e.find(".art-current-product-link");r.current_product_title=a.text(),r.current_product_url=a.attr("href"),l(r,function(u){var d=document.getElementById("user-choose-doors-success");d.click(),e.find('input[name="name"]').val(""),e.find('input[name="phone"]').val(""),e.find('input[type="checkbox"]').prop("checked",!1),e.find(".field-error").remove(),window.dataLayer.push({event:e.find('input[name="event"]').val()})},function(u){u.status===422?i(u.responseJSON.errors):console.error("[Email]: init: error during sending the email.")})});function i(n){for(let t in n)e.find('input[name="'+t+'"]').val(""),e.find("."+t+"-field").after(`<p class="field-error ${t}">${n[t]}</p>`)}}function l(e,i,n,t){const r=routes.email.order_count_doors_route;c.ajax({url:r,type:"post",data:{_token:csrf,title:e.title,name:e.name,phone:e.phone,agree:e.agree,current_product_title:e.current_product_title,current_product_url:e.current_product_url},dataType:"json"}).done(function(o){i(o)}).fail(function(o){n(o)})}export{p as init};
