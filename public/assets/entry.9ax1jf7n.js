import{ah as w}from"/assets/entry.kt5nymfe.js?v=50f150d24267cb17";import{Ri as v}from"/assets/entry.nwwnhc74.js?v=50f150d24267cb17";import{Cr as k}from"/assets/entry.3ck3c65s.js?v=50f150d24267cb17";import{Er as G}from"/assets/entry.jjf5jc5d.js?v=50f150d24267cb17";import{Fr as E}from"/assets/entry.r3nyea1z.js?v=50f150d24267cb17";var z=E(G(),1);var H=v.div`
    .fade-enter,
    .fade-exit,
    .fade-appear {
        will-change: opacity;
    }

    .fade-enter,
    .fade-appear {
        opacity: 0;

        &.fade-enter-active,
        &.fade-appear-active {
            opacity: 1;
            transition-property: opacity;
            transition-timing-function: cubic-bezier(0.4, 0, 1, 1);
            transition-duration: ${(b)=>b.$timeout}ms;
        }
    }

    .fade-exit {
        opacity: 1;

        &.fade-exit-active {
            opacity: 0;
            transition-property: opacity;
            transition-timing-function: cubic-bezier(0.4, 0, 1, 1);
            transition-duration: ${(b)=>b.$timeout}ms;
        }
    }
`,A=({timeout:b,children:B,...D})=>{let q=z.useRef(null);return k(H,{$timeout:b,children:k(w,{timeout:b,classNames:"fade",nodeRef:q,...D,children:k("div",{ref:q,children:B})})})};A.displayName="Fade";var M=A;
export{M as Le};
