import{Ii as w}from"/assets/entry.xsv58d3x.js?v=50f150d24267cb17";var x=()=>{return w((b)=>b.flashes)},M=(b)=>{let{addFlash:q,clearFlashes:z,clearAndAddHttpError:B}=x();return{addFlash:(j)=>q({...j,key:b}),addError:(j,C)=>q({key:b,message:j,title:C,type:"error"}),clearFlashes:()=>z(b),clearAndAddHttpError:(j)=>B({key:b,error:j})}};var P=x;
export{M as qe,P as re};
