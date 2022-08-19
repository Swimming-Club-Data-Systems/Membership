"use strict";(self.webpackChunksrc=self.webpackChunksrc||[]).push([[298],{36286:function(e,t,a){a.d(t,{km:function(){return s},oY:function(){return r},zv:function(){return o},pB:function(){return l},Cq:function(){return c},OW:function(){return i},Td:function(){return u}});var n=a(99677);function s(e){return n.Z.getState()["SKIPCLEAR/GlobalSettings"][e]}function r(){return d("name")}function o(){return d("id")}function l(){return d("uuid")}function c(){return d("swim_england_code")}function i(e){return d("club_logo_path")+e}function d(e){return n.Z.getState()["SKIPCLEAR/Tenant"][e]}function u(e){document.title=e+" - "+s("club_name")}},83298:function(e,t,a){a.r(t),a.d(t,{default:function(){return b}});var n=a(67294),s=a(36286),r=a(62598),o=a(77289),l=a(88375),c=a(3330),i=a(35005),d=a(50533),u=a(21073),m=a(9669),p=a.n(m),w=a(39711),f=a(96974),g=a(60458),E=e=>n.createElement(n.Fragment,null,!e.loaded&&n.createElement(n.Fragment,null,n.createElement(g.Z,{xs:6,animation:"glow"}),n.createElement(g.Z,{className:"w-75",animation:"glow"})," ",n.createElement(g.Z,{className:"w-25",animation:"glow"})),e.loaded&&e.children);const h=o.Ry().shape({password:o.Z_().required("You must provide a password").min(8,"Your password must be at least 8 characters").matches(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/,"Your password must contain at least one lower case letter, one upper case letter and one number").test("is-pwned","Your password is insecure",(async e=>await(async e=>{try{return!(await p().post("/api/utilities/pwned-password-check",{password:e})).data.pwned}catch(e){return!0}})(e))),confirmPassword:o.Z_().required("You must confirm your password").oneOf([o.iH("password"),null],"Passwords do not match")});var b=(0,d.$j)(u.K,u.ZS)((e=>{const[t,a]=(0,n.useState)(null),[o,d]=(0,n.useState)(!1),[u,m]=(0,n.useState)(!1),[g]=(0,w.lr)(),b=(0,f.s0)();return(0,n.useEffect)((()=>{(async()=>{s.Td("Get back into your account"),e.setType("resetPassword");const t=await p().post("/api/auth/can-password-reset",{token:g.get("auth-code")});d(t.data.success),m(!0)})()}),[]),n.createElement(E,{loaded:u},!o&&n.createElement(n.Fragment,null,n.createElement(l.Z,{variant:"danger"},n.createElement("p",{className:"mb-0"},n.createElement("strong",null,"We couldn't find a matching password reset request")),n.createElement("p",{className:"mb-0"},"Please try checking the link in the password reset email we sent you. Reset links expire after two days."))),o&&n.createElement(n.Fragment,null,t&&n.createElement("div",{className:"alert alert-danger"},t.message),n.createElement(r.J9,{validationSchema:h,onSubmit:async(e,t)=>{let{setSubmitting:n}=t;n(!0);try{const t=await p().post("/api/auth/complete-password-reset",{token:g.get("auth-code"),password:e.password});t.data.success?b("/login",{state:{successfulReset:!0}}):a({type:"danger",message:t.data.message})}catch(e){a({type:"danger",message:e.message})}n(!1)},initialValues:{password:"",confirmPassword:""}},(e=>{let{handleSubmit:t,handleChange:a,handleBlur:s,values:r,touched:o,isValid:l,errors:d,isSubmitting:u,dirty:m}=e;return n.createElement(c.Z,{noValidate:!0,onSubmit:t,onBlur:s},n.createElement("div",{className:"mb-3"},n.createElement(c.Z.Group,{controlId:"password"},n.createElement(c.Z.Label,null,"New password"),n.createElement(c.Z.Control,{type:"password",name:"password",value:r.password,onChange:a,isValid:o.password&&!d.password,isInvalid:o.password&&d.password,size:"lg",autoComplete:"new-password"}),d.password&&n.createElement(c.Z.Control.Feedback,{type:"invalid"},d.password))),n.createElement("div",{className:"mb-3"},n.createElement(c.Z.Group,{controlId:"confirmPassword"},n.createElement(c.Z.Label,null,"Confirm password"),n.createElement(c.Z.Control,{type:"password",name:"confirmPassword",value:r.confirmPassword,onChange:a,isValid:o.confirmPassword&&!d.confirmPassword,isInvalid:o.confirmPassword&&d.confirmPassword,size:"lg",autoComplete:"new-password"}),d.confirmPassword&&n.createElement(c.Z.Control.Feedback,{type:"invalid"},d.confirmPassword))),n.createElement("p",{className:"mb-5"},n.createElement(i.Z,{size:"lg",type:"submit",disabled:!m||!l||u},"Change password")))}))))}))}}]);
//# sourceMappingURL=298.f97deff61a7623eb987d.js.map