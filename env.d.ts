/// <reference types="vite/client" />
/// <reference types="@userfrosting/sprinkle-core" />
/// <reference types="@userfrosting/sprinkle-account" />
/// <reference types="@userfrosting/theme-pink-cupcake/components" />

/**
 * This is required for webpack to correctly import vue file when using TypeScript.
 */ 
declare module '*.vue' {
    const content: any;
    export default content;
}