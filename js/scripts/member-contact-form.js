parcelRequire=function(e,r,t,n){var i,o="function"==typeof parcelRequire&&parcelRequire,u="function"==typeof require&&require;function f(t,n){if(!r[t]){if(!e[t]){var i="function"==typeof parcelRequire&&parcelRequire;if(!n&&i)return i(t,!0);if(o)return o(t,!0);if(u&&"string"==typeof t)return u(t);var c=new Error("Cannot find module '"+t+"'");throw c.code="MODULE_NOT_FOUND",c}p.resolve=function(r){return e[t][1][r]||r},p.cache={};var l=r[t]=new f.Module(t);e[t][0].call(l.exports,p,l,l.exports,this)}return r[t].exports;function p(e){return f(p.resolve(e))}}f.isParcelRequire=!0,f.Module=function(e){this.id=e,this.bundle=f,this.exports={}},f.modules=e,f.cache=r,f.parent=o,f.register=function(r,t){e[r]=[function(e,r){r.exports=t},{}]};for(var c=0;c<t.length;c++)try{f(t[c])}catch(e){i||(i=e)}if(t.length){var l=f(t[t.length-1]);"object"==typeof exports&&"undefined"!=typeof module?module.exports=l:"function"==typeof define&&define.amd?define(function(){return l}):n&&(this[n]=l)}if(parcelRequire=f,i)throw i;return f}({"fcMS":[function(require,module,exports) {
function n(n,o){if(!(n instanceof o))throw new TypeError("Cannot call a class as a function")}module.exports=n;
},{}],"P8NW":[function(require,module,exports) {
function e(e,r){for(var n=0;n<r.length;n++){var t=r[n];t.enumerable=t.enumerable||!1,t.configurable=!0,"value"in t&&(t.writable=!0),Object.defineProperty(e,t.key,t)}}function r(r,n,t){return n&&e(r.prototype,n),t&&e(r,t),r}module.exports=r;
},{}],"E7HD":[function(require,module,exports) {
function e(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}module.exports=e;
},{}],"AkAO":[function(require,module,exports) {
function t(o,e){return module.exports=t=Object.setPrototypeOf||function(t,o){return t.__proto__=o,t},t(o,e)}module.exports=t;
},{}],"d4H2":[function(require,module,exports) {
var e=require("./setPrototypeOf");function r(r,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");r.prototype=Object.create(t&&t.prototype,{constructor:{value:r,writable:!0,configurable:!0}}),t&&e(r,t)}module.exports=r;
},{"./setPrototypeOf":"AkAO"}],"b9XL":[function(require,module,exports) {
function o(t){return"function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?module.exports=o=function(o){return typeof o}:module.exports=o=function(o){return o&&"function"==typeof Symbol&&o.constructor===Symbol&&o!==Symbol.prototype?"symbol":typeof o},o(t)}module.exports=o;
},{}],"pxk2":[function(require,module,exports) {
var e=require("../helpers/typeof"),r=require("./assertThisInitialized");function t(t,i){return!i||"object"!==e(i)&&"function"!=typeof i?r(t):i}module.exports=t;
},{"../helpers/typeof":"b9XL","./assertThisInitialized":"E7HD"}],"UJE0":[function(require,module,exports) {
function t(e){return module.exports=t=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)},t(e)}module.exports=t;
},{}],"Eofm":[function(require,module,exports) {
"use strict";var e=a(require("@babel/runtime/helpers/classCallCheck")),t=a(require("@babel/runtime/helpers/createClass")),i=a(require("@babel/runtime/helpers/assertThisInitialized")),n=a(require("@babel/runtime/helpers/inherits")),r=a(require("@babel/runtime/helpers/possibleConstructorReturn")),o=a(require("@babel/runtime/helpers/getPrototypeOf"));function a(e){return e&&e.__esModule?e:{default:e}}function s(e){var t=l();return function(){var i,n=(0,o.default)(e);if(t){var a=(0,o.default)(this).constructor;i=Reflect.construct(n,arguments,a)}else i=n.apply(this,arguments);return(0,r.default)(this,i)}}function l(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],function(){})),!0}catch(e){return!1}}var c=wp.element,d=c.Component,u=c.render,m=c.createElement,p=c.Fragment,f=wp.i18n.__,v=wp.blockEditor.RichText,h=wp.components,g=h.Button,b=h.TextControl,C=h.Modal,S=h.Snackbar,y=h.SelectControl,E=h.Notice,k=wp,M=k.apiFetch,w=wp.url.isEmail,I=function(r){(0,n.default)(a,r);var o=s(a);function a(){var t;return(0,e.default)(this,a),(t=o.apply(this,arguments)).state={name:"",email:"",message:"",confirmationCode:"",displayUserId:0,loggedInUserId:0,resultMessage:"",needsValidation:!1,checked:!1,sending:!1,verifiedEmail:{},isEditorOpen:!1,feedback:[],situations:[],situation:"reception-contact-member",needsContent:!0,modalTitle:f("Envoyer un message","reception"),modalAction:f("Envoyer","reception")},t.closeEmailEditor=t.closeEmailEditor.bind((0,i.default)(t)),t.sendValidationCode=t.sendValidationCode.bind((0,i.default)(t)),t.checkValidationCode=t.checkValidationCode.bind((0,i.default)(t)),t.isSelfProfile=!1,t.isUserLoggedIn=!1,t}return(0,t.default)(a,[{key:"componentDidMount",value:function(){var e=this.state,t=e.displayUserId,i=e.loggedInUserId;window.receptionMemberContactForm&&(window.receptionMemberContactForm.displayUserId&&(t=parseInt(window.receptionMemberContactForm.displayUserId,10),this.setState({displayUserId:t})),window.receptionMemberContactForm.loggedInUserId&&(i=parseInt(window.receptionMemberContactForm.loggedInUserId,10),this.setState({loggedInUserId:i})),window.receptionMemberContactForm.name&&this.setState({name:window.receptionMemberContactForm.name}),window.receptionMemberContactForm.email&&this.setState({email:window.receptionMemberContactForm.email}),window.receptionMemberContactForm.situations&&this.setState({situations:JSON.parse(window.receptionMemberContactForm.situations)})),this.isUserLoggedIn=i&&0!==i,this.isSelfProfile=this.isUserLoggedIn&&t===i}},{key:"setSituation",value:function(e){var t=this.state,i=t.situations,n=t.modalTitle,r=t.modalAction,o=!0,a=n,s=r;i.forEach(function(t){e===t.value&&(t.needs_content&&!1!==t.needs_content||(o=!1),t.label&&(a=t.label),t.action&&(s=t.action))}),this.setState({situation:e,needsContent:o,modalTitle:a,modalAction:s})}},{key:"openEmailEditor",value:function(e){var t=this;e.preventDefault();var i=this.state,n=i.name,r=i.email,o=i.checked;if(this.setState({isEditorOpen:!0}),!this.isUserLoggedIn||this.isSelfProfile){if(!n)return void this.setState({feedback:[m(E,{key:"missing-name",status:"error",isDismissible:!1},f("Merci de renseigner un prénom et un nom.","reception"))]});if(!w(r))return void this.setState({feedback:[m(E,{key:"missing-email",status:"error",isDismissible:!1},f("Merci de renseigner un e-mail valide.","reception"))]});o||(this.setState({feedback:[m(E,{key:"checking-email",status:"info",isDismissible:!1},f("Vérification de votre e-mail. merci de patienter.","reception"))]}),M({path:"/reception/v1/email/check/"+r,method:"GET"}).then(function(e){var i=[];t.isSelfProfile?!0===e.spam?i=[m(E,{key:"reception-spam",status:"error",isDismissible:!1},f("Désolé, l’e-mail du visiteur a été marqué comme indésirable : vous ne pouvez pas utiliser le site pour le contacter.","reception"))]:e.id&&e.confirmed||(i=[m(E,{key:"reception-not-verified",status:"error",isDismissible:!1},f("L‘email du visisteur que vous souhaitez contacter n’a pas été vérifié, merci de lui demander de le faire ou de le contacter directement.","reception"))]):!0===e.spam?i=[m(E,{key:"reception-spam",status:"error",isDismissible:!1},f("Désolé, votre e-mail a été marqué comme indésirable : vous ne pouvez pas contacter ce membre.","reception"))]:e.id?e.confirmed||(i=[m(E,{key:"reception-do-verify",status:"info",isDismissible:!1},f("Le code de validation associé à votre e-mail a besoin d’être vérifié, Merci de copier le code de validation que vous avez reçu dans le champ ci-dessous avant de lancer la vérification.","reception"))],t.setState({needsValidation:!0})):i=[m(p,{key:"reception-unverified"},m(E,{status:"warning",isDismissible:!1},f("Votre e-mail a besoin d’être validé, cette étape de validation est nécessaire afin de garantir à nos membres qu’ils ne recevront pas de messages indésirables.","reception")),m("p",{className:"reception-help description"},f("Merci de cliquer sur le bouton « Obtenir le code de validation » afin de recevoir un e-mail le contenant dans les prochaines minutes.","reception")),m("p",{className:"reception-help description"},f("Dés que vous l’aurez reçu, vous pourrez revenir sur cette page afin de l’utiliser pour déverrouiller cette sécurité et contacter ce membre. Merci de votre compréhension.","reception")),m(g,{isPrimary:!0,onClick:function(e){return t.sendValidationCode(e)}},f("Obtenir le code de validation","reception")))],t.setState({feedback:i,verifiedEmail:e})}),this.setState({checked:!0}))}}},{key:"closeEmailEditor",value:function(){this.setState({isEditorOpen:!1,feedback:[],checked:!1})}},{key:"sendValidationCode",value:function(e){var t=this;e.preventDefault();var i=this.state,n=i.name,r=i.email,o=i.displayUserId;M({path:"/reception/v1/email",method:"POST",data:{name:n,email:r,member_id:o}}).then(function(e){t.setState({resultMessage:f("L’e-mail contenant le code de validation vous a bien été transmis","reception"),verifiedEmail:e})},function(){t.setState({resultMessage:f("Désolé, une erreur a empêché l’envoi de s’effectuer.","reception")})}),this.closeEmailEditor()}},{key:"checkValidationCode",value:function(e){var t=this;e.preventDefault();var i=this.state,n=i.email,r=i.confirmationCode;M({path:"/reception/v1/email/validate/"+n,method:"PUT",data:{code:r}}).then(function(e){t.setState({resultMessage:f("Merci d’avoir validé votre e-mail. Vous pouvez poursuivre la rédaction de votre message","reception"),verifiedEmail:e,needsValidation:!1})},function(){t.setState({resultMessage:f("Désolé, la validation de votre e-mail a échoué.","reception"),confirmationCode:""})}),this.closeEmailEditor()}},{key:"sendEmail",value:function(e){var t=this;e.preventDefault();var i=this.state,n=i.name,r=i.email,o=i.message,a=i.displayUserId,s=i.loggedInUserId,l=i.situation,c=i.needsContent,d=i.sending,u={name:n,email:r,message:o};if(s&&!this.isSelfProfile&&(u.current_user=s),"reception-contact-member"!==l&&(u.situation=l,c||(u.message=l)),!u.message)return this.setState({resultMessage:f("Merci d’ajouter du texte à votre e-mail.","reception"),message:"",sending:!1}),void this.closeEmailEditor();d||(this.setState({sending:!0}),M({path:"/reception/v1/email/send/"+a,method:"POST",data:u}).then(function(e){t.setState({resultMessage:f("Votre e-mail a bien été transmis.","reception"),verifiedEmail:e.verifiedEmail,message:"",sending:!1})},function(){t.setState({resultMessage:f("Désolé, l’envoi de votre e-mail a échoué.","reception"),message:"",sending:!1})})),this.closeEmailEditor()}},{key:"render",value:function(){var e,t=this,i=this.state,n=i.displayUserId,r=i.loggedInUserId,o=i.name,a=i.email,s=i.isEditorOpen,l=i.feedback,c=i.resultMessage,d=i.confirmationCode,u=i.needsValidation,h=i.message,E=i.situations,k=i.situation,M=i.needsContent,w=i.modalTitle,I=i.modalAction,U=n&&this.isSelfProfile?f("E-mail du destinataire (obligatoire)","reception"):f("Votre e-mail (obligatoire)","reception"),D=n&&this.isSelfProfile?f("Prénom et nom du destinataire (obligatoire)","reception"):f("Vos prénom et nom (obligatoire)","reception"),V=0!==l.length?f("Fermer","reception"):f("Annuler","reception");return this.isUserLoggedIn&&!this.isSelfProfile||(e=m(p,null,m(b,{label:D,type:"text",value:o,onChange:function(e){return t.setState({name:e})},required:!0}),m(b,{label:U,type:"email",value:a,onChange:function(e){return t.setState({email:e})},required:!0}),0!==E.length&&0===r&&m(y,{label:f("Motif de votre contact","reception"),value:k,options:E,onChange:function(e){return t.setSituation(e)}}))),m(p,null,e,m(g,{isPrimary:!0,onClick:function(e){return t.openEmailEditor(e)}},f("Rédiger votre message","reception")),""!==c&&m(S,{onRemove:function(){return t.setState({resultMessage:""})}},c),s&&m(C,{title:w,onRequestClose:this.closeEmailEditor,className:"reception-contact-form-modal"},m("div",{className:"reception-modal-content"},l,0===l.length&&m(p,null,!0===M&&m(p,null,m("h2",null,f("Votre message","reception")),m(v,{value:h,tagName:"p",onChange:function(e){return t.setState({message:e})},placeholder:f("Utilisez cette zone pour rédiger votre message","reception"),multiline:!0})),m(g,{isPrimary:!0,onClick:function(e){return t.sendEmail(e)}},I)),!0===u&&m(p,null,m(b,{label:f("Code de validation","reception"),type:"password",value:d,onChange:function(e){return t.setState({confirmationCode:e})},required:!0}),m(g,{isPrimary:!0,onClick:function(e){return t.checkValidationCode(e)}},f("Lancer la vérification","reception"))),m(g,{onClick:function(){return t.closeEmailEditor()}},V))))}}]),a}(d);u(m(I,null),document.querySelector(".reception-member-contact-form-content"));
},{"@babel/runtime/helpers/classCallCheck":"fcMS","@babel/runtime/helpers/createClass":"P8NW","@babel/runtime/helpers/assertThisInitialized":"E7HD","@babel/runtime/helpers/inherits":"d4H2","@babel/runtime/helpers/possibleConstructorReturn":"pxk2","@babel/runtime/helpers/getPrototypeOf":"UJE0"}]},{},["Eofm"], null)
//# sourceMappingURL=/scripts/member-contact-form.js.map