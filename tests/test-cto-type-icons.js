const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('addons/mapa-cto/assets/js/cto-type-icons.js','utf8');
vm.runInThisContext(source);
const icons=CtoTypeIcons.types.map(type=>CtoTypeIcons.svg(type,'#10b981','28'));
assert.equal(icons.length,17); assert.equal(new Set(icons).size,17);
for(const type of CtoTypeIcons.types){
 for(const color of ['#10b981','#ef4444','#9ca3af']){
  const svg=CtoTypeIcons.svg(type,color,'28');
  assert(svg.includes('fill="'+color+'"'));assert(svg.includes('>28</text>'));
  assert(!/[^\x00-\x7f]/.test(svg),'Google btoa accepts '+type);
 }
}
assert.equal(CtoTypeIcons.normalize(' fttx '),'FTTX');
assert(!CtoTypeIcons.svg('<script>','#fff','<img>').includes('<script>'));
assert(CtoTypeIcons.label('CEO').startsWith('CEO'));
assert(CtoTypeIcons.svg('NEW','#9ca3af').includes('Tipo NEW'));
const map=fs.readFileSync('addons/mapa-cto/src/cto/componente/mapadectos/mapadectos.view.php','utf8');
assert(map.includes('markerSvgComNumero(cor, cto.nome, cto.tipo)'));
assert(map.includes('markerSvgComNumero(corMarcadorCto(cto), cto.nome, cto.tipo)'));
const rendered=map.replace(/<\?php[\s\S]*?\?>/g,'null');
for(const [i,script] of [...rendered.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)].entries()){
 let js=script[1];
 new vm.Script(js,{filename:'map-script-'+i});
}
console.log('PASS: 17 types, status colors, numbers, Google encoding, map call sites and inline JavaScript syntax.');
