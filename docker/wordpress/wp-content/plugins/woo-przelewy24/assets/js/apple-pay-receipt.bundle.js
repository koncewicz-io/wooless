!function(){"use strict";class e{constructor(){this.reset(),this.debug=!1,this.regulation=!1,this.checkout="block"}setError(e){this.error=!0,this.errorMessage=e,this.debug&&console.log("Error:",e)}setCheckout(e){this.checkout=e}hasError(){return this.error}getError(){return this.errorMessage}reset(){this.error=!1,this.errorMessage=null}setRegulation(e){this.regulation=!!e}}class t extends e{constructor(){super(),this.paymentDetails={total:"0.00",currency:""},this.token=null,this.cancel=!1,this.paymentClient=null,this.paymentCanBeUsed=!1}reset(){super.reset(),this.cancel=!1,this.token=null}isCanceled(){return this.cancel}setCancel(e){this.cancel=e}setPaymentDetails(e,t){this.paymentDetails={total:parseFloat(e).toFixed(2).toString(),currency:t}}getPaymentData(){const{regulation:e}=this;return{payload:!!this.token&&btoa(this.token),regulation:e}}}const s=async(e,t,s=(()=>{}),i=!1)=>{let n=document.getElementById(e);if(n&&i&&(n.remove(),n=!1),!n){const i=document.createElement("script");return i.src=t,i.id=e,document.head.appendChild(i),await new Promise((e=>{i.addEventListener("load",(()=>e(s())))}))}return s(),!0};class i{constructor(e){const{mode:t="sandbox",debug:s=!1}=e;this.iframeContainer="card-whitelabel",this.debug=s,this.mode=t,this.promise={reject:()=>{},resolve:()=>{},redirect:""}}init(){document.addEventListener("Przelewy24CardWhileLabelHandlerReady",(()=>this.whiteLabelHandler()),{once:!0})}getWhiteLabelElement(){return document.getElementById(this.iframeContainer)}resetWhiteLabelIframe(){const e=this.getWhiteLabelElement();e&&(e.innerHTML="")}onNeedInteraction(){}onNoNeedInteraction(){const{redirect:e,resolve:t}=this.promise;t({success:!0,redirect:e})}onFailed(e){const{resolve:t}=this.promise;t({error:e?.message||"Something goes wrong"})}onSuccess(){}whiteLabelHandler(){this.debug&&console.log("Run whitelabel event handler"),Przelewy24CardWhileLabelHandler.config({P24TargetElementId:this.iframeContainer,P24CardWhiteLabelStartEventCallback:e=>{this.debug&&(console.log("Start on merchant site"),console.log(e))},P24NeedInteractionEventCallback:e=>{this.debug&&(console.log("Need interaction on merchant site"),console.log(e)),this.onNeedInteraction(e)},P24DontNeedInteractionEventCallback:e=>{this.debug&&(console.log("Don't need interaction on merchant site"),console.log(e)),this.onNoNeedInteraction(e)},P24ScriptFailedEventCallback:e=>{this.debug&&(console.log("Fail!"),console.log(e)),this.onFailed(e)},P24ScriptSuccessfulEndsEventCallback:e=>{this.debug&&(console.log("Success!"),console.log(e)),this.onSuccess(e)}}),Przelewy24CardWhileLabelHandler.main()}runObserver(){const e=this.getWhiteLabelElement();if(!e)return;new MutationObserver(((t,s)=>{for(const s of t)if("childList"===s.type){const t=e.querySelector("iframe");t&&(window.location=t.src)}})).observe(e,{childList:!0})}getIframeUrl(){const e=this.getWhiteLabelElement().querySelector("iframe");return e?.src||null}async load(e){this.resetWhiteLabelIframe(),await s("card-whitelabel-script",`https://${this.mode}.przelewy24.pl/whitelabel/card/javascript/${e}`,(()=>{}),!0)}async onResponse(e){return new Promise(((t,s)=>{const{error:i,token:n,redirect:r}=e;this.promise.resolve=t,this.promise.reject=s,this.promise.redirect=r,i?t({error:i}):n&&(this.debug&&console.log("Transaction token",n),this.load(n))}))}}class n extends t{constructor(e){super();const{config:t,mode:s="sandbox",debug:n=!1}=e,{appleMerchantId:r,merchantName:o,countryCode:a="PL",url:c}=t;this.debug=n,this.session=null,this.validateUrl=c,this.whiteLabel=new i(e),this.merchantId=r,this.merchantName=o,this.countryCode=a}async init(){await s("apple-pay","https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js",(()=>this.getClient()))}async canPayment(){if(this.paymentCanBeUsed)return!0;const e=this.getClient();if(!e)return!1;try{const t=await e.canMakePayments();return this.paymentCanBeUsed=t,t}catch(e){return!1}}setCountryCode(e){this.countryCode=e}getClient(){return null===this.paymentClient&&window.ApplePaySession&&(this.paymentClient=window.ApplePaySession),this.paymentClient}getPaymentRequest(){const{merchantName:e,countryCode:t,paymentDetails:s}=this,{total:i,currency:n}=s,r={countryCode:t,currencyCode:n,merchantCapabilities:["supports3DS","supportsCredit","supportsDebit"],supportedNetworks:["visa","masterCard","amex","discover"],total:{label:e,type:"final",amount:i}};return this.debug&&console.log("Base payment data",r),r}addListenersToSession(){this.session.onvalidatemerchant=e=>{this.validateMerchant()},this.session.onpaymentauthorized=e=>{this.token=e.payment.token,this.debug&&console.log("Result",this.token,e),this.resolve(!0)},this.session.oncancel=e=>{this.reject("CANCELED")}}complete(e){const t={status:e?ApplePaySession.STATUS_SUCCESS:ApplePaySession.STATUS_FAILURE};this.session.completePayment(t)}async validateMerchant(){try{let e=await fetch(this.validateUrl);const t=await e.json();t&&this.session.completeMerchantValidation(t)}catch(e){this.reject(e)}}async processPayment(e,t){this.reset();let s=this.getClient();try{await new Promise((async(e,t)=>{this.session=new s(14,this.getPaymentRequest()),this.reject=t,this.resolve=e,this.addListenersToSession(),this.session.begin()}))}catch(e){"CANCELED"===e?this.setCancel(!0):this.setError(e||null)}}}class r{constructor(e){this.parent=e,this.notice=document.createElement("div"),this.notice.classList.add("wc-block-components-notice-banner"),this.notice.classList.add("is-error")}show(e){e&&(e="object"==typeof e?e?.message:e,this.notice.innerHTML=e,this.parent.prepend(this.notice))}hide(){this.notice.remove()}}class o{constructor(){this.selectors={form:document.querySelector("form.p24-payment-container"),submitButton:document.getElementById("submit"),regulation:document.getElementById("regulation")},this.config=window._config,this.service=null,this.notice=new r(this.selectors.form),this.submitHandler(),this.regulationHandlers()}regulationHandlers(){const{regulation:e}=this.selectors;e&&e.addEventListener("change",(()=>{this.service.setRegulation(e.checked)}))}submitHandler(){const{form:e}=this.selectors;e.addEventListener("submit",(e=>{e.preventDefault(),this.notice.hide(),this.submit()}))}async submit(){}async submitData(){const e={type:"register-transaction",orderId:this.config.orderId,orderKey:this.config.orderKey,checkout:this.service.checkout,paymentDetails:this.service.getPaymentData()};let t=await fetch(this.config.url,{method:"POST",body:JSON.stringify(e)});const{data:s}=await t.json()||{data:{}};return s}}class a extends o{async canPayment(){const{submitButton:e}=this.selectors;this.enabled=await this.service.canPayment(),this.enabled?e.disabled=!1:this.notice.show(_config.i18n.error.unavailable)}async submit(){const{submitButton:e}=this.selectors;e.disabled=!0;try{const{total:t,currency:s}=_config.paymentDetails;if(this.service.setPaymentDetails(t,s),await this.service.processPayment(),this.service.hasError())throw new Error(this.service.getError());if(this.service.isCanceled())return void(e.disabled=!1);const i={type:"register-transaction",orderId:_config.orderId,orderKey:_config.orderKey,paymentDetails:this.service.getPaymentData()},{success:n,error:r,message:o,token:a}=await this.submitData(i);if(r&&o)throw new Error(o);n&&a&&await this.service.whiteLabel.load(a)}catch(e){this.notice.show(e)}e.disabled=!1}}class c extends a{constructor(){super(),this.service=new n(_config),this.service.setCheckout("legacy"),this.init()}async init(){await this.service.init(),await this.canPayment(),this.service.whiteLabel.init(),this.service.whiteLabel.runObserver()}}window.addEventListener("DOMContentLoaded",(()=>{window.p24ApplePayReceipt=new c}))}();
