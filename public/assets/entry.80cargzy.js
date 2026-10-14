import{Uf as q}from"/assets/entry.sc6hj0mk.js?v=50f150d24267cb17";import{mi as j}from"/assets/entry.28c27r1s.js?v=50f150d24267cb17";var F=async(x,z,A)=>{let{data:c}=await j.put(`/api/client/servers/${x}/startup/variable`,{key:z,value:A});return[q(c),c.meta.startup_command]};
export{F as Xc};
