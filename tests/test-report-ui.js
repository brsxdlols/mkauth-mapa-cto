const assert=require('node:assert/strict');
const ui=require('../addons/rel_cto_cad_cli/report.js');
const clients=[
 {key:'cliente:1',type:'cliente',port:1,status:'online',login:'titular',name:'José',olt:'OLT A'},
 {key:'adicional:1',type:'adicional',port:15,status:'offline',login:'extra',name:'Adicional',olt:'OLT A'},
 {key:'adicional:2',type:'adicional',port:2,status:'online',login:'extra2'},
 {key:'cliente:2',type:'cliente',port:2,status:'online',login:'principal2'},
 {key:'adicional:3',type:'adicional',port:null,status:'offline',login:'sem_porta'}
];
const box={key:'caixa 20',name:'CAIXA 20',address:'Rua São João',type:'CTO',clients,ports:[{number:1,state:'used',clients:['cliente:1']},{number:2,state:'conflict',clients:['adicional:2','cliente:2']},{number:3,state:'free',clients:[]},{number:15,state:'used',clients:['adicional:1']}],stats:{connections:5,principal:2,additional:3,online:3,offline:2,free:1,capacity:4,conflicts:1}};
assert.equal(ui.rows(box,'all',null).length,6);
assert.equal(ui.rows(box,'additional',null).length,3);
assert.equal(ui.rows(box,'free',null).length,1);
assert.equal(ui.rows(box,'conflict',null).length,2);
assert.equal(ui.rows(box,'online',null).length,3);
assert.equal(ui.rows(box,'offline',null).length,2);
assert.equal(ui.rows(box,'all',15)[0].client.key,'adicional:1');
assert.equal(ui.rows(box,'all',2).length,2);
assert(ui.matchBox(box,'sao joao','',''));
assert(ui.matchBox(box,'jose','caixa 20','OLT A'));
assert(!ui.matchBox(box,'','other',''));
assert(!ui.matchBox(box,'','','OLT B'));
assert.equal(ui.summarize([box]).additional,3);
assert.equal(ui.summarize([box]).free,1);
const limited={...box,stats:null,ports:[{number:3,state:'unknown',clients:[]}]};
assert.equal(ui.rows(limited,'free',null).length,0);
console.log('PASS: filters, additional rows, port modal selection, accent-insensitive search, CTO/OLT filters, summary and partial occupancy.');

const fs=require('node:fs'),path=require('node:path');
const entry=fs.readFileSync(path.join(__dirname,'../addons/rel_cto_cad_cli/index.php'),'utf8');
assert(entry.includes("include __DIR__.'/../../topo.php'"));
assert(entry.indexOf('js/jquery-3.6.0.min.js')<entry.indexOf('../../scripts/mk-auth.js'));
assert(entry.includes('<script src="../../menu.js.php"></script>'));
console.log('PASS: native header and menu dependencies preserved in load order.');

const empty={...box,key:'empty',clients:[],ports:[],stats:{connections:0,principal:0,additional:0}};
const onlyAdditional={...box,key:'extra',registered:false,clients:[clients[1]],ports:[],stats:{connections:1,principal:0,additional:1}};
assert.equal(ui.summarize([box,empty,onlyAdditional]).withClients,2);
assert.equal(ui.summarize([box,empty,onlyAdditional]).principal,2);
assert.equal(ui.rows(box,'principals',null).length,2);
assert.equal(ui.rows(onlyAdditional,'principals',null).length,0);
assert.equal(ui.rows(empty,'withClients',null).length,0);
assert.equal(ui.rows(onlyAdditional,'withClients',null).length,1);
assert.equal(ui.summarize([limited]).withClients,0);
console.log('PASS: occupied CTOs include additional-only references; principal counts exclude additions; restricted totals stay hidden.');
