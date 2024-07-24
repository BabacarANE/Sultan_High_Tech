import{x as q,r as n,l as O,o as Y,m as K,b as v,c as w,d as k,e as s,f as t,g as i,i as f,t as _,n as G,k as D}from"./index-96009e84.js";import{a as b}from"./axios-9cbf0d09.js";const H={class:"grid"},J={class:"col-12"},Q={class:"card"},W={class:"my-2"},X={class:"flex flex-column md:flex-row md:justify-content-between md:align-items-center"},Z=s("h5",{class:"m-0"},"Manage Categories",-1),ee={class:"block mt-2 md:mt-0 p-input-icon-left"},te=s("i",{class:"pi pi-search"},null,-1),ae=s("span",{class:"p-column-title"},"ID",-1),le=s("span",{class:"p-column-title"},"Name",-1),oe=s("span",{class:"p-column-title"},"Created At",-1),se=s("span",{class:"p-column-title"},"Updated At",-1),ie={class:"field"},re=s("label",{for:"name"},"Name",-1),ne={key:0,class:"p-invalid"},de={class:"flex align-items-center justify-content-center"},ce=s("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}},null,-1),ue={key:0},pe={class:"flex align-items-center justify-content-center"},me=s("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}},null,-1),ve={key:0},he={__name:"Category",setup(fe){const d=q(),u=n(null),p=n(!1),g=n(!1),y=n(!1),l=n({}),c=n(null),S=n(null),C=n({}),h=n(!1);O(()=>{I()}),Y(()=>{N()});const N=async()=>{try{const a=await b.get("http://127.0.0.1:8000/api/categories");u.value=a.data}catch(a){console.error("Error fetching categories:",a),d.add({severity:"error",summary:"Error",detail:"Failed to fetch categories",life:3e3})}},V=a=>new Date(a).toLocaleString(),T=()=>{l.value={},h.value=!1,p.value=!0},E=()=>{p.value=!1,h.value=!1},U=async()=>{if(h.value=!0,l.value.name&&l.value.name.trim())try{l.value.id?(await b.post(`http://127.0.0.1:8000/api/categories/${l.value.id}`,l.value),d.add({severity:"success",summary:"Successful",detail:"Category Updated",life:3e3})):(await b.post("http://127.0.0.1:8000/api/categories",l.value),d.add({severity:"success",summary:"Successful",detail:"Category Created",life:3e3})),p.value=!1,l.value={},await N()}catch(a){console.error("Error saving category:",a),d.add({severity:"error",summary:"Error",detail:"Failed to save category",life:3e3})}},M=a=>{l.value={...a},p.value=!0},A=a=>{l.value=a,g.value=!0},F=async()=>{try{await b.delete(`http://127.0.0.1:8000/api/categories/${l.value.id}`),u.value=u.value.filter(a=>a.id!==l.value.id),g.value=!1,l.value={},d.add({severity:"success",summary:"Successful",detail:"Category Deleted",life:3e3})}catch(a){console.error("Error deleting category:",a),d.add({severity:"error",summary:"Error",detail:"Failed to delete category",life:3e3})}},L=()=>{S.value.exportCSV()},$=()=>{y.value=!0},B=async()=>{try{for(let a of c.value)await b.delete(`http://127.0.0.1:8000/api/categories/${a.id}`);u.value=u.value.filter(a=>!c.value.includes(a)),y.value=!1,c.value=null,d.add({severity:"success",summary:"Successful",detail:"Categories Deleted",life:3e3})}catch(a){console.error("Error deleting selected categories:",a),d.add({severity:"error",summary:"Error",detail:"Failed to delete selected categories",life:3e3})}},I=()=>{C.value={global:{value:null,matchMode:K.CONTAINS}}};return(a,o)=>{const r=v("Button"),R=v("Toolbar"),P=v("InputText"),m=v("Column"),j=v("DataTable"),x=v("Dialog");return w(),k("div",H,[s("div",J,[s("div",Q,[t(R,{class:"mb-4"},{start:i(()=>[s("div",W,[t(r,{label:"New",icon:"pi pi-plus",class:"mr-2",severity:"success",onClick:T}),t(r,{label:"Delete",icon:"pi pi-trash",severity:"danger",onClick:$,disabled:!c.value||!c.value.length},null,8,["disabled"])])]),end:i(()=>[t(r,{label:"Export",icon:"pi pi-upload",severity:"help",onClick:o[0]||(o[0]=e=>L(e))})]),_:1}),t(j,{ref_key:"dt",ref:S,value:u.value,selection:c.value,"onUpdate:selection":o[2]||(o[2]=e=>c.value=e),dataKey:"id",paginator:!0,rows:10,filters:C.value,paginatorTemplate:"FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown",rowsPerPageOptions:[5,10,25],currentPageReportTemplate:"Showing {first} to {last} of {totalRecords} categories"},{header:i(()=>[s("div",X,[Z,s("span",ee,[te,t(P,{modelValue:C.value.global.value,"onUpdate:modelValue":o[1]||(o[1]=e=>C.value.global.value=e),placeholder:"Search..."},null,8,["modelValue"])])])]),default:i(()=>[t(m,{selectionMode:"multiple",headerStyle:"width: 3rem"}),t(m,{field:"id",header:"ID",sortable:!0,headerStyle:"width:15%; min-width:8rem;"},{body:i(e=>[ae,f(" "+_(e.data.id),1)]),_:1}),t(m,{field:"name",header:"Name",sortable:!0,headerStyle:"width:35%; min-width:10rem;"},{body:i(e=>[le,f(" "+_(e.data.name),1)]),_:1}),t(m,{field:"created_at",header:"Created At",sortable:!0,headerStyle:"width:20%; min-width:10rem;"},{body:i(e=>[oe,f(" "+_(V(e.data.created_at)),1)]),_:1}),t(m,{field:"updated_at",header:"Updated At",sortable:!0,headerStyle:"width:20%; min-width:10rem;"},{body:i(e=>[se,f(" "+_(V(e.data.updated_at)),1)]),_:1}),t(m,{headerStyle:"min-width:10rem;"},{body:i(e=>[t(r,{icon:"pi pi-pencil",class:"p-button-rounded p-button-success mr-2",onClick:z=>M(e.data)},null,8,["onClick"]),t(r,{icon:"pi pi-trash",class:"p-button-rounded p-button-warning",onClick:z=>A(e.data)},null,8,["onClick"])]),_:1})]),_:1},8,["value","selection","filters"]),t(x,{visible:p.value,"onUpdate:visible":o[4]||(o[4]=e=>p.value=e),style:{width:"450px"},header:"Category Details",modal:!0,class:"p-fluid"},{footer:i(()=>[t(r,{label:"Cancel",icon:"pi pi-times",class:"p-button-text",onClick:E}),t(r,{label:"Save",icon:"pi pi-check",class:"p-button-text",onClick:U})]),default:i(()=>[s("div",ie,[re,t(P,{id:"name",modelValue:l.value.name,"onUpdate:modelValue":o[3]||(o[3]=e=>l.value.name=e),modelModifiers:{trim:!0},required:"true",autofocus:"",class:G({"p-invalid":h.value&&!l.value.name})},null,8,["modelValue","class"]),h.value&&!l.value.name?(w(),k("small",ne,"Name is required.")):D("",!0)])]),_:1},8,["visible"]),t(x,{visible:g.value,"onUpdate:visible":o[6]||(o[6]=e=>g.value=e),style:{width:"450px"},header:"Confirm",modal:!0},{footer:i(()=>[t(r,{label:"No",icon:"pi pi-times",class:"p-button-text",onClick:o[5]||(o[5]=e=>g.value=!1)}),t(r,{label:"Yes",icon:"pi pi-check",class:"p-button-text",onClick:F})]),default:i(()=>[s("div",de,[ce,l.value?(w(),k("span",ue,[f("Are you sure you want to delete "),s("b",null,_(l.value.name),1),f("?")])):D("",!0)])]),_:1},8,["visible"]),t(x,{visible:y.value,"onUpdate:visible":o[8]||(o[8]=e=>y.value=e),style:{width:"450px"},header:"Confirm",modal:!0},{footer:i(()=>[t(r,{label:"No",icon:"pi pi-times",class:"p-button-text",onClick:o[7]||(o[7]=e=>y.value=!1)}),t(r,{label:"Yes",icon:"pi pi-check",class:"p-button-text",onClick:B})]),default:i(()=>[s("div",pe,[me,l.value?(w(),k("span",ve,"Are you sure you want to delete the selected categories?")):D("",!0)])]),_:1},8,["visible"])])])])}}};export{he as default};