!function(){"use strict";window.przelewy24Subscriptions={selectors:{subscriptionsCancelButtons:document.querySelectorAll(".account-p24-subscriptions-table button[data-delete]")},params:window.przelewy24SubscriptionsParams||null,init(){this.params&&this.subscriptionsHandleDeleteButtons()},subscriptionsHandleDeleteButtons(){const{subscriptionsCancelButtons:t}=this.selectors;t.length&&[...t].forEach((t=>t.addEventListener("click",(e=>{e.preventDefault();const{id:n,nonce:s}=t.dataset,o=new FormData;o.append("nonce",s),o.append("id",parseInt(n)),fetch(this.params.url,{method:"POST",body:o}).then((t=>t.json())).then((()=>window.location.reload()))}))))}},document.addEventListener("DOMContentLoaded",(()=>{przelewy24Subscriptions.init()}))}();
