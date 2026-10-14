function z(h,j=2){if(h<1)return"0 Bytes";j=Math.floor(Math.max(0,j));let k=Math.floor(Math.log(h)/Math.log(1024));return`${Number((h/Math.pow(1024,k)).toFixed(j))} ${["Bytes","KiB","MiB","GiB","TiB"][k]}`}function A(h){return/([a-f0-9:]+:+)+[a-f0-9]+/.test(h)?`[${h}]`:h}
export{z as Bf,A as Cf};
