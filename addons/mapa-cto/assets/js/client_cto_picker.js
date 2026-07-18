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
      '.cto-picker-actions button,.cto-picker-open{border:0;border-radius:6px;background:#10b981;color:#fff;font-weight:700;cursor:pointer}',
      '.cto-picker-actions button{height:36px;padding:0 14px}',
      '.cto-picker-open{margin-top:6px;padding:8px 12px;font-size:13px}',
      '.cto-picker-empty{padding:18px;text-align:center;color:#6b7280;background:#f8fafc;border-radius:8px}',
      '@media(max-width:700px){.cto-picker-card{grid-template-columns:1fr}.cto-picker-actions{justify-content:space-between}}'
    ].join('');
    document.head.appendChild(style);
  }

  function openPicker(fields) {
    injectStyles();
    var overlay = document.createElement('div');
    overlay.className = 'cto-picker-overlay';
    overlay.innerHTML = [
      '<div class="cto-picker-modal">',
      '<div class="cto-picker-head"><h3>Selecionar CTO e porta livre</h3><button type="button" class="cto-picker-close">x</button></div>',
      '<div class="cto-picker-body">',
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
        var portas = (cto.portas_livres || []).map(function (p) {
          return '<option value="' + String(p).replace(/"/g, '&quot;') + '">' + p + '</option>';
        }).join('');
        return [
          '<div class="cto-picker-card" data-idx="' + idx + '">',
          '<div><strong>' + cto.nome + '</strong><span class="cto-picker-badge">' + cto.livres + '/' + cto.capacidade + ' livres</span>',
          '<div class="cto-picker-meta">Endereço: ' + (cto.endereco || '-') + '<br>OLT: ' + (cto.olt || '-') + ' | Sinal: ' + (cto.sinal || '-') + '</div></div>',
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
        list.innerHTML = '<div class="cto-picker-empty">Erro ao carregar CTOs disponíveis.</div>';
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

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.id = 'cto-picker-open';
    btn.className = 'cto-picker-open';
    btn.textContent = '📦 Escolher CTO/porta livre';
    btn.onclick = function () { openPicker(fields); };

    var target = closestField(cto) || closestField(findField(['endereco'])) || cto.parentNode;
    if (target && target.parentNode) {
      target.parentNode.insertBefore(btn, target.nextSibling);
    } else {
      cto.parentNode.appendChild(btn);
    }
  });
})();
