import{$ as m}from"./app-2c14b8a7.js";import{r as I,c as S}from"./jquery-5c3435d1.js";var R={exports:{}},g={exports:{}},y;function w(){return y||(y=1,function(f){(function(n){f.exports?f.exports=n(I()):n(jQuery)})(function(n){var h=n(window.document),c=0,d=/\w\b/g,p={13:"enter",27:"escape",40:"downArrow",38:"upArrow"};function u(e,s){this.init.apply(this,arguments)}return n.extend(u.prototype,{init:function(e,s){s=this.options=n.extend(!0,{},u.defaults,s),this.$input=n(e),this.$el=s.wrapSelector instanceof n?s.wrapSelector:this.$input.closest(s.wrapSelector),u.pickTo(s,this.$el.data(),["url","onItemSelect","noResultsText","inputIdName","apiInputName"]),s.url=s.url||this.$el.attr("action"),this.ens=".fastsearch"+ ++c,this.itemSelector=u.selectorFromClass(s.itemClass),this.focusedItemSelector=u.selectorFromClass(s.focusedItemClass),this.events()},namespaceEvents:function(e){var s=this.ens;return e.replace(d,function(t){return t+s})},events:function(){var e=this,s=this.options;this.$input.on(this.namespaceEvents("keyup focus click"),function(t){p[t.keyCode]!=="enter"&&e.handleTyping()}).on(this.namespaceEvents("keydown"),function(t){if(p[t.keyCode]==="enter"&&s.preventSubmit&&t.preventDefault(),e.hasResults&&e.resultsOpened)switch(p[t.keyCode]){case"downArrow":t.preventDefault(),e.navigateItem("down");break;case"upArrow":t.preventDefault(),e.navigateItem("up");break;case"enter":e.onEnter(t);break}}),this.$el.on(this.namespaceEvents("click"),this.itemSelector,function(t){t.preventDefault(),e.handleItemSelect(n(this))}),s.mouseEvents&&this.$el.on(this.namespaceEvents("mouseleave"),this.itemSelector,function(t){n(this).removeClass(s.focusedItemClass)}).on(this.namespaceEvents("mouseenter"),this.itemSelector,function(t){e.$resultItems.removeClass(s.focusedItemClass),n(this).addClass(s.focusedItemClass)})},handleTyping:function(){var e=n.trim(this.$input.val()),s=this;e.length<this.options.minQueryLength?this.hideResults():e===this.query?this.showResults():(clearTimeout(this.keyupTimeout),this.keyupTimeout=setTimeout(function(){s.$el.addClass(s.options.loadingClass),s.query=e,s.getResults(function(t){s.showResults(s.storeResponse(t).generateResults(t))})},this.options.typeTimeout))},getResults:function(e){var s=this,t=this.options,i=this.$el.find("input, textarea, select").serializeArray();t.apiInputName&&i.push({name:t.apiInputName,value:this.$input.val()}),n.get(t.url,i,function(l){e(t.parseResponse?t.parseResponse.call(s,l,s):l)})},storeResponse:function(e){return this.responseData=e,this.hasResults=e.length!==0,this},generateResults:function(e){var s=n("<div>"),t=this.options;return t.template?n(t.template(e,this)):(e.length===0?s.html('<p class="'+t.noResultsClass+'">'+(typeof t.noResultsText=="function"?t.noResultsText.call(this):t.noResultsText)+"</p>"):this.options.responseType==="html"?s.html(e):this["generate"+(e[0][t.responseFormat.groupItems]?"GroupedResults":"SimpleResults")](e,s),s.children())},generateSimpleResults:function(e,s){var t=this;this.itemModels=e,n.each(e,function(i,l){s.append(t.generateItem(l))})},generateGroupedResults:function(e,s){var t=this,i=this.options,l=i.responseFormat;this.itemModels=[],n.each(e,function(o,a){var r=n('<div class="'+i.groupClass+'">').appendTo(s);a[l.groupCaption]&&r.append('<h3 class="'+i.groupTitleClass+'">'+a[l.groupCaption]+"</h3>"),n.each(a.items,function(v,C){t.itemModels.push(C),r.append(t.generateItem(C))}),i.onGroupCreate&&i.onGroupCreate.call(t,r,a,t)})},generateItem:function(e){var s=this.options,t=s.responseFormat,i=e[t.url],l=e[t.html]||e[t.label],o=n("<"+(i?"a":"span")+">").html(l).addClass(s.itemClass);return i&&o.attr("href",i),s.onItemCreate&&s.onItemCreate.call(this,o,e,this),o},showResults:function(e){!e&&this.resultsOpened||(this.$el.removeClass(this.options.loadingClass).addClass(this.options.resultsOpenedClass),this.options.flipOnBottom&&this.checkDropdownPosition(),this.$resultsCont=this.$resultsCont||n("<div>").addClass(this.options.resultsContClass).appendTo(this.$el),e&&(this.$resultsCont.html(e),this.$resultItems=this.$resultsCont.find(this.itemSelector),this.options.onResultsCreate&&this.options.onResultsCreate.call(this,this.$resultsCont,this.responseData,this)),this.resultsOpened||(this.documentCancelEvents("on"),this.$input.trigger("openingResults")),this.options.focusFirstItem&&this.$resultItems&&this.$resultItems.length&&this.navigateItem("down"),this.resultsOpened=!0)},checkDropdownPosition:function(){var e=this.options.flipOnBottom,s=typeof e=="boolean"&&e?400:e,t=this.$input.offset().top+s>h.height();this.$el.toggleClass(this.options.resultsFlippedClass,t)},documentCancelEvents:function(e,s){var t=this;if(e==="off"&&this.closeEventsSetuped){h.off(this.ens),this.closeEventsSetuped=!1;return}else e==="on"&&!this.closeEventsSetuped&&(h.on(this.namespaceEvents("click keyup"),function(i){(p[i.keyCode]==="escape"||!n(i.target).is(t.$el)&&!n.contains(t.$el.get(0),i.target)&&n.contains(document.documentElement,i.target))&&(s?s.call(t):t.hideResults())}),this.closeEventsSetuped=!0)},navigateItem:function(e){var s=this.$resultItems.filter(this.focusedItemSelector),t=this.$resultItems.length-1;if(s.length===0){this.$resultItems.eq(e==="up"?t:0).addClass(this.options.focusedItemClass);return}var i=this.$resultItems.index(s),l=e==="up"?i-1:i+1;l>t&&(l=0),l<0&&(l=t),s.removeClass(this.options.focusedItemClass),this.$resultItems.eq(l).addClass(this.options.focusedItemClass)},navigateDown:function(){this.navigateItem("down")},navigateUp:function(){this.navigateItem("up")},onEnter:function(e){var s=this.$resultItems.filter(this.focusedItemSelector);s.length&&(e.preventDefault(),this.handleItemSelect(s))},handleItemSelect:function(e){var s=this.options.onItemSelect,t=this.itemModels.length?this.itemModels[this.$resultItems.index(e)]:{};this.$input.trigger("itemSelected"),s==="fillInput"?this.fillInput(t):s==="follow"?window.location.href=e.attr("href"):typeof s=="function"&&s.call(this,e,t,this)},fillInput:function(e){var s=this.options,t=s.responseFormat;if(this.query=e[t.label],this.$input.val(e[t.label]).trigger("change"),s.fillInputId&&e.id){if(!this.$inputId){var i=s.inputIdName||this.$input.attr("name")+"_id";this.$inputId=this.$el.find('input[name="'+i+'"]'),this.$inputId.length||(this.$inputId=n('<input type="hidden" name="'+i+'" />').appendTo(this.$el))}this.$inputId.val(e.id).trigger("change")}this.hideResults()},hideResults:function(){return this.resultsOpened&&(this.resultsOpened=!1,this.$el.removeClass(this.options.resultsOpenedClass),this.$input.trigger("closingResults"),this.documentCancelEvents("off")),this},clear:function(){return this.hideResults(),this.$input.val("").trigger("change"),this},destroy:function(){h.off(this.ens),this.$input.off(this.ens),this.$el.off(this.ens).removeClass(this.options.resultsOpenedClass).removeClass(this.options.loadingClass),this.$resultsCont&&(this.$resultsCont.remove(),delete this.$resultsCont),delete this.$el.data().fastsearch}}),n.extend(u,{pickTo:function(e,s,t){return n.each(t,function(i,l){e[l]=s&&s[l]||e[l]}),e},selectorFromClass:function(e){return"."+e.replace(/\s/g,".")}}),u.defaults={wrapSelector:"form",url:null,responseType:"JSON",preventSubmit:!1,resultsContClass:"fs_results",resultsOpenedClass:"fsr_opened",resultsFlippedClass:"fsr_flipped",groupClass:"fs_group",itemClass:"fs_result_item",groupTitleClass:"fs_group_title",loadingClass:"loading",noResultsClass:"fs_no_results",focusedItemClass:"focused",typeTimeout:140,minQueryLength:2,template:null,mouseEvents:!("ontouchstart"in window||navigator.maxTouchPoints>0||navigator.msMaxTouchPoints>0),focusFirstItem:!1,flipOnBottom:!1,responseFormat:{url:"url",html:"html",label:"label",groupCaption:"caption",groupItems:"items"},fillInputId:!0,inputIdName:null,apiInputName:null,noResultsText:"No results found",onItemSelect:"follow",parseResponse:null,onResultsCreate:null,onGroupCreate:null,onItemCreate:null},n.fastsearch=u,n.fn.fastsearch=function(e){return this.each(function(){n.data(this,"fastsearch")||n.data(this,"fastsearch",new u(this,e))})},n})}(g)),g.exports}(function(f){(function(n){f.exports?f.exports=n(I()):n(jQuery)})(function(n){var h=n(window.document),c=0,d=/\w\b/g,p={13:"enter",27:"escape",40:"downArrow",38:"upArrow"};function u(e,s){this.init.apply(this,arguments)}return n.extend(u.prototype,{init:function(e,s){s=this.options=n.extend(!0,{},u.defaults,s),this.$input=n(e),this.$el=s.wrapSelector instanceof n?s.wrapSelector:this.$input.closest(s.wrapSelector),u.pickTo(s,this.$el.data(),["url","onItemSelect","onLoad","noResultsText","inputIdName","apiInputName"]),s.url=s.url||this.$el.attr("action"),this.ens=".fastsearch"+ ++c,this.itemSelector=u.selectorFromClass(s.itemClass),this.focusedItemSelector=u.selectorFromClass(s.focusedItemClass),this.events()},namespaceEvents:function(e){var s=this.ens;return e.replace(d,function(t){return t+s})},events:function(){var e=this,s=this.options;this.$input.on(this.namespaceEvents("keyup focus click"),function(t){p[t.keyCode]!=="enter"&&e.handleTyping()}).on(this.namespaceEvents("keydown"),function(t){if(p[t.keyCode]==="enter"&&s.preventSubmit&&t.preventDefault(),e.hasResults&&e.resultsOpened)switch(p[t.keyCode]){case"downArrow":t.preventDefault(),e.navigateItem("down");break;case"upArrow":t.preventDefault(),e.navigateItem("up");break;case"enter":e.onEnter(t);break}}),this.$el.on(this.namespaceEvents("click"),this.itemSelector,function(t){t.preventDefault(),e.handleItemSelect(n(this))}),s.mouseEvents&&this.$el.on(this.namespaceEvents("mouseleave"),this.itemSelector,function(t){n(this).removeClass(s.focusedItemClass)}).on(this.namespaceEvents("mouseenter"),this.itemSelector,function(t){e.$resultItems.removeClass(s.focusedItemClass),n(this).addClass(s.focusedItemClass)})},handleTyping:function(){var e=n.trim(this.$input.val()),s=this;e.length<this.options.minQueryLength?this.hideResults():e===this.query?this.showResults():(clearTimeout(this.keyupTimeout),this.keyupTimeout=setTimeout(function(){s.$el.addClass(s.options.loadingClass),s.query=e,s.getResults(function(t){s.showResults(s.storeResponse(t).generateResults(t))})},this.options.typeTimeout))},getResults:function(e){var s=this,t=this.options,i=this.$el.find("input, textarea, select").serializeArray();t.apiInputName&&i.push({name:t.apiInputName,value:this.$input.val()}),n.get(t.url,i,function(l){e(t.parseResponse?t.parseResponse.call(s,l,s):l)})},storeResponse:function(e){return this.responseData=e,this.hasResults=e.length!==0,this},generateResults:function(e){var s=n("<div>"),t=this.options;return t.template?n(t.template(e,this)):(e.length===0?s.html('<p class="'+t.noResultsClass+'">'+(typeof t.noResultsText=="function"?t.noResultsText.call(this):t.noResultsText)+"</p>"):this.options.responseType==="html"?s.html(e):this["generate"+(e[0][t.responseFormat.groupItems]?"GroupedResults":"SimpleResults")](e,s),s.children())},generateSimpleResults:function(e,s){var t=this;this.itemModels=e,n.each(e,function(i,l){s.append(t.generateItem(l))})},generateGroupedResults:function(e,s){var t=this,i=this.options,l=i.responseFormat;this.itemModels=[],n.each(e,function(o,a){var r=n('<div class="'+i.groupClass+'">').appendTo(s);a[l.groupCaption]&&r.append('<h3 class="'+i.groupTitleClass+'">'+a[l.groupCaption]+"</h3>"),n.each(a.items,function(v,C){t.itemModels.push(C),r.append(t.generateItem(C))}),i.onGroupCreate&&i.onGroupCreate.call(t,r,a,t)})},generateItem:function(e){var s=this.options,t=s.responseFormat,i=e[t.url],l=e[t.html]||e[t.label],o=n("<"+(i?"a":"span")+">").html(l).addClass(s.itemClass);return i&&o.attr("href",i),s.onItemCreate&&s.onItemCreate.call(this,o,e,this),o},showResults:function(e){!e&&this.resultsOpened||(this.$el.removeClass(this.options.loadingClass).addClass(this.options.resultsOpenedClass),this.options.flipOnBottom&&this.checkDropdownPosition(),this.$resultsCont=this.$resultsCont||n("<div>").addClass(this.options.resultsContClass).appendTo(this.$el),e&&(this.$resultsCont.html(e),this.$resultItems=this.$resultsCont.find(this.itemSelector),this.options.onResultsCreate&&this.options.onResultsCreate.call(this,this.$resultsCont,this.responseData,this)),this.resultsOpened||(this.documentCancelEvents("on"),this.$input.trigger("openingResults")),this.options.focusFirstItem&&this.$resultItems&&this.$resultItems.length&&this.navigateItem("down"),this.resultsOpened=!0)},checkDropdownPosition:function(){var e=this.options.flipOnBottom,s=typeof e=="boolean"&&e?400:e,t=this.$input.offset().top+s>h.height();this.$el.toggleClass(this.options.resultsFlippedClass,t)},documentCancelEvents:function(e,s){var t=this;if(e==="off"&&this.closeEventsSetuped){h.off(this.ens),this.closeEventsSetuped=!1;return}else e==="on"&&!this.closeEventsSetuped&&(h.on(this.namespaceEvents("click keyup"),function(i){(p[i.keyCode]==="escape"||!n(i.target).is(t.$el)&&!n.contains(t.$el.get(0),i.target)&&n.contains(document.documentElement,i.target))&&(s?s.call(t):t.hideResults())}),this.closeEventsSetuped=!0)},navigateItem:function(e){var s=this.$resultItems.filter(this.focusedItemSelector),t=this.$resultItems.length-1;if(s.length===0){this.$resultItems.eq(e==="up"?t:0).addClass(this.options.focusedItemClass);return}var i=this.$resultItems.index(s),l=e==="up"?i-1:i+1;l>t&&(l=0),l<0&&(l=t),s.removeClass(this.options.focusedItemClass),this.$resultItems.eq(l).addClass(this.options.focusedItemClass)},navigateDown:function(){this.navigateItem("down")},navigateUp:function(){this.navigateItem("up")},onEnter:function(e){var s=this.$resultItems.filter(this.focusedItemSelector);s.length&&(e.preventDefault(),this.handleItemSelect(s))},handleItemSelect:function(e){var s=this.options.onItemSelect,t=this.itemModels.length?this.itemModels[this.$resultItems.index(e)]:{};this.$input.trigger("itemSelected"),s==="fillInput"?this.fillInput(t):s==="follow"?window.location.href=e.attr("href"):typeof s=="function"&&s.call(this,e,t,this)},fillInput:function(e){var s=this.options,t=s.responseFormat;if(this.query=e[t.label],this.$input.val(e[t.label]).trigger("change"),s.fillInputId&&e.id){if(!this.$inputId){var i=s.inputIdName||this.$input.attr("name")+"_id";this.$inputId=this.$el.find('input[name="'+i+'"]'),this.$inputId.length||(this.$inputId=n('<input type="hidden" name="'+i+'" />').appendTo(this.$el))}this.$inputId.val(e.id).trigger("change")}this.hideResults()},hideResults:function(){return this.resultsOpened&&(this.resultsOpened=!1,this.$el.removeClass(this.options.resultsOpenedClass),this.$input.trigger("closingResults"),this.documentCancelEvents("off")),this},clear:function(){return this.hideResults(),this.$input.val("").trigger("change"),this},destroy:function(){h.off(this.ens),this.$input.off(this.ens),this.$el.off(this.ens).removeClass(this.options.resultsOpenedClass).removeClass(this.options.loadingClass),this.$resultsCont&&(this.$resultsCont.remove(),delete this.$resultsCont),delete this.$el.data().fastsearch}}),n.extend(u,{pickTo:function(e,s,t){return n.each(t,function(i,l){e[l]=s&&s[l]||e[l]}),e},selectorFromClass:function(e){return"."+e.replace(/\s/g,".")}}),u.defaults={wrapSelector:"form",url:null,responseType:"JSON",preventSubmit:!1,resultsContClass:"fs_results",resultsOpenedClass:"fsr_opened",resultsFlippedClass:"fsr_flipped",groupClass:"fs_group",itemClass:"fs_result_item",groupTitleClass:"fs_group_title",loadingClass:"loading",noResultsClass:"fs_no_results",focusedItemClass:"focused",typeTimeout:140,minQueryLength:2,template:null,mouseEvents:!("ontouchstart"in window||navigator.maxTouchPoints>0||navigator.msMaxTouchPoints>0),focusFirstItem:!1,flipOnBottom:!1,responseFormat:{url:"url",html:"html",label:"label",groupCaption:"caption",groupItems:"items"},fillInputId:!0,inputIdName:null,apiInputName:null,noResultsText:"No results found",onItemSelect:"follow",onLoad:null,parseResponse:null,onResultsCreate:null,onGroupCreate:null,onItemCreate:null},n.fastsearch=u,n.fn.fastsearch=function(e){return this.each(function(){n.data(this,"fastsearch")||n.data(this,"fastsearch",new u(this,e))})},n}),function(n,h){f.exports?f.exports=h(I(),w()):h(n.jQuery)}(S,function(n){var h=n(document),c=0,d=n.fastsearch,p=d.pickTo,u=d.selectorFromClass;function e(t,i){this.init.apply(this,arguments)}n.extend(e.prototype,{init:function(t,i){this.$input=n(t),this.options=p(n.extend(!0,{},e.defaults,i,{placeholder:this.$input.attr("placeholder")}),this.$input.data(),["url","loadOnce","apiParam","initialValue","userOptionAllowed"]),this.ens=".fastselect"+ ++c,this.hasCustomLoader=this.$input.is("input"),this.isMultiple=!!this.$input.attr("multiple"),this.userOptionAllowed=this.hasCustomLoader&&this.isMultiple&&this.options.userOptionAllowed,this.optionsCollection=new s(p({multipleValues:this.isMultiple},this.options,["url","loadOnce","parseData","onLoad","matcher"])),this.setupDomElements(),this.setupFastsearch(),this.setupEvents()},setupDomElements:function(){this.$el=n("<div>").addClass(this.options.elementClass),this[this.isMultiple?"setupMultipleElement":"setupSingleElement"](function(){this.updateDomElements(),this.$controls.appendTo(this.$el),this.$el.insertAfter(this.$input),this.$input.detach().appendTo(this.$el)})},setupSingleElement:function(t){var i=this.processInitialOptions(),l=i&&i.length?i[0].text:this.options.placeholder;this.$el.addClass(this.options.singleModeClass),this.$controls=n("<div>").addClass(this.options.controlsClass),this.$toggleBtn=n("<div>").addClass(this.options.toggleButtonClass).text(l).appendTo(this.$el),this.$queryInput=n("<input>").attr("placeholder",this.options.searchPlaceholder).addClass(this.options.queryInputClass).appendTo(this.$controls),t.call(this)},setupMultipleElement:function(t){var i=this,l=i.options,o=this.processInitialOptions();this.$el.addClass(l.multipleModeClass),this.$controls=n("<div>").addClass(l.controlsClass),this.$queryInput=n("<input>").addClass(l.queryInputClass).appendTo(this.$controls),o&&n.each(o,function(a,r){i.addChoiceItem(r)}),t.call(this)},updateDomElements:function(){this.$el.toggleClass(this.options.noneSelectedClass,!this.optionsCollection.hasSelectedValues()),this.adjustQueryInputLayout()},processInitialOptions:function(){var t=this,i;return this.hasCustomLoader?(i=this.options.initialValue,n.isPlainObject(i)&&(i=[i])):i=n.map(this.$input.find("option:selected").get(),function(l){var o=n(l);return{text:o.text(),value:o.attr("value")}}),i&&n.each(i,function(l,o){t.optionsCollection.setSelected(o)}),i},addChoiceItem:function(t){n('<div data-text="'+t.text+'" data-value="'+t.value+'" class="'+this.options.choiceItemClass+'">'+n("<div>").html(t.text).text()+'<button class="'+this.options.choiceRemoveClass+'" type="button">×</button></div>').insertBefore(this.$queryInput)},setupFastsearch:function(){var t=this,i=this.options,l={};p(l,i,["resultsContClass","resultsOpenedClass","resultsFlippedClass","groupClass","itemClass","focusFirstItem","groupTitleClass","loadingClass","noResultsClass","noResultsText","focusedItemClass","flipOnBottom"]),this.fastsearch=new d(this.$queryInput.get(0),n.extend(l,{wrapSelector:this.isMultiple?this.$el:this.$controls,minQueryLength:0,typeTimeout:this.hasCustomLoader?i.typeTimeout:0,preventSubmit:!0,fillInputId:!1,responseFormat:{label:"text",groupCaption:"label"},onItemSelect:function(o,a,r){var v=i.maxItems;t.isMultiple&&v&&t.optionsCollection.getValues().length>v-1?i.onMaxItemsReached&&i.onMaxItemsReached(this):(t.setSelectedOption(a),t.writeToInput(),!t.isMultiple&&t.hide(),i.clearQueryOnSelect&&r.clear(),t.userOptionAllowed&&a.isUserOption&&(r.$resultsCont.remove(),delete r.$resultsCont,t.hide()),i.onItemSelect&&i.onItemSelect.call(t,o,a,t,r))},onLoad:function(){},onItemCreate:function(o,a){a.$item=o,a.selected&&o.addClass(i.itemSelectedClass),t.userOptionAllowed&&a.isUserOption&&o.text(t.options.userOptionPrefix+o.text()).addClass(t.options.userOptionClass),i.onItemCreate&&i.onItemCreate.call(t,o,a,t)}})),this.fastsearch.getResults=function(){t.userOptionAllowed&&t.$queryInput.val().length>1&&t.renderOptions(),t.getOptions(function(){t.renderOptions(!0)})}},getOptions:function(t){var i=this.options,l={};if(this.hasCustomLoader){var o=n.trim(this.$queryInput.val());o&&i.apiParam&&(l[i.apiParam]=o),this.optionsCollection.fetch(l,t)}else!this.optionsCollection.models&&this.optionsCollection.reset(this.gleanSelectData(this.$input)),t()},namespaceEvents:function(t){return d.prototype.namespaceEvents.call(this,t)},setupEvents:function(){var t=this,i=this.options;this.isMultiple?(this.$el.on(this.namespaceEvents("click"),function(l){n(l.target).is(u(i.controlsClass))&&t.$queryInput.focus()}),this.$queryInput.on(this.namespaceEvents("keyup"),function(l){t.adjustQueryInputLayout(),t.show()}).on(this.namespaceEvents("focus"),function(){t.show()}),this.$el.on(this.namespaceEvents("click"),u(i.choiceRemoveClass),function(l){var o=n(l.currentTarget).closest(u(i.choiceItemClass));t.removeSelectedOption({value:o.attr("data-value"),text:o.attr("data-text")},o)})):(this.$el.on("change",function(l){this.querySelectorAll("input")[1].value==""&&t.$el.find(".fstToggleBtn").html("")}),this.$el.on(this.namespaceEvents("click"),u(i.toggleButtonClass),function(){t.$el.hasClass(i.activeClass)?t.hide():t.show(!0)}))},adjustQueryInputLayout:function(){if(this.isMultiple&&this.$queryInput){var t=this.$el.hasClass(this.options.noneSelectedClass);this.$queryInput.toggleClass(this.options.queryInputExpandedClass,t),t?this.$queryInput.attr({style:"",placeholder:this.options.placeholder}):(this.$fakeInput=this.$fakeInput||n("<span>").addClass(this.options.fakeInputClass),this.$fakeInput.text(this.$queryInput.val().replace(/\s/g,"&nbsp;")),this.$queryInput.removeAttr("placeholder").css("width",this.$fakeInput.insertAfter(this.$queryInput).width()+20),this.$fakeInput.detach())}},show:function(t){this.$el.addClass(this.options.activeClass),t?this.$queryInput.focus():this.fastsearch.handleTyping(),this.documentCancelEvents("on")},hide:function(){this.$el.removeClass(this.options.activeClass),this.documentCancelEvents("off")},documentCancelEvents:function(t){d.prototype.documentCancelEvents.call(this,t,this.hide)},setSelectedOption:function(t){if(!this.optionsCollection.isSelected(t.value)){this.optionsCollection.setSelected(t);var i=this.optionsCollection.findWhere(function(l){return l.value===t.value});this.isMultiple?this.$controls&&this.addChoiceItem(t):(this.fastsearch&&this.fastsearch.$resultItems.removeClass(this.options.itemSelectedClass),this.$toggleBtn&&this.$toggleBtn.text(t.text)),i&&i.$item.addClass(this.options.itemSelectedClass),this.updateDomElements()}},removeSelectedOption:function(t,i){var l=this.optionsCollection.removeSelected(t);l&&l.$item&&l.$item.removeClass(this.options.itemSelectedClass),i?i.remove():this.$el.find(u(this.options.choiceItemClass)+'[data-value="'+t.value+'"]').remove(),this.updateDomElements(),this.writeToInput()},writeToInput:function(){var t=this.optionsCollection.getValues(),i=this.options.valueDelimiter,l=this.isMultiple?this.hasCustomLoader?t.join(i):t:t[0];this.$input.val(l).trigger("change")},renderOptions:function(t){var i=this.$queryInput.val(),l;if(this.optionsCollection.models?l=(t?this.optionsCollection.filter(i):this.optionsCollection.models).slice(0):l=[],this.userOptionAllowed){var o=this.optionsCollection.models&&this.optionsCollection.findWhere(function(a){return a.value===i});i&&!o&&l.unshift({text:i,value:i,isUserOption:!0})}this.fastsearch.showResults(this.fastsearch.storeResponse(l).generateResults(l))},gleanSelectData:function(t){var i=this,l=t.children();return l.eq(0).is("optgroup")?n.map(l.get(),function(o){var a=n(o);return{label:a.attr("label"),items:i.gleanOptionsData(a.children())}}):this.gleanOptionsData(l)},gleanOptionsData:function(t){return n.map(t.get(),function(i){var l=n(i);return{text:l.text(),value:l.attr("value"),selected:l.is(":selected")}})},destroy:function(){h.off(this.ens),this.fastsearch.destroy(),this.$input.off(this.ens).detach().insertAfter(this.$el),this.$el.off(this.ens).remove(),this.$input.data()&&delete this.$input.data().fastselect}});function s(t){this.init(t)}return n.extend(s.prototype,{defaults:{loadOnce:!1,url:null,parseData:null,onLoad:null,multipleValues:!1,matcher:function(t,i){return t.toLowerCase().indexOf(i.toLowerCase())>-1}},init:function(t){this.options=n.extend({},this.defaults,t),this.selectedValues={}},fetch:function(t,i){var l=this,o=function(){l.applySelectedValues(i)};this.options.loadOnce?(this.fetchDeferred=this.fetchDeferred||this.load(t),this.fetchDeferred.done(o)):this.load(t,o)},reset:function(t){this.models=this.options.parseData?this.options.parseData(t):t,this.applySelectedValues()},applySelectedValues:function(t){this.each(function(i){this.options.multipleValues&&i.selected?this.selectedValues[i.value]=!0:i.selected=!!this.selectedValues[i.value]}),t&&t.call(this)},load:function(t,i){var l=this,o=this.options;let a=this.options.onLoad?this.options.onLoad():o.url;return n.get(a,t,function(r){l.models=o.parseData?o.parseData(r):r,i&&i.call(l)})},setSelected:function(t){this.options.multipleValues||(this.selectedValues={}),this.selectedValues[t.value]=!0,this.applySelectedValues()},removeSelected:function(t){var i=this.findWhere(function(l){return t.value===l.value});return i&&(i.selected=!1),delete this.selectedValues[t.value],i},isSelected:function(t){return!!this.selectedValues[t]},hasSelectedValues:function(){return this.getValues().length>0},each:function(t){var i=this;this.models&&n.each(this.models,function(l,o){o.items?n.each(o.items,function(a,r){t.call(i,r)}):t.call(i,o)})},where:function(t){var i=[];return this.each(function(l){t(l)&&i.push(l)}),i},findWhere:function(t){var i=this.where(t);return i.length?i[0]:void 0},filter:function(t){var i=this;function l(o){return i.options.matcher(o.text,t)?o:null}return!t||t.length===0?this.models:n.map(this.models,function(o){if(o.items){var a=n.map(o.items,l);return a.length?{label:o.label,items:a}:null}else return l(o)})},getValues:function(){return n.map(this.selectedValues,function(t,i){return t?i:null})}}),e.defaults={elementClass:"fstElement",singleModeClass:"fstSingleMode",noneSelectedClass:"fstNoneSelected",multipleModeClass:"fstMultipleMode",queryInputClass:"fstQueryInput",queryInputExpandedClass:"fstQueryInputExpanded",fakeInputClass:"fstFakeInput",controlsClass:"fstControls",toggleButtonClass:"fstToggleBtn",activeClass:"fstActive",itemSelectedClass:"fstSelected",choiceItemClass:"fstChoiceItem",choiceRemoveClass:"fstChoiceRemove",userOptionClass:"fstUserOption",resultsContClass:"fstResults",resultsOpenedClass:"fstResultsOpened",resultsFlippedClass:"fstResultsFilpped",groupClass:"fstGroup",itemClass:"fstResultItem",groupTitleClass:"fstGroupTitle",loadingClass:"fstLoading",noResultsClass:"fstNoResults",focusedItemClass:"fstFocused",matcher:null,url:null,loadOnce:!1,apiParam:"query",initialValue:null,clearQueryOnSelect:!0,minQueryLength:1,focusFirstItem:!1,flipOnBottom:!0,typeTimeout:150,userOptionAllowed:!1,valueDelimiter:",",maxItems:null,parseData:null,onItemSelect:null,onItemCreate:null,onMaxItemsReached:null,onLoad:null,placeholder:"Choose option",searchPlaceholder:"Search options",noResultsText:"No results",userOptionPrefix:"Add "},n.Fastselect=e,n.Fastselect.OptionsCollection=s,n.fn.fastselect=function(t){return this.each(function(){n.data(this,"fastselect")||n.data(this,"fastselect",new e(this,t))})},n})})(R);function x(){let f=m(".np-city-select").val(),n=m(".sat-city-select").val();m(".meest-city-select").val(),m(".region-select").fastselect({searchPlaceholder:translations.checkout_search_area,placeholder:translations.checkout_search_city,noResultsText:translations.checkout_search_city_not_found}),m(".np-city-select").fastselect({url:routes.delivery.np.cities,searchPlaceholder:translations.checkout_search_city,placeholder:translations.checkout_search_city,noResultsText:translations.checkout_search_city_not_found,loadOnce:!1,apiParam:"query",onItemSelect:function(h,c){c.hasOwnProperty("value")&&(f=c.value)}}),m(".np-department-select").fastselect({url:routes.delivery.np.cities,searchPlaceholder:translations.checkout_search_np_department,placeholder:translations.checkout_search_np_department,noResultsText:translations.checkout_search_city_not_found,loadOnce:!1,apiParam:"query",onItemSelect:function(h,c){},onLoad:function(){return routes.delivery.np.departments+"?cityRef="+f}}),m(".sat-city-select").fastselect({url:routes.delivery.sat.cities,searchPlaceholder:translations.checkout_search_city,placeholder:translations.checkout_search_city,noResultsText:translations.checkout_search_city_not_found,loadOnce:!1,apiParam:"query",onItemSelect:function(h,c){console.log(c),c.hasOwnProperty("value")&&(n=c.value)}}),m(".sat-department-select").fastselect({url:routes.delivery.sat.cities,searchPlaceholder:translations.checkout_search_np_department,placeholder:translations.checkout_search_np_department,noResultsText:translations.checkout_search_city_not_found,loadOnce:!1,apiParam:"query",onItemSelect:function(h,c){},onLoad:function(){return routes.delivery.sat.departments+"?cityRef="+n}})}export{x as init};