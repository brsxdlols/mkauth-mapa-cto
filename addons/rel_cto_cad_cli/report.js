(function(root){
'use strict';
function text(value){return String(value==null?'':value);}
function escape(value){return text(value).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
function fold(value){var s=text(value).toLowerCase();return s.normalize?s.normalize('NFD').replace(/[\u0300-\u036f]/g,''):s;}
function rows(box,filter,port){
    var list=[],indexed={},seen={};box.clients.forEach(function(c){indexed[c.key]=c;});
    box.ports.forEach(function(p){
        if(p.clients.length){p.clients.forEach(function(key){var c=indexed[key];if(c){seen[key]=true;list.push({port:p.number,state:p.state,client:c});}});}
        else list.push({port:p.number,state:p.state,client:null});
    });
    box.clients.forEach(function(c){if(!seen[c.key])list.push({port:c.port,state:c.port===null?'unassigned':'outside',client:c});});
    return list.filter(function(r){
        if(port!==null && r.port!==port)return false;
        if(filter==='withClients')return box.clients.length>0;
        if(filter==='principals')return !!r.client && r.client.type==='cliente';
        if(filter==='free')return r.state==='free';
        if(filter==='conflict')return r.state==='conflict';
        if(filter==='additional')return !!r.client && r.client.type==='adicional';
        if(filter==='online'||filter==='offline')return !!r.client && r.client.status===filter;
        return true;
    });
}
function matchBox(box,query,cto,olt){
    if(cto && box.key!==cto)return false;
    if(olt && !box.clients.some(function(c){return text(c.olt)===olt;}))return false;
    var hay=[box.name,box.address,box.type];box.clients.forEach(function(c){hay.push(c.login,c.name,c.address,c.neighborhood,c.olt,c.owner);});
    return !query || fold(hay.join(' ')).indexOf(fold(query.trim()))!==-1;
}
function summarize(boxes){var s={ctos:boxes.length,withClients:0,connections:0,additional:0,online:0,offline:0,free:0,capacity:0,principal:0,conflicts:0};boxes.forEach(function(b){if(b.stats){if(b.clients.length)s.withClients++;Object.keys(s).forEach(function(k){if(k!=='ctos'&&k!=='withClients')s[k]+=Number(b.stats[k])||0;});}});return s;}
var api={rows:rows,matchBox:matchBox,summarize:summarize};
if(typeof module!=='undefined'&&module.exports)module.exports=api;
if(typeof document==='undefined')return;
var node=document.getElementById('report-data'),shell=document.getElementById('cto-report');if(!node||!shell)return;
var data;try{data=JSON.parse(node.textContent);}catch(e){data=null;}
shell.setAttribute('aria-busy','false');if(!data){['report-summary','report-boxes-section','report-more'].forEach(function(id){document.getElementById(id).hidden=true;});return;}
var params=new URLSearchParams(location.search),initial=params.get('filtro'),state={query:params.get('busca')||'',cto:fold(params.get('cto')||''),olt:'',filter:initial==='on'?'online':initial==='off'?'offline':'all',view:'overview',limit:8,modal:null,modalFilter:'all',port:null};
try{state.view=params.get('visao')==='lista'?'list':(localStorage.getItem('rel-cto-view')==='list'?'list':'overview');}catch(e){}
var byKey=new Map(data.ctos.map(function(b){return [b.key,b];})),dialog=document.getElementById('report-dialog');
var search=document.getElementById('report-search'),ctoSelect=document.getElementById('report-cto'),oltSelect=document.getElementById('report-olt');
search.value=state.query;data.ctos.forEach(function(b){ctoSelect.add(new Option(b.name,b.key));});
if(byKey.has(state.cto))ctoSelect.value=state.cto;else state.cto='';
var olts=new Set();data.ctos.forEach(function(b){b.clients.forEach(function(c){if(c.olt)olts.add(c.olt);});});Array.from(olts).sort().forEach(function(o){oltSelect.add(new Option(o,o));});
function number(v){return Number(v).toLocaleString('pt-BR');}
function badge(kind,label){return '<span class="report-badge '+kind+'">'+escape(label)+'</span>';}
function metric(label,value,hint,filter,color,active){return '<button type="button" class="report-stat '+(color||'')+'" data-filter="'+filter+'" aria-pressed="'+(!!active)+'"><span class="label">'+label+'</span><strong>'+value+'</strong><small>'+hint+'</small></button>';}
function renderSummary(boxes){
    var el=document.getElementById('report-summary');el.hidden=!data.canTotals;if(!data.canTotals)return;
    var s=summarize(boxes);
    el.innerHTML=metric('CTOs',number(s.ctos),'Caixas encontradas','all','',state.filter==='all')+metric('CTOs com clientes',number(s.withClients),'Caixas com principais ou adicionais','withClients','',state.filter==='withClients')+metric('Clientes cadastrados nas CTOs',number(s.principal),'Clientes principais · adicionais à parte','principals','',state.filter==='principals')+metric('Conexões',number(s.connections),number(s.principal)+' principais','all','',false)+metric('Adicionais',number(s.additional),'Conexões adicionais','additional','purple',state.filter==='additional')+metric('Portas livres',data.fullScope?number(s.free):'—',data.fullScope?'Capacidade cadastrada':'Ocupação parcial por grupo','free','green',state.filter==='free')+metric('Online',number(s.online),'Conexões ativas','online','green',state.filter==='online')+metric('Offline',number(s.offline),'Sem sessão ativa','offline','red',state.filter==='offline');
    if(!data.fullScope){var free=el.querySelector('[data-filter="free"]');free.disabled=true;free.setAttribute('aria-disabled','true');}
}
function boxCard(b){
    var s=b.stats,metrics='',bottom='';
    if(s){
        metrics='<div class="report-box-metrics"><span><strong>'+number(s.principal)+'</strong><small>principais</small></span><span><strong>'+number(s.additional)+'</strong><small>adicionais</small></span><span><strong>'+(s.free===null?'—':number(s.free))+'</strong><small>portas livres</small></span></div>';
        metrics+='<div class="report-utilization" aria-hidden="true"><i style="width:'+(s.capacity?Math.min(100,s.used/s.capacity*100):0)+'%"></i></div>';
        bottom='<span><i class="report-dot online"></i>'+number(s.online)+' online</span><span><i class="report-dot offline"></i>'+number(s.offline)+' offline</span>';
    }
    return '<button type="button" class="report-box" data-open-cto="'+escape(b.key)+'" aria-label="Abrir '+escape(b.name)+'"><span class="report-box-head"><span class="report-box-name">'+escape(b.name)+'</span><span class="report-type">'+escape(b.type||'CTO')+'</span></span><span class="report-box-address">'+escape(b.address||'Endereço não informado')+'</span>'+metrics+'<span class="report-box-bottom">'+bottom+'<span class="report-detail-link">Ver portas →</span></span>'+(b.warnings.length?'<span class="report-warning">'+escape(b.warnings[0])+'</span>':'')+'</button>';
}
function date(value){if(!value)return 'Sem registro';var d=new Date(text(value).replace(' ','T'));return isNaN(d.getTime())?escape(value):d.toLocaleString('pt-BR',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});}
function table(b,filter,port){
    var entries=rows(b,filter,port);
    if(!entries.length)return '<p class="report-list-hint">Nenhuma porta ou conexão neste filtro.</p>';
    var html='<div class="report-table-scroll"><table class="report-table"><thead><tr><th>Porta</th><th>Status</th><th>Cliente / login</th><th>Tipo</th><th>Endereço</th><th>Bairro</th><th>PON / OLT</th><th>Conexão</th></tr></thead><tbody>';
    entries.forEach(function(r){
        var c=r.client,p=r.port!==null?String(r.port).padStart(2,'0'):'—';
        if(!c){html+='<tr><td class="report-port-number">'+p+'</td><td>'+badge(r.state,r.state==='free'?'Livre':'Não verificada')+'</td><td colspan="6">'+(r.state==='free'?'Disponível para atribuição':'Ocupação não exibida para este perfil')+'</td></tr>';return;}
        var link='/admin/cliente_alt.'+data.clientExtension+'?uuid='+encodeURIComponent(c.uuid),portLabel=data.signalMode?escape(c.signal||'—'):p;
        html+='<tr'+(r.state==='conflict'?' class="is-conflict"':'')+'><td class="report-port-number">'+portLabel+(r.state==='conflict'?'<small>Conflito</small>':r.state==='outside'?'<small>Fora da capacidade</small>':r.state==='unassigned'?'<small>Sem porta</small>':'')+'</td><td>'+badge(c.status,c.status==='online'?'Online':'Offline')+'</td><td><a class="report-login" href="'+link+'" target="_blank" rel="noopener" title="'+(c.type==='adicional'?'Abrir cadastro do titular':'Abrir cadastro do cliente')+'">'+escape(c.login)+'</a><small>'+escape(c.name)+(c.type==='adicional'?' · Titular: '+escape(c.owner):'')+'</small></td><td>'+badge(c.type==='adicional'?'additional':'',c.type==='adicional'?'Adicional':'Principal')+'</td><td class="report-address">'+escape(c.address||'—')+'</td><td>'+escape(c.neighborhood||'—')+'</td><td>'+escape(c.olt||'—')+'</td><td>Início: '+date(c.start)+(c.status==='offline'?'<small>Fim: '+date(c.stop)+'</small>':'')+'</td></tr>';
    });
    return html+'</tbody></table></div>';
}
function listGroup(b){var s=b.stats;return '<section class="report-list-group"><header class="report-list-heading"><button type="button" class="report-cto-open" data-open-cto="'+escape(b.key)+'">'+escape(b.name)+' ↗</button><div class="report-list-meta">'+(s?'<span>'+number(s.connections)+' conexões</span><span>'+number(s.additional)+' adicionais</span><span>'+(s.free===null?'Ocupação parcial':number(s.free)+' portas livres')+'</span>':'')+'<button type="button" class="report-clear" data-open-cto="'+escape(b.key)+'">Abrir detalhes</button></div></header>'+table(b,state.filter,null)+'</section>';}
function render(){
    shell.classList.toggle('view-list',state.view==='list');
    document.querySelectorAll('[data-view]').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.view===state.view));});
    var scoped=data.ctos.filter(function(b){return matchBox(b,state.query,state.cto,state.olt);});
    renderSummary(scoped);
    var visible=scoped.filter(function(b){return state.filter==='all'||rows(b,state.filter,null).length>0;}),page=visible.slice(0,state.limit);
    document.querySelectorAll('.report-chips [data-filter]').forEach(function(b){b.setAttribute('aria-pressed',String(b.dataset.filter===state.filter));});
    document.querySelectorAll('[data-filter="free"]').forEach(function(b){b.disabled=!data.fullScope;});
    document.getElementById('report-boxes').innerHTML=page.map(boxCard).join('');
    document.getElementById('report-list').innerHTML=page.map(listGroup).join('');
    document.getElementById('report-more').hidden=page.length>=visible.length;
    document.getElementById('report-more').textContent='Mostrar mais CTOs'+(data.canTotals?' ('+number(visible.length-page.length)+' restantes)':'');
    document.getElementById('report-empty').hidden=visible.length>0;
    var labels={all:'todos os registros',withClients:'CTOs com clientes (inclui caixas não cadastradas)',principals:'clientes principais cadastrados nas CTOs',online:'somente online',offline:'somente offline',additional:'somente adicionais',free:'somente portas livres',conflict:'conflitos de porta'};
    document.getElementById('report-scope').textContent=(data.canTotals?number(visible.length)+' CTOs · ':'')+'Exibindo '+labels[state.filter]+(state.cto?' · '+byKey.get(state.cto).name:'')+(!data.fullScope?' · Acesso limitado aos seus grupos. Portas sem cliente visível não são consideradas livres.':'');
    document.getElementById('report-updated').textContent='Consultado em '+date(data.updatedAt)+' · '+number(data.elapsedMs||0)+' ms';
}
function dialogStat(label,value,color){return '<div class="report-stat '+(color||'')+'"><span class="label">'+label+'</span><strong>'+value+'</strong></div>';}
function renderDialog(){
    var b=byKey.get(state.modal);if(!b)return;var s=b.stats;
    document.getElementById('report-dialog-title').textContent=b.name;
    document.getElementById('report-dialog-description').textContent=[b.type,b.address].filter(Boolean).join(' · ');
    var html=s?'<div class="report-summary">'+dialogStat('Principais',number(s.principal))+dialogStat('Adicionais',number(s.additional),'purple')+dialogStat('Portas ocupadas',number(s.used))+dialogStat('Portas livres',s.free===null?'—':number(s.free),'green')+'</div>':'';
    if(b.warnings.length)html+='<p class="report-warning">'+escape(b.warnings.join(' · '))+'</p>';
    html+='<h3>Portas da caixa</h3>';
    if(!b.ports.length)html+='<p class="report-list-hint">Cadastre a capacidade desta caixa para visualizar as portas.</p>';
    else html+='<div class="report-port-grid">'+b.ports.map(function(p){var label={free:'Livre',used:'Ocupada',conflict:'Conflito',unknown:'Não verificada'}[p.state];return '<button type="button" data-port="'+p.number+'" class="report-port '+p.state+(state.port===p.number?' selected':'')+'" aria-pressed="'+(state.port===p.number)+'" aria-label="Porta '+p.number+' '+label+'">'+String(p.number).padStart(2,'0')+'<small>'+label+'</small></button>';}).join('')+'</div>';
    html+='<div class="report-dialog-toolbar"><h3>'+(state.port!==null?'Porta '+String(state.port).padStart(2,'0'):'Conexões da CTO')+'</h3><div class="report-chips" role="group" aria-label="Filtrar detalhes">'+[['all','Todos'],['online','Online'],['offline','Offline'],['additional','Adicionais']].map(function(f){return '<button type="button" data-modal-filter="'+f[0]+'" aria-pressed="'+(state.modalFilter===f[0])+'">'+f[1]+'</button>';}).join('')+(state.port!==null?'<button type="button" data-dialog-clear-port>Ver todas as portas</button>':'')+'</div></div>';
    html+='<div class="report-list-group">'+table(b,state.modalFilter,state.port)+'</div>';
    if(b.id)html+='<div class="report-dialog-actions"><a class="report-button" href="../caixas/?_route=editar&amp;id='+encodeURIComponent(b.id)+'" target="_blank" rel="noopener">Abrir cadastro da CTO</a></div>';
    document.getElementById('report-dialog-body').innerHTML=html;
}
function openBox(key){if(!byKey.has(key))return;state.modal=key;state.modalFilter='all';state.port=null;renderDialog();if(!dialog.open)dialog.showModal();}
function clear(){state.query='';state.cto='';state.olt='';state.filter='all';state.limit=8;search.value='';ctoSelect.value='';oltSelect.value='';render();}
document.addEventListener('click',function(e){
    var button=e.target.closest('button');if(!button||(!shell.contains(button)&&!dialog.contains(button)))return;
    if(button.hasAttribute('data-open-cto'))openBox(button.dataset.openCto);
    else if(button.hasAttribute('data-view')){state.view=button.dataset.view;try{localStorage.setItem('rel-cto-view',state.view);}catch(error){}render();}
    else if(button.hasAttribute('data-modal-filter')){state.modalFilter=button.dataset.modalFilter;renderDialog();}
    else if(button.hasAttribute('data-port')){var port=Number(button.dataset.port);state.port=state.port===port?null:port;state.modalFilter='all';renderDialog();}
    else if(button.hasAttribute('data-dialog-clear-port')){state.port=null;renderDialog();}
    else if(button.hasAttribute('data-filter')){state.filter=state.filter===button.dataset.filter?'all':button.dataset.filter;state.limit=8;render();}
    else if(button.hasAttribute('data-clear')||button.id==='report-clear')clear();
});
var timer;search.addEventListener('input',function(){clearTimeout(timer);timer=setTimeout(function(){state.query=search.value;state.limit=8;render();},120);});
ctoSelect.addEventListener('change',function(){state.cto=this.value;state.limit=8;render();});oltSelect.addEventListener('change',function(){state.olt=this.value;state.limit=8;render();});
document.getElementById('report-more').addEventListener('click',function(){state.limit+=8;render();});
document.getElementById('report-dialog-close').addEventListener('click',function(){dialog.close();});
dialog.addEventListener('click',function(e){if(e.target===dialog){var r=dialog.getBoundingClientRect();if(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom)dialog.close();}});
document.getElementById('report-reload').addEventListener('click',function(){location.reload();});
var printLimit=null;document.getElementById('report-print').addEventListener('click',function(){printLimit=state.limit;state.limit=Infinity;render();window.print();});
window.addEventListener('afterprint',function(){if(printLimit!==null){state.limit=printLimit;printLimit=null;render();}});
render();
}(typeof window!=='undefined'?window:globalThis));
