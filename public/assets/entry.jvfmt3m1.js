import{mi as t}from"/assets/entry.28c27r1s.js?v=50f150d24267cb17";var p=(r,s)=>{return new Promise((n,i)=>{t.get(`/api/client/servers/${r}/files/contents`,{params:{file:s},transformResponse:(e)=>e,responseType:"text"}).then(({data:e})=>n(e)).catch(i)})};
export{p as kc};
