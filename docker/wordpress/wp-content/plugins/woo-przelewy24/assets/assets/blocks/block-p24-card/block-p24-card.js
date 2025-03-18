(()=>{"use strict";const e=window.React,t=window.wp.htmlEntities,{i18n:n,data:i}=window.wp,{__}=n,{CheckboxControl:s}=window.wc.blocksCheckout,r=e=>{const{description:n}=e;return(0,t.decodeEntities)(n||"")},a=t=>{let{src:n,name:i=""}=t.icon;return"string"==typeof t.icon&&(n=t.icon),(0,e.createElement)("img",{style:{maxHeight:"20px"},src:n,title:i,alt:i,loading:"lazy"})},o=t=>{const{icons:n,additional:i}=t;return n.length?(0,e.createElement)("span",{style:{display:"flex",flexWrap:"wrap",gap:"6px",fontSize:"65%",alignItems:"center",justifyContent:"flex-end"}},n.map((({src:t,name:n},i)=>(0,e.createElement)(a,{icon:{src:t,name:n},key:i}))),i?(0,e.createElement)("span",{style:{lineHeight:"1.2"}},__("+%d other methods","woocommerce-p24").replace("%d",i)):""):""},c=({required:t=!1,label:n,checked:i,onChange:r,...a})=>(0,e.createElement)(s,{required:t,label:(0,e.createElement)("span",{dangerouslySetInnerHTML:{__html:n}}),checked:i,onChange:r,...a,__nextHasNoMarginBottom:!0});class l{constructor(){this.reset(),this.debug=!1,this.regulation=!1,this.checkout="block"}setError(e){this.error=!0,this.errorMessage=e,this.debug&&console.log("Error:",e)}setCheckout(e){this.checkout=e}hasError(){return this.error}getError(){return this.errorMessage}reset(){this.error=!1,this.errorMessage=null}setRegulation(e){this.regulation=!!e}}const d=async(e,t,n=(()=>{}),i=!1)=>{let s=document.getElementById(e);if(s&&i&&(s.remove(),s=!1),!s){const i=document.createElement("script");return i.src=t,i.id=e,document.head.appendChild(i),await new Promise((e=>{i.addEventListener("load",(()=>e(n())))}))}return n(),!0},h=class{constructor(e){const{mode:t="sandbox",debug:n=!1}=e;this.iframeContainer="card-whitelabel",this.debug=n,this.mode=t,this.promise={reject:()=>{},resolve:()=>{},redirect:""}}init(){document.addEventListener("Przelewy24CardWhileLabelHandlerReady",(()=>this.whiteLabelHandler()),{once:!0})}getWhiteLabelElement(){return document.getElementById(this.iframeContainer)}resetWhiteLabelIframe(){const e=this.getWhiteLabelElement();e&&(e.innerHTML="")}onNeedInteraction(){}onNoNeedInteraction(){const{redirect:e,resolve:t}=this.promise;t({success:!0,redirect:e})}onFailed(e){const{resolve:t}=this.promise;t({error:e?.message||"Something goes wrong"})}onSuccess(){}whiteLabelHandler(){this.debug&&console.log("Run whitelabel event handler"),Przelewy24CardWhileLabelHandler.config({P24TargetElementId:this.iframeContainer,P24CardWhiteLabelStartEventCallback:e=>{this.debug&&(console.log("Start on merchant site"),console.log(e))},P24NeedInteractionEventCallback:e=>{this.debug&&(console.log("Need interaction on merchant site"),console.log(e)),this.onNeedInteraction(e)},P24DontNeedInteractionEventCallback:e=>{this.debug&&(console.log("Don't need interaction on merchant site"),console.log(e)),this.onNoNeedInteraction(e)},P24ScriptFailedEventCallback:e=>{this.debug&&(console.log("Fail!"),console.log(e)),this.onFailed(e)},P24ScriptSuccessfulEndsEventCallback:e=>{this.debug&&(console.log("Success!"),console.log(e)),this.onSuccess(e)}}),Przelewy24CardWhileLabelHandler.main()}runObserver(){const e=this.getWhiteLabelElement();e&&new MutationObserver(((t,n)=>{for(const n of t)if("childList"===n.type){const t=e.querySelector("iframe");t&&(window.location=t.src)}})).observe(e,{childList:!0})}getIframeUrl(){const e=this.getWhiteLabelElement().querySelector("iframe");return e?.src||null}async load(e){this.resetWhiteLabelIframe(),await d("card-whitelabel-script",`https://${this.mode}.przelewy24.pl/whitelabel/card/javascript/${e}`,(()=>{}),!0)}async onResponse(e){return new Promise(((t,n)=>{const{error:i,token:s,redirect:r}=e;this.promise.resolve=t,this.promise.reject=n,this.promise.redirect=r,i?t({error:i}):s&&(this.debug&&console.log("Transaction token",s),this.load(s))}))}},{registerPaymentMethod:m}=window.wc.wcBlocksRegistry,{getSetting:u}=window.wc.wcSettings,{CheckboxControl:g,ValidationInputError:p}=window.wc.blocksCheckout,{useSelect:k}=window.wp.data,E=u("p24-card_data",{}),b=(0,t.decodeEntities)(E.title),y=E.i18n||{},C=new class extends l{constructor(e){super();const{config:t,mode:n="sandbox",lang:i="pl",clickToPay:s,recurring:r=!1,debug:a=!1}=e,{merchant_id:o,session_id:c,signature:l,options:d}=t;this.debug=a,this.tokenizerElement="card-tokenizer",this.merchantId=o,this.sessionId=c,this.signature=l,this.recurring=r,this.mode=n,this.clickToPay=s,this.whiteLabel=new h(e),this.tokenizer=null,this.error=null,this.cardData=null,this.oneClick=!1,this.saveOneClick=!1,this.prepareOptions(d,i)}prepareOptions(e,t){if("string"==typeof e)try{this.options=JSON.parse(e)}catch(e){this.options={}}else this.options=e;this.options.lang=t,this.options.c2p=!1,this.options?.agreement?this.options.agreement.TOSLanguage=t:this.agreement={contentEnabled:{enabled:!1,checkboxEnabled:!1},TOSLanguage:t},this.clickToPay?.enabled&&(this.options.c2p=!0,this.clickToPay?.email&&this.setClickToPayEmail(this.clickToPay.email))}setClickToPayEmail(e){this.options.c2p&&(this.options.psu={email:e})}setRecurring(e){this.recurring=e}setSaveOneClick(e){this.saveOneClick=e}setOneClick(e){this.oneClick=e||!1}async prepareData(){return this.error=null,this.cardData=null,!!this.oneClick||await this.tokenize()}async tokenize(){const e=this.saveOneClick||this.recurring?"permanent":"temporary";this.debug&&console.log("Tokenize mode",e);const t=await this.tokenizer.tokenize(e),{errors:n=[],data:i}=t?.data||{};if(n.length){const[e]=n;this.error=e}else i&&(this.cardData=i);return!!i}getError(){return this.error}loadTokenizer(){d("card-tokenizer-script",`https://${this.mode}.przelewy24.pl/js/cardTokenizationIframe.min.js`,(()=>this.onLoadTokenizer()))}onLoadTokenizer(){this.tokenizer=new Przelewy24CardTokenization(this.merchantId,this.sessionId,this.signature),this.renderTokenizer()}renderTokenizer(){this.tokenizer.render("form",`#${this.tokenizerElement}`,this.options)}reRenderTokenizer(){document.getElementById(this.tokenizerElement).innerHTML="",this.loadTokenizer(),this.whiteLabel.init(),this.whiteLabel.runObserver()}getPaymentData(){let e="card-data";return this.oneClick&&(e="one-click"),{type:e,regulation:this.regulation,checkout:this.checkout,...Object.entries(this.cardData||{}).reduce(((e,[t,n])=>({...e,[t]:""+(n||"")})),{}),oneClick:!!this.oneClick&&this.oneClick.toString(),save:this.saveOneClick}}}(E),w=t=>{const{onChange:n,options:i,value:s}=t;return i.length?(0,e.createElement)("div",{className:"p24-1clicks"},(0,e.createElement)("div",{className:"p24-1clicks__label"},y.use_saved),(0,e.createElement)("div",{className:"p24-1clicks__items"},i.map(((t,i)=>{let r="p24-1click p24-1click--card";return s===t.id&&(r+=" p24-1click--active"),(0,e.createElement)("button",{key:i,className:r,type:"button",onClick:()=>{return e=t.id,void n(s===e?null:e);var e}},t?.logo&&(0,e.createElement)("figure",{className:"p24-1click__logo p24-1click--card__logo"},(0,e.createElement)("img",{src:t.logo.url,alt:t.logo.alt})),(0,e.createElement)("span",{className:"p24-1click--card__number"},(0,e.createElement)("small",null,"✱✱✱✱ ✱✱✱✱ ✱✱✱✱")," ",t.last_digits),(0,e.createElement)("span",{className:"p24-1click--card__valid"},t.valid_to))}))),(0,e.createElement)("div",{className:"p24-1clicks__or"},y.or)):""};m({name:E.name,label:(0,e.createElement)((t=>{const{PaymentMethodLabel:n}=t.components,{label:i,icon:s,icons:r,additional:c}=t;return(0,e.createElement)("span",{style:{width:"100%",display:"flex",gap:"12px",justifyContent:"space-between",alignItems:"center"}},(0,e.createElement)(n,{text:i}),r?(0,e.createElement)(o,{icons:r,additional:c}):"",s?(0,e.createElement)(a,{icon:s}):"")}),{label:b,icon:E.icon}),content:(0,e.createElement)((({eventRegistration:t,emitResponse:n})=>{const{responseTypes:i,noticeContexts:s}=n,{onPaymentSetup:a,onCheckoutSuccess:o,onCheckoutFail:l}=t,[d,h]=(0,e.useState)(!1),[m,u]=(0,e.useState)(!1),[b,v,S,f]=function(t,n){const[i,s]=(0,e.useState)(""),[r,a]=(0,e.useState)(!1),[o,c]=(0,e.useState)(!1),[l,d]=(0,e.useState)(!0);return(0,e.useEffect)((()=>{o&&s(l&&!r?n:"")}),[r,l,o]),[r,i,e=>{o||c(!0),t&&t.setRegulation(e),a(e)},(e=!0)=>{let t=!1;return e&&!r?(s(n),t=!1):(e&&r||!e)&&(s(""),t=!0),t},e=>{e||s(""),d(e)}]}(C,y.error.rules),z=E.clickToPay?.enabled?k((e=>e("wc/store/cart").getCustomerData()?.billingAddress?.email)):null;return(0,e.useEffect)((()=>{E.clickToPay?.enabled&&(z&&C.setClickToPayEmail(z),document.addEventListener("change",(e=>{"email"===e.target.id&&(C.setClickToPayEmail(e.target.value),C.reRenderTokenizer())}))),C.loadTokenizer(),C.whiteLabel.init(),C.whiteLabel.runObserver()}),[]),(0,e.useEffect)((()=>{C.setSaveOneClick(d)}),[d]),(0,e.useEffect)((()=>{C.setOneClick(m),m&&h(!1)}),[m]),(0,e.useEffect)((()=>{const e=a((async()=>{const e={type:i.ERROR};return f()?(await C.prepareData(),C.hasError()?e.message=C.getError():(e.type=i.SUCCESS,e.meta={paymentMethodData:C.getPaymentData()}),e):e}));return()=>{e()}}),[i.ERROR,i.SUCCESS,a,m,d,b,v]),(0,e.useEffect)((()=>{const e=o((async({processingResponse:e})=>{const t={type:i.ERROR},n=await C.whiteLabel.onResponse(e?.paymentDetails||{});return n.success&&n.redirect&&(t.type=i.SUCCESS,t.redirectUrl=n.redirect),n.error&&n.message&&(t.message=n.message,t.messageContext=s.PAYMENTS),t})),t=l((({processingResponse:e})=>{const{message:t}=e?.paymentDetails||{},n={type:i.ERROR};return t&&(n.message=t,n.messageContext=s.PAYMENTS),n}));return()=>{e(),t()}}),[i.ERROR,i.SUCCESS,o,l]),(0,e.createElement)("div",{className:"p24-payment-container"},E.description&&(0,e.createElement)(r,{description:E.description}),E.oneClick.enabled&&(0,e.createElement)(w,{value:m,onChange:u,options:E.oneClick.items}),(0,e.createElement)("div",{id:"card-tokenizer"}),(0,e.createElement)("div",{id:"card-whitelabel"}),E.oneClick.enabled&&(0,e.createElement)("div",{className:"p24-checkbox"},(0,e.createElement)(g,{disabled:!!m,label:y.label.save,checked:d,onChange:h})),(0,e.createElement)("div",{className:"p24-checkbox"},(0,e.createElement)(c,{required:!0,label:y.label.regulation,checked:b,onChange:S}),v?(0,e.createElement)(p,{errorMessage:v}):""))}),null),edit:(0,e.createElement)(r,{description:E.description}),canMakePayment:()=>!0,ariaLabel:b,supports:{features:E.supports}})})();