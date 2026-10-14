import{jr as q}from"/assets/entry.dmqm9rx7.js?v=50f150d24267cb17";var w=()=>q((b)=>b.flashes),I=(b)=>{let{addFlash:j,clearFlashes:x,clearAndAddHttpError:z}=w();return{addFlash:(f)=>j({...f,key:b}),addError:(f,B)=>j({key:b,message:f,title:B,type:"error"}),clearFlashes:()=>x(b),clearAndAddHttpError:(f)=>z({key:b,error:f})}};var L=w;
export{I as nq,L as oq};
