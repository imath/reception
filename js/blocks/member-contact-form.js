parcelRequire=function(e,r,t,n){var i,o="function"==typeof parcelRequire&&parcelRequire,u="function"==typeof require&&require;function f(t,n){if(!r[t]){if(!e[t]){var i="function"==typeof parcelRequire&&parcelRequire;if(!n&&i)return i(t,!0);if(o)return o(t,!0);if(u&&"string"==typeof t)return u(t);var c=new Error("Cannot find module '"+t+"'");throw c.code="MODULE_NOT_FOUND",c}p.resolve=function(r){return e[t][1][r]||r},p.cache={};var l=r[t]=new f.Module(t);e[t][0].call(l.exports,p,l,l.exports,this)}return r[t].exports;function p(e){return f(p.resolve(e))}}f.isParcelRequire=!0,f.Module=function(e){this.id=e,this.bundle=f,this.exports={}},f.modules=e,f.cache=r,f.parent=o,f.register=function(r,t){e[r]=[function(e,r){r.exports=t},{}]};for(var c=0;c<t.length;c++)try{f(t[c])}catch(e){i||(i=e)}if(t.length){var l=f(t[t.length-1]);"object"==typeof exports&&"undefined"!=typeof module?module.exports=l:"function"==typeof define&&define.amd?define(function(){return l}):n&&(this[n]=l)}if(parcelRequire=f,i)throw i;return f}({"L11C":[function(require,module,exports) {
var e=wp.blocks.registerBlockType,t=wp.element,r=t.createElement,o=t.Fragment,i=wp.components,l=i.Disabled,n=i.PanelBody,c=i.TextControl,a=wp.blockEditor.InspectorControls,p=wp.editor.ServerSideRender,s=wp.i18n.__;e("reception/member-contact-form",{title:s("Formulaire de contact du membre","reception"),description:s("Ce bloc permet aux visiteurs de votre site de contacter un membre de votre communauté.","reception"),supports:{className:!1,anchor:!1,multiple:!1,reusable:!1},icon:"email-alt",category:"widgets",attributes:{blockTitle:{type:"string",default:s("Contacter ce membre","reception")}},edit:function(e){var t=e.attributes,i=e.setAttributes,u=t.blockTitle;return r(o,null,r(a,null,r(n,{title:s("Réglages","reception"),initialOpen:!0},r(c,{label:s("Titre du bloc","reception"),value:u,onChange:function(e){i({blockTitle:e})},help:s("Pour masquer le titre du bloc, laisser ce champ vide.","reception")}))),r(l,null,r(p,{block:"reception/member-contact-form",attributes:t})))}});
},{}]},{},["L11C"], null)
//# sourceMappingURL=/blocks/member-contact-form.js.map