const assert=require('node:assert/strict');
let currentFilter='all',usarLeaflet=false;
const clientesData=[{status:'online'},{status:'offline'}];
const marcadores=clientesData.map(()=>({visible:true,setVisible(v){this.visible=v;}}));
let totalsEnabled=false; const counters={};
const document={getElementById(id){return totalsEnabled?(counters[id]||(counters[id]={})):null;},querySelectorAll(){return [];},querySelector(){return {classList:{add(){}}};}};
        function filterMarkers(filterType) {
            currentFilter = filterType;
            let visibleCount = 0;
            let visibleOnline = 0;
            let visibleOffline = 0;

            marcadores.forEach((marcador, index) => {
                const cliente = clientesData[index];
                let mostrar = false;

                if (filterType === 'all') {
                    mostrar = true;
                } else if (filterType === 'online') {
                    mostrar = cliente.status === 'online';
                } else if (filterType === 'offline') {
                    mostrar = cliente.status === 'offline';
                }

                if (usarLeaflet) {
                    if (mostrar) {
                        marcador.setOpacity(1);
                        visibleCount++;
                        if (cliente.status === 'online') visibleOnline++;
                        if (cliente.status === 'offline') visibleOffline++;
                    } else {
                        marcador.setOpacity(0);
                    }
                } else {
                    marcador.setVisible(mostrar);
                    if (mostrar) {
                        visibleCount++;
                        if (cliente.status === 'online') visibleOnline++;
                        if (cliente.status === 'offline') visibleOffline++;
                    }
                }
            });

            // Atualizar contadores
            if (document.getElementById('total-clientes')) document.getElementById('total-clientes').textContent = visibleCount;
            if (document.getElementById('total-online')) document.getElementById('total-online').textContent = visibleOnline;
            if (document.getElementById('total-offline')) document.getElementById('total-offline').textContent = visibleOffline;

            // Atualizar botões ativos
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-filter="${filterType}"]`).classList.add('active');
        }


for(const enabled of [false,true]) {
totalsEnabled=enabled;
for(const filter of ['all','online','offline']) {
filterMarkers(filter);
assert.deepEqual(marcadores.map(m=>m.visible),filter==='all'?[true,true]:filter==='online'?[true,false]:[false,true]);
if(enabled)assert.equal(counters['total-clientes'].textContent,filter==='all'?2:1);
}
}
console.log('PASS: filters work with totals present and absent');
