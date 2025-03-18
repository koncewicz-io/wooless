!function(){"use strict";class e{constructor(){this.reset(),this.debug=!1,this.regulation=!1,this.checkout="block"}setError(e){this.error=!0,this.errorMessage=e,this.debug&&console.log("Error:",e)}setCheckout(e){this.checkout=e}hasError(){return this.error}getError(){return this.errorMessage}reset(){this.error=!1,this.errorMessage=null}setRegulation(e){this.regulation=!!e}}class t extends e{constructor(e){super();const{url:t,refreshTime:s=1e3,totalWaitTime:i=45e3,i18n:r,orderId:o=null,orderKey:c=null,debug:n=!1}=e;this.debug=n,this.url=t,this.refreshTime=s,this.totalWaitTime=i,this.currentWaitingTime=0,this.i18n=r||{},this.orderId=o,this.orderKey=c,this.abort=!0,this.timer=null,this.timeout=null,this.oneClick=!1,this.saveOneClick=!1,this.code=""}setCode(e){this.code=e}format(e){return(e=e.replace(/\D/g,"")).length>=4&&(e=e.replace(/^(.{3})/g,"$1 ")),e.slice(0,7)}setOneClick(e){this.oneClick=e||!1}setSaveOneClick(e){this.saveOneClick=e}codeValidation(e){return 6===(e||this.code).replace(/\D/g,"").length}longPooling(e=!1){this.currentWaitingTime=0,this.forAlias=e;const t=new Promise(((e,t)=>{this.resolve=e,this.reject=t}));return this.abort=!1,this.checkStatus(!0),t}cancelAliasWaiting(){this.resolve&&this.resolve({success:!0})}wait(){return this.timer=this.totalWaitTime/1e3,new Promise((e=>{this.timeout=setTimeout(e,this.refreshTime)}))}async checkStatus(e=!1){const{error:t}=this.i18n;e||(this.currentWaitingTime+=this.refreshTime,this.timer--);const s={type:this.forAlias?"get-alias-status":"get-order-status",checkout:this.checkout};this.orderId&&(s.orderId=this.orderId),this.orderKey&&(s.orderKey=this.orderKey);let i=await fetch(this.url,{method:"POST",body:JSON.stringify(s)});if(200===i.status)try{if(i=await i.json(),i?.data?.completed)this.resolve({success:!0,redirect:i?.data?.redirect});else if(this.currentWaitingTime<this.totalWaitTime){if(this.abort)return;await this.wait(),await this.checkStatus()}else this.currentWaitingTime>=this.totalWaitTime?(this.forAlias?this.resolve({success:!0,redirect:i?.data?.redirect}):this.resolve({error:!0,message:t.timeout}),this.resolve({error:!0,message:t.timeout})):this.resolve({error:!0,message:t.unexpected})}catch(e){this.resolve({error:!0,message:t.unexpected})}else this.reject({error:!0,message:t.unexpected})}cleanup(){const{error:e}=this.i18n;clearTimeout(this.timeout),this.timer=null,this.oneClick=!1,this.saveOneClick=!1,this.code="",this.timeout=null,this.abort=!0,this.resolve&&this.resolve({error:!0,message:e.aborted})}getPaymentData(){let e="code";return this.oneClick&&(e="one-click"),{type:e,regulation:this.regulation,checkout:this.checkout,code:this.code.replace(/\D/g,""),oneClick:!!this.oneClick&&this.oneClick.toString(),save:this.saveOneClick}}}class s{constructor(e){this.parent=e,this.notice=document.createElement("div"),this.notice.classList.add("wc-block-components-notice-banner"),this.notice.classList.add("is-error")}show(e){e&&(e="object"==typeof e?e?.message:e,this.notice.innerHTML=e,this.parent.prepend(this.notice))}hide(){this.notice.remove()}}class i{constructor(){this.selectors={form:document.querySelector("form.p24-payment-container"),submitButton:document.getElementById("submit"),regulation:document.getElementById("regulation")},this.config=window._config,this.service=null,this.notice=new s(this.selectors.form),this.submitHandler(),this.regulationHandlers()}regulationHandlers(){const{regulation:e}=this.selectors;e&&e.addEventListener("change",(()=>{this.service.setRegulation(e.checked)}))}submitHandler(){const{form:e}=this.selectors;e.addEventListener("submit",(e=>{e.preventDefault(),this.notice.hide(),this.submit()}))}async submit(){}async submitData(){const e={type:"register-transaction",orderId:this.config.orderId,orderKey:this.config.orderKey,checkout:this.service.checkout,paymentDetails:this.service.getPaymentData()};let t=await fetch(this.config.url,{method:"POST",body:JSON.stringify(e)});const{data:s}=await t.json()||{data:{}};return s}}class r extends i{constructor(){super(),this.selectors={...this.selectors,saveOneClick:document.getElementById("save-one-click"),oneClickItems:document.querySelectorAll(".woocommerce-order-pay .p24-1click")},this.oneClickHandlers()}oneClickHandlers(){const{saveOneClick:e,oneClickItems:t}=this.selectors;e&&e.addEventListener("change",(()=>{this.service.setSaveOneClick(e.checked)})),t.length&&t.forEach((s=>s.addEventListener("click",(()=>{const{id:i}=s.dataset,{oneClick:r}=this.service;t.forEach((e=>e.classList.remove("p24-1click--active"))),console.log(r,i),r===i?(this.service.setOneClick(!1),this.service.setSaveOneClick(!1),e.checked=!1):(s.classList.add("p24-1click--active"),this.service.setOneClick(i))}))))}}class o extends r{constructor(){super(),this.selectors={...this.selectors,input:document.getElementById("blik-code"),waitingStatus:document.getElementById("waiting-status")},this.service=new t(_config),this.service.setCheckout("legacy"),this.inputListener()}inputListener(){const{input:e}=this.selectors;e&&e.addEventListener("input",(t=>{const s=this.service.format(e.value);e.value=s;this.service.codeValidation(s)?e.setCustomValidity(""):e.setCustomValidity(_config.i18n.error.validation),this.service.setCode(s)}))}async submit(){const{waitingStatus:e,submitButton:t,input:s}=this.selectors;if(s.checkValidity()){t.disabled=!0,e.classList.remove("hidden");try{const t=await this.submitData();if(t.error&&t.message)throw new Error(t.message);const{success:s,error:i,message:r,redirect:o}=await this.service.longPooling().then((async t=>{const{error:s,message:i}=t;if(s&&i)throw new Error(i);return this.service.saveOneClick?(e.classList.add("hidden"),await this.service.longPooling(!0)):t}));if(i&&r)throw new Error(r);s&&o&&(window.location=o)}catch(e){this.notice.show(e)}}s.reportValidity(),e.classList.add("hidden"),t.disabled=!1}}window.addEventListener("DOMContentLoaded",(()=>{window.p24BlikReceipt=new o}))}();
