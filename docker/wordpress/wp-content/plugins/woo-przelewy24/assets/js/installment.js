!function(){"use strict";class t{constructor(t){this.config=t,t.show&&window.InstallmentCalculatorApp&&this.init()}async init(){const{config:t,widgetType:n,showSimulator:e}=this.config,a=new window.InstallmentCalculatorApp(t);if((await a.create(`${n}-widget`)).render("p24_installment_widget"),e){const t=await a.create("calculator-modal"),n=document.getElementById("p24_installment_widget");t.render("p24_installment_modal"),n&&n.addEventListener("click",(()=>{const t=document.getElementById("installment-calculator-modal");t&&(t.style.display="block")}))}}}document.addEventListener("DOMContentLoaded",(async()=>{new t(window.p24InstallmentsData)}))}();
