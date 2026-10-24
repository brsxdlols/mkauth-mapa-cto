(function (root) {
    'use strict';
    if (root.CtoTypeIcons) return;
    var labels = {
        CTO: 'CTO — Caixa de terminação óptica', CEO: 'CEO — Central de equipamentos ópticos',
        FTTA: 'FTTA — Fibra até o armário', FTTB: 'FTTB — Fibra até o prédio',
        FTTC: 'FTTC — Fibra até a rua', FTTD: 'FTTD — Fibra até a porta',
        FTTH: 'FTTH — Fibra até o domicílio', FTTN: 'FTTN — Fibra até o nó',
        FTTO: 'FTTO — Fibra até o escritório', FTTP: 'FTTP — Fibra até o poste',
        FTTX: 'FTTX — Outras fibras até X', PON: 'PON — Passive Optical Network',
        LAN: 'LAN — Local Area Network', ETH: 'ETH — Ethernet',
        AP: 'AP — Access Point Rádio', STA: 'STA — Station Rádio', TO: 'TO — Torre de Rádio'
    };
    // Every type has its own silhouette; status continues to control the fill color.
    var shapes = {
        CTO: '<rect x="9" y="3" width="30" height="26" rx="4"/><path d="M15 3v26m18-26v26M19 10h10m-10 6h10m-10 6h10"/>',
        CEO: '<rect x="6" y="8" width="36" height="16" rx="7"/><path d="M13 7v18m22-18v18M2 16h4m36 0h4M20 12h8m-8 8h8"/>',
        FTTA: '<rect x="11" y="2" width="26" height="28" rx="2"/><path d="M24 3v26m4-14h3M15 8h5m-5 5h5m-5 9h5"/>',
        FTTB: '<path d="M9 30V2h30v28zM16 7h3m10 0h3m-16 7h3m10 0h3m-16 7h3m10 0h3m-11 9v-6h4v6"/>',
        FTTC: '<path d="M10 30 19 2h10l9 28zM24 6v5m0 5v5m0 5v3"/>',
        FTTD: '<path d="M12 30V2h24v28zM17 29V7h14v22m-4-12h1"/>',
        FTTH: '<path d="m4 15 20-13 20 13-5 1v14H9V16zM20 30V20h8v10"/>',
        FTTN: '<path d="m24 2 13 7v14l-13 7-13-7V9zM24 9v14m-7-10 14 6m0-6-14 6"/>',
        FTTO: '<path d="M5 30V9h23v21m0-27h15v27M11 15h4m5 0h3m-12 7h4m5 0h3M33 9h4m-4 7h4m-4 7h4M4 30h40"/>',
        FTTP: '<path d="M21 2h6v28h-6zM6 9h36M11 5v8m26-8v8M8 18h13m6 0h13"/>',
        FTTX: '<path d="m24 1 21 15-21 15L3 16zM17 10l14 12m0-12L17 22"/>',
        PON: '<path d="M20 2h8v7h-8zM24 9v8M8 17h32M8 17v6m16-6v6m16-6v6M4 23h8v7H4zm16 0h8v7h-8zm16 0h8v7h-8z"/>',
        LAN: '<rect x="5" y="8" width="38" height="17" rx="3"/><path d="M10 13h5v6h-5zm11 0h5v6h-5zm11 0h5v6h-5zM12 25v5m24-5v5"/>',
        ETH: '<path d="M12 3h24v21l-7 6H19l-7-6zM17 3v9m5-9v9m5-9v9m5-9v9M17 18h14v5H17z"/>',
        AP: '<rect x="14" y="20" width="20" height="10" rx="3"/><path d="M19 20v-5m10 5v-5M7 10q17-16 34 0M12 15q12-12 24 0M18 19q6-6 12 0"/>',
        STA: '<path d="M10 3q-4 22 20 23zM25 20l9-11m-5-2 7 7M24 25v5m-7 0h16M36 3q8 2 8 10"/>',
        TO: '<path d="m24 2-12 28h24zM18 15h12m-16 9h20M20 11l11 13m-3-13L17 24M10 5q-7 9 0 17M38 5q7 9 0 17"/>'
    };
    function normalize(type) { return String(type || '').trim().toUpperCase(); }
    function label(type) { var key = normalize(type); return labels[key] || (key ? 'Tipo ' + key : 'Selecione um tipo'); }
    function escape(value) { return String(value).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }).replace(/[^\x00-\x7f]/g, function (c) {return '&#'+c.charCodeAt(0)+';';}); }
    function svg(type, color, number) {
        var key = normalize(type), shape = shapes[key] || '<circle cx="24" cy="16" r="13"/><path d="M20 10q10-5 9 3l-5 5m0 5v1"/>';
        color = /^#[0-9a-f]{6}$/i.test(color || '') ? color : '#64748b';
        var digits = String(number || '').replace(/[^0-9]/g, '').slice(0, 5);
        var lineType = ['FTTP','PON','AP','STA','TO'].indexOf(key) !== -1;
        var drawing = lineType ? '<g fill="'+color+'" stroke="#fff" stroke-width="5" stroke-linejoin="round" stroke-linecap="round">'+shape+'</g><g fill="'+color+'" stroke="'+color+'" stroke-width="2" stroke-linejoin="round" stroke-linecap="round">'+shape+'</g>' : '<g fill="'+color+'" stroke="#fff" stroke-width="2" stroke-linejoin="round" stroke-linecap="round">'+shape+'</g>';
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 44" width="38" height="32" role="img" aria-label="'+escape(label(key))+'"><title>'+escape(label(key))+'</title>'+drawing+'<rect x="7" y="32" width="34" height="11" rx="4" fill="'+color+'" stroke="#fff" stroke-width="1"/><text x="24" y="40" text-anchor="middle" font-family="Arial,sans-serif" font-size="9" font-weight="700" fill="#fff">'+escape(digits || (labels[key] ? key : '?'))+'</text></svg>';
    }
    function preview(field) {
        var id = 'cto-type-preview-' + field.id, box = document.getElementById(id);
        if (!box) {
            box = document.createElement('div'); box.id = id; box.setAttribute('aria-live', 'polite');
            box.style.cssText = 'display:flex;align-items:center;gap:10px;padding:8px 10px;margin-top:7px;border:1px solid #cbd5e1;border-radius:8px;background:#f8fafc;color:#334155;font:12px/1.4 Arial,sans-serif;';
            field.insertAdjacentElement('afterend', box);
        }
        box.innerHTML = '<span style="display:block;flex:0 0 48px">'+svg(field.value, '#64748b')+'</span><span>Ícone no mapa<br><strong>'+escape(label(field.value))+'</strong></span>';
    }
    function mount() {
        document.querySelectorAll('#TIPO, #tipo, #novaCtoTipo').forEach(preview);
    }
    root.CtoTypeIcons = {svg:svg, label:label, normalize:normalize, mount:mount, types:Object.keys(labels)};
    if (typeof document !== 'undefined') {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mount); else mount();
        ['input', 'change'].forEach(function (event) {document.addEventListener(event, function (e) {
            if (e.target && ['TIPO','tipo','novaCtoTipo'].indexOf(e.target.id) !== -1) preview(e.target);
        });});
        document.addEventListener('reset', function () {setTimeout(mount, 0);});
    }
}(typeof window !== 'undefined' ? window : globalThis));
