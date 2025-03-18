(()=>{"use strict";const e=window.React,t=window.wp.htmlEntities,{i18n:n,data:s}=window.wp,{__}=n,{CheckboxControl:a}=window.wc.blocksCheckout,r=e=>{const{description:n}=e;return(0,t.decodeEntities)(n||"")},o=t=>{let{src:n,name:s=""}=t.icon;return"string"==typeof t.icon&&(n=t.icon),(0,e.createElement)("img",{style:{maxHeight:"20px"},src:n,title:s,alt:s,loading:"lazy"})},i=t=>{const{icons:n,additional:s}=t;return n.length?(0,e.createElement)("span",{style:{display:"flex",flexWrap:"wrap",gap:"6px",fontSize:"65%",alignItems:"center",justifyContent:"flex-end"}},n.map((({src:t,name:n},s)=>(0,e.createElement)(o,{icon:{src:t,name:n},key:s}))),s?(0,e.createElement)("span",{style:{lineHeight:"1.2"}},__("+%d other methods","woocommerce-p24").replace("%d",s)):""):""},c=({required:t=!1,label:n,checked:s,onChange:r,...o})=>(0,e.createElement)(a,{required:t,label:(0,e.createElement)("span",{dangerouslySetInnerHTML:{__html:n}}),checked:s,onChange:r,...o,__nextHasNoMarginBottom:!0});class l{constructor(){this.reset(),this.debug=!1,this.regulation=!1,this.checkout="block"}setError(e){this.error=!0,this.errorMessage=e,this.debug&&console.log("Error:",e)}setCheckout(e){this.checkout=e}hasError(){return this.error}getError(){return this.errorMessage}reset(){this.error=!1,this.errorMessage=null}setRegulation(e){this.regulation=!!e}}class d extends l{constructor(){super(),this.paymentDetails={total:"0.00",currency:""},this.token=null,this.cancel=!1,this.paymentClient=null,this.paymentCanBeUsed=!1}reset(){super.reset(),this.cancel=!1,this.token=null}isCanceled(){return this.cancel}setCancel(e){this.cancel=e}setPaymentDetails(e,t){this.paymentDetails={total:parseFloat(e).toFixed(2).toString(),currency:t}}getPaymentData(){const{regulation:e}=this;return{payload:!!this.token&&btoa(this.token),regulation:e}}}const h=async(e,t,n=(()=>{}),s=!1)=>{let a=document.getElementById(e);if(a&&s&&(a.remove(),a=!1),!a){const s=document.createElement("script");return s.src=t,s.id=e,document.head.appendChild(s),await new Promise((e=>{s.addEventListener("load",(()=>e(n())))}))}return n(),!0},u=class{constructor(e){const{mode:t="sandbox",debug:n=!1}=e;this.iframeContainer="card-whitelabel",this.debug=n,this.mode=t,this.promise={reject:()=>{},resolve:()=>{},redirect:""}}init(){document.addEventListener("Przelewy24CardWhileLabelHandlerReady",(()=>this.whiteLabelHandler()),{once:!0})}getWhiteLabelElement(){return document.getElementById(this.iframeContainer)}resetWhiteLabelIframe(){const e=this.getWhiteLabelElement();e&&(e.innerHTML="")}onNeedInteraction(){}onNoNeedInteraction(){const{redirect:e,resolve:t}=this.promise;t({success:!0,redirect:e})}onFailed(e){const{resolve:t}=this.promise;t({error:e?.message||"Something goes wrong"})}onSuccess(){}whiteLabelHandler(){this.debug&&console.log("Run whitelabel event handler"),Przelewy24CardWhileLabelHandler.config({P24TargetElementId:this.iframeContainer,P24CardWhiteLabelStartEventCallback:e=>{this.debug&&(console.log("Start on merchant site"),console.log(e))},P24NeedInteractionEventCallback:e=>{this.debug&&(console.log("Need interaction on merchant site"),console.log(e)),this.onNeedInteraction(e)},P24DontNeedInteractionEventCallback:e=>{this.debug&&(console.log("Don't need interaction on merchant site"),console.log(e)),this.onNoNeedInteraction(e)},P24ScriptFailedEventCallback:e=>{this.debug&&(console.log("Fail!"),console.log(e)),this.onFailed(e)},P24ScriptSuccessfulEndsEventCallback:e=>{this.debug&&(console.log("Success!"),console.log(e)),this.onSuccess(e)}}),Przelewy24CardWhileLabelHandler.main()}runObserver(){const e=this.getWhiteLabelElement();e&&new MutationObserver(((t,n)=>{for(const n of t)if("childList"===n.type){const t=e.querySelector("iframe");t&&(window.location=t.src)}})).observe(e,{childList:!0})}getIframeUrl(){const e=this.getWhiteLabelElement().querySelector("iframe");return e?.src||null}async load(e){this.resetWhiteLabelIframe(),await h("card-whitelabel-script",`https://${this.mode}.przelewy24.pl/whitelabel/card/javascript/${e}`,(()=>{}),!0)}async onResponse(e){return new Promise(((t,n)=>{const{error:s,token:a,redirect:r}=e;this.promise.resolve=t,this.promise.reject=n,this.promise.redirect=r,s?t({error:s}):a&&(this.debug&&console.log("Transaction token",a),this.load(a))}))}},{registerPaymentMethod:m}=window.wc.wcBlocksRegistry,{getSetting:p}=window.wc.wcSettings,{ValidationInputError:y}=window.wc.blocksCheckout,g=p("p24-apple-pay_data",{}),w=(0,t.decodeEntities)(g.title),C=g.i18n||{},b=new class extends d{constructor(e){super();const{config:t,mode:n="sandbox",debug:s=!1}=e,{appleMerchantId:a,merchantName:r,countryCode:o="PL",url:i}=t;this.debug=s,this.session=null,this.validateUrl=i,this.whiteLabel=new u(e),this.merchantId=a,this.merchantName=r,this.countryCode=o}async init(){await h("apple-pay","https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js",(()=>this.getClient()))}async canPayment(){if(this.paymentCanBeUsed)return!0;const e=this.getClient();if(!e)return!1;try{const t=await e.canMakePayments();return this.paymentCanBeUsed=t,t}catch(e){return!1}}setCountryCode(e){this.countryCode=e}getClient(){return null===this.paymentClient&&window.ApplePaySession&&(this.paymentClient=window.ApplePaySession),this.paymentClient}getPaymentRequest(){const{merchantName:e,countryCode:t,paymentDetails:n}=this,{total:s,currency:a}=n,r={countryCode:t,currencyCode:a,merchantCapabilities:["supports3DS","supportsCredit","supportsDebit"],supportedNetworks:["visa","masterCard","amex","discover"],total:{label:e,type:"final",amount:s}};return this.debug&&console.log("Base payment data",r),r}addListenersToSession(){this.session.onvalidatemerchant=e=>{this.validateMerchant()},this.session.onpaymentauthorized=e=>{this.token=e.payment.token,this.debug&&console.log("Result",this.token,e),this.resolve(!0)},this.session.oncancel=e=>{this.reject("CANCELED")}}complete(e){const t={status:e?ApplePaySession.STATUS_SUCCESS:ApplePaySession.STATUS_FAILURE};this.session.completePayment(t)}async validateMerchant(){try{let e=await fetch(this.validateUrl);const t=await e.json();t&&this.session.completeMerchantValidation(t)}catch(e){this.reject(e)}}async processPayment(e,t){this.reset();let n=this.getClient();try{await new Promise((async(e,t)=>{this.session=new n(14,this.getPaymentRequest()),this.reject=t,this.resolve=e,this.addListenersToSession(),this.session.begin()}))}catch(e){"CANCELED"===e?this.setCancel(!0):this.setError(e||null)}}}(g);m({name:g.name,label:(0,e.createElement)((t=>{const{PaymentMethodLabel:n}=t.components,{label:s,icon:a,icons:r,additional:c}=t;return(0,e.createElement)("span",{style:{width:"100%",display:"flex",gap:"12px",justifyContent:"space-between",alignItems:"center"}},(0,e.createElement)(n,{text:s}),r?(0,e.createElement)(i,{icons:r,additional:c}):"",a?(0,e.createElement)(o,{icon:a}):"")}),{label:w,icon:g.icon}),content:(0,e.createElement)((({eventRegistration:t,emitResponse:n})=>{const{responseTypes:s,noticeContexts:a}=n,{onPaymentSetup:o,onCheckoutSuccess:i,onCheckoutFail:l}=t,[d,h,u,m]=function(t,n){const[s,a]=(0,e.useState)(""),[r,o]=(0,e.useState)(!1),[i,c]=(0,e.useState)(!1),[l,d]=(0,e.useState)(!0);return(0,e.useEffect)((()=>{i&&a(l&&!r?n:"")}),[r,l,i]),[r,s,e=>{i||c(!0),t&&t.setRegulation(e),o(e)},(e=!0)=>{let t=!1;return e&&!r?(a(n),t=!1):(e&&r||!e)&&(a(""),t=!0),t},e=>{e||a(""),d(e)}]}(b,C.error.rules);return(0,e.useEffect)((()=>{b.whiteLabel.init(),b.whiteLabel.runObserver()}),[]),(0,e.useEffect)((()=>{const e=o((async()=>{const e={type:s.ERROR};if(!m())return e;const t=(()=>{const{data:e}=window.wp,{CART_STORE_KEY:t}=window.wc.wcBlocksData,n=e.select(t).getCartTotals(),s=e.select(t).getCartData();return{total:Number(n.total_price)/100,currency:n.currency_code,country:s.billingAddress?.country||"PL"}})();return b.setPaymentDetails(t.total,t.currency),b.setCountryCode(t.country),await b.processPayment(),b.hasError()||b.isCanceled()?e.message=b.getError():(e.type=s.SUCCESS,e.meta={paymentMethodData:b.getPaymentData()}),e}));return()=>{e()}}),[s.ERROR,s.SUCCESS,o,d,h]),(0,e.useEffect)((()=>{const e=i((async({processingResponse:e})=>{const t={type:s.ERROR};b.complete(response.success);const{success:n,error:r,redirect:o}=await b.whiteLabel.onResponse(e?.paymentDetails||{});return n&&o&&(t.redirectUrl=o,t.type=s.SUCCESS),r&&(t.message=r,t.messageContext=a.PAYMENTS),t})),t=l((({processingResponse:e})=>{const{message:t}=e?.paymentDetails||{},n={type:s.ERROR};return t&&(n.message=t,n.messageContext=a.PAYMENTS),n}));return()=>{e(),t()}}),[s.ERROR,s.SUCCESS,i,l]),(0,e.createElement)("div",{className:"p24-payment-container"},g.description&&(0,e.createElement)(r,{description:g.description}),(0,e.createElement)("div",{id:"card-whitelabel"}),(0,e.createElement)("div",{className:"p24-checkbox"},(0,e.createElement)(c,{required:!0,label:C.label.regulation,checked:d,onChange:u}),h?(0,e.createElement)(y,{errorMessage:h}):""))}),null),edit:(0,e.createElement)(r,null),canMakePayment:async()=>(await b.init(),await b.canPayment()),ariaLabel:w,placeOrderButtonLabel:C.label.submit,supports:{features:g.supports}})})();