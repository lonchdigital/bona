import{$ as t}from"./app-bbb14717.js";import"./bootstrap-08b03379.js";import"./jquery-5c3435d1.js";let d=[];const g=['<div class="tooltip tooltip-filter-item--type-custom" role="tooltip">','<div class="tooltip-inner">',"</div>","</div>"].join("");function E(){t(".filters .filter-box").each(function(){t(this).hasClass("active")&&t(this).find(".filter-content").show()});var s=t(".filters .title");s.append("<span></span>"),r(".archive-catalog-filter-left .filter-item--type-custom",".checkbox-preview",".custom-control-label",".custom-checkbox"),r(".archive-catalog-filter-left .filter-item--brands",".checkbox-preview",".custom-control-label",".custom-checkbox"),r(".archive-catalog-filter-left .filter-item--colors",".color-wrapper",".link-color",".color-wrapper"),r(".archive-catalog-filter-left .filter-item--countries",".custom-control",".custom-control-label",".custom-control"),t(document).mousedown(function(o){const i=t(".tooltip");i.length&&(i.is(o.target)||i.has(o.target).length===0)&&d.forEach(function(c){c.tooltip("hide")})}),t(function(){if(window.innerWidth>991){const o=['<div class="tooltip tooltip-help-color" role="tooltip">','<div class="arrow"></div>','<div class="tooltip-inner ">',"</div>","</div>"].join("");t(".link-color").tooltip({trigger:"hover",html:!0,placement:"top",template:o,fallbackPlacement:[]})}}),t(function(){const o=['<div class="tooltip tooltip-help-field-error" role="tooltip">','<div class="arrow"></div>','<div class="tooltip-inner ">',"</div>","</div>"].join("");t(".field.field-error input").tooltip({trigger:"hover",html:!0,placement:"top",template:o,fallbackPlacement:[]})})}function r(s,o,i,c){t(s).each(function(){const a=t(this),m=t(this).find(o);m.tooltip({boundary:"window",trigger:"manual",title:'<div class="filter-find p-4 bg-white"><div class="filter-find-info text-center mb-3 d-flex justify-content-center">'+translations.filter_found+': <strong id="products-count" class="mx-1"><span class="loading-spinner mx-1"></span></strong> '+translations.filter_options+'</div><a href="#" class="btn btn-empty color-dark w-100 filter-submit-main">'+translations.filter_show+"</a></div>",html:!0,placement:"right",container:"body",template:g,fallbackPlacement:[]}),d.push(m),a.find(i).click(function(p,f){if(f)return;d.forEach(function(l){l.tooltip("hide")}),a.find(c).hasClass("checked")?t(this).closest(o).tooltip("show"):t(this).closest(o).tooltip("hide")});const n=a.find(".brands");if(n.length){const p=n.position().top,f=p+n.outerHeight(!0);let e=!1;n.scroll(function(){const l=t('div[role="tooltip"]').attr("id"),h=t("#"+l);if(l){const u=t(this).find('div[aria-describedby="'+l+'"]'),v=u.position().top,b=v+u.outerHeight(!0);p>=v||f<=b?e||(e=!0,h.attr("style","display: none;")):e&&(h.removeAttr("style"),e=!1)}})}})}export{E as init};
