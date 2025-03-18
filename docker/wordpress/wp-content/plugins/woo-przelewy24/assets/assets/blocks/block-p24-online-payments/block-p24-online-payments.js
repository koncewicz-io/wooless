(()=>{"use strict";const e=window.React,t=window.wp.htmlEntities,{i18n:n,data:a}=window.wp,{__}=n,{CheckboxControl:l}=window.wc.blocksCheckout,r=e=>{const{description:n}=e;return(0,t.decodeEntities)(n||"")},o=t=>{let{src:n,name:a=""}=t.icon;return"string"==typeof t.icon&&(n=t.icon),(0,e.createElement)("img",{style:{maxHeight:"20px"},src:n,title:a,alt:a,loading:"lazy"})},c=t=>{const{icons:n,additional:a}=t;return n.length?(0,e.createElement)("span",{style:{display:"flex",flexWrap:"wrap",gap:"6px",fontSize:"65%",alignItems:"center",justifyContent:"flex-end"}},n.map((({src:t,name:n},a)=>(0,e.createElement)(o,{icon:{src:t,name:n},key:a}))),a?(0,e.createElement)("span",{style:{lineHeight:"1.2"}},__("+%d other methods","woocommerce-p24").replace("%d",a)):""):""},s=({required:t=!1,label:n,checked:a,onChange:r,...o})=>(0,e.createElement)(l,{required:t,label:(0,e.createElement)("span",{dangerouslySetInnerHTML:{__html:n}}),checked:a,onChange:r,...o,__nextHasNoMarginBottom:!0}),i=t=>{const{PaymentMethodLabel:n}=t.components,{label:a,icon:l,icons:r,additional:s}=t;return(0,e.createElement)("span",{style:{width:"100%",display:"flex",gap:"12px",justifyContent:"space-between",alignItems:"center"}},(0,e.createElement)(n,{text:a}),r?(0,e.createElement)(c,{icons:r,additional:s}):"",l?(0,e.createElement)(o,{icon:l}):"")};function m(t,n){const[a,l]=(0,e.useState)(""),[r,o]=(0,e.useState)(!1),[c,s]=(0,e.useState)(!1),[i,m]=(0,e.useState)(!0);return(0,e.useEffect)((()=>{c&&l(i&&!r?n:"")}),[r,i,c]),[r,a,e=>{c||s(!0),t&&t.setRegulation(e),o(e)},(e=!0)=>{let t=!1;return e&&!r?(l(n),t=!1):(e&&r||!e)&&(l(""),t=!0),t},e=>{e||l(""),m(e)}]}const{registerPaymentMethod:d}=window.wc.wcBlocksRegistry,{getSetting:u}=window.wc.wcSettings,{ValidationInputError:p}=window.wc.blocksCheckout,E=u("p24-online-payments_data",{}),h=(0,t.decodeEntities)(E.title),g=E.i18n||{},y=({method:t,current:n=null,onSelect:a})=>{let l="p24-method-item";return t.id===n&&(l+=" p24-method-item--active"),(0,e.createElement)("div",{className:l,onClick:()=>{a(t.id===n?null:t.id)}},(0,e.createElement)("picture",null,(0,e.createElement)("img",{src:t.mobileImgUrl,alt:t.name})),(0,e.createElement)("span",null,t.name))},f=({eventRegistration:t,emitResponse:n})=>{const{responseTypes:a}=n,{onPaymentSetup:l}=t,[r,o,c,i]=m(null,g.error.rules);return(0,e.useEffect)((()=>{const e=l((()=>{const e={type:a.ERROR};return i()?(e.type=a.SUCCESS,e.meta={paymentMethodData:{regulation:r}},e):e}));return()=>{e()}}),[a.ERROR,a.SUCCESS,l,r,o]),(0,e.createElement)("div",{className:"p24-payment-container"},(0,e.createElement)("div",{className:"p24-checkbox"},(0,e.createElement)(s,{required:!0,label:g.label.regulation,checked:r,onChange:c}),o?(0,e.createElement)(p,{errorMessage:o}):""))};E.featured.length&&E.featured.forEach((t=>{d({name:`${E.name}-${t.id}`,label:(0,e.createElement)(i,{label:t.name,icon:t.mobileImgUrl}),content:(0,e.createElement)(f,null),edit:(0,e.createElement)(r,null),canMakePayment:()=>!0,ariaLabel:h,placeOrderButtonLabel:g.label.submit,supports:{features:E.supports}})})),d({name:E.name,label:E.info.show?(0,e.createElement)(i,{label:h,icons:E.info.icons,additional:E.info.additional}):(0,e.createElement)(i,{label:h}),content:(0,e.createElement)((({eventRegistration:t,emitResponse:n})=>{const{responseTypes:a,noticeContexts:l}=n,{onPaymentSetup:o,onCheckoutSuccess:c,onCheckoutFail:i}=t,[d,u]=(0,e.useState)(null),[h,f]=(0,e.useState)([]),[S,b]=(0,e.useState)([]),[w,C,k,R,x]=m(null,g.error.rules);return(0,e.useEffect)((()=>{const e=o((()=>{const e={type:a.ERROR};return R(d)?(e.type=a.SUCCESS,e.meta={paymentMethodData:{method:!!d&&d.toString(),regulation:w}},e):e}));return()=>{e()}}),[a.ERROR,a.SUCCESS,o,d,w,C]),(0,e.useEffect)((()=>{b(E.methods.items.filter((({featured:e})=>e))),f(E.methods.items.filter((({featured:e})=>!e)))}),[E]),(0,e.useEffect)((()=>{x(!!d)}),[d]),(0,e.createElement)("div",{className:"p24-payment-container"},E.description&&(0,e.createElement)(r,{description:E.description}),E.methods.show&&(0,e.createElement)("div",{className:"p24-methods"},S.length>0&&(0,e.createElement)("div",{className:"p24-methods__items p24-methods__items--featured"},S.map(((t,n)=>(0,e.createElement)(y,{key:n,onSelect:u,method:t,current:d})))),h.length>0&&(0,e.createElement)("div",{className:"p24-methods__items"},h.map(((t,n)=>(0,e.createElement)(y,{key:n,onSelect:u,method:t,current:d}))))),E.methods.show&&(0,e.createElement)("div",{className:"p24-checkbox"},(0,e.createElement)(s,{required:d,label:g.label.regulation,checked:w,onChange:k}),C?(0,e.createElement)(p,{errorMessage:C}):""))}),null),edit:(0,e.createElement)(r,{description:E.description}),canMakePayment:()=>!0,ariaLabel:h,paymentMethodId:E.name,placeOrderButtonLabel:g.label.submit,supports:{features:E.supports}})})();