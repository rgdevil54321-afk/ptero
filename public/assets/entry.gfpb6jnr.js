var i=new Map,s=new Map,g=(e,n)=>{let t=i.get(e)??[];if(t.push(n),t.length>500)t.splice(0,t.length-500);i.set(e,t),s.get(e)?.forEach((o)=>o(n))},c=(e)=>[...i.get(e)??[]],r=(e,n)=>{let t=s.get(e)??new Set;return t.add(n),s.set(e,t),()=>{if(t.delete(n),t.size===0)s.delete(e)}};
export{g as xd,c as yd,r as zd};
