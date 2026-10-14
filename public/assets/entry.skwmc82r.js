import{Dc as q,Jc as x}from"/assets/entry.5dr1vx5m.js?v=50f150d24267cb17";import{mi as m}from"/assets/entry.28c27r1s.js?v=50f150d24267cb17";var D=async(b,g,j)=>{await m.post(`/api/client/servers/${b}/files/write`,j,{params:{file:g},headers:{"Content-Type":"text/plain"}}),x(b,g,j),q({serverUuid:b,file:g,content:j})};
export{D as lc};
