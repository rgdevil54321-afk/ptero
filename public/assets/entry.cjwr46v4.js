import{Er as V8}from"/assets/entry.jjf5jc5d.js?v=50f150d24267cb17";import{Fr as X8}from"/assets/entry.r3nyea1z.js?v=50f150d24267cb17";var l=X8(V8(),1);/*!
 * Font Awesome Free 7.2.0 by @fontawesome - https://fontawesome.com
 * License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License)
 * Copyright 2026 Fonticons, Inc.
 */function $0(J,Z){(Z==null||Z>J.length)&&(Z=J.length);for(var q=0,Q=Array(Z);q<Z;q++)Q[q]=J[q];return Q}function m6(J){if(Array.isArray(J))return J}function l6(J){if(Array.isArray(J))return $0(J)}function c6(J,Z){if(!(J instanceof Z))throw TypeError("Cannot call a class as a function")}function Z1(J,Z){for(var q=0;q<Z.length;q++){var Q=Z[q];Q.enumerable=Q.enumerable||!1,Q.configurable=!0,"value"in Q&&(Q.writable=!0),Object.defineProperty(J,N1(Q.key),Q)}}function s6(J,Z,q){return Z&&Z1(J.prototype,Z),q&&Z1(J,q),Object.defineProperty(J,"prototype",{writable:!1}),J}function X0(J,Z){var q=typeof Symbol<"u"&&J[Symbol.iterator]||J["@@iterator"];if(!q){if(Array.isArray(J)||(q=d0(J))||Z&&J&&typeof J.length=="number"){q&&(J=q);var Q=0,K=function(){};return{s:K,n:function(){return Q>=J.length?{done:!0}:{done:!1,value:J[Q++]}},e:function(V){throw V},f:K}}throw TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}var B,z=!0,X=!1;return{s:function(){q=q.call(J)},n:function(){var V=q.next();return z=V.done,V},e:function(V){X=!0,B=V},f:function(){try{z||q.return==null||q.return()}finally{if(X)throw B}}}}function F(J,Z,q){return(Z=N1(Z))in J?Object.defineProperty(J,Z,{value:q,enumerable:!0,configurable:!0,writable:!0}):J[Z]=q,J}function n6(J){if(typeof Symbol<"u"&&J[Symbol.iterator]!=null||J["@@iterator"]!=null)return Array.from(J)}function i6(J,Z){var q=J==null?null:typeof Symbol<"u"&&J[Symbol.iterator]||J["@@iterator"];if(q!=null){var Q,K,B,z,X=[],V=!0,G=!1;try{if(B=(q=q.call(J)).next,Z===0){if(Object(q)!==q)return;V=!1}else for(;!(V=(Q=B.call(q)).done)&&(X.push(Q.value),X.length!==Z);V=!0);}catch(W){G=!0,K=W}finally{try{if(!V&&q.return!=null&&(z=q.return(),Object(z)!==z))return}finally{if(G)throw K}}return X}}function o6(){throw TypeError(`Invalid attempt to destructure non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function r6(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function q1(J,Z){var q=Object.keys(J);if(Object.getOwnPropertySymbols){var Q=Object.getOwnPropertySymbols(J);Z&&(Q=Q.filter(function(K){return Object.getOwnPropertyDescriptor(J,K).enumerable})),q.push.apply(q,Q)}return q}function H(J){for(var Z=1;Z<arguments.length;Z++){var q=arguments[Z]!=null?arguments[Z]:{};Z%2?q1(Object(q),!0).forEach(function(Q){F(J,Q,q[Q])}):Object.getOwnPropertyDescriptors?Object.defineProperties(J,Object.getOwnPropertyDescriptors(q)):q1(Object(q)).forEach(function(Q){Object.defineProperty(J,Q,Object.getOwnPropertyDescriptor(q,Q))})}return J}function w0(J,Z){return m6(J)||i6(J,Z)||d0(J,Z)||o6()}function P(J){return l6(J)||n6(J)||d0(J)||r6()}function t6(J,Z){if(typeof J!="object"||!J)return J;var q=J[Symbol.toPrimitive];if(q!==void 0){var Q=q.call(J,Z||"default");if(typeof Q!="object")return Q;throw TypeError("@@toPrimitive must return a primitive value.")}return(Z==="string"?String:Number)(J)}function N1(J){var Z=t6(J,"string");return typeof Z=="symbol"?Z:Z+""}function G0(J){return G0=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(Z){return typeof Z}:function(Z){return Z&&typeof Symbol=="function"&&Z.constructor===Symbol&&Z!==Symbol.prototype?"symbol":typeof Z},G0(J)}function d0(J,Z){if(J){if(typeof J=="string")return $0(J,Z);var q={}.toString.call(J).slice(8,-1);return q==="Object"&&J.constructor&&(q=J.constructor.name),q==="Map"||q==="Set"?Array.from(J):q==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(q)?$0(J,Z):void 0}}var Q1=function(){},m0={},E1={},g1=null,S1={mark:Q1,measure:Q1};try{if(typeof window<"u")m0=window;if(typeof document<"u")E1=document;if(typeof MutationObserver<"u")g1=MutationObserver;if(typeof performance<"u")S1=performance}catch(J){}var a6=m0.navigator||{},K1=a6.userAgent,B1=K1===void 0?"":K1,u=m0,L=E1,z1=g1,B0=S1,A8=!!u.document,g=!!L.documentElement&&!!L.head&&typeof L.addEventListener==="function"&&typeof L.createElement==="function",b1=~B1.indexOf("MSIE")||~B1.indexOf("Trident/"),R0,e6=/fa(k|kd|s|r|l|t|d|dr|dl|dt|b|slr|slpr|wsb|tl|ns|nds|es|gt|jr|jfr|jdr|usb|ufsb|udsb|cr|ss|sr|sl|st|sds|sdr|sdl|sdt)?[\-\ ]/,J7=/Font ?Awesome ?([567 ]*)(Solid|Regular|Light|Thin|Duotone|Brands|Free|Pro|Sharp Duotone|Sharp|Kit|Notdog Duo|Notdog|Chisel|Etch|Graphite|Thumbprint|Jelly Fill|Jelly Duo|Jelly|Utility|Utility Fill|Utility Duo|Slab Press|Slab|Whiteboard)?.*/i,y1={classic:{fa:"solid",fas:"solid","fa-solid":"solid",far:"regular","fa-regular":"regular",fal:"light","fa-light":"light",fat:"thin","fa-thin":"thin",fab:"brands","fa-brands":"brands"},duotone:{fa:"solid",fad:"solid","fa-solid":"solid","fa-duotone":"solid",fadr:"regular","fa-regular":"regular",fadl:"light","fa-light":"light",fadt:"thin","fa-thin":"thin"},sharp:{fa:"solid",fass:"solid","fa-solid":"solid",fasr:"regular","fa-regular":"regular",fasl:"light","fa-light":"light",fast:"thin","fa-thin":"thin"},"sharp-duotone":{fa:"solid",fasds:"solid","fa-solid":"solid",fasdr:"regular","fa-regular":"regular",fasdl:"light","fa-light":"light",fasdt:"thin","fa-thin":"thin"},slab:{"fa-regular":"regular",faslr:"regular"},"slab-press":{"fa-regular":"regular",faslpr:"regular"},thumbprint:{"fa-light":"light",fatl:"light"},whiteboard:{"fa-semibold":"semibold",fawsb:"semibold"},notdog:{"fa-solid":"solid",fans:"solid"},"notdog-duo":{"fa-solid":"solid",fands:"solid"},etch:{"fa-solid":"solid",faes:"solid"},graphite:{"fa-thin":"thin",fagt:"thin"},jelly:{"fa-regular":"regular",fajr:"regular"},"jelly-fill":{"fa-regular":"regular",fajfr:"regular"},"jelly-duo":{"fa-regular":"regular",fajdr:"regular"},chisel:{"fa-regular":"regular",facr:"regular"},utility:{"fa-semibold":"semibold",fausb:"semibold"},"utility-duo":{"fa-semibold":"semibold",faudsb:"semibold"},"utility-fill":{"fa-semibold":"semibold",faufsb:"semibold"}},Z7={GROUP:"duotone-group",SWAP_OPACITY:"swap-opacity",PRIMARY:"primary",SECONDARY:"secondary"},u1=["fa-classic","fa-duotone","fa-sharp","fa-sharp-duotone","fa-thumbprint","fa-whiteboard","fa-notdog","fa-notdog-duo","fa-chisel","fa-etch","fa-graphite","fa-jelly","fa-jelly-fill","fa-jelly-duo","fa-slab","fa-slab-press","fa-utility","fa-utility-duo","fa-utility-fill"],T="classic",J0="duotone",f1="sharp",_1="sharp-duotone",p1="chisel",d1="etch",m1="graphite",l1="jelly",c1="jelly-duo",s1="jelly-fill",n1="notdog",i1="notdog-duo",o1="slab",r1="slab-press",t1="thumbprint",a1="utility",e1="utility-duo",J6="utility-fill",Z6="whiteboard",q7="Classic",Q7="Duotone",K7="Sharp",B7="Sharp Duotone",z7="Chisel",X7="Etch",V7="Graphite",H7="Jelly",G7="Jelly Duo",Y7="Jelly Fill",W7="Notdog",w7="Notdog Duo",U7="Slab",j7="Slab Press",F7="Thumbprint",D7="Utility",M7="Utility Duo",R7="Utility Fill",L7="Whiteboard",q6=[T,J0,f1,_1,p1,d1,m1,l1,c1,s1,n1,i1,o1,r1,t1,a1,e1,J6,Z6],x8=(R0={},F(F(F(F(F(F(F(F(F(F(R0,T,q7),J0,Q7),f1,K7),_1,B7),p1,z7),d1,X7),m1,V7),l1,H7),c1,G7),s1,Y7),F(F(F(F(F(F(F(F(F(R0,n1,W7),i1,w7),o1,U7),r1,j7),t1,F7),a1,D7),e1,M7),J6,R7),Z6,L7)),v7={classic:{900:"fas",400:"far",normal:"far",300:"fal",100:"fat"},duotone:{900:"fad",400:"fadr",300:"fadl",100:"fadt"},sharp:{900:"fass",400:"fasr",300:"fasl",100:"fast"},"sharp-duotone":{900:"fasds",400:"fasdr",300:"fasdl",100:"fasdt"},slab:{400:"faslr"},"slab-press":{400:"faslpr"},whiteboard:{600:"fawsb"},thumbprint:{300:"fatl"},notdog:{900:"fans"},"notdog-duo":{900:"fands"},etch:{900:"faes"},graphite:{100:"fagt"},chisel:{400:"facr"},jelly:{400:"fajr"},"jelly-fill":{400:"fajfr"},"jelly-duo":{400:"fajdr"},utility:{600:"fausb"},"utility-duo":{600:"faudsb"},"utility-fill":{600:"faufsb"}},C7={"Font Awesome 7 Free":{900:"fas",400:"far"},"Font Awesome 7 Pro":{900:"fas",400:"far",normal:"far",300:"fal",100:"fat"},"Font Awesome 7 Brands":{400:"fab",normal:"fab"},"Font Awesome 7 Duotone":{900:"fad",400:"fadr",normal:"fadr",300:"fadl",100:"fadt"},"Font Awesome 7 Sharp":{900:"fass",400:"fasr",normal:"fasr",300:"fasl",100:"fast"},"Font Awesome 7 Sharp Duotone":{900:"fasds",400:"fasdr",normal:"fasdr",300:"fasdl",100:"fasdt"},"Font Awesome 7 Jelly":{400:"fajr",normal:"fajr"},"Font Awesome 7 Jelly Fill":{400:"fajfr",normal:"fajfr"},"Font Awesome 7 Jelly Duo":{400:"fajdr",normal:"fajdr"},"Font Awesome 7 Slab":{400:"faslr",normal:"faslr"},"Font Awesome 7 Slab Press":{400:"faslpr",normal:"faslpr"},"Font Awesome 7 Thumbprint":{300:"fatl",normal:"fatl"},"Font Awesome 7 Notdog":{900:"fans",normal:"fans"},"Font Awesome 7 Notdog Duo":{900:"fands",normal:"fands"},"Font Awesome 7 Etch":{900:"faes",normal:"faes"},"Font Awesome 7 Graphite":{100:"fagt",normal:"fagt"},"Font Awesome 7 Chisel":{400:"facr",normal:"facr"},"Font Awesome 7 Whiteboard":{600:"fawsb",normal:"fawsb"},"Font Awesome 7 Utility":{600:"fausb",normal:"fausb"},"Font Awesome 7 Utility Duo":{600:"faudsb",normal:"faudsb"},"Font Awesome 7 Utility Fill":{600:"faufsb",normal:"faufsb"}},h7=new Map([["classic",{defaultShortPrefixId:"fas",defaultStyleId:"solid",styleIds:["solid","regular","light","thin","brands"],futureStyleIds:[],defaultFontWeight:900}],["duotone",{defaultShortPrefixId:"fad",defaultStyleId:"solid",styleIds:["solid","regular","light","thin"],futureStyleIds:[],defaultFontWeight:900}],["sharp",{defaultShortPrefixId:"fass",defaultStyleId:"solid",styleIds:["solid","regular","light","thin"],futureStyleIds:[],defaultFontWeight:900}],["sharp-duotone",{defaultShortPrefixId:"fasds",defaultStyleId:"solid",styleIds:["solid","regular","light","thin"],futureStyleIds:[],defaultFontWeight:900}],["chisel",{defaultShortPrefixId:"facr",defaultStyleId:"regular",styleIds:["regular"],futureStyleIds:[],defaultFontWeight:400}],["etch",{defaultShortPrefixId:"faes",defaultStyleId:"solid",styleIds:["solid"],futureStyleIds:[],defaultFontWeight:900}],["graphite",{defaultShortPrefixId:"fagt",defaultStyleId:"thin",styleIds:["thin"],futureStyleIds:[],defaultFontWeight:100}],["jelly",{defaultShortPrefixId:"fajr",defaultStyleId:"regular",styleIds:["regular"],futureStyleIds:[],defaultFontWeight:400}],["jelly-duo",{defaultShortPrefixId:"fajdr",defaultStyleId:"regular",styleIds:["regular"],futureStyleIds:[],defaultFontWeight:400}],["jelly-fill",{defaultShortPrefixId:"fajfr",defaultStyleId:"regular",styleIds:["regular"],futureStyleIds:[],defaultFontWeight:400}],["notdog",{defaultShortPrefixId:"fans",defaultStyleId:"solid",styleIds:["solid"],futureStyleIds:[],defaultFontWeight:900}],["notdog-duo",{defaultShortPrefixId:"fands",defaultStyleId:"solid",styleIds:["solid"],futureStyleIds:[],defaultFontWeight:900}],["slab",{defaultShortPrefixId:"faslr",defaultStyleId:"regular",styleIds:["regular"],futureStyleIds:[],defaultFontWeight:400}],["slab-press",{defaultShortPrefixId:"faslpr",defaultStyleId:"regular",styleIds:["regular"],futureStyleIds:[],defaultFontWeight:400}],["thumbprint",{defaultShortPrefixId:"fatl",defaultStyleId:"light",styleIds:["light"],futureStyleIds:[],defaultFontWeight:300}],["utility",{defaultShortPrefixId:"fausb",defaultStyleId:"semibold",styleIds:["semibold"],futureStyleIds:[],defaultFontWeight:600}],["utility-duo",{defaultShortPrefixId:"faudsb",defaultStyleId:"semibold",styleIds:["semibold"],futureStyleIds:[],defaultFontWeight:600}],["utility-fill",{defaultShortPrefixId:"faufsb",defaultStyleId:"semibold",styleIds:["semibold"],futureStyleIds:[],defaultFontWeight:600}],["whiteboard",{defaultShortPrefixId:"fawsb",defaultStyleId:"semibold",styleIds:["semibold"],futureStyleIds:[],defaultFontWeight:600}]]),O7={chisel:{regular:"facr"},classic:{brands:"fab",light:"fal",regular:"far",solid:"fas",thin:"fat"},duotone:{light:"fadl",regular:"fadr",solid:"fad",thin:"fadt"},etch:{solid:"faes"},graphite:{thin:"fagt"},jelly:{regular:"fajr"},"jelly-duo":{regular:"fajdr"},"jelly-fill":{regular:"fajfr"},notdog:{solid:"fans"},"notdog-duo":{solid:"fands"},sharp:{light:"fasl",regular:"fasr",solid:"fass",thin:"fast"},"sharp-duotone":{light:"fasdl",regular:"fasdr",solid:"fasds",thin:"fasdt"},slab:{regular:"faslr"},"slab-press":{regular:"faslpr"},thumbprint:{light:"fatl"},utility:{semibold:"fausb"},"utility-duo":{semibold:"faudsb"},"utility-fill":{semibold:"faufsb"},whiteboard:{semibold:"fawsb"}},Q6=["fak","fa-kit","fakd","fa-kit-duotone"],X1={kit:{fak:"kit","fa-kit":"kit"},"kit-duotone":{fakd:"kit-duotone","fa-kit-duotone":"kit-duotone"}},T7=["kit"],$7="kit",k7="kit-duotone",P7="Kit",A7="Kit Duotone",I8=F(F({},$7,P7),k7,A7),x7={kit:{"fa-kit":"fak"},"kit-duotone":{"fa-kit-duotone":"fakd"}},I7={"Font Awesome Kit":{400:"fak",normal:"fak"},"Font Awesome Kit Duotone":{400:"fakd",normal:"fakd"}},N7={kit:{fak:"fa-kit"},"kit-duotone":{fakd:"fa-kit-duotone"}},V1={kit:{kit:"fak"},"kit-duotone":{"kit-duotone":"fakd"}},L0,z0={GROUP:"duotone-group",SWAP_OPACITY:"swap-opacity",PRIMARY:"primary",SECONDARY:"secondary"},E7=["fa-classic","fa-duotone","fa-sharp","fa-sharp-duotone","fa-thumbprint","fa-whiteboard","fa-notdog","fa-notdog-duo","fa-chisel","fa-etch","fa-graphite","fa-jelly","fa-jelly-fill","fa-jelly-duo","fa-slab","fa-slab-press","fa-utility","fa-utility-duo","fa-utility-fill"],g7="classic",S7="duotone",b7="sharp",y7="sharp-duotone",u7="chisel",f7="etch",_7="graphite",p7="jelly",d7="jelly-duo",m7="jelly-fill",l7="notdog",c7="notdog-duo",s7="slab",n7="slab-press",i7="thumbprint",o7="utility",r7="utility-duo",t7="utility-fill",a7="whiteboard",e7="Classic",J9="Duotone",Z9="Sharp",q9="Sharp Duotone",Q9="Chisel",K9="Etch",B9="Graphite",z9="Jelly",X9="Jelly Duo",V9="Jelly Fill",H9="Notdog",G9="Notdog Duo",Y9="Slab",W9="Slab Press",w9="Thumbprint",U9="Utility",j9="Utility Duo",F9="Utility Fill",D9="Whiteboard",N8=(L0={},F(F(F(F(F(F(F(F(F(F(L0,g7,e7),S7,J9),b7,Z9),y7,q9),u7,Q9),f7,K9),_7,B9),p7,z9),d7,X9),m7,V9),F(F(F(F(F(F(F(F(F(L0,l7,H9),c7,G9),s7,Y9),n7,W9),i7,w9),o7,U9),r7,j9),t7,F9),a7,D9)),M9="kit",R9="kit-duotone",L9="Kit",v9="Kit Duotone",E8=F(F({},M9,L9),R9,v9),C9={classic:{"fa-brands":"fab","fa-duotone":"fad","fa-light":"fal","fa-regular":"far","fa-solid":"fas","fa-thin":"fat"},duotone:{"fa-regular":"fadr","fa-light":"fadl","fa-thin":"fadt"},sharp:{"fa-solid":"fass","fa-regular":"fasr","fa-light":"fasl","fa-thin":"fast"},"sharp-duotone":{"fa-solid":"fasds","fa-regular":"fasdr","fa-light":"fasdl","fa-thin":"fasdt"},slab:{"fa-regular":"faslr"},"slab-press":{"fa-regular":"faslpr"},whiteboard:{"fa-semibold":"fawsb"},thumbprint:{"fa-light":"fatl"},notdog:{"fa-solid":"fans"},"notdog-duo":{"fa-solid":"fands"},etch:{"fa-solid":"faes"},graphite:{"fa-thin":"fagt"},jelly:{"fa-regular":"fajr"},"jelly-fill":{"fa-regular":"fajfr"},"jelly-duo":{"fa-regular":"fajdr"},chisel:{"fa-regular":"facr"},utility:{"fa-semibold":"fausb"},"utility-duo":{"fa-semibold":"faudsb"},"utility-fill":{"fa-semibold":"faufsb"}},h9={classic:["fas","far","fal","fat","fad"],duotone:["fadr","fadl","fadt"],sharp:["fass","fasr","fasl","fast"],"sharp-duotone":["fasds","fasdr","fasdl","fasdt"],slab:["faslr"],"slab-press":["faslpr"],whiteboard:["fawsb"],thumbprint:["fatl"],notdog:["fans"],"notdog-duo":["fands"],etch:["faes"],graphite:["fagt"],jelly:["fajr"],"jelly-fill":["fajfr"],"jelly-duo":["fajdr"],chisel:["facr"],utility:["fausb"],"utility-duo":["faudsb"],"utility-fill":["faufsb"]},k0={classic:{fab:"fa-brands",fad:"fa-duotone",fal:"fa-light",far:"fa-regular",fas:"fa-solid",fat:"fa-thin"},duotone:{fadr:"fa-regular",fadl:"fa-light",fadt:"fa-thin"},sharp:{fass:"fa-solid",fasr:"fa-regular",fasl:"fa-light",fast:"fa-thin"},"sharp-duotone":{fasds:"fa-solid",fasdr:"fa-regular",fasdl:"fa-light",fasdt:"fa-thin"},slab:{faslr:"fa-regular"},"slab-press":{faslpr:"fa-regular"},whiteboard:{fawsb:"fa-semibold"},thumbprint:{fatl:"fa-light"},notdog:{fans:"fa-solid"},"notdog-duo":{fands:"fa-solid"},etch:{faes:"fa-solid"},graphite:{fagt:"fa-thin"},jelly:{fajr:"fa-regular"},"jelly-fill":{fajfr:"fa-regular"},"jelly-duo":{fajdr:"fa-regular"},chisel:{facr:"fa-regular"},utility:{fausb:"fa-semibold"},"utility-duo":{faudsb:"fa-semibold"},"utility-fill":{faufsb:"fa-semibold"}},O9=["fa-solid","fa-regular","fa-light","fa-thin","fa-duotone","fa-brands","fa-semibold"],K6=["fa","fas","far","fal","fat","fad","fadr","fadl","fadt","fab","fass","fasr","fasl","fast","fasds","fasdr","fasdl","fasdt","faslr","faslpr","fawsb","fatl","fans","fands","faes","fagt","fajr","fajfr","fajdr","facr","fausb","faudsb","faufsb"].concat(E7,O9),T9=["solid","regular","light","thin","duotone","brands","semibold"],B6=[1,2,3,4,5,6,7,8,9,10],$9=B6.concat([11,12,13,14,15,16,17,18,19,20]),k9=["aw","fw","pull-left","pull-right"],P9=[].concat(P(Object.keys(h9)),T9,k9,["2xs","xs","sm","lg","xl","2xl","beat","border","fade","beat-fade","bounce","flip-both","flip-horizontal","flip-vertical","flip","inverse","layers","layers-bottom-left","layers-bottom-right","layers-counter","layers-text","layers-top-left","layers-top-right","li","pull-end","pull-start","pulse","rotate-180","rotate-270","rotate-90","rotate-by","shake","spin-pulse","spin-reverse","spin","stack-1x","stack-2x","stack","ul","width-auto","width-fixed",z0.GROUP,z0.SWAP_OPACITY,z0.PRIMARY,z0.SECONDARY]).concat(B6.map(function(J){return"".concat(J,"x")})).concat($9.map(function(J){return"w-".concat(J)})),A9={"Font Awesome 5 Free":{900:"fas",400:"far"},"Font Awesome 5 Pro":{900:"fas",400:"far",normal:"far",300:"fal"},"Font Awesome 5 Brands":{400:"fab",normal:"fab"},"Font Awesome 5 Duotone":{900:"fad"}},N="___FONT_AWESOME___",P0=16,z6="fa",X6="svg-inline--fa",d="data-fa-i2svg",A0="data-fa-pseudo-element",x9="data-fa-pseudo-element-pending",l0="data-prefix",c0="data-icon",H1="fontawesome-i2svg",I9="async",N9=["HTML","HEAD","STYLE","SCRIPT"],V6=["::before","::after",":before",":after"],H6=function(){try{return!0}catch(J){return!1}}();function Z0(J){return new Proxy(J,{get:function(q,Q){return Q in q?q[Q]:q[T]}})}var G6=H({},y1);G6[T]=H(H(H(H({},{"fa-duotone":"duotone"}),y1[T]),X1.kit),X1["kit-duotone"]);var E9=Z0(G6),x0=H({},O7);x0[T]=H(H(H(H({},{duotone:"fad"}),x0[T]),V1.kit),V1["kit-duotone"]);var G1=Z0(x0),I0=H({},k0);I0[T]=H(H({},I0[T]),N7.kit);var s0=Z0(I0),N0=H({},C9);N0[T]=H(H({},N0[T]),x7.kit);var g8=Z0(N0),g9=e6,Y6="fa-layers-text",S9=J7,b9=H({},v7),S8=Z0(b9),y9=["class","data-prefix","data-icon","data-fa-transform","data-fa-mask"],v0=Z7,u9=[].concat(P(T7),P(P9)),t=u.FontAwesomeConfig||{};function f9(J){var Z=L.querySelector("script["+J+"]");if(Z)return Z.getAttribute(J)}function _9(J){if(J==="")return!0;if(J==="false")return!1;if(J==="true")return!0;return J}if(L&&typeof L.querySelector==="function")E0=[["data-family-prefix","familyPrefix"],["data-css-prefix","cssPrefix"],["data-family-default","familyDefault"],["data-style-default","styleDefault"],["data-replacement-class","replacementClass"],["data-auto-replace-svg","autoReplaceSvg"],["data-auto-add-css","autoAddCss"],["data-search-pseudo-elements","searchPseudoElements"],["data-search-pseudo-elements-warnings","searchPseudoElementsWarnings"],["data-search-pseudo-elements-full-scan","searchPseudoElementsFullScan"],["data-observe-mutations","observeMutations"],["data-mutate-approach","mutateApproach"],["data-keep-original-source","keepOriginalSource"],["data-measure-performance","measurePerformance"],["data-show-missing-icons","showMissingIcons"]],E0.forEach(function(J){var Z=w0(J,2),q=Z[0],Q=Z[1],K=_9(f9(q));if(K!==void 0&&K!==null)t[Q]=K});var E0,W6={styleDefault:"solid",familyDefault:T,cssPrefix:z6,replacementClass:X6,autoReplaceSvg:!0,autoAddCss:!0,searchPseudoElements:!1,searchPseudoElementsWarnings:!0,searchPseudoElementsFullScan:!1,observeMutations:!0,mutateApproach:"async",keepOriginalSource:!0,measurePerformance:!1,showMissingIcons:!0};if(t.familyPrefix)t.cssPrefix=t.familyPrefix;var n=H(H({},W6),t);if(!n.autoReplaceSvg)n.observeMutations=!1;var w={};Object.keys(W6).forEach(function(J){Object.defineProperty(w,J,{enumerable:!0,set:function(q){n[J]=q,a.forEach(function(Q){return Q(w)})},get:function(){return n[J]}})});Object.defineProperty(w,"familyPrefix",{enumerable:!0,set:function(Z){n.cssPrefix=Z,a.forEach(function(q){return q(w)})},get:function(){return n.cssPrefix}});u.FontAwesomeConfig=w;var a=[];function p9(J){return a.push(J),function(){a.splice(a.indexOf(J),1)}}var y=P0,A={size:16,x:0,y:0,rotate:0,flipX:!1,flipY:!1};function d9(J){if(!J||!g)return;var Z=L.createElement("style");Z.setAttribute("type","text/css"),Z.innerHTML=J;var q=L.head.childNodes,Q=null;for(var K=q.length-1;K>-1;K--){var B=q[K],z=(B.tagName||"").toUpperCase();if(["STYLE","LINK"].indexOf(z)>-1)Q=B}return L.head.insertBefore(Z,Q),J}var m9="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";function Y1(){var J=12,Z="";while(J-- >0)Z+=m9[Math.random()*62|0];return Z}function i(J){var Z=[];for(var q=(J||[]).length>>>0;q--;)Z[q]=J[q];return Z}function n0(J){if(J.classList)return i(J.classList);else return(J.getAttribute("class")||"").split(" ").filter(function(Z){return Z})}function w6(J){return"".concat(J).replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/'/g,"&#39;").replace(/</g,"&lt;").replace(/>/g,"&gt;")}function l9(J){return Object.keys(J||{}).reduce(function(Z,q){return Z+"".concat(q,'="').concat(w6(J[q]),'" ')},"").trim()}function U0(J){return Object.keys(J||{}).reduce(function(Z,q){return Z+"".concat(q,": ").concat(J[q].trim(),";")},"")}function i0(J){return J.size!==A.size||J.x!==A.x||J.y!==A.y||J.rotate!==A.rotate||J.flipX||J.flipY}function c9(J){var{transform:Z,containerWidth:q,iconWidth:Q}=J,K={transform:"translate(".concat(q/2," 256)")},B="translate(".concat(Z.x*32,", ").concat(Z.y*32,") "),z="scale(".concat(Z.size/16*(Z.flipX?-1:1),", ").concat(Z.size/16*(Z.flipY?-1:1),") "),X="rotate(".concat(Z.rotate," 0 0)"),V={transform:"".concat(B," ").concat(z," ").concat(X)},G={transform:"translate(".concat(Q/2*-1," -256)")};return{outer:K,inner:V,path:G}}function s9(J){var{transform:Z,width:q}=J,Q=q===void 0?P0:q,K=J.height,B=K===void 0?P0:K,z=J.startCentered,X=z===void 0?!1:z,V="";if(X&&b1)V+="translate(".concat(Z.x/y-Q/2,"em, ").concat(Z.y/y-B/2,"em) ");else if(X)V+="translate(calc(-50% + ".concat(Z.x/y,"em), calc(-50% + ").concat(Z.y/y,"em)) ");else V+="translate(".concat(Z.x/y,"em, ").concat(Z.y/y,"em) ");return V+="scale(".concat(Z.size/y*(Z.flipX?-1:1),", ").concat(Z.size/y*(Z.flipY?-1:1),") "),V+="rotate(".concat(Z.rotate,"deg) "),V}var n9=`:root, :host {
  --fa-font-solid: normal 900 1em/1 'Font Awesome 7 Free';
  --fa-font-regular: normal 400 1em/1 'Font Awesome 7 Free';
  --fa-font-light: normal 300 1em/1 'Font Awesome 7 Pro';
  --fa-font-thin: normal 100 1em/1 'Font Awesome 7 Pro';
  --fa-font-duotone: normal 900 1em/1 'Font Awesome 7 Duotone';
  --fa-font-duotone-regular: normal 400 1em/1 'Font Awesome 7 Duotone';
  --fa-font-duotone-light: normal 300 1em/1 'Font Awesome 7 Duotone';
  --fa-font-duotone-thin: normal 100 1em/1 'Font Awesome 7 Duotone';
  --fa-font-brands: normal 400 1em/1 'Font Awesome 7 Brands';
  --fa-font-sharp-solid: normal 900 1em/1 'Font Awesome 7 Sharp';
  --fa-font-sharp-regular: normal 400 1em/1 'Font Awesome 7 Sharp';
  --fa-font-sharp-light: normal 300 1em/1 'Font Awesome 7 Sharp';
  --fa-font-sharp-thin: normal 100 1em/1 'Font Awesome 7 Sharp';
  --fa-font-sharp-duotone-solid: normal 900 1em/1 'Font Awesome 7 Sharp Duotone';
  --fa-font-sharp-duotone-regular: normal 400 1em/1 'Font Awesome 7 Sharp Duotone';
  --fa-font-sharp-duotone-light: normal 300 1em/1 'Font Awesome 7 Sharp Duotone';
  --fa-font-sharp-duotone-thin: normal 100 1em/1 'Font Awesome 7 Sharp Duotone';
  --fa-font-slab-regular: normal 400 1em/1 'Font Awesome 7 Slab';
  --fa-font-slab-press-regular: normal 400 1em/1 'Font Awesome 7 Slab Press';
  --fa-font-whiteboard-semibold: normal 600 1em/1 'Font Awesome 7 Whiteboard';
  --fa-font-thumbprint-light: normal 300 1em/1 'Font Awesome 7 Thumbprint';
  --fa-font-notdog-solid: normal 900 1em/1 'Font Awesome 7 Notdog';
  --fa-font-notdog-duo-solid: normal 900 1em/1 'Font Awesome 7 Notdog Duo';
  --fa-font-etch-solid: normal 900 1em/1 'Font Awesome 7 Etch';
  --fa-font-graphite-thin: normal 100 1em/1 'Font Awesome 7 Graphite';
  --fa-font-jelly-regular: normal 400 1em/1 'Font Awesome 7 Jelly';
  --fa-font-jelly-fill-regular: normal 400 1em/1 'Font Awesome 7 Jelly Fill';
  --fa-font-jelly-duo-regular: normal 400 1em/1 'Font Awesome 7 Jelly Duo';
  --fa-font-chisel-regular: normal 400 1em/1 'Font Awesome 7 Chisel';
  --fa-font-utility-semibold: normal 600 1em/1 'Font Awesome 7 Utility';
  --fa-font-utility-duo-semibold: normal 600 1em/1 'Font Awesome 7 Utility Duo';
  --fa-font-utility-fill-semibold: normal 600 1em/1 'Font Awesome 7 Utility Fill';
}

.svg-inline--fa {
  box-sizing: content-box;
  display: var(--fa-display, inline-block);
  height: 1em;
  overflow: visible;
  vertical-align: -0.125em;
  width: var(--fa-width, 1.25em);
}
.svg-inline--fa.fa-2xs {
  vertical-align: 0.1em;
}
.svg-inline--fa.fa-xs {
  vertical-align: 0em;
}
.svg-inline--fa.fa-sm {
  vertical-align: -0.0714285714em;
}
.svg-inline--fa.fa-lg {
  vertical-align: -0.2em;
}
.svg-inline--fa.fa-xl {
  vertical-align: -0.25em;
}
.svg-inline--fa.fa-2xl {
  vertical-align: -0.3125em;
}
.svg-inline--fa.fa-pull-left,
.svg-inline--fa .fa-pull-start {
  float: inline-start;
  margin-inline-end: var(--fa-pull-margin, 0.3em);
}
.svg-inline--fa.fa-pull-right,
.svg-inline--fa .fa-pull-end {
  float: inline-end;
  margin-inline-start: var(--fa-pull-margin, 0.3em);
}
.svg-inline--fa.fa-li {
  width: var(--fa-li-width, 2em);
  inset-inline-start: calc(-1 * var(--fa-li-width, 2em));
  inset-block-start: 0.25em; /* syncing vertical alignment with Web Font rendering */
}

.fa-layers-counter, .fa-layers-text {
  display: inline-block;
  position: absolute;
  text-align: center;
}

.fa-layers {
  display: inline-block;
  height: 1em;
  position: relative;
  text-align: center;
  vertical-align: -0.125em;
  width: var(--fa-width, 1.25em);
}
.fa-layers .svg-inline--fa {
  inset: 0;
  margin: auto;
  position: absolute;
  transform-origin: center center;
}

.fa-layers-text {
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  transform-origin: center center;
}

.fa-layers-counter {
  background-color: var(--fa-counter-background-color, #ff253a);
  border-radius: var(--fa-counter-border-radius, 1em);
  box-sizing: border-box;
  color: var(--fa-inverse, #fff);
  line-height: var(--fa-counter-line-height, 1);
  max-width: var(--fa-counter-max-width, 5em);
  min-width: var(--fa-counter-min-width, 1.5em);
  overflow: hidden;
  padding: var(--fa-counter-padding, 0.25em 0.5em);
  right: var(--fa-right, 0);
  text-overflow: ellipsis;
  top: var(--fa-top, 0);
  transform: scale(var(--fa-counter-scale, 0.25));
  transform-origin: top right;
}

.fa-layers-bottom-right {
  bottom: var(--fa-bottom, 0);
  right: var(--fa-right, 0);
  top: auto;
  transform: scale(var(--fa-layers-scale, 0.25));
  transform-origin: bottom right;
}

.fa-layers-bottom-left {
  bottom: var(--fa-bottom, 0);
  left: var(--fa-left, 0);
  right: auto;
  top: auto;
  transform: scale(var(--fa-layers-scale, 0.25));
  transform-origin: bottom left;
}

.fa-layers-top-right {
  top: var(--fa-top, 0);
  right: var(--fa-right, 0);
  transform: scale(var(--fa-layers-scale, 0.25));
  transform-origin: top right;
}

.fa-layers-top-left {
  left: var(--fa-left, 0);
  right: auto;
  top: var(--fa-top, 0);
  transform: scale(var(--fa-layers-scale, 0.25));
  transform-origin: top left;
}

.fa-1x {
  font-size: 1em;
}

.fa-2x {
  font-size: 2em;
}

.fa-3x {
  font-size: 3em;
}

.fa-4x {
  font-size: 4em;
}

.fa-5x {
  font-size: 5em;
}

.fa-6x {
  font-size: 6em;
}

.fa-7x {
  font-size: 7em;
}

.fa-8x {
  font-size: 8em;
}

.fa-9x {
  font-size: 9em;
}

.fa-10x {
  font-size: 10em;
}

.fa-2xs {
  font-size: calc(10 / 16 * 1em); /* converts a 10px size into an em-based value that's relative to the scale's 16px base */
  line-height: calc(1 / 10 * 1em); /* sets the line-height of the icon back to that of it's parent */
  vertical-align: calc((6 / 10 - 0.375) * 1em); /* vertically centers the icon taking into account the surrounding text's descender */
}

.fa-xs {
  font-size: calc(12 / 16 * 1em); /* converts a 12px size into an em-based value that's relative to the scale's 16px base */
  line-height: calc(1 / 12 * 1em); /* sets the line-height of the icon back to that of it's parent */
  vertical-align: calc((6 / 12 - 0.375) * 1em); /* vertically centers the icon taking into account the surrounding text's descender */
}

.fa-sm {
  font-size: calc(14 / 16 * 1em); /* converts a 14px size into an em-based value that's relative to the scale's 16px base */
  line-height: calc(1 / 14 * 1em); /* sets the line-height of the icon back to that of it's parent */
  vertical-align: calc((6 / 14 - 0.375) * 1em); /* vertically centers the icon taking into account the surrounding text's descender */
}

.fa-lg {
  font-size: calc(20 / 16 * 1em); /* converts a 20px size into an em-based value that's relative to the scale's 16px base */
  line-height: calc(1 / 20 * 1em); /* sets the line-height of the icon back to that of it's parent */
  vertical-align: calc((6 / 20 - 0.375) * 1em); /* vertically centers the icon taking into account the surrounding text's descender */
}

.fa-xl {
  font-size: calc(24 / 16 * 1em); /* converts a 24px size into an em-based value that's relative to the scale's 16px base */
  line-height: calc(1 / 24 * 1em); /* sets the line-height of the icon back to that of it's parent */
  vertical-align: calc((6 / 24 - 0.375) * 1em); /* vertically centers the icon taking into account the surrounding text's descender */
}

.fa-2xl {
  font-size: calc(32 / 16 * 1em); /* converts a 32px size into an em-based value that's relative to the scale's 16px base */
  line-height: calc(1 / 32 * 1em); /* sets the line-height of the icon back to that of it's parent */
  vertical-align: calc((6 / 32 - 0.375) * 1em); /* vertically centers the icon taking into account the surrounding text's descender */
}

.fa-width-auto {
  --fa-width: auto;
}

.fa-fw,
.fa-width-fixed {
  --fa-width: 1.25em;
}

.fa-ul {
  list-style-type: none;
  margin-inline-start: var(--fa-li-margin, 2.5em);
  padding-inline-start: 0;
}
.fa-ul > li {
  position: relative;
}

.fa-li {
  inset-inline-start: calc(-1 * var(--fa-li-width, 2em));
  position: absolute;
  text-align: center;
  width: var(--fa-li-width, 2em);
  line-height: inherit;
}

/* Heads Up: Bordered Icons will not be supported in the future!
  - This feature will be deprecated in the next major release of Font Awesome (v8)!
  - You may continue to use it in this version *v7), but it will not be supported in Font Awesome v8.
*/
/* Notes:
* --@{v.$css-prefix}-border-width = 1/16 by default (to render as ~1px based on a 16px default font-size)
* --@{v.$css-prefix}-border-padding =
  ** 3/16 for vertical padding (to give ~2px of vertical whitespace around an icon considering it's vertical alignment)
  ** 4/16 for horizontal padding (to give ~4px of horizontal whitespace around an icon)
*/
.fa-border {
  border-color: var(--fa-border-color, #eee);
  border-radius: var(--fa-border-radius, 0.1em);
  border-style: var(--fa-border-style, solid);
  border-width: var(--fa-border-width, 0.0625em);
  box-sizing: var(--fa-border-box-sizing, content-box);
  padding: var(--fa-border-padding, 0.1875em 0.25em);
}

.fa-pull-left,
.fa-pull-start {
  float: inline-start;
  margin-inline-end: var(--fa-pull-margin, 0.3em);
}

.fa-pull-right,
.fa-pull-end {
  float: inline-end;
  margin-inline-start: var(--fa-pull-margin, 0.3em);
}

.fa-beat {
  animation-name: fa-beat;
  animation-delay: var(--fa-animation-delay, 0s);
  animation-direction: var(--fa-animation-direction, normal);
  animation-duration: var(--fa-animation-duration, 1s);
  animation-iteration-count: var(--fa-animation-iteration-count, infinite);
  animation-timing-function: var(--fa-animation-timing, ease-in-out);
}

.fa-bounce {
  animation-name: fa-bounce;
  animation-delay: var(--fa-animation-delay, 0s);
  animation-direction: var(--fa-animation-direction, normal);
  animation-duration: var(--fa-animation-duration, 1s);
  animation-iteration-count: var(--fa-animation-iteration-count, infinite);
  animation-timing-function: var(--fa-animation-timing, cubic-bezier(0.28, 0.84, 0.42, 1));
}

.fa-fade {
  animation-name: fa-fade;
  animation-delay: var(--fa-animation-delay, 0s);
  animation-direction: var(--fa-animation-direction, normal);
  animation-duration: var(--fa-animation-duration, 1s);
  animation-iteration-count: var(--fa-animation-iteration-count, infinite);
  animation-timing-function: var(--fa-animation-timing, cubic-bezier(0.4, 0, 0.6, 1));
}

.fa-beat-fade {
  animation-name: fa-beat-fade;
  animation-delay: var(--fa-animation-delay, 0s);
  animation-direction: var(--fa-animation-direction, normal);
  animation-duration: var(--fa-animation-duration, 1s);
  animation-iteration-count: var(--fa-animation-iteration-count, infinite);
  animation-timing-function: var(--fa-animation-timing, cubic-bezier(0.4, 0, 0.6, 1));
}

.fa-flip {
  animation-name: fa-flip;
  animation-delay: var(--fa-animation-delay, 0s);
  animation-direction: var(--fa-animation-direction, normal);
  animation-duration: var(--fa-animation-duration, 1s);
  animation-iteration-count: var(--fa-animation-iteration-count, infinite);
  animation-timing-function: var(--fa-animation-timing, ease-in-out);
}

.fa-shake {
  animation-name: fa-shake;
  animation-delay: var(--fa-animation-delay, 0s);
  animation-direction: var(--fa-animation-direction, normal);
  animation-duration: var(--fa-animation-duration, 1s);
  animation-iteration-count: var(--fa-animation-iteration-count, infinite);
  animation-timing-function: var(--fa-animation-timing, linear);
}

.fa-spin {
  animation-name: fa-spin;
  animation-delay: var(--fa-animation-delay, 0s);
  animation-direction: var(--fa-animation-direction, normal);
  animation-duration: var(--fa-animation-duration, 2s);
  animation-iteration-count: var(--fa-animation-iteration-count, infinite);
  animation-timing-function: var(--fa-animation-timing, linear);
}

.fa-spin-reverse {
  --fa-animation-direction: reverse;
}

.fa-pulse,
.fa-spin-pulse {
  animation-name: fa-spin;
  animation-direction: var(--fa-animation-direction, normal);
  animation-duration: var(--fa-animation-duration, 1s);
  animation-iteration-count: var(--fa-animation-iteration-count, infinite);
  animation-timing-function: var(--fa-animation-timing, steps(8));
}

@media (prefers-reduced-motion: reduce) {
  .fa-beat,
  .fa-bounce,
  .fa-fade,
  .fa-beat-fade,
  .fa-flip,
  .fa-pulse,
  .fa-shake,
  .fa-spin,
  .fa-spin-pulse {
    animation: none !important;
    transition: none !important;
  }
}
@keyframes fa-beat {
  0%, 90% {
    transform: scale(1);
  }
  45% {
    transform: scale(var(--fa-beat-scale, 1.25));
  }
}
@keyframes fa-bounce {
  0% {
    transform: scale(1, 1) translateY(0);
  }
  10% {
    transform: scale(var(--fa-bounce-start-scale-x, 1.1), var(--fa-bounce-start-scale-y, 0.9)) translateY(0);
  }
  30% {
    transform: scale(var(--fa-bounce-jump-scale-x, 0.9), var(--fa-bounce-jump-scale-y, 1.1)) translateY(var(--fa-bounce-height, -0.5em));
  }
  50% {
    transform: scale(var(--fa-bounce-land-scale-x, 1.05), var(--fa-bounce-land-scale-y, 0.95)) translateY(0);
  }
  57% {
    transform: scale(1, 1) translateY(var(--fa-bounce-rebound, -0.125em));
  }
  64% {
    transform: scale(1, 1) translateY(0);
  }
  100% {
    transform: scale(1, 1) translateY(0);
  }
}
@keyframes fa-fade {
  50% {
    opacity: var(--fa-fade-opacity, 0.4);
  }
}
@keyframes fa-beat-fade {
  0%, 100% {
    opacity: var(--fa-beat-fade-opacity, 0.4);
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(var(--fa-beat-fade-scale, 1.125));
  }
}
@keyframes fa-flip {
  50% {
    transform: rotate3d(var(--fa-flip-x, 0), var(--fa-flip-y, 1), var(--fa-flip-z, 0), var(--fa-flip-angle, -180deg));
  }
}
@keyframes fa-shake {
  0% {
    transform: rotate(-15deg);
  }
  4% {
    transform: rotate(15deg);
  }
  8%, 24% {
    transform: rotate(-18deg);
  }
  12%, 28% {
    transform: rotate(18deg);
  }
  16% {
    transform: rotate(-22deg);
  }
  20% {
    transform: rotate(22deg);
  }
  32% {
    transform: rotate(-12deg);
  }
  36% {
    transform: rotate(12deg);
  }
  40%, 100% {
    transform: rotate(0deg);
  }
}
@keyframes fa-spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.fa-rotate-90 {
  transform: rotate(90deg);
}

.fa-rotate-180 {
  transform: rotate(180deg);
}

.fa-rotate-270 {
  transform: rotate(270deg);
}

.fa-flip-horizontal {
  transform: scale(-1, 1);
}

.fa-flip-vertical {
  transform: scale(1, -1);
}

.fa-flip-both,
.fa-flip-horizontal.fa-flip-vertical {
  transform: scale(-1, -1);
}

.fa-rotate-by {
  transform: rotate(var(--fa-rotate-angle, 0));
}

.svg-inline--fa .fa-primary {
  fill: var(--fa-primary-color, currentColor);
  opacity: var(--fa-primary-opacity, 1);
}

.svg-inline--fa .fa-secondary {
  fill: var(--fa-secondary-color, currentColor);
  opacity: var(--fa-secondary-opacity, 0.4);
}

.svg-inline--fa.fa-swap-opacity .fa-primary {
  opacity: var(--fa-secondary-opacity, 0.4);
}

.svg-inline--fa.fa-swap-opacity .fa-secondary {
  opacity: var(--fa-primary-opacity, 1);
}

.svg-inline--fa mask .fa-primary,
.svg-inline--fa mask .fa-secondary {
  fill: black;
}

.svg-inline--fa.fa-inverse {
  fill: var(--fa-inverse, #fff);
}

.fa-stack {
  display: inline-block;
  height: 2em;
  line-height: 2em;
  position: relative;
  vertical-align: middle;
  width: 2.5em;
}

.fa-inverse {
  color: var(--fa-inverse, #fff);
}

.svg-inline--fa.fa-stack-1x {
  --fa-width: 1.25em;
  height: 1em;
  width: var(--fa-width);
}
.svg-inline--fa.fa-stack-2x {
  --fa-width: 2.5em;
  height: 2em;
  width: var(--fa-width);
}

.fa-stack-1x,
.fa-stack-2x {
  inset: 0;
  margin: auto;
  position: absolute;
  z-index: var(--fa-stack-z-index, auto);
}`;function U6(){var J=z6,Z=X6,q=w.cssPrefix,Q=w.replacementClass,K=n9;if(q!==J||Q!==Z){var B=new RegExp("\\.".concat(J,"\\-"),"g"),z=new RegExp("\\--".concat(J,"\\-"),"g"),X=new RegExp("\\.".concat(Z),"g");K=K.replace(B,".".concat(q,"-")).replace(z,"--".concat(q,"-")).replace(X,".".concat(Q))}return K}var W1=!1;function C0(){if(w.autoAddCss&&!W1)d9(U6()),W1=!0}var i9={mixout:function(){return{dom:{css:U6,insertCss:C0}}},hooks:function(){return{beforeDOMElementCreation:function(){C0()},beforeI2svg:function(){C0()}}}},E=u||{};if(!E[N])E[N]={};if(!E[N].styles)E[N].styles={};if(!E[N].hooks)E[N].hooks={};if(!E[N].shims)E[N].shims=[];var k=E[N],j6=[],F6=function(){L.removeEventListener("DOMContentLoaded",F6),Y0=1,j6.map(function(Z){return Z()})},Y0=!1;if(g){if(Y0=(L.documentElement.doScroll?/^loaded|^c/:/^loaded|^i|^c/).test(L.readyState),!Y0)L.addEventListener("DOMContentLoaded",F6)}function o9(J){if(!g)return;Y0?setTimeout(J,0):j6.push(J)}function q0(J){var{tag:Z,attributes:q}=J,Q=q===void 0?{}:q,K=J.children,B=K===void 0?[]:K;if(typeof J==="string")return w6(J);else return"<".concat(Z," ").concat(l9(Q),">").concat(B.map(q0).join(""),"</").concat(Z,">")}function w1(J,Z,q){if(J&&J[Z]&&J[Z][q])return{prefix:Z,iconName:q,icon:J[Z][q]}}var r9=function(Z,q){return function(Q,K,B,z){return Z.call(q,Q,K,B,z)}},h0=function(Z,q,Q,K){var B=Object.keys(Z),z=B.length,X=K!==void 0?r9(q,K):q,V,G,W;if(Q===void 0)V=1,W=Z[B[0]];else V=0,W=Q;for(;V<z;V++)G=B[V],W=X(W,Z[G],G,Z);return W};function D6(J){if(P(J).length!==1)return null;return J.codePointAt(0).toString(16)}function U1(J){return Object.keys(J).reduce(function(Z,q){var Q=J[q],K=!!Q.icon;if(K)Z[Q.iconName]=Q.icon;else Z[q]=Q;return Z},{})}function g0(J,Z){var q=arguments.length>2&&arguments[2]!==void 0?arguments[2]:{},Q=q.skipHooks,K=Q===void 0?!1:Q,B=U1(Z);if(typeof k.hooks.addPack==="function"&&!K)k.hooks.addPack(J,U1(Z));else k.styles[J]=H(H({},k.styles[J]||{}),B);if(J==="fas")g0("fa",Z)}var{styles:e,shims:t9}=k,M6=Object.keys(s0),a9=M6.reduce(function(J,Z){return J[Z]=Object.keys(s0[Z]),J},{}),o0=null,R6={},L6={},v6={},C6={},h6={};function e9(J){return~u9.indexOf(J)}function JJ(J,Z){var q=Z.split("-"),Q=q[0],K=q.slice(1).join("-");if(Q===J&&K!==""&&!e9(K))return K;else return null}var O6=function(){var Z=function(B){return h0(e,function(z,X,V){return z[V]=h0(X,B,{}),z},{})};R6=Z(function(K,B,z){if(B[3])K[B[3]]=z;if(B[2]){var X=B[2].filter(function(V){return typeof V==="number"});X.forEach(function(V){K[V.toString(16)]=z})}return K}),L6=Z(function(K,B,z){if(K[z]=z,B[2]){var X=B[2].filter(function(V){return typeof V==="string"});X.forEach(function(V){K[V]=z})}return K}),h6=Z(function(K,B,z){var X=B[2];return K[z]=z,X.forEach(function(V){K[V]=z}),K});var q="far"in e||w.autoFetchSvg,Q=h0(t9,function(K,B){var z=B[0],X=B[1],V=B[2];if(X==="far"&&!q)X="fas";if(typeof z==="string")K.names[z]={prefix:X,iconName:V};if(typeof z==="number")K.unicodes[z.toString(16)]={prefix:X,iconName:V};return K},{names:{},unicodes:{}});v6=Q.names,C6=Q.unicodes,o0=j0(w.styleDefault,{family:w.familyDefault})};p9(function(J){o0=j0(J.styleDefault,{family:w.familyDefault})});O6();function r0(J,Z){return(R6[J]||{})[Z]}function ZJ(J,Z){return(L6[J]||{})[Z]}function p(J,Z){return(h6[J]||{})[Z]}function T6(J){return v6[J]||{prefix:null,iconName:null}}function qJ(J){var Z=C6[J],q=r0("fas",J);return Z||(q?{prefix:"fas",iconName:q}:null)||{prefix:null,iconName:null}}function f(){return o0}var $6=function(){return{prefix:null,iconName:null,rest:[]}};function QJ(J){var Z=T,q=M6.reduce(function(Q,K){return Q[K]="".concat(w.cssPrefix,"-").concat(K),Q},{});return q6.forEach(function(Q){if(J.includes(q[Q])||J.some(function(K){return a9[Q].includes(K)}))Z=Q}),Z}function j0(J){var Z=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},q=Z.family,Q=q===void 0?T:q,K=E9[Q][J];if(Q===J0&&!J)return"fad";var B=G1[Q][J]||G1[Q][K],z=J in k.styles?J:null,X=B||z||null;return X}function KJ(J){var Z=[],q=null;return J.forEach(function(Q){var K=JJ(w.cssPrefix,Q);if(K)q=K;else if(Q)Z.push(Q)}),{iconName:q,rest:Z}}function j1(J){return J.sort().filter(function(Z,q,Q){return Q.indexOf(Z)===q})}var F1=K6.concat(Q6);function F0(J){var Z=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},q=Z.skipLookups,Q=q===void 0?!1:q,K=null,B=j1(J.filter(function(j){return F1.includes(j)})),z=j1(J.filter(function(j){return!F1.includes(j)})),X=B.filter(function(j){return K=j,!u1.includes(j)}),V=w0(X,1),G=V[0],W=G===void 0?null:G,Y=QJ(B),U=H(H({},KJ(z)),{},{prefix:j0(W,{family:Y})});return H(H(H({},U),VJ({values:J,family:Y,styles:e,config:w,canonical:U,givenPrefix:K})),BJ(Q,K,U))}function BJ(J,Z,q){var{prefix:Q,iconName:K}=q;if(J||!Q||!K)return{prefix:Q,iconName:K};var B=Z==="fa"?T6(K):{},z=p(Q,K);if(K=B.iconName||z||K,Q=B.prefix||Q,Q==="far"&&!e.far&&e.fas&&!w.autoFetchSvg)Q="fas";return{prefix:Q,iconName:K}}var zJ=q6.filter(function(J){return J!==T||J!==J0}),XJ=Object.keys(k0).filter(function(J){return J!==T}).map(function(J){return Object.keys(k0[J])}).flat();function VJ(J){var{values:Z,family:q,canonical:Q,givenPrefix:K}=J,B=K===void 0?"":K,z=J.styles,X=z===void 0?{}:z,V=J.config,G=V===void 0?{}:V,W=q===J0,Y=Z.includes("fa-duotone")||Z.includes("fad"),U=G.familyDefault==="duotone",j=Q.prefix==="fad"||Q.prefix==="fa-duotone";if(!W&&(Y||U||j))Q.prefix="fad";if(Z.includes("fa-brands")||Z.includes("fab"))Q.prefix="fab";if(!Q.prefix&&zJ.includes(q)){var R=Object.keys(X).find(function(v){return XJ.includes(v)});if(R||G.autoFetchSvg){var M=h7.get(q).defaultShortPrefixId;Q.prefix=M,Q.iconName=p(Q.prefix,Q.iconName)||Q.iconName}}if(Q.prefix==="fa"||B==="fa")Q.prefix=f()||"fas";return Q}var HJ=function(){function J(){c6(this,J),this.definitions={}}return s6(J,[{key:"add",value:function(){var q=this;for(var Q=arguments.length,K=Array(Q),B=0;B<Q;B++)K[B]=arguments[B];var z=K.reduce(this._pullDefinitions,{});Object.keys(z).forEach(function(X){q.definitions[X]=H(H({},q.definitions[X]||{}),z[X]),g0(X,z[X]);var V=s0[T][X];if(V)g0(V,z[X]);O6()})}},{key:"reset",value:function(){this.definitions={}}},{key:"_pullDefinitions",value:function(q,Q){var K=Q.prefix&&Q.iconName&&Q.icon?{0:Q}:Q;return Object.keys(K).map(function(B){var z=K[B],X=z.prefix,V=z.iconName,G=z.icon,W=G[2];if(!q[X])q[X]={};if(W.length>0)W.forEach(function(Y){if(typeof Y==="string")q[X][Y]=G});q[X][V]=G}),q}}])}(),D1=[],c={},s={},GJ=Object.keys(s);function YJ(J,Z){var q=Z.mixoutsTo;return D1=J,c={},Object.keys(s).forEach(function(Q){if(GJ.indexOf(Q)===-1)delete s[Q]}),D1.forEach(function(Q){var K=Q.mixout?Q.mixout():{};if(Object.keys(K).forEach(function(z){if(typeof K[z]==="function")q[z]=K[z];if(G0(K[z])==="object")Object.keys(K[z]).forEach(function(X){if(!q[z])q[z]={};q[z][X]=K[z][X]})}),Q.hooks){var B=Q.hooks();Object.keys(B).forEach(function(z){if(!c[z])c[z]=[];c[z].push(B[z])})}if(Q.provides)Q.provides(s)}),q}function S0(J,Z){for(var q=arguments.length,Q=Array(q>2?q-2:0),K=2;K<q;K++)Q[K-2]=arguments[K];var B=c[J]||[];return B.forEach(function(z){Z=z.apply(null,[Z].concat(Q))}),Z}function m(J){for(var Z=arguments.length,q=Array(Z>1?Z-1:0),Q=1;Q<Z;Q++)q[Q-1]=arguments[Q];var K=c[J]||[];K.forEach(function(B){B.apply(null,q)});return}function _(){var J=arguments[0],Z=Array.prototype.slice.call(arguments,1);return s[J]?s[J].apply(null,Z):void 0}function b0(J){if(J.prefix==="fa")J.prefix="fas";var Z=J.iconName,q=J.prefix||f();if(!Z)return;return Z=p(q,Z)||Z,w1(k6.definitions,q,Z)||w1(k.styles,q,Z)}var k6=new HJ,WJ=function(){w.autoReplaceSvg=!1,w.observeMutations=!1,m("noAuto")},wJ={i2svg:function(){var Z=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{};if(g)return m("beforeI2svg",Z),_("pseudoElements2svg",Z),_("i2svg",Z);else return Promise.reject(Error("Operation requires a DOM of some kind."))},watch:function(){var Z=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{},q=Z.autoReplaceSvgRoot;if(w.autoReplaceSvg===!1)w.autoReplaceSvg=!0;w.observeMutations=!0,o9(function(){jJ({autoReplaceSvgRoot:q}),m("watch",Z)})}},UJ={icon:function(Z){if(Z===null)return null;if(G0(Z)==="object"&&Z.prefix&&Z.iconName)return{prefix:Z.prefix,iconName:p(Z.prefix,Z.iconName)||Z.iconName};if(Array.isArray(Z)&&Z.length===2){var q=Z[1].indexOf("fa-")===0?Z[1].slice(3):Z[1],Q=j0(Z[0]);return{prefix:Q,iconName:p(Q,q)||q}}if(typeof Z==="string"&&(Z.indexOf("".concat(w.cssPrefix,"-"))>-1||Z.match(g9))){var K=F0(Z.split(" "),{skipLookups:!0});return{prefix:K.prefix||f(),iconName:p(K.prefix,K.iconName)||K.iconName}}if(typeof Z==="string"){var B=f();return{prefix:B,iconName:p(B,Z)||Z}}}},$={noAuto:WJ,config:w,dom:wJ,parse:UJ,library:k6,findIconDefinition:b0,toHtml:q0},jJ=function(){var Z=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{},q=Z.autoReplaceSvgRoot,Q=q===void 0?L:q;if((Object.keys(k.styles).length>0||w.autoFetchSvg)&&g&&w.autoReplaceSvg)$.dom.i2svg({node:Q})};function D0(J,Z){return Object.defineProperty(J,"abstract",{get:Z}),Object.defineProperty(J,"html",{get:function(){return J.abstract.map(function(Q){return q0(Q)})}}),Object.defineProperty(J,"node",{get:function(){if(!g)return;var Q=L.createElement("div");return Q.innerHTML=J.html,Q.children}}),J}function FJ(J){var{children:Z,main:q,mask:Q,attributes:K,styles:B,transform:z}=J;if(i0(z)&&q.found&&!Q.found){var{width:X,height:V}=q,G={x:X/V/2,y:0.5};K.style=U0(H(H({},B),{},{"transform-origin":"".concat(G.x+z.x/16,"em ").concat(G.y+z.y/16,"em")}))}return[{tag:"svg",attributes:K,children:Z}]}function DJ(J){var{prefix:Z,iconName:q,children:Q,attributes:K,symbol:B}=J,z=B===!0?"".concat(Z,"-").concat(w.cssPrefix,"-").concat(q):B;return[{tag:"svg",attributes:{style:"display: none;"},children:[{tag:"symbol",attributes:H(H({},K),{},{id:z}),children:Q}]}]}function MJ(J){var Z=["aria-label","aria-labelledby","title","role"];return Z.some(function(q){return q in J})}function t0(J){var Z=J.icons,q=Z.main,Q=Z.mask,K=J.prefix,B=J.iconName,z=J.transform,X=J.symbol,V=J.maskId,G=J.extra,W=J.watchable,Y=W===void 0?!1:W,U=Q.found?Q:q,j=U.width,R=U.height,M=[w.replacementClass,B?"".concat(w.cssPrefix,"-").concat(B):""].filter(function(D){return G.classes.indexOf(D)===-1}).filter(function(D){return D!==""||!!D}).concat(G.classes).join(" "),v={children:[],attributes:H(H({},G.attributes),{},{"data-prefix":K,"data-icon":B,class:M,role:G.attributes.role||"img",viewBox:"0 0 ".concat(j," ").concat(R)})};if(!MJ(G.attributes)&&!G.attributes["aria-hidden"])v.attributes["aria-hidden"]="true";if(Y)v.attributes[d]="";var C=H(H({},v),{},{prefix:K,iconName:B,main:q,mask:Q,maskId:V,transform:z,symbol:X,styles:H({},G.styles)}),h=Q.found&&q.found?_("generateAbstractMask",C)||{children:[],attributes:{}}:_("generateAbstractIcon",C)||{children:[],attributes:{}},O=h.children,I=h.attributes;if(C.children=O,C.attributes=I,X)return DJ(C);else return FJ(C)}function M1(J){var{content:Z,width:q,height:Q,transform:K,extra:B,watchable:z}=J,X=z===void 0?!1:z,V=H(H({},B.attributes),{},{class:B.classes.join(" ")});if(X)V[d]="";var G=H({},B.styles);if(i0(K))G.transform=s9({transform:K,startCentered:!0,width:q,height:Q}),G["-webkit-transform"]=G.transform;var W=U0(G);if(W.length>0)V.style=W;var Y=[];return Y.push({tag:"span",attributes:V,children:[Z]}),Y}function RJ(J){var{content:Z,extra:q}=J,Q=H(H({},q.attributes),{},{class:q.classes.join(" ")}),K=U0(q.styles);if(K.length>0)Q.style=K;var B=[];return B.push({tag:"span",attributes:Q,children:[Z]}),B}var O0=k.styles;function y0(J){var Z=J[0],q=J[1],Q=J.slice(4),K=w0(Q,1),B=K[0],z=null;if(Array.isArray(B))z={tag:"g",attributes:{class:"".concat(w.cssPrefix,"-").concat(v0.GROUP)},children:[{tag:"path",attributes:{class:"".concat(w.cssPrefix,"-").concat(v0.SECONDARY),fill:"currentColor",d:B[0]}},{tag:"path",attributes:{class:"".concat(w.cssPrefix,"-").concat(v0.PRIMARY),fill:"currentColor",d:B[1]}}]};else z={tag:"path",attributes:{fill:"currentColor",d:B}};return{found:!0,width:Z,height:q,icon:z}}var LJ={found:!1,width:512,height:512};function vJ(J,Z){if(!H6&&!w.showMissingIcons&&J)console.error('Icon with name "'.concat(J,'" and prefix "').concat(Z,'" is missing.'))}function u0(J,Z){var q=Z;if(Z==="fa"&&w.styleDefault!==null)Z=f();return new Promise(function(Q,K){if(q==="fa"){var B=T6(J)||{};J=B.iconName||J,Z=B.prefix||Z}if(J&&Z&&O0[Z]&&O0[Z][J]){var z=O0[Z][J];return Q(y0(z))}vJ(J,Z),Q(H(H({},LJ),{},{icon:w.showMissingIcons&&J?_("missingIconAbstract")||{}:{}}))})}var R1=function(){},f0=w.measurePerformance&&B0&&B0.mark&&B0.measure?B0:{mark:R1,measure:R1},r='FA "7.2.0"',CJ=function(Z){return f0.mark("".concat(r," ").concat(Z," begins")),function(){return P6(Z)}},P6=function(Z){f0.mark("".concat(r," ").concat(Z," ends")),f0.measure("".concat(r," ").concat(Z),"".concat(r," ").concat(Z," begins"),"".concat(r," ").concat(Z," ends"))},a0={begin:CJ,end:P6},V0=function(){};function L1(J){var Z=J.getAttribute?J.getAttribute(d):null;return typeof Z==="string"}function hJ(J){var Z=J.getAttribute?J.getAttribute(l0):null,q=J.getAttribute?J.getAttribute(c0):null;return Z&&q}function OJ(J){return J&&J.classList&&J.classList.contains&&J.classList.contains(w.replacementClass)}function TJ(){if(w.autoReplaceSvg===!0)return H0.replace;var J=H0[w.autoReplaceSvg];return J||H0.replace}function $J(J){return L.createElementNS("http://www.w3.org/2000/svg",J)}function kJ(J){return L.createElement(J)}function A6(J){var Z=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},q=Z.ceFn,Q=q===void 0?J.tag==="svg"?$J:kJ:q;if(typeof J==="string")return L.createTextNode(J);var K=Q(J.tag);Object.keys(J.attributes||[]).forEach(function(z){K.setAttribute(z,J.attributes[z])});var B=J.children||[];return B.forEach(function(z){K.appendChild(A6(z,{ceFn:Q}))}),K}function PJ(J){var Z=" ".concat(J.outerHTML," ");return Z="".concat(Z,"Font Awesome fontawesome.com "),Z}var H0={replace:function(Z){var q=Z[0];if(q.parentNode)if(Z[1].forEach(function(K){q.parentNode.insertBefore(A6(K),q)}),q.getAttribute(d)===null&&w.keepOriginalSource){var Q=L.createComment(PJ(q));q.parentNode.replaceChild(Q,q)}else q.remove()},nest:function(Z){var q=Z[0],Q=Z[1];if(~n0(q).indexOf(w.replacementClass))return H0.replace(Z);var K=new RegExp("".concat(w.cssPrefix,"-.*"));if(delete Q[0].attributes.id,Q[0].attributes.class){var B=Q[0].attributes.class.split(" ").reduce(function(X,V){if(V===w.replacementClass||V.match(K))X.toSvg.push(V);else X.toNode.push(V);return X},{toNode:[],toSvg:[]});if(Q[0].attributes.class=B.toSvg.join(" "),B.toNode.length===0)q.removeAttribute("class");else q.setAttribute("class",B.toNode.join(" "))}var z=Q.map(function(X){return q0(X)}).join(`
`);q.setAttribute(d,""),q.innerHTML=z}};function v1(J){J()}function x6(J,Z){var q=typeof Z==="function"?Z:V0;if(J.length===0)q();else{var Q=v1;if(w.mutateApproach===I9)Q=u.requestAnimationFrame||v1;Q(function(){var K=TJ(),B=a0.begin("mutate");J.map(K),B(),q()})}}var e0=!1;function I6(){e0=!0}function _0(){e0=!1}var W0=null;function C1(J){if(!z1)return;if(!w.observeMutations)return;var Z=J.treeCallback,q=Z===void 0?V0:Z,Q=J.nodeCallback,K=Q===void 0?V0:Q,B=J.pseudoElementsCallback,z=B===void 0?V0:B,X=J.observeMutationsRoot,V=X===void 0?L:X;if(W0=new z1(function(G){if(e0)return;var W=f();i(G).forEach(function(Y){if(Y.type==="childList"&&Y.addedNodes.length>0&&!L1(Y.addedNodes[0])){if(w.searchPseudoElements)z(Y.target);q(Y.target)}if(Y.type==="attributes"&&Y.target.parentNode&&w.searchPseudoElements)z([Y.target],!0);if(Y.type==="attributes"&&L1(Y.target)&&~y9.indexOf(Y.attributeName)){if(Y.attributeName==="class"&&hJ(Y.target)){var U=F0(n0(Y.target)),j=U.prefix,R=U.iconName;if(Y.target.setAttribute(l0,j||W),R)Y.target.setAttribute(c0,R)}else if(OJ(Y.target))K(Y.target)}})}),!g)return;W0.observe(V,{childList:!0,attributes:!0,characterData:!0,subtree:!0})}function AJ(){if(!W0)return;W0.disconnect()}function xJ(J){var Z=J.getAttribute("style"),q=[];if(Z)q=Z.split(";").reduce(function(Q,K){var B=K.split(":"),z=B[0],X=B.slice(1);if(z&&X.length>0)Q[z]=X.join(":").trim();return Q},{});return q}function IJ(J){var Z=J.getAttribute("data-prefix"),q=J.getAttribute("data-icon"),Q=J.innerText!==void 0?J.innerText.trim():"",K=F0(n0(J));if(!K.prefix)K.prefix=f();if(Z&&q)K.prefix=Z,K.iconName=q;if(K.iconName&&K.prefix)return K;if(K.prefix&&Q.length>0)K.iconName=ZJ(K.prefix,J.innerText)||r0(K.prefix,D6(J.innerText));if(!K.iconName&&w.autoFetchSvg&&J.firstChild&&J.firstChild.nodeType===Node.TEXT_NODE)K.iconName=J.firstChild.data;return K}function NJ(J){var Z=i(J.attributes).reduce(function(q,Q){if(q.name!=="class"&&q.name!=="style")q[Q.name]=Q.value;return q},{});return Z}function EJ(){return{iconName:null,prefix:null,transform:A,symbol:!1,mask:{iconName:null,prefix:null,rest:[]},maskId:null,extra:{classes:[],styles:{},attributes:{}}}}function h1(J){var Z=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{styleParser:!0},q=IJ(J),Q=q.iconName,K=q.prefix,B=q.rest,z=NJ(J),X=S0("parseNodeAttributes",{},J),V=Z.styleParser?xJ(J):[];return H({iconName:Q,prefix:K,transform:A,mask:{iconName:null,prefix:null,rest:[]},maskId:null,symbol:!1,extra:{classes:B,styles:V,attributes:z}},X)}var gJ=k.styles;function N6(J){var Z=w.autoReplaceSvg==="nest"?h1(J,{styleParser:!1}):h1(J);if(~Z.extra.classes.indexOf(Y6))return _("generateLayersText",J,Z);else return _("generateSvgReplacementMutation",J,Z)}function SJ(){return[].concat(P(Q6),P(K6))}function O1(J){var Z=arguments.length>1&&arguments[1]!==void 0?arguments[1]:null;if(!g)return Promise.resolve();var q=L.documentElement.classList,Q=function(Y){return q.add("".concat(H1,"-").concat(Y))},K=function(Y){return q.remove("".concat(H1,"-").concat(Y))},B=w.autoFetchSvg?SJ():u1.concat(Object.keys(gJ));if(!B.includes("fa"))B.push("fa");var z=[".".concat(Y6,":not([").concat(d,"])")].concat(B.map(function(W){return".".concat(W,":not([").concat(d,"])")})).join(", ");if(z.length===0)return Promise.resolve();var X=[];try{X=i(J.querySelectorAll(z))}catch(W){}if(X.length>0)Q("pending"),K("complete");else return Promise.resolve();var V=a0.begin("onTree"),G=X.reduce(function(W,Y){try{var U=N6(Y);if(U)W.push(U)}catch(j){if(!H6){if(j.name==="MissingIcon")console.error(j)}}return W},[]);return new Promise(function(W,Y){Promise.all(G).then(function(U){x6(U,function(){if(Q("active"),Q("complete"),K("pending"),typeof Z==="function")Z();V(),W()})}).catch(function(U){V(),Y(U)})})}function bJ(J){var Z=arguments.length>1&&arguments[1]!==void 0?arguments[1]:null;N6(J).then(function(q){if(q)x6([q],Z)})}function yJ(J){return function(Z){var q=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},Q=(Z||{}).icon?Z:b0(Z||{}),K=q.mask;if(K)K=(K||{}).icon?K:b0(K||{});return J(Q,H(H({},q),{},{mask:K}))}}var uJ=function(Z){var q=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},Q=q.transform,K=Q===void 0?A:Q,B=q.symbol,z=B===void 0?!1:B,X=q.mask,V=X===void 0?null:X,G=q.maskId,W=G===void 0?null:G,Y=q.classes,U=Y===void 0?[]:Y,j=q.attributes,R=j===void 0?{}:j,M=q.styles,v=M===void 0?{}:M;if(!Z)return;var{prefix:C,iconName:h,icon:O}=Z;return D0(H({type:"icon"},Z),function(){return m("beforeDOMElementCreation",{iconDefinition:Z,params:q}),t0({icons:{main:y0(O),mask:V?y0(V.icon):{found:!1,width:null,height:null,icon:{}}},prefix:C,iconName:h,transform:H(H({},A),K),symbol:z,maskId:W,extra:{attributes:R,styles:v,classes:U}})})},fJ={mixout:function(){return{icon:yJ(uJ)}},hooks:function(){return{mutationObserverCallbacks:function(q){return q.treeCallback=O1,q.nodeCallback=bJ,q}}},provides:function(Z){Z.i2svg=function(q){var Q=q.node,K=Q===void 0?L:Q,B=q.callback,z=B===void 0?function(){}:B;return O1(K,z)},Z.generateSvgReplacementMutation=function(q,Q){var{iconName:K,prefix:B,transform:z,symbol:X,mask:V,maskId:G,extra:W}=Q;return new Promise(function(Y,U){Promise.all([u0(K,B),V.iconName?u0(V.iconName,V.prefix):Promise.resolve({found:!1,width:512,height:512,icon:{}})]).then(function(j){var R=w0(j,2),M=R[0],v=R[1];Y([q,t0({icons:{main:M,mask:v},prefix:B,iconName:K,transform:z,symbol:X,maskId:G,extra:W,watchable:!0})])}).catch(U)})},Z.generateAbstractIcon=function(q){var{children:Q,attributes:K,main:B,transform:z,styles:X}=q,V=U0(X);if(V.length>0)K.style=V;var G;if(i0(z))G=_("generateAbstractTransformGrouping",{main:B,transform:z,containerWidth:B.width,iconWidth:B.width});return Q.push(G||B.icon),{children:Q,attributes:K}}}},_J={mixout:function(){return{layer:function(q){var Q=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},K=Q.classes,B=K===void 0?[]:K;return D0({type:"layer"},function(){m("beforeDOMElementCreation",{assembler:q,params:Q});var z=[];return q(function(X){Array.isArray(X)?X.map(function(V){z=z.concat(V.abstract)}):z=z.concat(X.abstract)}),[{tag:"span",attributes:{class:["".concat(w.cssPrefix,"-layers")].concat(P(B)).join(" ")},children:z}]})}}}},pJ={mixout:function(){return{counter:function(q){var Q=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},K=Q.title,B=K===void 0?null:K,z=Q.classes,X=z===void 0?[]:z,V=Q.attributes,G=V===void 0?{}:V,W=Q.styles,Y=W===void 0?{}:W;return D0({type:"counter",content:q},function(){return m("beforeDOMElementCreation",{content:q,params:Q}),RJ({content:q.toString(),title:B,extra:{attributes:G,styles:Y,classes:["".concat(w.cssPrefix,"-layers-counter")].concat(P(X))}})})}}}},dJ={mixout:function(){return{text:function(q){var Q=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{},K=Q.transform,B=K===void 0?A:K,z=Q.classes,X=z===void 0?[]:z,V=Q.attributes,G=V===void 0?{}:V,W=Q.styles,Y=W===void 0?{}:W;return D0({type:"text",content:q},function(){return m("beforeDOMElementCreation",{content:q,params:Q}),M1({content:q,transform:H(H({},A),B),extra:{attributes:G,styles:Y,classes:["".concat(w.cssPrefix,"-layers-text")].concat(P(X))}})})}}},provides:function(Z){Z.generateLayersText=function(q,Q){var{transform:K,extra:B}=Q,z=null,X=null;if(b1){var V=parseInt(getComputedStyle(q).fontSize,10),G=q.getBoundingClientRect();z=G.width/V,X=G.height/V}return Promise.resolve([q,M1({content:q.innerHTML,width:z,height:X,transform:K,extra:B,watchable:!0})])}}},E6=new RegExp('"',"ug"),T1=[1105920,1112319],$1=H(H(H(H({},{FontAwesome:{normal:"fas",400:"fas"}}),C7),A9),I7),p0=Object.keys($1).reduce(function(J,Z){return J[Z.toLowerCase()]=$1[Z],J},{}),mJ=Object.keys(p0).reduce(function(J,Z){var q=p0[Z];return J[Z]=q[900]||P(Object.entries(q))[0][1],J},{});function lJ(J){var Z=J.replace(E6,"");return D6(P(Z)[0]||"")}function cJ(J){var Z=J.getPropertyValue("font-feature-settings").includes("ss01"),q=J.getPropertyValue("content"),Q=q.replace(E6,""),K=Q.codePointAt(0),B=K>=T1[0]&&K<=T1[1],z=Q.length===2?Q[0]===Q[1]:!1;return B||z||Z}function sJ(J,Z){var q=J.replace(/^['"]|['"]$/g,"").toLowerCase(),Q=parseInt(Z),K=isNaN(Q)?"normal":Q;return(p0[q]||{})[K]||mJ[q]}function k1(J,Z){var q="".concat(x9).concat(Z.replace(":","-"));return new Promise(function(Q,K){if(J.getAttribute(q)!==null)return Q();var B=i(J.children),z=B.filter(function(K0){return K0.getAttribute(A0)===Z})[0],X=u.getComputedStyle(J,Z),V=X.getPropertyValue("font-family"),G=V.match(S9),W=X.getPropertyValue("font-weight"),Y=X.getPropertyValue("content");if(z&&!G)return J.removeChild(z),Q();else if(G&&Y!=="none"&&Y!==""){var U=X.getPropertyValue("content"),j=sJ(V,W),R=lJ(U),M=G[0].startsWith("FontAwesome"),v=cJ(X),C=r0(j,R),h=C;if(M){var O=qJ(R);if(O.iconName&&O.prefix)C=O.iconName,j=O.prefix}if(C&&!v&&(!z||z.getAttribute(l0)!==j||z.getAttribute(c0)!==h)){if(J.setAttribute(q,h),z)J.removeChild(z);var I=EJ(),D=I.extra;D.attributes[A0]=Z,u0(C,j).then(function(K0){var p6=t0(H(H({},I),{},{icons:{main:K0,mask:$6()},prefix:j,iconName:h,extra:D,watchable:!0})),M0=L.createElementNS("http://www.w3.org/2000/svg","svg");if(Z==="::before")J.insertBefore(M0,J.firstChild);else J.appendChild(M0);M0.outerHTML=p6.map(function(d6){return q0(d6)}).join(`
`),J.removeAttribute(q),Q()}).catch(K)}else Q()}else Q()})}function nJ(J){return Promise.all([k1(J,"::before"),k1(J,"::after")])}function iJ(J){return J.parentNode!==document.head&&!~N9.indexOf(J.tagName.toUpperCase())&&!J.getAttribute(A0)&&(!J.parentNode||J.parentNode.tagName!=="svg")}var oJ=function(Z){return!!Z&&V6.some(function(q){return Z.includes(q)})},rJ=function(Z){if(!Z)return[];var q=new Set,Q=Z.split(/,(?![^()]*\))/).map(function(V){return V.trim()});Q=Q.flatMap(function(V){return V.includes("(")?V:V.split(",").map(function(G){return G.trim()})});var K=X0(Q),B;try{for(K.s();!(B=K.n()).done;){var z=B.value;if(oJ(z)){var X=V6.reduce(function(V,G){return V.replace(G,"")},z);if(X!==""&&X!=="*")q.add(X)}}}catch(V){K.e(V)}finally{K.f()}return q};function P1(J){var Z=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1;if(!g)return;var q;if(Z)q=J;else if(w.searchPseudoElementsFullScan)q=J.querySelectorAll("*");else{var Q=new Set,K=X0(document.styleSheets),B;try{for(K.s();!(B=K.n()).done;){var z=B.value;try{var X=X0(z.cssRules),V;try{for(X.s();!(V=X.n()).done;){var G=V.value,W=rJ(G.selectorText),Y=X0(W),U;try{for(Y.s();!(U=Y.n()).done;){var j=U.value;Q.add(j)}}catch(M){Y.e(M)}finally{Y.f()}}}catch(M){X.e(M)}finally{X.f()}}catch(M){if(w.searchPseudoElementsWarnings)console.warn("Font Awesome: cannot parse stylesheet: ".concat(z.href," (").concat(M.message,`)
If it declares any Font Awesome CSS pseudo-elements, they will not be rendered as SVG icons. Add crossorigin="anonymous" to the <link>, enable searchPseudoElementsFullScan for slower but more thorough DOM parsing, or suppress this warning by setting searchPseudoElementsWarnings to false.`))}}}catch(M){K.e(M)}finally{K.f()}if(!Q.size)return;var R=Array.from(Q).join(", ");try{q=J.querySelectorAll(R)}catch(M){}}return new Promise(function(M,v){var C=i(q).filter(iJ).map(nJ),h=a0.begin("searchPseudoElements");I6(),Promise.all(C).then(function(){h(),_0(),M()}).catch(function(){h(),_0(),v()})})}var tJ={hooks:function(){return{mutationObserverCallbacks:function(q){return q.pseudoElementsCallback=P1,q}}},provides:function(Z){Z.pseudoElements2svg=function(q){var Q=q.node,K=Q===void 0?L:Q;if(w.searchPseudoElements)P1(K)}}},A1=!1,aJ={mixout:function(){return{dom:{unwatch:function(){I6(),A1=!0}}}},hooks:function(){return{bootstrap:function(){C1(S0("mutationObserverCallbacks",{}))},noAuto:function(){AJ()},watch:function(q){var Q=q.observeMutationsRoot;if(A1)_0();else C1(S0("mutationObserverCallbacks",{observeMutationsRoot:Q}))}}}},x1=function(Z){var q={size:16,x:0,y:0,flipX:!1,flipY:!1,rotate:0};return Z.toLowerCase().split(" ").reduce(function(Q,K){var B=K.toLowerCase().split("-"),z=B[0],X=B.slice(1).join("-");if(z&&X==="h")return Q.flipX=!0,Q;if(z&&X==="v")return Q.flipY=!0,Q;if(X=parseFloat(X),isNaN(X))return Q;switch(z){case"grow":Q.size=Q.size+X;break;case"shrink":Q.size=Q.size-X;break;case"left":Q.x=Q.x-X;break;case"right":Q.x=Q.x+X;break;case"up":Q.y=Q.y-X;break;case"down":Q.y=Q.y+X;break;case"rotate":Q.rotate=Q.rotate+X;break}return Q},q)},eJ={mixout:function(){return{parse:{transform:function(q){return x1(q)}}}},hooks:function(){return{parseNodeAttributes:function(q,Q){var K=Q.getAttribute("data-fa-transform");if(K)q.transform=x1(K);return q}}},provides:function(Z){Z.generateAbstractTransformGrouping=function(q){var{main:Q,transform:K,containerWidth:B,iconWidth:z}=q,X={transform:"translate(".concat(B/2," 256)")},V="translate(".concat(K.x*32,", ").concat(K.y*32,") "),G="scale(".concat(K.size/16*(K.flipX?-1:1),", ").concat(K.size/16*(K.flipY?-1:1),") "),W="rotate(".concat(K.rotate," 0 0)"),Y={transform:"".concat(V," ").concat(G," ").concat(W)},U={transform:"translate(".concat(z/2*-1," -256)")},j={outer:X,inner:Y,path:U};return{tag:"g",attributes:H({},j.outer),children:[{tag:"g",attributes:H({},j.inner),children:[{tag:Q.icon.tag,children:Q.icon.children,attributes:H(H({},Q.icon.attributes),j.path)}]}]}}}},T0={x:0,y:0,width:"100%",height:"100%"};function I1(J){var Z=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!0;if(J.attributes&&(J.attributes.fill||Z))J.attributes.fill="black";return J}function J8(J){if(J.tag==="g")return J.children;else return[J]}var Z8={hooks:function(){return{parseNodeAttributes:function(q,Q){var K=Q.getAttribute("data-fa-mask"),B=!K?$6():F0(K.split(" ").map(function(z){return z.trim()}));if(!B.prefix)B.prefix=f();return q.mask=B,q.maskId=Q.getAttribute("data-fa-mask-id"),q}}},provides:function(Z){Z.generateAbstractMask=function(q){var{children:Q,attributes:K,main:B,mask:z,maskId:X,transform:V}=q,G=B.width,W=B.icon,Y=z.width,U=z.icon,j=c9({transform:V,containerWidth:Y,iconWidth:G}),R={tag:"rect",attributes:H(H({},T0),{},{fill:"white"})},M=W.children?{children:W.children.map(I1)}:{},v={tag:"g",attributes:H({},j.inner),children:[I1(H({tag:W.tag,attributes:H(H({},W.attributes),j.path)},M))]},C={tag:"g",attributes:H({},j.outer),children:[v]},h="mask-".concat(X||Y1()),O="clip-".concat(X||Y1()),I={tag:"mask",attributes:H(H({},T0),{},{id:h,maskUnits:"userSpaceOnUse",maskContentUnits:"userSpaceOnUse"}),children:[R,C]},D={tag:"defs",children:[{tag:"clipPath",attributes:{id:O},children:J8(U)},I]};return Q.push(D,{tag:"rect",attributes:H({fill:"currentColor","clip-path":"url(#".concat(O,")"),mask:"url(#".concat(h,")")},T0)}),{children:Q,attributes:K}}}},q8={provides:function(Z){var q=!1;if(u.matchMedia)q=u.matchMedia("(prefers-reduced-motion: reduce)").matches;Z.missingIconAbstract=function(){var Q=[],K={fill:"currentColor"},B={attributeType:"XML",repeatCount:"indefinite",dur:"2s"};Q.push({tag:"path",attributes:H(H({},K),{},{d:"M156.5,447.7l-12.6,29.5c-18.7-9.5-35.9-21.2-51.5-34.9l22.7-22.7C127.6,430.5,141.5,440,156.5,447.7z M40.6,272H8.5 c1.4,21.2,5.4,41.7,11.7,61.1L50,321.2C45.1,305.5,41.8,289,40.6,272z M40.6,240c1.4-18.8,5.2-37,11.1-54.1l-29.5-12.6 C14.7,194.3,10,216.7,8.5,240H40.6z M64.3,156.5c7.8-14.9,17.2-28.8,28.1-41.5L69.7,92.3c-13.7,15.6-25.5,32.8-34.9,51.5 L64.3,156.5z M397,419.6c-13.9,12-29.4,22.3-46.1,30.4l11.9,29.8c20.7-9.9,39.8-22.6,56.9-37.6L397,419.6z M115,92.4 c13.9-12,29.4-22.3,46.1-30.4l-11.9-29.8c-20.7,9.9-39.8,22.6-56.8,37.6L115,92.4z M447.7,355.5c-7.8,14.9-17.2,28.8-28.1,41.5 l22.7,22.7c13.7-15.6,25.5-32.9,34.9-51.5L447.7,355.5z M471.4,272c-1.4,18.8-5.2,37-11.1,54.1l29.5,12.6 c7.5-21.1,12.2-43.5,13.6-66.8H471.4z M321.2,462c-15.7,5-32.2,8.2-49.2,9.4v32.1c21.2-1.4,41.7-5.4,61.1-11.7L321.2,462z M240,471.4c-18.8-1.4-37-5.2-54.1-11.1l-12.6,29.5c21.1,7.5,43.5,12.2,66.8,13.6V471.4z M462,190.8c5,15.7,8.2,32.2,9.4,49.2h32.1 c-1.4-21.2-5.4-41.7-11.7-61.1L462,190.8z M92.4,397c-12-13.9-22.3-29.4-30.4-46.1l-29.8,11.9c9.9,20.7,22.6,39.8,37.6,56.9 L92.4,397z M272,40.6c18.8,1.4,36.9,5.2,54.1,11.1l12.6-29.5C317.7,14.7,295.3,10,272,8.5V40.6z M190.8,50 c15.7-5,32.2-8.2,49.2-9.4V8.5c-21.2,1.4-41.7,5.4-61.1,11.7L190.8,50z M442.3,92.3L419.6,115c12,13.9,22.3,29.4,30.5,46.1 l29.8-11.9C470,128.5,457.3,109.4,442.3,92.3z M397,92.4l22.7-22.7c-15.6-13.7-32.8-25.5-51.5-34.9l-12.6,29.5 C370.4,72.1,384.4,81.5,397,92.4z"})});var z=H(H({},B),{},{attributeName:"opacity"}),X={tag:"circle",attributes:H(H({},K),{},{cx:"256",cy:"364",r:"28"}),children:[]};if(!q)X.children.push({tag:"animate",attributes:H(H({},B),{},{attributeName:"r",values:"28;14;28;28;14;28;"})},{tag:"animate",attributes:H(H({},z),{},{values:"1;0;1;1;0;1;"})});if(Q.push(X),Q.push({tag:"path",attributes:H(H({},K),{},{opacity:"1",d:"M263.7,312h-16c-6.6,0-12-5.4-12-12c0-71,77.4-63.9,77.4-107.8c0-20-17.8-40.2-57.4-40.2c-29.1,0-44.3,9.6-59.2,28.7 c-3.9,5-11.1,6-16.2,2.4l-13.1-9.2c-5.6-3.9-6.9-11.8-2.6-17.2c21.2-27.2,46.4-44.7,91.2-44.7c52.3,0,97.4,29.8,97.4,80.2 c0,67.6-77.4,63.5-77.4,107.8C275.7,306.6,270.3,312,263.7,312z"}),children:q?[]:[{tag:"animate",attributes:H(H({},z),{},{values:"1;0;0;0;0;1;"})}]}),!q)Q.push({tag:"path",attributes:H(H({},K),{},{opacity:"0",d:"M232.5,134.5l7,168c0.3,6.4,5.6,11.5,12,11.5h9c6.4,0,11.7-5.1,12-11.5l7-168c0.3-6.8-5.2-12.5-12-12.5h-23 C237.7,122,232.2,127.7,232.5,134.5z"}),children:[{tag:"animate",attributes:H(H({},z),{},{values:"0;0;1;1;0;0;"})}]});return{tag:"g",attributes:{class:"missing"},children:Q}}}},Q8={hooks:function(){return{parseNodeAttributes:function(q,Q){var K=Q.getAttribute("data-fa-symbol"),B=K===null?!1:K===""?!0:K;return q.symbol=B,q}}}},K8=[i9,fJ,_J,pJ,dJ,tJ,aJ,eJ,Z8,q8,Q8];YJ(K8,{mixoutsTo:$});var{noAuto:b8,config:S,library:y8,dom:u8,parse:J1,findIconDefinition:f8,toHtml:_8,icon:g6,layer:p8,text:B8,counter:z8}=$;function H8(J){return J=J-0,J===J}function u6(J){if(H8(J))return J;return J=J.replace(/[_-]+(.)?/g,(Z,q)=>{return q?q.toUpperCase():""}),J.charAt(0).toLowerCase()+J.slice(1)}var G8=(J,Z)=>l.default.createElement("stop",{key:`${Z}-${J.offset}`,offset:J.offset,stopColor:J.color,...J.opacity!==void 0&&{stopOpacity:J.opacity}});function Y8(J){return J.charAt(0).toUpperCase()+J.slice(1)}var o=new Map,W8=1000;function w8(J){if(o.has(J))return o.get(J);let Z={},q=0,Q=J.length;while(q<Q){let K=J.indexOf(";",q),B=K===-1?Q:K,z=J.slice(q,B).trim();if(z){let X=z.indexOf(":");if(X>0){let V=z.slice(0,X).trim(),G=z.slice(X+1).trim();if(V&&G){let W=u6(V);Z[W.startsWith("webkit")?Y8(W):W]=G}}}q=B+1}if(o.size===W8){let K=o.keys().next().value;if(K)o.delete(K)}return o.set(J,Z),Z}function f6(J,Z,q={}){if(typeof Z==="string")return Z;let Q=(Z.children||[]).map((Y)=>{let U=Y;if((("fill"in q)||q.gradientFill)&&Y.tag==="path"&&"fill"in Y.attributes)U={...Y,attributes:{...Y.attributes,fill:void 0}};return f6(J,U)}),K=Z.attributes||{},B={};for(let[Y,U]of Object.entries(K))switch(!0){case Y==="class":{B.className=U;break}case Y==="style":{B.style=w8(String(U));break}case Y.startsWith("aria-"):case Y.startsWith("data-"):{B[Y.toLowerCase()]=U;break}default:B[u6(Y)]=U}let{style:z,role:X,"aria-label":V,gradientFill:G,...W}=q;if(z)B.style=B.style?{...B.style,...z}:z;if(X)B.role=X;if(V)B["aria-label"]=V,B["aria-hidden"]="false";if(G){B.fill=`url(#${G.id})`;let{type:Y,stops:U=[],...j}=G;Q.unshift(J(Y==="linear"?"linearGradient":"radialGradient",{...j,id:G.id},U.map(G8)))}return J(Z.tag,{...B,...W},...Q)}var U8=f6.bind(null,l.default.createElement),S6=(J,Z)=>{let q=l.useId();return J||(Z?q:void 0)},j8=class{constructor(J="react-fontawesome"){this.enabled=!1;let Z=!1;try{Z=typeof process<"u"&&process.env?.NODE_ENV==="development"}catch{}this.scope=J,this.enabled=Z}log(...J){if(!this.enabled)return;console.log(`[${this.scope}]`,...J)}warn(...J){if(!this.enabled)return;console.warn(`[${this.scope}]`,...J)}error(...J){if(!this.enabled)return;console.error(`[${this.scope}]`,...J)}};typeof process<"u"&&process.env?.FA_VERSION;var F8="searchPseudoElementsFullScan"in S&&typeof S.searchPseudoElementsFullScan==="boolean"?"7.0.0":"6.0.0",D8=Number.parseInt(F8)>=7,M8=()=>D8,Q0="fa",b={beat:"fa-beat",fade:"fa-fade",beatFade:"fa-beat-fade",bounce:"fa-bounce",shake:"fa-shake",spin:"fa-spin",spinPulse:"fa-spin-pulse",spinReverse:"fa-spin-reverse",pulse:"fa-pulse"},R8={left:"fa-pull-left",right:"fa-pull-right"},L8={"90":"fa-rotate-90","180":"fa-rotate-180","270":"fa-rotate-270"},v8={"2xs":"fa-2xs",xs:"fa-xs",sm:"fa-sm",lg:"fa-lg",xl:"fa-xl","2xl":"fa-2xl","1x":"fa-1x","2x":"fa-2x","3x":"fa-3x","4x":"fa-4x","5x":"fa-5x","6x":"fa-6x","7x":"fa-7x","8x":"fa-8x","9x":"fa-9x","10x":"fa-10x"},x={border:"fa-border",fixedWidth:"fa-fw",flip:"fa-flip",flipHorizontal:"fa-flip-horizontal",flipVertical:"fa-flip-vertical",inverse:"fa-inverse",rotateBy:"fa-rotate-by",swapOpacity:"fa-swap-opacity",widthAuto:"fa-width-auto"},C8={default:"fa-layers"};function h8(J){let Z=S.cssPrefix||S.familyPrefix||Q0;return Z===Q0?J:J.replace(new RegExp(String.raw`(?<=^|\s)${Q0}-`,"g"),`${Z}-`)}function O8(J){let{beat:Z,fade:q,beatFade:Q,bounce:K,shake:B,spin:z,spinPulse:X,spinReverse:V,pulse:G,fixedWidth:W,inverse:Y,border:U,flip:j,size:R,rotation:M,pull:v,swapOpacity:C,rotateBy:h,widthAuto:O,className:I}=J,D=[];if(I)D.push(...I.split(" "));if(Z)D.push(b.beat);if(q)D.push(b.fade);if(Q)D.push(b.beatFade);if(K)D.push(b.bounce);if(B)D.push(b.shake);if(z)D.push(b.spin);if(V)D.push(b.spinReverse);if(X)D.push(b.spinPulse);if(G)D.push(b.pulse);if(W)D.push(x.fixedWidth);if(Y)D.push(x.inverse);if(U)D.push(x.border);if(j===!0)D.push(x.flip);if(j==="horizontal"||j==="both")D.push(x.flipHorizontal);if(j==="vertical"||j==="both")D.push(x.flipVertical);if(R!==void 0&&R!==null)D.push(v8[R]);if(M!==void 0&&M!==null&&M!==0)D.push(L8[M]);if(v!==void 0&&v!==null)D.push(R8[v]);if(C)D.push(x.swapOpacity);if(!M8())return D;if(h)D.push(x.rotateBy);if(O)D.push(x.widthAuto);return(S.cssPrefix||S.familyPrefix||Q0)===Q0?D:D.map(h8)}var T8=(J)=>typeof J==="object"&&("icon"in J)&&!!J.icon;function b6(J){if(!J)return;if(T8(J))return J;return J1.icon(J)}function $8(J){return Object.keys(J)}var y6=new j8("FontAwesomeIcon"),_6={border:!1,className:"",mask:void 0,maskId:void 0,fixedWidth:!1,inverse:!1,flip:!1,icon:void 0,listItem:!1,pull:void 0,pulse:!1,rotation:void 0,rotateBy:!1,size:void 0,spin:!1,spinPulse:!1,spinReverse:!1,beat:!1,fade:!1,beatFade:!1,bounce:!1,shake:!1,symbol:!1,title:"",titleId:void 0,transform:void 0,swapOpacity:!1,widthAuto:!1},k8=new Set(Object.keys(_6)),P8=l.default.forwardRef((J,Z)=>{let q={..._6,...J},{icon:Q,mask:K,symbol:B,title:z,titleId:X,maskId:V,transform:G}=q,W=S6(V,Boolean(K)),Y=S6(X,Boolean(z)),U=b6(Q);if(!U)return y6.error("Icon lookup is undefined",Q),null;let j=O8(q),R=typeof G==="string"?J1.transform(G):G,M=b6(K),v=g6(U,{...j.length>0&&{classes:j},...R&&{transform:R},...M&&{mask:M},symbol:B,title:z,titleId:Y,maskId:W});if(!v)return y6.error("Could not find icon",U),null;let{abstract:C}=v,h={ref:Z};for(let O of $8(q)){if(k8.has(O))continue;h[O]=q[O]}return U8(C[0],h)});P8.displayName="FontAwesomeIcon";var s8=`${C8.default} ${x.fixedWidth}`;
export{P8 as uj};
