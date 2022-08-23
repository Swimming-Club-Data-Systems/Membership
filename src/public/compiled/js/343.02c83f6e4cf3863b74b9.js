(self.webpackChunksrc=self.webpackChunksrc||[]).push([[343],{4147:function(e,t,a){"use strict";a.r(t),a.d(t,{default:function(){return k}});var n=a(67294),r=a(77289),s=a(79236),l=a(36286),m=a(62598),c=a(3330);function i(){return i=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var a=arguments[t];for(var n in a)Object.prototype.hasOwnProperty.call(a,n)&&(e[n]=a[n])}return e},i.apply(this,arguments)}var u=e=>{let{label:t,helpText:a,mb:r,disabled:s,...l}=e;const[u,o]=(0,m.U$)(l),{isSubmitting:d}=(0,m.u6)(),j=r||"mb-3";return n.createElement(n.Fragment,null,n.createElement(c.Z.Group,{className:j,controlId:l.id||l.name},n.createElement(c.Z.Label,null,t),n.createElement(c.Z.Control,i({type:"date",isValid:o.touched&&!o.error,isInvalid:o.touched&&o.error,disabled:d||s},u,l)),o.touched&&o.error?n.createElement(c.Z.Control.Feedback,{type:"invalid"},o.error):null,a&&n.createElement(c.Z.Text,{className:"text-muted"},a)))},o=a(70977),d=a(21407),j=a(39380),g=a(88375),b=a(86025),h=a(16714),E=a(9669),p=a.n(E),f=a(30381),v=a.n(f);const y=e=>n.createElement(n.Fragment,null,e&&n.createElement("ul",{className:"list-unstyled"},e.map(((e,t)=>n.createElement("li",{key:t},n.createElement("h4",null,"".concat(e.first_name," ").concat(e.last_name)),n.createElement("dl",{className:"row"},n.createElement("dt",{className:"col-6"},"Date of birth"),n.createElement("dd",{className:"col-6"},v()(e.date_of_birth).format("DD/MM/YYYY")),n.createElement("dt",{className:"col-6"},"Age today"),n.createElement("dd",{className:"col-6"},e.age_today),n.createElement("dt",{className:"col-6"},"Age on day"),n.createElement("dd",{className:"col-6"},e.age_on_day),n.createElement("dt",{className:"col-6"},"ASA Number"),n.createElement("dd",{className:"col-6"},e.ngb_id),n.createElement("dt",{className:"col-6"},"ASA Type"),n.createElement("dd",{className:"col-6"},e.ngb_category_name),e.gender_identity&&n.createElement(n.Fragment,null,n.createElement("dt",{className:"col-6"},"Gender Identity"),n.createElement("dd",{className:"col-6"},e.gender_identity))))))),!e&&n.createElement(g.Z,{variant:"warning"},"No matches"));var k=()=>{const[e,t]=(0,n.useState)(null),[a,m]=(0,n.useState)(null);return(0,n.useEffect)((()=>{l.Td("Junior and Arena League Members Report")}),[]),n.createElement(n.Fragment,null,n.createElement(d.Z,{breadcrumbs:n.createElement(j.Z,{crumbs:[{to:"/admin",title:"Admin",name:"Admin"},{to:"/admin/reports",title:"Reports",name:"Reports"},{to:"/admin/reports/junior-league-report",title:"Eligible League Members Report",name:"League Members"}]}),title:"Junior League Members",subtitle:"Get a list of members valid for Junior or Arena League"}),n.createElement("div",{className:"container-xl"},n.createElement("div",{className:"row"},n.createElement("div",{className:"col-lg-8"},n.createElement(b.Z,{className:"mb-3"},n.createElement(b.Z.Body,null,a&&n.createElement(g.Z,{variant:"danger"},a),n.createElement(s.Z,{initialValues:{minAge:"",maxAge:"",ageOn:v()().format("YYYY-MM-DD")},validationSchema:r.Ry({minAge:r.Rx("You must enter a minimum age").required("You must enter a minimum age").integer("You must enter a whole number").min(0,"You must enter a value greater than zero").max(120,"You must enter a value less than 120"),maxAge:r.Rx("You must enter a maximum age").required("You must enter a maximum age").integer("You must enter a whole number").min(0,"You must enter a value greater than zero").max(120,"You must enter a value less than 120").test({name:"max-greater-than-min",exclusive:!1,params:{},message:"You must enter a maximum age which is greater than or equal to the minimum age",test:function(e){return e>=parseInt(this.parent.minAge)}}).test({name:"range-less-than-twenty",exclusive:!1,params:{},message:"The range between the minimum age and maximum age must not be greater than twenty",test:function(e){return e-parseInt(this.parent.minAge)<=20}}),ageOn:r.hT().required("You must enter a date").min("2000-01-01","You must enter a date greater than 1 January 2000")}),onSubmit:async(e,a)=>{let{setSubmitting:n}=a;n(!0);try{const a=await p().get("/api/admin/reports/league-members-report",{params:{min_age:e.minAge,max_age:e.maxAge,age_on_day:e.ageOn}});a.data.success?(n(!1),m(null),t(a.data.members)):(n(!1),m(a.data.message),t(null))}catch(e){n(!1),m("An unknown error has occurred"),t(null)}},submitTitle:"View report",onClear:()=>{t(null)}},n.createElement("div",{className:"row"},n.createElement("div",{className:"col-md"},n.createElement(o.Z,{label:"Minimum age",name:"minAge",type:"number",inputMode:"numeric",min:"0",max:"120",step:"1"})),n.createElement("div",{className:"col-md"},n.createElement(o.Z,{label:"Maximum age",name:"maxAge",type:"number",inputMode:"numeric",min:"0",max:"120",step:"1"}))),n.createElement(u,{label:"Age on day",name:"ageOn"})))),e&&n.createElement(n.Fragment,null,0==e.length&&n.createElement(g.Z,{variant:"warning"},n.createElement("p",{className:"mb-0"},n.createElement("strong",null,"No members in range")),n.createElement("p",{className:"mb-0"},"Please try a new selection")),e.length>0&&n.createElement(b.Z,null,n.createElement(h.Z,{variant:"flush"},e.map((e=>n.createElement(h.Z.Item,{key:e.age},n.createElement("h2",null,"Age ",e.age),n.createElement("div",{className:"row"},n.createElement("div",{className:"col-md"},n.createElement("h3",null,"Male"),y(e.male)),n.createElement("div",{className:"col-md"},n.createElement("h3",null,"Female"),y(e.female)))))))))))))}},36286:function(e,t,a){"use strict";a.d(t,{km:function(){return r},oY:function(){return s},zv:function(){return l},pB:function(){return m},Cq:function(){return c},Td:function(){return u}});var n=a(99677);function r(e){return n.Z.getState()["SKIPCLEAR/GlobalSettings"][e]}function s(){return i("name")}function l(){return i("id")}function m(){return i("uuid")}function c(){return i("swim_england_code")}function i(e){return n.Z.getState()["SKIPCLEAR/Tenant"][e]}function u(e){document.title=e+" - "+r("club_name")}},39380:function(e,t,a){"use strict";var n=a(67294),r=a(96974),s=a(39711);t.Z=e=>{const t=e.crumbs;if(0===t.length)return;const a=t.map((e=>{let t=(0,r.WU)(e.to);return(0,r.bS)({path:t.pathname,end:!0})?n.createElement("li",{className:"breadcrumb-item active",key:e.to,title:e.title,"aria-current":"page"},e.name):n.createElement("li",{className:"breadcrumb-item",key:e.to},n.createElement(s.rU,{to:e.to,title:e.title,state:{global_questionable_link:!0}},e.name))}));return n.createElement("nav",{"aria-label":"breadcrumb"},n.createElement("ol",{className:"breadcrumb"},a))}},79236:function(e,t,a){"use strict";var n=a(67294),r=a(62598),s=a(35005);const l=e=>{const{isSubmitting:t,dirty:a,isValid:l,errors:m,handleReset:c}=(0,r.u6)();return n.createElement(n.Fragment,null,!1,n.createElement("div",{className:"row"},n.createElement("div",{className:"col-auto ms-auto"},!e.hideClear&&n.createElement(n.Fragment,null,n.createElement(s.Z,{variant:"secondary",type:"button",onClick:()=>{e.onClear&&e.onClear(),c()},disabled:t||!a},e.clearTitle||"Clear")," "),n.createElement(s.Z,{variant:"primary",type:"submit",disabled:!a||!l||t},e.submitTitle||"Submit"))))};t.Z=e=>{const{initialValues:t,validationSchema:a,onSubmit:s,submitTitle:m,hideClear:c,clearTitle:i,onClear:u,hideButtons:o,...d}=e;return n.createElement(n.Fragment,null,n.createElement(r.J9,{initialValues:t,validationSchema:a,onSubmit:s},n.createElement(r.l0,d,e.children,!o&&n.createElement(l,{submitTitle:m,hideClear:c,clearTitle:i,onClear:u}))))}},70977:function(e,t,a){"use strict";var n=a(67294),r=a(62598),s=a(3330);function l(){return l=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var a=arguments[t];for(var n in a)Object.prototype.hasOwnProperty.call(a,n)&&(e[n]=a[n])}return e},l.apply(this,arguments)}t.Z=e=>{let{label:t,helpText:a,mb:m,disabled:c,...i}=e;const[u,o]=(0,r.U$)(i),{isSubmitting:d}=(0,r.u6)(),j=m||"mb-3";return n.createElement(n.Fragment,null,n.createElement(s.Z.Group,{className:j,controlId:i.id||i.name},n.createElement(s.Z.Label,null,t),n.createElement(s.Z.Control,l({isValid:o.touched&&!o.error,isInvalid:o.touched&&o.error,disabled:d||c},u,i)),o.touched&&o.error?n.createElement(s.Z.Control.Feedback,{type:"invalid"},o.error):null,a&&n.createElement(s.Z.Text,{className:"text-muted"},a)))}},46700:function(e,t,a){var n={"./af":42786,"./af.js":42786,"./ar":30867,"./ar-dz":14130,"./ar-dz.js":14130,"./ar-kw":96135,"./ar-kw.js":96135,"./ar-ly":56440,"./ar-ly.js":56440,"./ar-ma":47702,"./ar-ma.js":47702,"./ar-sa":16040,"./ar-sa.js":16040,"./ar-tn":37100,"./ar-tn.js":37100,"./ar.js":30867,"./az":31083,"./az.js":31083,"./be":9808,"./be.js":9808,"./bg":68338,"./bg.js":68338,"./bm":67438,"./bm.js":67438,"./bn":8905,"./bn-bd":76225,"./bn-bd.js":76225,"./bn.js":8905,"./bo":11560,"./bo.js":11560,"./br":1278,"./br.js":1278,"./bs":80622,"./bs.js":80622,"./ca":2468,"./ca.js":2468,"./cs":5822,"./cs.js":5822,"./cv":50877,"./cv.js":50877,"./cy":47373,"./cy.js":47373,"./da":24780,"./da.js":24780,"./de":59740,"./de-at":60217,"./de-at.js":60217,"./de-ch":60894,"./de-ch.js":60894,"./de.js":59740,"./dv":5300,"./dv.js":5300,"./el":50837,"./el.js":50837,"./en-au":78348,"./en-au.js":78348,"./en-ca":77925,"./en-ca.js":77925,"./en-gb":22243,"./en-gb.js":22243,"./en-ie":46436,"./en-ie.js":46436,"./en-il":47207,"./en-il.js":47207,"./en-in":44175,"./en-in.js":44175,"./en-nz":76319,"./en-nz.js":76319,"./en-sg":31662,"./en-sg.js":31662,"./eo":92915,"./eo.js":92915,"./es":55655,"./es-do":55251,"./es-do.js":55251,"./es-mx":96112,"./es-mx.js":96112,"./es-us":71146,"./es-us.js":71146,"./es.js":55655,"./et":5603,"./et.js":5603,"./eu":77763,"./eu.js":77763,"./fa":76959,"./fa.js":76959,"./fi":11897,"./fi.js":11897,"./fil":42549,"./fil.js":42549,"./fo":94694,"./fo.js":94694,"./fr":94470,"./fr-ca":63049,"./fr-ca.js":63049,"./fr-ch":52330,"./fr-ch.js":52330,"./fr.js":94470,"./fy":5044,"./fy.js":5044,"./ga":29295,"./ga.js":29295,"./gd":2101,"./gd.js":2101,"./gl":38794,"./gl.js":38794,"./gom-deva":27884,"./gom-deva.js":27884,"./gom-latn":23168,"./gom-latn.js":23168,"./gu":95349,"./gu.js":95349,"./he":24206,"./he.js":24206,"./hi":30094,"./hi.js":30094,"./hr":30316,"./hr.js":30316,"./hu":22138,"./hu.js":22138,"./hy-am":11423,"./hy-am.js":11423,"./id":29218,"./id.js":29218,"./is":90135,"./is.js":90135,"./it":90626,"./it-ch":10150,"./it-ch.js":10150,"./it.js":90626,"./ja":39183,"./ja.js":39183,"./jv":24286,"./jv.js":24286,"./ka":12105,"./ka.js":12105,"./kk":47772,"./kk.js":47772,"./km":18758,"./km.js":18758,"./kn":79282,"./kn.js":79282,"./ko":33730,"./ko.js":33730,"./ku":1408,"./ku.js":1408,"./ky":33291,"./ky.js":33291,"./lb":36841,"./lb.js":36841,"./lo":55466,"./lo.js":55466,"./lt":57010,"./lt.js":57010,"./lv":37595,"./lv.js":37595,"./me":39861,"./me.js":39861,"./mi":35493,"./mi.js":35493,"./mk":95966,"./mk.js":95966,"./ml":87341,"./ml.js":87341,"./mn":5115,"./mn.js":5115,"./mr":10370,"./mr.js":10370,"./ms":9847,"./ms-my":41237,"./ms-my.js":41237,"./ms.js":9847,"./mt":72126,"./mt.js":72126,"./my":56165,"./my.js":56165,"./nb":64924,"./nb.js":64924,"./ne":16744,"./ne.js":16744,"./nl":93901,"./nl-be":59814,"./nl-be.js":59814,"./nl.js":93901,"./nn":83877,"./nn.js":83877,"./oc-lnc":92135,"./oc-lnc.js":92135,"./pa-in":15858,"./pa-in.js":15858,"./pl":64495,"./pl.js":64495,"./pt":89520,"./pt-br":57971,"./pt-br.js":57971,"./pt.js":89520,"./ro":96459,"./ro.js":96459,"./ru":21793,"./ru.js":21793,"./sd":40950,"./sd.js":40950,"./se":10490,"./se.js":10490,"./si":90124,"./si.js":90124,"./sk":64249,"./sk.js":64249,"./sl":14985,"./sl.js":14985,"./sq":51104,"./sq.js":51104,"./sr":49131,"./sr-cyrl":79915,"./sr-cyrl.js":79915,"./sr.js":49131,"./ss":85893,"./ss.js":85893,"./sv":98760,"./sv.js":98760,"./sw":91172,"./sw.js":91172,"./ta":27333,"./ta.js":27333,"./te":23110,"./te.js":23110,"./tet":52095,"./tet.js":52095,"./tg":27321,"./tg.js":27321,"./th":9041,"./th.js":9041,"./tk":19005,"./tk.js":19005,"./tl-ph":75768,"./tl-ph.js":75768,"./tlh":89444,"./tlh.js":89444,"./tr":72397,"./tr.js":72397,"./tzl":28254,"./tzl.js":28254,"./tzm":51106,"./tzm-latn":30699,"./tzm-latn.js":30699,"./tzm.js":51106,"./ug-cn":9288,"./ug-cn.js":9288,"./uk":67691,"./uk.js":67691,"./ur":13795,"./ur.js":13795,"./uz":6791,"./uz-latn":60588,"./uz-latn.js":60588,"./uz.js":6791,"./vi":65666,"./vi.js":65666,"./x-pseudo":14378,"./x-pseudo.js":14378,"./yo":75805,"./yo.js":75805,"./zh-cn":83839,"./zh-cn.js":83839,"./zh-hk":55726,"./zh-hk.js":55726,"./zh-mo":99807,"./zh-mo.js":99807,"./zh-tw":74152,"./zh-tw.js":74152};function r(e){var t=s(e);return a(t)}function s(e){if(!a.o(n,e)){var t=new Error("Cannot find module '"+e+"'");throw t.code="MODULE_NOT_FOUND",t}return n[e]}r.keys=function(){return Object.keys(n)},r.resolve=s,e.exports=r,r.id=46700}}]);
//# sourceMappingURL=343.02c83f6e4cf3863b74b9.js.map