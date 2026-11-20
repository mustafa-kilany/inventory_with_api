const http=require('node:http');
const items=[{id:1,sku:'SYN-001',name:'Synthetic Scanner',quantity:12},{id:2,sku:'SYN-002',name:'Synthetic Tablet',quantity:4}];
function json(res,status,data){res.writeHead(status,{'Content-Type':'application/json'});res.end(JSON.stringify(data))}
async function body(req){let s='';for await(const c of req)s+=c;return s?JSON.parse(s):{}}
const server=http.createServer(async(req,res)=>{
 const u=new URL(req.url,'http://localhost');
 if(req.method==='GET'&&u.pathname==='/' )return json(res,200,{name:'Inventory API Demo',routes:['GET /api/items','POST /api/items','PATCH /api/items/:id/stock']});
 if(req.method==='GET'&&u.pathname==='api/items')return json(res,200,{items});
 if(req.method==='POST'&&u.pathname==='api/items'){try{const b=await body(req);const item={id:items.length+1,sku:b.sku||`SYN-${items.length+1}`,name:b.name||'Synthetic Item',quantity:Number(b.quantity||0)};items.push(item);return json(res,201,item)}catch{return json(res,400,{error:'Invalid JSON'})}}
 if(req.method==='PATCH'&&u.pathname.match(/^\/api\/items\/\d+\/stock$/)){const id=Number(u.pathname.split('/')[3]);const item=items.find(x=>x.id===id);if(!item)return json(res,404,{error:'Item not found'});try{const b=await body(req);item.quantity=Number(b.quantity);return json(res,200,item)}catch{return json(res,400,{error:'Invalid JSON'})}}
 json(res,404,{error:'Not found'});
});
if(require.main===module)server.listen(process.env.PORT||4002,()=>console.log('Inventory API running'));
module.exports={server,items};
