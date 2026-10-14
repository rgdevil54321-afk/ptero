import{Le as z}from"/assets/entry.9ax1jf7n.js?v=50f150d24267cb17";import{Xh as _}from"/assets/entry.3re8nd10.js?v=50f150d24267cb17";import{Qi as u,Ri as A,Wi as L}from"/assets/entry.nwwnhc74.js?v=50f150d24267cb17";import{Cr as t,Dr as K}from"/assets/entry.3ck3c65s.js?v=50f150d24267cb17";import{Er as Z}from"/assets/entry.jjf5jc5d.js?v=50f150d24267cb17";import{Fr as J}from"/assets/entry.r3nyea1z.js?v=50f150d24267cb17";var x=J(Z(),1);function Y(B){return(k)=>{return($,...y)=>u`
            @media (min-width: ${B[k]}px) {
                ${u($,...y)}
            }
        `}}var q=Y({xs:0,sm:640,md:768,lg:1024,xl:1280,xxl:1920});var Q=J(_(),1);var C=A.div.attrs({className:"fixed z-50 overflow-auto flex w-full inset-0"})`
    background: rgba(0, 0, 0, 0.7);
`,F=A.div.attrs({className:"relative flex flex-col w-full m-auto"})`
    max-width: 95%;
    max-height: calc(100vh - 8rem);
    ${q("md")`max-width: 75%`};
    ${q("lg")`max-width: 50%`};

    ${(B)=>B.alignTop&&u`
            margin-top: 20%;
            ${q("md")`margin-top: 10%`};
        `};

    margin-bottom: auto;

    & > .close-icon {
        position: absolute;
        right: 0px;
        padding: 0.5rem;
        color: white;
        cursor: pointer;
        opacity: 0.5;
        transition: all 150ms linear;
        top: -2.5rem;

        &:hover {
            opacity: 1;
            transform: rotate(90deg);
        }

        & > svg {
            width: 1.5rem;
            height: 1.5rem;
        }
    }
`,G=({visible:B,appear:k,dismissable:$,showSpinnerOverlay:y,top:U=!0,closeOnBackground:V=!0,closeOnEscape:I=!0,onDismissed:g,children:W})=>{let f=($||!0)&&!y;return x.useEffect(()=>{if(!f||!I)return;let T=(X)=>{if(X.key==="Escape"&&B)g()};return window.addEventListener("keydown",T),()=>{window.removeEventListener("keydown",T)}},[f,I,B,g]),t(z,{in:B,timeout:150,appear:k||!0,unmountOnExit:!0,onExited:()=>g(),children:t(C,{onClick:(T)=>T.stopPropagation(),onContextMenu:(T)=>T.stopPropagation(),onMouseDown:(T)=>{if(f&&V){if(T.stopPropagation(),T.target===T.currentTarget)g()}},children:K(F,{alignTop:U,children:[f&&t("div",{className:"close-icon",onClick:()=>g(),children:t("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor",children:t("path",{strokeLinecap:"round",strokeLinejoin:"round",strokeWidth:"2",d:"M6 18L18 6M6 6l12 12"})})}),y&&t(z,{timeout:150,appear:!0,in:!0,children:t("div",{className:"absolute w-full h-full rounded-lg flex items-center justify-center",style:{background:"hsla(211, 10%, 53%, 0.35)",zIndex:9999},children:t(L,{})})}),t("div",{className:"transition-all duration-150",children:W})]})})})},H=({children:B,...k})=>{let $=x.useRef(document.getElementById("modal-portal"));return Q.createPortal(t(G,{...k,children:B}),$.current)},b=H;
export{C as Nb,b as Ob};
