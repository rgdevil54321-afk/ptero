import{iq as h}from"/assets/entry.vff0hng4.js?v=50f150d24267cb17";import{Cr as e,Dr as d}from"/assets/entry.3ck3c65s.js?v=50f150d24267cb17";import{Er as g}from"/assets/entry.jjf5jc5d.js?v=50f150d24267cb17";import{Fr as y}from"/assets/entry.r3nyea1z.js?v=50f150d24267cb17";var m=y(g(),1);var C=({checked:n,onChange:t,className:l,id:a,disabled:o,...c})=>{let r=m.default.useMemo(()=>a||`checkbox-${Math.random().toString(36).substr(2,9)}`,[a]);return d("div",{className:"relative flex items-center",children:[e("input",{type:"checkbox",checked:n,onChange:t,className:"sr-only",id:r,disabled:o,...c}),e("label",{htmlFor:r,className:`
                    inline-flex items-center justify-center min-w-[20px] min-h-[20px] rounded-lg border-2 
                    ${n?"bg-hyper-accent border-hyper-accent":"bg-transparent border-hyper-accent"}
                    ${o?"opacity-50 cursor-not-allowed":"cursor-pointer"}
                    transition-all duration-200
                    ${l||""}
                `,children:n&&e("svg",{className:"w-3 h-3",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24",style:{color:"var(--hyper-button-primary-text, var(--hyper-text-primary, #ffffff))"},children:e("path",{strokeLinecap:"round",strokeLinejoin:"round",strokeWidth:2,d:"M5 13l4 4L19 7"})})})]})},f=({name:n,value:t,className:l,id:a,disabled:o,...c})=>e(h,{name:n,children:({field:r,form:p})=>{if(!Array.isArray(r.value))return null;let s=(r.value||[]).includes(t),u=a||`checkbox-${n}-${t}`;return d("div",{className:"relative flex items-center",children:[e("input",{...c,id:u,type:"checkbox",disabled:o,checked:s,className:"sr-only",onClick:()=>p.setFieldTouched(r.name,!0),onChange:(b)=>{let i=new Set(r.value);i.has(t)?i.delete(t):i.add(t),r.onChange(b),p.setFieldValue(r.name,Array.from(i))}}),e("label",{htmlFor:u,className:`
                            inline-flex items-center justify-center min-w-[20px] min-h-[20px] rounded-lg border-2 
                            ${s?"bg-hyper-accent border-hyper-accent":"bg-transparent border-hyper-accent"}
                            ${o?"opacity-50 cursor-not-allowed":"cursor-pointer"}
                            transition-all duration-200
                            ${l||""}
                        `,children:s&&e("svg",{className:"w-3 h-3",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24",style:{color:"var(--hyper-button-primary-text, var(--hyper-text-primary, #ffffff))"},children:e("path",{strokeLinecap:"round",strokeLinejoin:"round",strokeWidth:2,d:"M5 13l4 4L19 7"})})})]})}}),F=f;
export{C as Sc,F as Tc};
