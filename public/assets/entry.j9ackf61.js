import{Er as a}from"/assets/entry.jjf5jc5d.js?v=50f150d24267cb17";import{Fr as S}from"/assets/entry.r3nyea1z.js?v=50f150d24267cb17";var t=S(a(),1);function u(e,i){let[n,s]=t.useState(()=>{try{let r=localStorage.getItem(e);if(r===null)return i;return JSON.parse(r)}catch(r){return i}});return t.useEffect(()=>{localStorage.setItem(e,JSON.stringify(n))},[e,n]),[n,s]}
export{u as vd};
