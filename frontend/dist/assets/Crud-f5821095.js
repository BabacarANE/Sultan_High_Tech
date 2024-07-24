import{x as le,r as d,l as ae,o as T,m as se,b as r,c as y,d as g,e as a,f as l,g as i,E as q,i as p,t as _,k,G as E}from"./index-96009e84.js";import{a as b}from"./axios-9cbf0d09.js";const ie={class:"grid"},re={class:"col-12"},ne={class:"card"},de={class:"my-2"},ce={class:"flex flex-column md:flex-row md:justify-content-between md:align-items-center"},ue=a("h5",{class:"m-0"},"Manage Products",-1),pe=a("span",{class:"p-column-title"},"ID",-1),me=a("span",{class:"p-column-title"},"Name",-1),ve=a("span",{class:"p-column-title"},"Image",-1),_e=["src","alt"],fe=a("span",{class:"p-column-title"},"Price",-1),he=a("span",{class:"p-column-title"},"Category",-1),ye=a("span",{class:"p-column-title"},"Stock",-1),ge=["src","alt"],be={class:"field"},we=a("label",{for:"nom"},"Name",-1),xe={key:0,class:"p-invalid"},ke={class:"field"},Ce=a("label",{for:"description"},"Description",-1),Pe={class:"formgrid grid"},Se={class:"field col"},Ve=a("label",{for:"prix"},"Price",-1),De={key:0,class:"p-invalid"},Fe={class:"field col"},Ie=a("label",{for:"quantite_en_stock"},"Quantity",-1),Ne={class:"field"},Te=a("label",{for:"category_id"},"Category",-1),qe={class:"field"},Ee=a("label",{for:"photo"},"Photo",-1),Ue=a("p",null,"Drag and drop image here to upload.",-1),Le={class:"flex align-items-center justify-content-center"},$e=a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}},null,-1),Me={key:0},Be={class:"flex align-items-center justify-content-center"},Re=a("i",{class:"pi pi-exclamation-triangle mr-3",style:{"font-size":"2rem"}},null,-1),ze={key:0},Xe={__name:"Crud",setup(Oe){const c=le(),f=d(null),h=d(!1),w=d(!1),x=d(!1),o=d({}),m=d(null),V=d(null),C=d({}),v=d(!1),P=d(null),U=t=>t>10?"success":t>0?"warning":"danger";ae(()=>{G()}),T(()=>{D()});const D=async()=>{try{const t=await b.get("http://127.0.0.1:8000/api/products");f.value=t.data}catch(t){console.error("Error fetching products:",t),c.add({severity:"error",summary:"Error",detail:"Failed to fetch products",life:3e3})}},L=t=>parseFloat(t).toLocaleString("fr-FR",{style:"currency",currency:"XOF"}),$=()=>{o.value={},v.value=!1,h.value=!0},M=()=>{h.value=!1,v.value=!1},B=t=>{P.value=t.files[0]},R=async()=>{if(v.value=!0,o.value.nom&&o.value.nom.trim()&&o.value.prix)try{const t=new FormData;t.append("nom",o.value.nom),t.append("description",o.value.description),t.append("prix",o.value.prix),t.append("quantite_en_stock",o.value.quantite_en_stock),t.append("category_id",o.value.category_id),P.value&&t.append("photo",P.value),o.value.id?(await b.post(`http://127.0.0.1:8000/api/products/${o.value.id}`,t,{headers:{"Content-Type":"multipart/form-data"}}),c.add({severity:"success",summary:"Successful",detail:"Product Updated",life:3e3})):(await b.post("http://127.0.0.1:8000/api/products",t,{headers:{"Content-Type":"multipart/form-data"}}),c.add({severity:"success",summary:"Successful",detail:"Product Created",life:3e3})),h.value=!1,o.value={},P.value=null,await D()}catch(t){console.error("Error saving product:",t),c.add({severity:"error",summary:"Error",detail:"Failed to save product",life:3e3})}},z=t=>{o.value={...t},h.value=!0},O=t=>{o.value=t,w.value=!0},j=async()=>{try{await b.delete(`http://127.0.0.1:8000/api/products/${o.value.id}`),f.value=f.value.filter(t=>t.id!==o.value.id),w.value=!1,o.value={},c.add({severity:"success",summary:"Successful",detail:"Product Deleted",life:3e3})}catch(t){console.error("Error deleting product:",t),c.add({severity:"error",summary:"Error",detail:"Failed to delete product",life:3e3})}},A=()=>{V.value.exportCSV()},X=()=>{x.value=!0},Y=async()=>{try{for(let t of m.value)await b.delete(`http://127.0.0.1:8000/api/products/${t.id}`);f.value=f.value.filter(t=>!m.value.includes(t)),x.value=!1,m.value=null,c.add({severity:"success",summary:"Successful",detail:"Products Deleted",life:3e3})}catch(t){console.error("Error deleting selected products:",t),c.add({severity:"error",summary:"Error",detail:"Failed to delete selected products",life:3e3})}},G=()=>{C.value={global:{value:null,matchMode:se.CONTAINS}}},F=d([]);T(()=>{K()});const K=async()=>{try{const t=await b.get("http://127.0.0.1:8000/api/categories");F.value=t.data}catch(t){console.error("Error fetching categories:",t),c.add({severity:"error",summary:"Error",detail:"Failed to fetch categories",life:3e3})}};return(t,s)=>{const n=r("Button"),Q=r("Toolbar"),H=r("InputIcon"),I=r("InputText"),J=r("IconField"),u=r("Column"),W=r("Tag"),Z=r("DataTable"),ee=r("Textarea"),N=r("InputNumber"),te=r("Dropdown"),S=r("Dialog");return y(),g("div",ie,[a("div",re,[a("div",ne,[l(Q,{class:"mb-4"},{start:i(()=>[a("div",de,[l(n,{label:"New",icon:"pi pi-plus",class:"mr-2",severity:"success",onClick:$}),l(n,{label:"Delete",icon:"pi pi-trash",severity:"danger",onClick:X,disabled:!m.value||!m.value.length},null,8,["disabled"])])]),end:i(()=>[l(q(E),{mode:"basic",accept:"image/*",maxFileSize:1e6,label:"Import",chooseLabel:"Import",class:"mr-2 inline-block"}),l(n,{label:"Export",icon:"pi pi-upload",severity:"help",onClick:s[0]||(s[0]=e=>A(e))})]),_:1}),l(Z,{ref_key:"dt",ref:V,value:f.value,selection:m.value,"onUpdate:selection":s[2]||(s[2]=e=>m.value=e),dataKey:"id",paginator:!0,rows:10,filters:C.value,paginatorTemplate:"FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown",rowsPerPageOptions:[5,10,25],currentPageReportTemplate:"Showing {first} to {last} of {totalRecords} products"},{header:i(()=>[a("div",ce,[ue,l(J,{iconPosition:"left",class:"block mt-2 md:mt-0"},{default:i(()=>[l(H,{class:"pi pi-search"}),l(I,{class:"w-full sm:w-auto",modelValue:C.value.global.value,"onUpdate:modelValue":s[1]||(s[1]=e=>C.value.global.value=e),placeholder:"Search..."},null,8,["modelValue"])]),_:1})])]),default:i(()=>[l(u,{selectionMode:"multiple",headerStyle:"width: 3rem"}),l(u,{field:"id",header:"ID",sortable:!0,headerStyle:"width:14%; min-width:10rem;"},{body:i(e=>[pe,p(" "+_(e.data.id),1)]),_:1}),l(u,{field:"nom",header:"Name",sortable:!0,headerStyle:"width:14%; min-width:10rem;"},{body:i(e=>[me,p(" "+_(e.data.nom),1)]),_:1}),l(u,{header:"Image",headerStyle:"width:14%; min-width:10rem;"},{body:i(e=>[ve,a("img",{src:`http://127.0.0.1:8000/storage/${e.data.photo}`,alt:e.data.nom,class:"shadow-2",width:"100"},null,8,_e)]),_:1}),l(u,{field:"prix",header:"Price",sortable:!0,headerStyle:"width:14%; min-width:8rem;"},{body:i(e=>[fe,p(" "+_(L(e.data.prix)),1)]),_:1}),l(u,{field:"category_id",header:"Category",sortable:!0,headerStyle:"width:14%; min-width:10rem;"},{body:i(e=>[he,p(" "+_(e.data.category_id),1)]),_:1}),l(u,{field:"quantite_en_stock",header:"Stock",sortable:!0,headerStyle:"width:14%; min-width:10rem;"},{body:i(e=>[ye,l(W,{severity:U(e.data.quantite_en_stock)},{default:i(()=>[p(_(e.data.quantite_en_stock),1)]),_:2},1032,["severity"])]),_:1}),l(u,{headerStyle:"min-width:10rem;"},{body:i(e=>[l(n,{icon:"pi pi-pencil",class:"mr-2",severity:"success",rounded:"",onClick:oe=>z(e.data)},null,8,["onClick"]),l(n,{icon:"pi pi-trash",class:"mt-2",severity:"warning",rounded:"",onClick:oe=>O(e.data)},null,8,["onClick"])]),_:1})]),_:1},8,["value","selection","filters"]),l(S,{visible:h.value,"onUpdate:visible":s[8]||(s[8]=e=>h.value=e),style:{width:"450px"},header:"Product Details",modal:!0,class:"p-fluid"},{footer:i(()=>[l(n,{label:"Cancel",icon:"pi pi-times",text:"",onClick:M}),l(n,{label:"Save",icon:"pi pi-check",text:"",onClick:R})]),default:i(()=>[o.value.photo?(y(),g("img",{key:0,src:`http://127.0.0.1:8000/storage/${o.value.photo}`,alt:o.value.nom,width:"150",class:"mt-0 mx-auto mb-5 block shadow-2"},null,8,ge)):k("",!0),a("div",be,[we,l(I,{id:"nom",modelValue:o.value.nom,"onUpdate:modelValue":s[3]||(s[3]=e=>o.value.nom=e),modelModifiers:{trim:!0},required:"true",autofocus:"",invalid:v.value&&!o.value.nom},null,8,["modelValue","invalid"]),v.value&&!o.value.nom?(y(),g("small",xe,"Name is required.")):k("",!0)]),a("div",ke,[Ce,l(ee,{id:"description",modelValue:o.value.description,"onUpdate:modelValue":s[4]||(s[4]=e=>o.value.description=e),required:"true",rows:"3",cols:"20"},null,8,["modelValue"])]),a("div",Pe,[a("div",Se,[Ve,l(N,{id:"prix",modelValue:o.value.prix,"onUpdate:modelValue":s[5]||(s[5]=e=>o.value.prix=e),mode:"currency",currency:"XOF",locale:"fr-FR",invalid:v.value&&!o.value.prix,required:!0},null,8,["modelValue","invalid"]),v.value&&!o.value.prix?(y(),g("small",De,"Price is required.")):k("",!0)]),a("div",Fe,[Ie,l(N,{id:"quantite_en_stock",modelValue:o.value.quantite_en_stock,"onUpdate:modelValue":s[6]||(s[6]=e=>o.value.quantite_en_stock=e),integeronly:""},null,8,["modelValue"])])]),a("div",Ne,[Te,l(te,{id:"category_id",modelValue:o.value.category_id,"onUpdate:modelValue":s[7]||(s[7]=e=>o.value.category_id=e),options:F.value,optionLabel:"name",optionValue:"id",placeholder:"Select a category"},{option:i(e=>[p(_(e.option.name),1)]),_:1},8,["modelValue","options"])]),a("div",qe,[Ee,l(q(E),{name:"photo",url:"./upload",onSelect:B,auto:!0,accept:"image/*",maxFileSize:1e6},{empty:i(()=>[Ue]),_:1})])]),_:1},8,["visible"]),l(S,{visible:w.value,"onUpdate:visible":s[10]||(s[10]=e=>w.value=e),style:{width:"450px"},header:"Confirm",modal:!0},{footer:i(()=>[l(n,{label:"No",icon:"pi pi-times",text:"",onClick:s[9]||(s[9]=e=>w.value=!1)}),l(n,{label:"Yes",icon:"pi pi-check",text:"",onClick:j})]),default:i(()=>[a("div",Le,[$e,o.value?(y(),g("span",Me,[p("Are you sure you want to delete "),a("b",null,_(o.value.nom),1),p("?")])):k("",!0)])]),_:1},8,["visible"]),l(S,{visible:x.value,"onUpdate:visible":s[12]||(s[12]=e=>x.value=e),style:{width:"450px"},header:"Confirm",modal:!0},{footer:i(()=>[l(n,{label:"No",icon:"pi pi-times",text:"",onClick:s[11]||(s[11]=e=>x.value=!1)}),l(n,{label:"Yes",icon:"pi pi-check",text:"",onClick:Y})]),default:i(()=>[a("div",Be,[Re,o.value?(y(),g("span",ze,"Are you sure you want to delete the selected products?")):k("",!0)])]),_:1},8,["visible"])])])])}}};export{Xe as default};
