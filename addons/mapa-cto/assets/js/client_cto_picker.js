(function () {
  'use strict';

  if (window.__mapaCtoClientePicker) return;
  window.__mapaCtoClientePicker = true;

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  function adminBase() {
    if (typeof window.minha_url === 'string' && window.minha_url) return window.minha_url;
    return window.location.protocol + '//' + window.location.host + '/admin/';
  }

  function findField(names) {
    for (var i = 0; i < names.length; i++) {
      var field = document.querySelector('[name="' + names[i] + '"], #' + names[i]);
      if (field) return field;
    }
    return null;
  }

  function parseCoord(value) {
    if (value === null || typeof value === 'undefined') return null;
    var text = String(value).trim().replace(',', '.');
    var number = parseFloat(text);
    if (!isFinite(number) || Math.abs(number) < 0.000001) return null;
    return number;
  }

  function findClientCoords() {
    var lat = findField(['latitude', 'lat', 'cli_latitude', 'cliente_latitude', 'coordenada_latitude']);
    var lng = findField(['longitude', 'lng', 'lon', 'cli_longitude', 'cliente_longitude', 'coordenada_longitude']);
    var latValue = lat ? parseCoord(lat.value) : null;
    var lngValue = lng ? parseCoord(lng.value) : null;

    if (latValue !== null && lngValue !== null) {
      return { lat: latValue, lng: lngValue };
    }

    var combo = findField(['coordenadas', 'coordenada', 'coord', 'coords']);
    if (combo && combo.value) {
      var parts = String(combo.value).split(/[,\s;]+/).filter(Boolean);
      if (parts.length >= 2) {
        latValue = parseCoord(parts[0]);
        lngValue = parseCoord(parts[1]);
        if (latValue !== null && lngValue !== null) {
          return { lat: latValue, lng: lngValue };
        }
      }
    }

    return null;
  }

  function findAddressTarget() {
    return findField(['coordenadas', 'coordenada', 'coord', 'coords']) ||
      findField(['latitude', 'lat', 'cli_latitude', 'cliente_latitude', 'coordenada_latitude']) ||
      findField(['endereco', 'endereco_res', 'logradouro']) ||
      findField(['cep']) ||
      findField(['numero']);
  }

  function distanceMeters(a, b) {
    var r = 6371000;
    var toRad = function (deg) { return deg * Math.PI / 180; };
    var dLat = toRad(b.lat - a.lat);
    var dLng = toRad(b.lng - a.lng);
    var lat1 = toRad(a.lat);
    var lat2 = toRad(b.lat);
    var h = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return Math.round(r * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h)));
  }

  function formatDistance(meters) {
    if (!isFinite(meters)) return '';
    if (meters >= 1000) return (meters / 1000).toFixed(meters >= 10000 ? 0 : 1).replace('.', ',') + ' km';
    return meters + ' m';
  }

  function setField(field, value) {
    if (!field) return;
    field.value = value;
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function closestField(el) {
    return el && (el.closest('.field') || el.closest('.form-group') || el.parentNode);
  }

  function normalize(txt) {
    return String(txt || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function injectStyles() {
    if (document.getElementById('cto-picker-style')) return;
    var style = document.createElement('style');
    style.id = 'cto-picker-style';
    style.textContent = [
      '.cto-picker-overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:99999;display:flex;align-items:center;justify-content:center;padding:18px}',
      '.cto-picker-modal{background:#fff;width:min(980px,96vw);max-height:88vh;border-radius:8px;box-shadow:0 18px 60px rgba(0,0,0,.28);display:flex;flex-direction:column;overflow:hidden;font-family:Arial,sans-serif}',
      '.cto-picker-head{padding:14px 18px;background:#5f6ee8;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:12px}',
      '.cto-picker-head h3{margin:0;font-size:18px;color:#fff}',
      '.cto-picker-close{border:0;background:rgba(255,255,255,.18);color:#fff;border-radius:6px;font-size:20px;width:34px;height:34px;cursor:pointer}',
      '.cto-picker-body{padding:14px 18px;overflow:auto}',
      '.cto-picker-search{width:100%;box-sizing:border-box;border:1px solid #d8dee9;border-radius:6px;padding:10px;margin-bottom:12px}',
      '.cto-picker-list{display:grid;gap:10px}',
      '.cto-picker-card{border:1px solid #dde3ef;border-radius:8px;padding:12px;background:#f8fafc;display:grid;grid-template-columns:1fr auto;gap:12px;align-items:center}',
      '.cto-picker-card strong{font-size:15px;color:#182033}',
      '.cto-picker-meta{font-size:12px;color:#536079;margin-top:4px;line-height:1.45}',
      '.cto-picker-badge{display:inline-block;background:#e8fff4;color:#087f5b;border:1px solid #baf0d6;border-radius:999px;padding:2px 8px;margin-left:6px;font-size:12px}',
      '.cto-picker-actions{display:flex;gap:8px;align-items:center}',
      '.cto-picker-actions select{height:36px;border:1px solid #d8dee9;border-radius:6px;padding:0 8px;background:#fff}',
      '.cto-picker-actions button,.cto-picker-open{border:0;border-radius:6px;background:#10b981;color:#fff;font-weight:700;cursor:pointer;box-shadow:none}',
      '.cto-picker-actions button{height:36px;padding:0 14px}',
      '.cto-picker-open{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:34px;margin:6px 0 8px 0;padding:0 14px;font-size:12px;line-height:1;white-space:nowrap;text-transform:uppercase}',
      '.cto-picker-open:hover{background:#059669;color:#fff}',
      '.cto-picker-empty{padding:18px;text-align:center;color:#6b7280;background:#f8fafc;border-radius:8px}',
      '.cto-picker-warning{border:1px solid #facc15;background:#fef9c3;color:#854d0e;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:13px;line-height:1.35}',
      '.cto-picker-alert{background:#fff;width:min(520px,94vw);border-radius:8px;box-shadow:0 18px 60px rgba(0,0,0,.28);overflow:hidden;font-family:Arial,sans-serif}',
      '.cto-picker-alert-body{padding:18px;color:#334155;font-size:14px;line-height:1.45}',
      '.cto-picker-alert-body p{margin:0 0 10px 0}',
      '.cto-picker-alert-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 18px 18px 18px}',
      '.cto-picker-alert-actions button{border:0;border-radius:6px;font-weight:700;cursor:pointer;height:38px;padding:0 14px}',
      '.cto-picker-fix{background:#5f6ee8;color:#fff}',
      '.cto-picker-ignore{background:#e5e7eb;color:#111827}',
      '.cto-picker-distance{font-size:12px;color:#0f766e;font-weight:700;margin-top:4px}',
      '@media(max-width:700px){.cto-picker-card{grid-template-columns:1fr}.cto-picker-actions{justify-content:space-between}.cto-picker-alert-actions{flex-direction:column}.cto-picker-alert-actions button{width:100%}}'
    ].join('');
    document.head.appendChild(style);
  }

  function openPicker(fields) {
    injectStyles();
    var clientCoords = findClientCoords();
    if (!clientCoords) {
      openCoordinateWarning(fields);
      return;
    }
    openPickerList(fields, clientCoords, false);
  }

  function openCoordinateWarning(fields) {
    var overlay = document.createElement('div');
    overlay.className = 'cto-picker-overlay';
    overlay.innerHTML = [
      '<div class="cto-picker-alert">',
      '<div class="cto-picker-head"><h3>Atencao</h3><button type="button" class="cto-picker-close">x</button></div>',
      '<div class="cto-picker-alert-body">',
      '<p>Preencha ou ajuste corretamente a coordenada do endereco do cliente antes de calcular a distancia ate a CTO.</p>',
      '<p>Voce pode corrigir agora ou continuar sem coordenada. Se continuar, as CTOs serao listadas sem mostrar a distancia.</p>',
      '</div>',
      '<div class="cto-picker-alert-actions">',
      '<button type="button" class="cto-picker-ignore">Ignorar Coordenada</button>',
      '<button type="button" class="cto-picker-fix">Corrigir Coordenada</button>',
      '</div>',
      '</div>'
    ].join('');
    document.body.appendChild(overlay);

    function close() { overlay.remove(); }
    overlay.querySelector('.cto-picker-close').onclick = close;
    overlay.querySelector('.cto-picker-ignore').onclick = function () {
      close();
      openPickerList(fields, null, true);
    };
    overlay.querySelector('.cto-picker-fix').onclick = function () {
      var target = findAddressTarget();
      close();
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(function () { target.focus(); }, 350);
      }
    };
  }

  function openPickerList(fields, clientCoords, ignoredMissingCoords) {
    var overlay = document.createElement('div');
    overlay.className = 'cto-picker-overlay';
    overlay.innerHTML = [
      '<div class="cto-picker-modal">',
      '<div class="cto-picker-head"><h3>LISTAR CTO E PORTA</h3><button type="button" class="cto-picker-close">x</button></div>',
      '<div class="cto-picker-body">',
      ignoredMissingCoords ? '<div class="cto-picker-warning">Atencao: coordenada do cliente nao informada. As CTOs serao listadas sem calculo de distancia.</div>' : '',
      '<input class="cto-picker-search" placeholder="Buscar por CTO, endereco ou OLT..." />',
      '<div class="cto-picker-list"><div class="cto-picker-empty">Carregando CTOs...</div></div>',
      '</div>',
      '</div>'
    ].join('');
    document.body.appendChild(overlay);

    var list = overlay.querySelector('.cto-picker-list');
    var search = overlay.querySelector('.cto-picker-search');
    overlay.querySelector('.cto-picker-close').onclick = function () { overlay.remove(); };
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay) overlay.remove();
    });

    var ctos = [];
    function render() {
      var q = normalize(search.value);
      var filtered = ctos.filter(function (cto) {
        var hay = normalize([cto.nome, cto.endereco, cto.olt, cto.tipo].join(' '));
        return !q || hay.indexOf(q) !== -1;
      });

      if (!filtered.length) {
        list.innerHTML = '<div class="cto-picker-empty">Nenhuma CTO com porta livre encontrada.</div>';
        return;
      }

      list.innerHTML = filtered.map(function (cto, idx) {
        var ctoCoords = { lat: parseCoord(cto.latitude), lng: parseCoord(cto.longitude) };
        var distance = '';
        if (clientCoords && ctoCoords.lat !== null && ctoCoords.lng !== null) {
          distance = '<div class="cto-picker-distance">Distancia aproximada: ' + formatDistance(distanceMeters(clientCoords, ctoCoords)) + '</div>';
        }
        var portas = (cto.portas_livres || []).map(function (p) {
          return '<option value="' + String(p).replace(/"/g, '&quot;') + '">' + p + '</option>';
        }).join('');
        return [
          '<div class="cto-picker-card" data-idx="' + idx + '">',
          '<div><strong>' + cto.nome + '</strong><span class="cto-picker-badge">' + cto.livres + '/' + cto.capacidade + ' livres</span>',
          '<div class="cto-picker-meta">Endereco: ' + (cto.endereco || '-') + '<br>OLT: ' + (cto.olt || '-') + ' | Sinal: ' + (cto.sinal || '-') + '</div>' + distance + '</div>',
          '<div class="cto-picker-actions"><select>' + portas + '</select><button type="button">Usar</button></div>',
          '</div>'
        ].join('');
      }).join('');

      Array.prototype.forEach.call(list.querySelectorAll('.cto-picker-card'), function (card) {
        var cto = filtered[parseInt(card.getAttribute('data-idx'), 10)];
        card.querySelector('button').onclick = function () {
          var porta = card.querySelector('select').value;
          setField(fields.cto, cto.nome);
          setField(fields.porta, porta);
          setField(fields.olt, cto.olt || fields.olt && fields.olt.value || '');
          overlay.remove();
        };
      });
    }

    search.addEventListener('input', render);
    fetch(adminBase() + 'addons/caixas/src/cto/api/ctos_disponiveis.php', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        ctos = (data.ctos || []).filter(function (cto) { return cto.livres > 0; });
        render();
      })
      .catch(function () {
        list.innerHTML = '<div class="cto-picker-empty">Erro ao carregar CTOs disponiveis.</div>';
      });
  }

  ready(function () {
    var cto = findField(['caixa_herm']);
    var porta = findField(['porta_splitter']);
    if (!cto || !porta || document.getElementById('cto-picker-open')) return;

    var fields = {
      cto: cto,
      porta: porta,
      olt: findField(['armario_olt'])
    };

    injectStyles();
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.id = 'cto-picker-open';
    btn.className = 'cto-picker-open';
    btn.textContent = 'LISTAR CTO E PORTA';
    btn.onclick = function () { openPicker(fields); };

    var target = closestField(cto) || closestField(findField(['endereco'])) || cto.parentNode;
    if (target && target.parentNode) {
      target.parentNode.insertBefore(btn, target.nextSibling);
    } else {
      cto.parentNode.appendChild(btn);
    }
  });
})();
