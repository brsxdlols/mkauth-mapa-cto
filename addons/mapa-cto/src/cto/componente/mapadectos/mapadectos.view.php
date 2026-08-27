<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Mapa de CTOs - GERENCIADOR FTTH</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            padding: 0;
        }

        .container {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 8px 10px 10px;
            display: flex;
            flex-direction: column;
            min-height: 0;
            gap: 8px;
        }

        .header {
            margin-bottom: 0;
            color: white;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .header h1 {
            font-size: clamp(1.25rem, 1.6vw, 1.7rem);
            margin-bottom: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-voltar {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid white;
            display: inline-block;
        }

        .btn-voltar:hover {
            background: white;
            color: #667eea;
        }

        .content {
            background: white;
            border-radius: 10px;
            padding: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.6s ease-out;
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .controls {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            flex-wrap: wrap;
            flex: 0 0 auto;
        }

        .filter-btn {
            padding: 6px 14px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .filter-btn:hover {
            border-color: #667eea;
        }

        .action-btn {
            border-color: #10b981;
            background: #10b981;
            color: white;
        }

        .action-btn.secondary {
            border-color: #64748b;
            background: #64748b;
        }

        .filter-btn.active,
        .action-btn.secondary.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.22);
            transform: translateY(-1px);
        }

        .action-btn.secondary.active::before {
            content: "✓ ";
            font-weight: 900;
        }

        .map-shell {
            position: relative;
            width: 100%;
            min-height: 0;
            flex: 1 1 auto;
            overflow: hidden;
            border-radius: 8px;
        }

        #map {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: inherit;
            border-radius: 8px;
            margin-bottom: 0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(150px, 1fr));
            gap: 8px;
            margin-bottom: 8px;
            flex: 0 0 auto;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .stat-card h3 {
            font-size: clamp(1.1rem, 1.5vw, 1.45rem);
            margin-bottom: 2px;
        }

        .stat-card p {
            opacity: 0.9;
            font-size: 0.78em;
        }

        .info-popup {
            background: white;
            padding: 15px;
            border-radius: 8px;
            max-width: 300px;
        }

        .info-popup h3 {
            color: #667eea;
            margin-bottom: 10px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
            font-size: 0.9em;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row strong {
            color: #333;
        }

        .info-row span {
            color: #666;
        }

        .progress-bar {
            height: 8px;
            background: #eee;
            border-radius: 4px;
            margin: 8px 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #f59e0b);
            border-radius: 4px;
        }

        .cto-map-panel {
            position: absolute;
            top: 12px;
            right: 12px;
            bottom: 12px;
            width: min(420px, calc(100% - 24px));
            z-index: 9999;
            display: none;
            overflow: visible;
            pointer-events: none;
        }

        .cto-map-panel.is-open {
            display: block;
        }

        .cto-panel-close {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 2;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.94);
            color: #475569;
            font-size: 26px;
            line-height: 1;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
            pointer-events: auto;
        }

        .cto-hover-tooltip {
            color: #1f2937;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            max-width: min(720px, 70vw);
            padding: 4px 2px;
        }

        .cto-hover-tooltip strong {
            color: #667eea;
            display: block;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .cto-popup {
            width: 100%;
            max-height: 100%;
            background: white;
            border-radius: 12px;
            overflow: visible;
            box-shadow: 0 10px 26px rgba(30, 41, 59, 0.2);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            pointer-events: auto;
        }

        .cto-popup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 16px;
            flex: 0 0 auto;
        }

        .cto-popup-header h3 {
            margin: 0 0 4px 0;
            font-size: clamp(1.05rem, 1.35vw, 1.35rem);
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .cto-popup-header p {
            margin: 0;
            font-size: 0.82rem;
            opacity: 0.92;
        }

        .cto-popup-body {
            padding: 10px;
            overflow: visible;
            min-height: 0;
            flex: 1 1 auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            align-content: start;
        }

        .cto-section {
            margin-bottom: 0;
        }

        .cto-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #667eea;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .cto-text {
            color: #334155;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .cto-tech-grid,
        .cto-client-grid {
            display: grid;
            gap: 8px;
        }

        .cto-tech-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            background: #f8fafc;
            border-radius: 8px;
            padding: 9px;
        }

        .cto-tech-item strong {
            display: block;
            color: #667eea;
            font-size: 0.78rem;
            margin-bottom: 4px;
        }

        .cto-tech-item span {
            color: #334155;
            overflow-wrap: anywhere;
        }

        .cto-client-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .cto-count-card {
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            min-width: 0;
        }

        .cto-count-card strong {
            display: block;
            font-size: 1.12rem;
            line-height: 1;
            margin-bottom: 5px;
        }

        .cto-count-card span {
            color: #64748b;
            font-size: 0.82rem;
        }

        .cto-count-total {
            background: #eef2ff;
            border-left: 3px solid #667eea;
            color: #667eea;
        }

        .cto-count-online {
            background: #d1fae5;
            border-left: 3px solid #10b981;
            color: #059669;
        }

        .cto-count-offline {
            background: #fee2e2;
            border-left: 3px solid #ef4444;
            color: #dc2626;
        }

        .cto-client-list {
            background: #f8fafc;
            border-radius: 8px;
            padding: 8px;
            grid-column: 1 / -1;
        }

        .cto-client-list-inner {
            max-height: none;
            overflow: visible;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            column-gap: 14px;
        }

        .cto-client-row {
            display: grid;
            grid-template-columns: minmax(110px, 1fr) auto;
            gap: 6px;
            align-items: center;
            padding: 4px 5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .cto-client-row:last-child {
            border-bottom: none;
        }

        .cto-client-name,
        .cto-client-login {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cto-client-login {
            color: #64748b;
            font-size: 0.74rem;
            white-space: normal;
        }

        .cto-empty {
            background: #fef3c7;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            color: #92400e;
        }

        .cto-progress-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 600;
        }

        .cto-progress-head span:last-child {
            color: #10b981;
        }

        .cto-edit {
            display: block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 9px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .cto-section-full {
            grid-column: 1 / -1;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            body {
                overflow: auto;
            }

            .container {
                height: auto;
                min-height: 100%;
            }

            .header h1 {
                font-size: 1.5em;
            }

            .map-shell {
                height: 68vh;
                min-height: 460px;
                flex: none;
            }

            .controls {
                flex-direction: column;
            }

            .filter-btn {
                width: 100%;
            }

            .cto-popup {
                min-height: 100%;
            }

            .cto-tech-grid,
            .cto-client-grid,
            .cto-popup-body {
                grid-template-columns: 1fr;
            }

            .cto-client-row {
                grid-template-columns: 1fr;
            }
        }
        .gm-style .gm-style-iw-c,.gm-style .gm-style-iw-d{max-height:none!important;overflow:visible!important}
        .cto-hover-tooltip{font-family:Arial,sans-serif;min-width:320px;max-width:min(720px,70vw);color:#1f2937}
        .leaflet-popup-content-wrapper,.leaflet-popup-content{max-height:none!important;overflow:visible!important}
        .leaflet-popup-content{margin:10px 12px!important}
        .cto-hover-popup .leaflet-popup-content-wrapper{overflow:visible!important}
        .cto-leaflet-marker svg{display:block}
        .cto-hover-title{font-weight:700;color:#4f63d8;margin-bottom:4px}
        .cto-hover-address{font-size:12px;color:#4b5563;margin-bottom:6px}
        .cto-hover-counts{font-size:12px;font-weight:700;margin-bottom:8px;color:#111827}
        .cto-hover-clients{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:4px 10px;overflow:visible}
        .cto-hover-client{display:grid;grid-template-columns:10px minmax(100px,1fr) minmax(58px,.7fr) auto;gap:5px;align-items:center;font-size:11px;border-top:1px solid #eef2f7;padding-top:4px;min-width:0}
        .cto-hover-dot{width:7px;height:7px;border-radius:50%;background:#ef4444}
        .cto-hover-client.online .cto-hover-dot{background:#10b981}
        .cto-hover-client.inactive{color:#6b7280}
        .cto-hover-client.inactive .cto-hover-dot{background:#9ca3af}
        .cto-hover-name{font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .cto-hover-name.inactive,.cto-client-name.inactive{color:#6b7280!important}
        .cto-inactive-tag{display:inline-flex;margin-left:4px;padding:1px 5px;border-radius:999px;background:#e5e7eb;color:#6b7280;font-size:10px;font-weight:800}
        .cto-hover-login{color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .cto-hover-type{font-size:10px;color:#475569;background:#eef2ff;border-radius:999px;padding:1px 5px}
        .cto-hover-empty,.cto-hover-more{font-size:11px;color:#64748b;margin-top:4px}
        .cto-port-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(54px,1fr));gap:6px}
        .cto-port-pill{border:1px solid #dbeafe;border-radius:6px;padding:5px 6px;font-size:11px;background:#eff6ff;color:#1e40af;text-align:center}
        .cto-port-pill.used{background:#fef2f2;border-color:#fecaca;color:#991b1b}
        .map-toast{position:absolute;left:12px;bottom:12px;z-index:10000;background:#111827;color:white;padding:10px 14px;border-radius:8px;font-weight:700;box-shadow:0 8px 20px rgba(0,0,0,.25)}
        .cto-add-modal{position:absolute;left:12px;top:12px;z-index:10001;width:min(360px,calc(100% - 24px));background:#fff;border-radius:10px;box-shadow:0 12px 32px rgba(15,23,42,.25);overflow:hidden}
        .cto-add-modal header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:12px 14px;font-weight:700;display:flex;justify-content:space-between;align-items:center}
        .cto-add-modal .body{padding:12px;display:grid;gap:8px}
        .cto-add-modal input,.cto-add-modal select,.cto-add-modal textarea{width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:7px;font-family:inherit}
        .cto-add-modal .grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
        .cto-add-modal footer{display:flex;gap:8px;justify-content:flex-end;padding:0 12px 12px}
        .cto-small-btn{border:0;border-radius:7px;padding:8px 12px;font-weight:700;cursor:pointer}
        .cto-small-btn.save{background:#10b981;color:#fff}
        .cto-small-btn.cancel{background:#e5e7eb;color:#111827}
        .cto-client-detail{position:absolute;top:12px;left:12px;z-index:10002;width:min(330px,calc(100% - 24px));background:white;border-radius:10px;box-shadow:0 12px 32px rgba(15,23,42,.25);padding:14px;color:#334155}
        .cto-client-detail h4{margin:0 0 8px;color:#667eea}
        .cto-client-detail h4.inactive{color:#6b7280}
        .cto-client-detail p{margin:4px 0;font-size:12px}
        .cto-client-location-editor{position:absolute;left:12px;bottom:12px;z-index:10003;width:min(340px,calc(100% - 24px));background:white;border-radius:10px;box-shadow:0 12px 32px rgba(15,23,42,.25);padding:14px;color:#334155}
        .cto-client-location-editor h4{margin:0 0 8px;color:#667eea}
        .cto-client-location-editor p{margin:4px 0;font-size:12px}
        .cto-link-list{max-height:220px;overflow:auto;display:grid;gap:6px;margin-top:8px;padding-right:4px}
        .cto-link-row{border:1px solid #dbeafe;background:#f8fafc;border-radius:8px;padding:8px;text-align:left;cursor:pointer}
        .cto-link-row:hover{border-color:#667eea;background:#eef2ff}
        .cto-link-row strong{display:block;color:#1f2937;margin-bottom:3px}
        .cto-link-row span{display:block;color:#64748b;font-size:11px}
        .cto-link-search{width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:7px;margin-top:8px}
        .cto-link-choice{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}
        .cto-count-card{border:0;cursor:pointer;font:inherit}
        .cto-count-card.active{outline:2px solid #2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.14)}
        .controls-spacer{flex:1 1 auto;min-width:20px}
        .cto-map-legend{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:8px 0 10px;font-size:11px;color:#334155}
        .cto-map-legend span{display:inline-flex;gap:5px;align-items:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:4px 8px}
        .legend-dot{width:10px;height:10px;border-radius:999px;display:inline-block}
        .legend-house{width:12px;height:12px;display:inline-block;clip-path:polygon(50% 0,100% 42%,88% 42%,88% 100%,12% 100%,12% 42%,0 42%)}
        .legend-blue{background:#2563eb}.legend-red{background:#ef4444}.legend-green{background:#10b981}.legend-gray{background:#9ca3af}
        .cto-location-editor{position:absolute;left:16px;top:82px;bottom:auto;z-index:1000000;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.22);padding:12px;width:300px;max-width:calc(100% - 32px)}
        .cto-location-editor h4{margin:0 0 8px;color:#667eea}
        .cto-location-editor p{margin:4px 0;font-size:12px}
        .cto-highlight-marker{width:54px;height:46px;filter:drop-shadow(0 8px 12px rgba(245,158,11,.45))}
        .cto-port-select-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(62px,1fr));gap:6px;margin:8px 0 10px}
        .cto-port-select{border:1px solid #bfdbfe;border-radius:7px;padding:7px 5px;background:#eff6ff;color:#1d4ed8;font-size:11px;text-align:center;cursor:pointer}
        .cto-port-select:hover{background:#dbeafe;border-color:#667eea}
        .cto-port-select.used{background:#fef2f2;border-color:#fecaca;color:#991b1b;cursor:not-allowed;opacity:.85}
        .cto-port-select.used.inactive,.cto-port-pill.used.inactive{background:#f3f4f6;border-color:#d1d5db;color:#6b7280}
        .cto-port-select.current{background:#fef3c7;border-color:#f59e0b;color:#92400e}
        .cto-port-select.selected{background:#fde68a;border-color:#f59e0b;color:#92400e;box-shadow:0 0 0 2px rgba(245,158,11,.28) inset}
        .cto-selected-port-note{background:#fffbeb;border-radius:7px;color:#92400e;font-weight:700;margin:8px 0 10px;padding:8px 10px}
        .cto-selected-client-pulse{width:34px;height:34px;border:3px solid #f59e0b;border-radius:999px;background:rgba(245,158,11,.18);box-shadow:0 0 0 rgba(245,158,11,.7);animation:ctoPulse 1.05s infinite}
        .cto-client-search-btn{min-width:220px;background:#fff!important;color:#1f2937!important;border:2px solid #667eea!important}
        .cto-client-search-panel{position:absolute;left:12px;top:12px;z-index:10004;width:min(430px,calc(100% - 24px));background:#fff;border-radius:10px;box-shadow:0 14px 36px rgba(15,23,42,.28);overflow:hidden;color:#1f2937}
        .cto-client-search-panel header{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:800}
        .cto-client-search-panel .body{padding:12px}
        .cto-client-search-panel input{width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:9px 10px;font:inherit;margin-bottom:8px}
        .cto-client-search-results{display:grid;gap:6px;max-height:330px;overflow:auto;padding-right:4px}
        .cto-client-search-row{border:1px solid #e2e8f0;background:#f8fafc;border-radius:8px;padding:8px 10px;text-align:left;cursor:pointer;color:#1f2937}
        .cto-client-search-row:hover{border-color:#667eea;background:#eef2ff}
        .cto-client-search-row.inactive{background:#f3f4f6;color:#6b7280}
        .cto-client-search-row strong{display:block;font-size:12px;margin-bottom:3px}
        .cto-client-search-row span{display:block;font-size:11px;color:#64748b}
        @keyframes ctoPulse{0%{transform:scale(.75);box-shadow:0 0 0 0 rgba(245,158,11,.7)}70%{transform:scale(1.2);box-shadow:0 0 0 14px rgba(245,158,11,0)}100%{transform:scale(.75);box-shadow:0 0 0 0 rgba(245,158,11,0)}}
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1> Gerenciador FTTH</h1>
            <div style="display: flex; gap: 10px;">
                <a href="?_route=inicio" class="btn-voltar">&lt;- Voltar a Listagem</a>
                <a href="?_route=painel" class="btn-voltar" style="background: rgba(255, 107, 107, 0.2); border-color: #ff6b6b;"> Painel Principal</a>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Estatsticas -->
            <div class="stats">
                <div class="stat-card">
                    <h3 id="totalCtos">0</h3>
                    <p>CTOs Cadastradas</p>
                </div>
                <div class="stat-card">
                    <h3 id="totalClientes">0</h3>
                    <p>Clientes em CTO</p>
                </div>
                <div class="stat-card">
                    <h3 id="clientesOnline">0</h3>
                    <p>Online em CTO</p>
                </div>
                <div class="stat-card">
                    <h3 id="clientesOffline">0</h3>
                    <p>Offline em CTO</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="controls">
                <button class="filter-btn active" data-filter="todos">Todas as CTOs</button>
                <button class="filter-btn" data-filter="comclientes">CTO com clientes</button>
                <button class="filter-btn" data-filter="semclientes">CTO sem clientes</button>
                <button class="filter-btn cto-client-search-btn" id="btnBuscaClienteMapa" type="button">Buscar cliente</button>
                <span class="controls-spacer"></span>
                <button class="filter-btn action-btn" id="btnAdicionarCto" type="button">Adicionar CTO no mapa</button>
                <button class="filter-btn action-btn secondary" id="btnMostrarTodosClientes" type="button">Mostrar clientes</button>
                <button class="filter-btn action-btn secondary" data-client-filter="todos" type="button">Todos clientes</button>
                <button class="filter-btn action-btn secondary" data-client-filter="semcto" type="button">Clientes sem CTO</button>
                <button class="filter-btn action-btn secondary" data-client-filter="comcto" type="button">Clientes com CTO</button>
                <button class="filter-btn action-btn secondary" id="btnLimparClientes" type="button">Limpar clientes</button>
            </div>
            <div class="cto-map-legend">
                <span><i class="legend-house legend-blue"></i>Cliente online</span>
                <span><i class="legend-house legend-red"></i>Cliente offline</span>
                <span><i class="legend-dot legend-green"></i>CTO com online</span>
                <span><i class="legend-dot legend-gray"></i>CTO sem cliente</span>
                <span><i class="legend-dot legend-red"></i>CTO com offline</span>
            </div>

            <!-- Mapa -->
            <div class="map-shell">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Map provider API -->
    <?php
    require_once dirname(__FILE__) . '/../../config/api.php';
    $api_key = getGoogleMapsApiKey();
    $map_provider = function_exists('getSystemMapProvider') ? getSystemMapProvider() : (!empty($api_key) ? 'google' : 'openstreet');
    if ($map_provider === 'google' && empty($api_key)) {
        $map_provider = 'openstreet';
    }
    ?>
    <?php if ($map_provider === 'google' && !empty($api_key)): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($api_key); ?>"></script>
    <?php else: ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>

    <script>
        // Dados das CTOs
        const ctosData = <?php echo $ctos_json; ?>;
        const todosClientesData = <?php echo $todos_clientes_json ?: '[]'; ?>;
        const MAP_PROVIDER = <?php echo json_encode($map_provider); ?>;
        let mapa = null;
        let marcadores = [];
        let marcadoresClientesHover = [];
        let filtroAtual = 'todos';
        let painelCtoConteudoAtual = '';
        let ctoHoverTimer = null;
        let modoAdicionarCto = false;
        let tempAddMarker = null;
        let bloqueiaAtualizacaoTempoReal = false;
        let clientesFixosAtivos = false;
        let todosClientesFixosAtivos = false;
        let filtroTodosClientesAtual = 'todos';
        let filtroClientesCtoAtual = 'total';
        let ctoUnicaVisivelId = null;
        let modoAtrelarCliente = null;
        let ctoDestinoAtrelamento = null;
        let portaSelecionadaAtrelamento = '';
        let linhaPreviewAtrelamento = null;
        let linhaRotaAtrelamento = null;
        let clienteDetalheAtual = null;
        let ctoHoverAtivoId = null;
        let ctoSelecionadaAtual = null;
        let marcadorClienteSelecionado = null;
        let modoAjustarCliente = null;
        let marcadorAjusteCliente = null;
        let ajusteClientePosicao = null;
        let estadoMapaAntesAcao = null;
        let modoClicarCtoAtrelamento = false;
        let marcadorCtoDestaqueLista = null;
        let modoAjustarCto = null;
        let marcadorAjusteCto = null;
        let ajusteCtoPosicao = null;
        let destaquesRadio = [];
        let clientesBuscaMapa = [];
        const CAIXAS_MAP_LAYER_KEY = 'caixas_modo_visualizacao_mapa';
        function getCaixasMapMode() { return (localStorage.getItem(CAIXAS_MAP_LAYER_KEY) === 'satelite') ? 'satelite' : 'mapa'; }
        function setCaixasMapMode(mode) { localStorage.setItem(CAIXAS_MAP_LAYER_KEY, mode === 'satelite' ? 'satelite' : 'mapa'); }
        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (char) {
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[char];
            });
        }

        function primeiroUltimoNome(nome) {
            const partes = String(nome || '').trim().split(/\s+/).filter(Boolean);
            if (partes.length <= 2) return partes.join(' ');
            return partes[0] + ' ' + partes[partes.length - 1];
        }

        function clienteDesativado(cliente) {
            return !!(cliente && (cliente.desativado || String(cliente.status || '').toLowerCase() === 'desativado'));
        }

        function statusClienteLabel(cliente) {
            if (clienteDesativado(cliente)) return 'Desativado';
            return cliente && cliente.status ? cliente.status : '-';
        }

        function nomeClienteComStatus(cliente) {
            const nome = cliente && cliente.nome ? cliente.nome : 'Cliente';
            return clienteDesativado(cliente) ? nome + ' - Desativado' : nome;
        }

        function normalizarBuscaTexto(valor) {
            return String(valor || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        function atualizarBotaoClientes() {
            const btn = document.getElementById('btnMostrarTodosClientes');
            if (btn) {
                btn.textContent = todosClientesFixosAtivos ? 'Ocultar clientes' : 'Mostrar clientes';
                btn.classList.toggle('active', todosClientesFixosAtivos);
            }
            document.querySelectorAll('[data-client-filter]').forEach(b => {
                b.classList.toggle('active', todosClientesFixosAtivos && b.getAttribute('data-client-filter') === filtroTodosClientesAtual);
            });
        }

        function clientesDisponiveisParaBusca() {
            const mapaClientes = new Map();
            const adicionar = cliente => {
                if (!cliente || !cliente.id) return;
                const chave = String(cliente.tipo || 'Cliente') + ':' + String(cliente.id);
                if (!mapaClientes.has(chave)) mapaClientes.set(chave, cliente);
            };
            (Array.isArray(todosClientesData) ? todosClientesData : []).forEach(adicionar);
            (Array.isArray(ctosData) ? ctosData : []).forEach(cto => {
                (Array.isArray(cto.clientes) ? cto.clientes : []).forEach(cliente => {
                    if (!cliente.caixa_herm) cliente.caixa_herm = cto.nome || '';
                    adicionar(cliente);
                });
            });
            return Array.from(mapaClientes.values()).sort((a, b) => String(a.nome || '').localeCompare(String(b.nome || ''), 'pt-BR'));
        }

        function fecharBuscaClienteMapa() {
            const painel = document.getElementById('ctoClientSearchPanel');
            if (painel) painel.remove();
        }

        function abrirBuscaClienteMapa() {
            const mapElement = document.getElementById('map');
            if (!mapElement) return;
            let painel = document.getElementById('ctoClientSearchPanel');
            if (!painel) {
                painel = document.createElement('div');
                painel.id = 'ctoClientSearchPanel';
                painel.className = 'cto-client-search-panel';
                mapElement.appendChild(painel);
            }
            painel.innerHTML = `
                <header>
                    <span>Buscar cliente</span>
                    <button type="button" class="cto-small-btn cancel" onclick="fecharBuscaClienteMapa()">x</button>
                </header>
                <div class="body">
                    <input type="text" id="ctoClientSearchInput" placeholder="Nome, login, CTO ou porta" oninput="renderBuscaClienteMapa(this.value)" autocomplete="off">
                    <div id="ctoClientSearchResults" class="cto-client-search-results"></div>
                </div>
            `;
            bloquearWheelMapa(painel);
            renderBuscaClienteMapa('');
            setTimeout(() => {
                const input = document.getElementById('ctoClientSearchInput');
                if (input) input.focus();
            }, 50);
        }

        function renderBuscaClienteMapa(termo) {
            const alvo = document.getElementById('ctoClientSearchResults');
            if (!alvo) return;
            const busca = normalizarBuscaTexto(termo);
            clientesBuscaMapa = clientesDisponiveisParaBusca().filter(cliente => {
                const texto = normalizarBuscaTexto([
                    cliente.nome,
                    cliente.login,
                    cliente.caixa_herm,
                    cliente.porta,
                    statusClienteLabel(cliente)
                ].join(' '));
                return !busca || texto.includes(busca);
            }).slice(0, 80);
            if (!clientesBuscaMapa.length) {
                alvo.innerHTML = '<div class="cto-hover-empty">Nenhum cliente encontrado</div>';
                return;
            }
            alvo.innerHTML = clientesBuscaMapa.map((cliente, index) => {
                const inactive = clienteDesativado(cliente);
                const meta = 'Login: ' + (cliente.login || '-') + ' | CTO: ' + (cliente.caixa_herm || 'sem CTO') + ' | Porta: ' + (cliente.porta || '-');
                return `<button type="button" class="cto-client-search-row ${inactive ? 'inactive' : ''}" onclick="selecionarClienteBuscaMapa(${index})">
                    <strong>${escapeHtml(nomeClienteComStatus(cliente))}</strong>
                    <span>${escapeHtml(meta)}</span>
                </button>`;
            }).join('');
        }

        function selecionarClienteBuscaMapa(index) {
            const cliente = clientesBuscaMapa[index];
            if (!cliente) return;
            fecharBuscaClienteMapa();
            abrirDetalheCliente(cliente);
            destacarClienteSelecionado(cliente);
            if (clienteTemCoordenada(cliente)) ajustarZoomAtrelamento(cliente);
            mostrarAvisoMapa('Cliente selecionado. Voce pode atrelar ou alterar a CTO/porta.');
        }

        function capturarViewportMapa() {
            if (!mapa) return null;
            if (MAP_PROVIDER === 'openstreet' && window.L && typeof mapa.getCenter === 'function') {
                const centro = mapa.getCenter();
                return {provider: 'openstreet', lat: centro.lat, lng: centro.lng, zoom: mapa.getZoom()};
            }
            if (window.google && google.maps && typeof mapa.getCenter === 'function') {
                const centro = mapa.getCenter();
                return {provider: 'google', lat: centro.lat(), lng: centro.lng(), zoom: mapa.getZoom()};
            }
            return null;
        }

        function restaurarViewportMapa(viewport) {
            if (!viewport || !mapa) return;
            setTimeout(() => {
                if (MAP_PROVIDER === 'openstreet' && window.L && typeof mapa.setView === 'function') {
                    mapa.setView([viewport.lat, viewport.lng], viewport.zoom, {animate: false});
                } else if (window.google && google.maps) {
                    mapa.setCenter({lat: viewport.lat, lng: viewport.lng});
                    mapa.setZoom(viewport.zoom);
                }
            }, 60);
        }

        function bloquearWheelMapa(elemento) {
            if (!elemento || elemento._ctoWheelBloqueado) return;
            elemento._ctoWheelBloqueado = true;
            ['wheel', 'mousewheel', 'DOMMouseScroll'].forEach(evento => {
                elemento.addEventListener(evento, ev => {
                    ev.stopPropagation();
                }, {passive: false});
            });
        }

        function removerOverlayMapa(obj) {
            if (!obj) return;
            if (Array.isArray(obj)) {
                obj.forEach(removerOverlayMapa);
            } else if (typeof obj.setMap === 'function') {
                obj.setMap(null);
            } else if (typeof obj.remove === 'function') {
                obj.remove();
            }
        }

        function distanciaMetrosEntre(lat1, lng1, lat2, lng2) {
            lat1 = parseFloat(lat1); lng1 = parseFloat(lng1); lat2 = parseFloat(lat2); lng2 = parseFloat(lng2);
            if ([lat1, lng1, lat2, lng2].some(v => isNaN(v))) return null;
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function distanciaClienteCto(cliente, cto) {
            if (!clienteTemCoordenada(cliente) || !cto) return null;
            return distanciaMetrosEntre(cliente.latitude, cliente.longitude, cto.latitude, cto.longitude);
        }

        function formatarDistancia(metros) {
            if (metros === null || isNaN(metros)) return '';
            return metros >= 1000 ? (metros / 1000).toFixed(metros >= 10000 ? 1 : 2).replace('.', ',') + ' km' : Math.round(metros) + ' m';
        }

        function limparLinhasClienteCto() {
            removerOverlayMapa(linhaPreviewAtrelamento);
            removerOverlayMapa(linhaRotaAtrelamento);
            linhaPreviewAtrelamento = null;
            linhaRotaAtrelamento = null;
        }

        function clientesFiltradosDaCto(cto, filtro) {
            const clientes = Array.isArray(cto && cto.clientes) ? cto.clientes : [];
            if (filtro === 'online') return clientes.filter(c => String(c.status || '').toLowerCase() === 'online');
            if (filtro === 'offline') return clientes.filter(c => String(c.status || '').toLowerCase() !== 'online');
            return clientes;
        }

        function ajustarViewportCtoComClientes(cto, filtro) {
            if (!cto || !mapa) return;
            const pontos = [];
            const latCto = parseFloat(cto.latitude);
            const lngCto = parseFloat(cto.longitude);
            if (!isNaN(latCto) && !isNaN(lngCto)) pontos.push({lat: latCto, lng: lngCto});
            clientesFiltradosDaCto(cto, filtro || filtroClientesCtoAtual)
                .filter(clienteTemCoordenada)
                .forEach(c => {
                    const lat = parseFloat(c.latitude);
                    const lng = parseFloat(c.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) pontos.push({lat, lng});
                });
            if (!pontos.length) return;
            setTimeout(() => {
                if (MAP_PROVIDER === 'openstreet' && window.L) {
                    if (pontos.length === 1) {
                        mapa.setView([pontos[0].lat, pontos[0].lng], Math.min(Math.max(mapa.getZoom ? mapa.getZoom() : 15, 15), 16), {animate: true});
                    } else {
                        mapa.fitBounds(L.latLngBounds(pontos.map(p => [p.lat, p.lng])), {
                            paddingTopLeft: [40, 60],
                            paddingBottomRight: [360, 80],
                            maxZoom: 15
                        });
                    }
                } else if (window.google && google.maps) {
                    if (pontos.length === 1) {
                        mapa.panTo(pontos[0]);
                        if (!mapa.getZoom || mapa.getZoom() < 14 || mapa.getZoom() > 16) mapa.setZoom(15);
                    } else {
                        const bounds = new google.maps.LatLngBounds();
                        pontos.forEach(p => bounds.extend(p));
                        mapa.fitBounds(bounds, 80);
                        setTimeout(() => { if (mapa.getZoom && mapa.getZoom() > 15) mapa.setZoom(15); }, 120);
                    }
                }
            }, 120);
        }

        function capturarEstadoVisual() {
            return {
                filtroAtual,
                clientesFixosAtivos,
                todosClientesFixosAtivos,
                filtroTodosClientesAtual,
                filtroClientesCtoAtual,
                ctoUnicaVisivelId,
                ctoSelecionadaId: ctoSelecionadaAtual ? String(ctoSelecionadaAtual.id) : null,
                viewport: capturarViewportMapa()
            };
        }

        function restaurarEstadoVisual(estado) {
            if (!estado) return;
            filtroAtual = estado.filtroAtual || 'todos';
            clientesFixosAtivos = !!estado.clientesFixosAtivos;
            todosClientesFixosAtivos = !!estado.todosClientesFixosAtivos;
            filtroTodosClientesAtual = estado.filtroTodosClientesAtual || 'todos';
            filtroClientesCtoAtual = estado.filtroClientesCtoAtual || 'total';
            ctoUnicaVisivelId = estado.ctoUnicaVisivelId || null;
            ctoSelecionadaAtual = estado.ctoSelecionadaId ? ctosData.find(item => String(item.id) === String(estado.ctoSelecionadaId)) || null : null;
            limparMarcadoresClientesHover();
            adicionarMarcadores(false);
            if (todosClientesFixosAtivos) {
                desenharTodosClientesNoMapa(false);
            } else if (clientesFixosAtivos && ctoSelecionadaAtual) {
                mostrarClientesCtoNoMapa(ctoSelecionadaAtual, true);
                abrirPainelCto(montarPainelCto(ctoSelecionadaAtual));
            }
            atualizarBotaoClientes();
            restaurarViewportMapa(estado.viewport);
        }

        function numeroDaCto(nome) {
            const match = String(nome || '').match(/\d+/);
            return match ? match[0] : '';
        }

        function encontrarCtoDoCliente(cliente) {
            const ctoAtual = String((cliente && cliente.caixa_herm) || '').trim().toLowerCase();
            if (!ctoAtual) return null;
            return (Array.isArray(ctosData) ? ctosData : []).find(cto => {
                const nome = String(cto.nome || '').trim().toLowerCase();
                return nome === ctoAtual || String(cto.id) === ctoAtual;
            }) || null;
        }

        function abrirDetalheCliente(cliente) {
            clienteDetalheAtual = cliente || null;
            const mapElement = document.getElementById('map');
            if (!mapElement) return;
            const ctoDaRota = ctoSelecionadaAtual || encontrarCtoDoCliente(cliente);
            if (ctoDaRota && clienteTemCoordenada(cliente)) {
                desenharPreviewAtrelamento(cliente, ctoDaRota);
            }
            let box = document.getElementById('ctoClientDetail');
            if (!box) {
                box = document.createElement('div');
                box.id = 'ctoClientDetail';
                box.className = 'cto-client-detail';
                mapElement.appendChild(box);
            }
            const textoAcao = cliente.caixa_herm ? 'Alterar CTO/porta' : 'Atrelar a uma CTO';
            const textoVincularCtoAtual = ctoSelecionadaAtual
                ? (cliente.caixa_herm ? 'Alterar para ' : 'Vincular a ') + (ctoSelecionadaAtual.nome || 'CTO')
                : '';
            box.innerHTML = `
                <button type="button" class="cto-panel-close" style="top:8px;right:8px;width:28px;height:28px;font-size:20px;" onclick="fecharDetalheCliente()">x</button>
                <h4 class="${clienteDesativado(cliente) ? 'inactive' : ''}">${escapeHtml(nomeClienteComStatus(cliente))}</h4>
                <p><strong>Login:</strong> ${escapeHtml(cliente.login || '-')}</p>
                <p><strong>Porta:</strong> ${escapeHtml(cliente.porta || '-')}</p>
                <p><strong>CTO atual:</strong> ${escapeHtml(cliente.caixa_herm || '-')}</p>
                <p><strong>Status:</strong> ${escapeHtml(statusClienteLabel(cliente))}</p>
                <p><strong>Tipo:</strong> ${escapeHtml(cliente.tipo || 'Cliente')}</p>
                <p><strong>Coordenadas:</strong> ${escapeHtml(cliente.latitude || '-')} / ${escapeHtml(cliente.longitude || '-')}</p>
                ${ctoSelecionadaAtual ? `<button type="button" class="cto-small-btn save" style="margin-top:10px;width:100%;" onclick="vincularClienteNaCtoSelecionada()">${escapeHtml(textoVincularCtoAtual)}</button>` : ''}
                <button type="button" class="cto-small-btn cancel" style="margin-top:10px;width:100%;" onclick="iniciarAjusteLocalizacaoCliente()">Ajustar localizacao do cliente</button>
                <button type="button" class="cto-small-btn save" style="margin-top:10px;width:100%;" onclick="iniciarAtrelamentoCliente()">${textoAcao}</button>
            `;
            bloquearWheelMapa(box);
        }

        function abrirClienteNoMapa(cliente) {
            const cto = encontrarCtoDoCliente(cliente);
            if (cto && (clientesFixosAtivos || todosClientesFixosAtivos)) {
                fixarClientesCto(cto.id);
            }
            abrirDetalheCliente(cliente);
        }

        function fecharDetalheCliente() {
            const detalhe = document.getElementById('ctoClientDetail');
            if (detalhe) detalhe.remove();
            clienteDetalheAtual = null;
        }

        function limparPreviewAtrelamento() {
            limparLinhasClienteCto();
            ctoDestinoAtrelamento = null;
            portaSelecionadaAtrelamento = '';
            modoAtrelarCliente = null;
            modoClicarCtoAtrelamento = false;
            limparDestaqueCtoLista();
            const modal = document.getElementById('ctoLinkConfirm');
            if (modal) modal.remove();
            const lista = document.getElementById('ctoLinkList');
            if (lista) lista.remove();
            const escolha = document.getElementById('ctoLinkChoice');
            if (escolha) escolha.remove();
        }

        function cancelarAtrelamentoCliente(restaurar) {
            limparPreviewAtrelamento();
            limparMarcadorClienteSelecionado();
            fecharDetalheCliente();
            if (restaurar !== false && estadoMapaAntesAcao) {
                const estado = estadoMapaAntesAcao;
                estadoMapaAntesAcao = null;
                restaurarEstadoVisual(estado);
            } else {
                atualizarBotaoClientes();
            }
        }

        function iniciarAtrelamentoCliente() {
            if (!clienteDetalheAtual || !clienteDetalheAtual.id) {
                mostrarAvisoMapa('Selecione um cliente antes de atrelar.');
                return;
            }
            if (!estadoMapaAntesAcao) estadoMapaAntesAcao = capturarEstadoVisual();
            modoAtrelarCliente = clienteDetalheAtual;
            limparPreviewAtrelamento();
            modoAtrelarCliente = clienteDetalheAtual;
            fecharDetalheCliente();
            destacarClienteSelecionado(modoAtrelarCliente);
            abrirEscolhaMetodoAtrelamento();
            mostrarAvisoMapa('Escolha se deseja listar as CTOs ou clicar diretamente em uma CTO no mapa.');
        }

        function vincularClienteNaCtoSelecionada() {
            if (!clienteDetalheAtual || !ctoSelecionadaAtual) return;
            modoAtrelarCliente = clienteDetalheAtual;
            selecionarCtoDestinoAtrelamento(ctoSelecionadaAtual);
        }

        function selecionarCtoDestinoAtrelamento(cto) {
            if (!modoAtrelarCliente || !modoAtrelarCliente.id || !cto) return;
            const lista = document.getElementById('ctoLinkList');
            if (lista) lista.remove();
            const escolha = document.getElementById('ctoLinkChoice');
            if (escolha) escolha.remove();
            if (typeof cto.closePopup === 'function') cto.closePopup();
            ctoDestinoAtrelamento = cto;
            portaSelecionadaAtrelamento = '';
            modoClicarCtoAtrelamento = false;
            desenharPreviewAtrelamento(modoAtrelarCliente, cto);
            abrirConfirmacaoAtrelamento(cto);
        }

        function desenharPreviewAtrelamento(cliente, cto) {
            if (!clienteTemCoordenada(cliente) || !mapa) {
                mostrarAvisoMapa('Cliente sem coordenada para desenhar a linha.');
                return;
            }
            if (!cto || isNaN(parseFloat(cto.latitude)) || isNaN(parseFloat(cto.longitude))) {
                mostrarAvisoMapa('CTO sem coordenada para desenhar a linha.');
                return;
            }
            removerOverlayMapa(linhaPreviewAtrelamento);
            removerOverlayMapa(linhaRotaAtrelamento);
            linhaRotaAtrelamento = null;
            const latCliente = parseFloat(cliente.latitude);
            const lngCliente = parseFloat(cliente.longitude);
            const latCto = parseFloat(cto.latitude);
            const lngCto = parseFloat(cto.longitude);
            if (MAP_PROVIDER === 'openstreet' && window.L) {
                linhaPreviewAtrelamento = L.polyline([[latCliente, lngCliente], [latCto, lngCto]], {
                    color: '#f59e0b',
                    weight: 4,
                    opacity: 0.95,
                    dashArray: '8 8'
                }).addTo(mapa);
            } else if (window.google && google.maps) {
                linhaPreviewAtrelamento = new google.maps.Polyline({
                    path: [{lat: latCliente, lng: lngCliente}, {lat: latCto, lng: lngCto}],
                    geodesic: true,
                    strokeColor: '#f59e0b',
                    strokeOpacity: 0.95,
                    strokeWeight: 4,
                    map: mapa
                });
            }
            desenharRotaAtrelamento(cliente, cto);
        }

        function desenharRotaAtrelamento(cliente, cto) {
            removerOverlayMapa(linhaRotaAtrelamento);
            linhaRotaAtrelamento = null;
            if (!clienteTemCoordenada(cliente) || !cto || !mapa) return;
            const latCliente = parseFloat(cliente.latitude);
            const lngCliente = parseFloat(cliente.longitude);
            const latCto = parseFloat(cto.latitude);
            const lngCto = parseFloat(cto.longitude);
            if ([latCliente, lngCliente, latCto, lngCto].some(v => isNaN(v))) return;
            if (MAP_PROVIDER === 'openstreet' && window.L) {
                const url = 'https://router.project-osrm.org/route/v1/driving/' + lngCliente + ',' + latCliente + ';' + lngCto + ',' + latCto + '?overview=full&geometries=geojson';
                fetch(url).then(resp => resp.json()).then(data => {
                    if (!data || !data.routes || !data.routes.length || !window.L || !mapa) return;
                    removerOverlayMapa(linhaRotaAtrelamento);
                    linhaRotaAtrelamento = L.geoJSON(data.routes[0].geometry, {
                        style: {color: '#2563eb', weight: 4, opacity: 0.72}
                    }).addTo(mapa);
                    const distancia = formatarDistancia(data.routes[0].distance);
                    if (distancia && linhaRotaAtrelamento.bindTooltip) linhaRotaAtrelamento.bindTooltip('Trajeto aproximado: ' + distancia);
                }).catch(() => {});
            } else if (window.google && google.maps && google.maps.DirectionsService) {
                const service = new google.maps.DirectionsService();
                const renderer = new google.maps.DirectionsRenderer({
                    map: mapa,
                    suppressMarkers: true,
                    preserveViewport: true,
                    polylineOptions: {strokeColor: '#2563eb', strokeOpacity: 0.72, strokeWeight: 4}
                });
                linhaRotaAtrelamento = renderer;
                service.route({
                    origin: {lat: latCliente, lng: lngCliente},
                    destination: {lat: latCto, lng: lngCto},
                    travelMode: google.maps.TravelMode.DRIVING
                }, (result, status) => {
                    if (status === 'OK') renderer.setDirections(result);
                    else removerOverlayMapa(renderer);
                });
            }
        }

        function limparMarcadorClienteSelecionado() {
            if (marcadorClienteSelecionado) {
                if (typeof marcadorClienteSelecionado.setMap === 'function') marcadorClienteSelecionado.setMap(null);
                else if (typeof marcadorClienteSelecionado.remove === 'function') marcadorClienteSelecionado.remove();
            }
            marcadorClienteSelecionado = null;
        }

        function destacarClienteSelecionado(cliente) {
            limparMarcadorClienteSelecionado();
            if (!clienteTemCoordenada(cliente) || !mapa) return;
            const lat = parseFloat(cliente.latitude);
            const lng = parseFloat(cliente.longitude);
            if (MAP_PROVIDER === 'openstreet' && window.L) {
                marcadorClienteSelecionado = L.marker([lat, lng], {
                    interactive: false,
                    icon: L.divIcon({
                        className: '',
                        html: '<div class="cto-selected-client-pulse"></div>',
                        iconSize: [34, 34],
                        iconAnchor: [17, 17]
                    })
                }).addTo(mapa);
            } else if (window.google && google.maps) {
                marcadorClienteSelecionado = new google.maps.Marker({
                    position: {lat, lng},
                    map: mapa,
                    clickable: false,
                    zIndex: 999999,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 14,
                        fillColor: '#f59e0b',
                        fillOpacity: 0.18,
                        strokeColor: '#f59e0b',
                        strokeWeight: 3
                    }
                });
            }
        }

        function ajustarZoomAtrelamento(cliente) {
            if (!clienteTemCoordenada(cliente) || !mapa) return;
            const lat = parseFloat(cliente.latitude);
            const lng = parseFloat(cliente.longitude);
            if (MAP_PROVIDER === 'openstreet' && window.L) {
                const zoomAtual = mapa.getZoom ? mapa.getZoom() : 15;
                mapa.setView([lat, lng], Math.max(14, Math.min(zoomAtual || 15, 16)), {animate: true});
            } else if (window.google && google.maps) {
                mapa.panTo({lat, lng});
                if (mapa.getZoom() < 14 || mapa.getZoom() > 16) mapa.setZoom(15);
            }
        }

        function portasLivresDaCto(cto, clienteAtual) {
            const capacidade = parseInt(cto.capacidade, 10) || 0;
            const usadas = {};
            (Array.isArray(cto.clientes) ? cto.clientes : []).forEach(cliente => {
                if (clienteAtual && String(cliente.id) === String(clienteAtual.id) && String(cliente.tipo || 'Cliente') === String(clienteAtual.tipo || 'Cliente')) return;
                const porta = parseInt(String(cliente.porta || cliente.porta_splitter || '').trim(), 10);
                if (!isNaN(porta) && porta > 0) usadas[porta] = true;
            });
            const livres = [];
            for (let i = 1; i <= capacidade; i++) {
                if (!usadas[i]) livres.push(String(i).padStart(2, '0'));
            }
            return livres;
        }

        function portasOcupadasDaCto(cto, clienteAtual) {
            const usadas = {};
            (Array.isArray(cto.clientes) ? cto.clientes : []).forEach(cliente => {
                const porta = parseInt(String(cliente.porta || cliente.porta_splitter || '').trim(), 10);
                if (!isNaN(porta) && porta > 0) usadas[porta] = cliente;
            });
            return usadas;
        }

        function montarGradePortasAtrelamento(cto) {
            const capacidade = parseInt(cto.capacidade, 10) || 0;
            if (!capacidade) return '<div class="cto-empty">Capacidade nao informada</div>';
            const usadas = portasOcupadasDaCto(cto, modoAtrelarCliente);
            let html = '<div class="cto-port-select-grid">';
            for (let i = 1; i <= capacidade; i++) {
                const porta = String(i).padStart(2, '0');
                const clientePorta = usadas[i];
                const isAtual = modoAtrelarCliente && clientePorta && String(clientePorta.id) === String(modoAtrelarCliente.id) && String(clientePorta.tipo || 'Cliente') === String(modoAtrelarCliente.tipo || 'Cliente');
                if (clientePorta && !isAtual) {
                    html += `<button type="button" class="cto-port-select used ${clienteDesativado(clientePorta) ? 'inactive' : ''}" disabled title="${escapeHtml(nomeClienteComStatus(clientePorta))}"><strong>${porta}</strong><br>Uso</button>`;
                } else {
                    html += `<button type="button" class="cto-port-select ${isAtual ? 'current' : ''}" data-porta="${porta}" onclick="selecionarPortaAtrelamento('${porta}')" title="${isAtual ? 'Porta atual do cliente' : 'Porta livre'}"><strong>${porta}</strong><br>${isAtual ? 'Atual' : 'Livre'}</button>`;
                }
            }
            html += '</div>';
            return html;
        }

        function selecionarPortaAtrelamento(porta) {
            portaSelecionadaAtrelamento = porta || '';
            document.querySelectorAll('#ctoLinkConfirm .cto-port-select').forEach(botao => {
                botao.classList.toggle('selected', botao.getAttribute('data-porta') === portaSelecionadaAtrelamento);
                if (botao.getAttribute('data-porta') === portaSelecionadaAtrelamento) {
                    botao.innerHTML = '<strong>' + portaSelecionadaAtrelamento + '</strong><br>Selecionada';
                } else if (!botao.classList.contains('used')) {
                    const numeroPorta = botao.getAttribute('data-porta') || '';
                    const atual = botao.classList.contains('current');
                    botao.innerHTML = '<strong>' + numeroPorta + '</strong><br>' + (atual ? 'Atual' : 'Livre');
                }
            });
            const note = document.getElementById('portaSelecionadaAtrelamentoInfo');
            if (note) {
                note.innerHTML = portaSelecionadaAtrelamento ? 'Porta selecionada: ' + portaSelecionadaAtrelamento : '';
                note.style.display = portaSelecionadaAtrelamento ? 'block' : 'none';
            }
            const gravar = document.getElementById('btnGravarAtrelamentoCliente');
            if (gravar) gravar.disabled = !portaSelecionadaAtrelamento;
        }

        function selecionarCtoDestinoAtrelamentoPorId(id) {
            const cto = ctosData.find(item => String(item.id) === String(id));
            if (cto) selecionarCtoDestinoAtrelamento(cto);
        }

        function prepararMapaParaEscolherCto() {
            const viewport = capturarViewportMapa();
            clientesFixosAtivos = true;
            todosClientesFixosAtivos = false;
            ctoUnicaVisivelId = null;
            ctoSelecionadaAtual = null;
            fecharPainelCto(false);
            limparMarcadoresClientesHover();
            adicionarMarcadores(false);
            destacarClienteSelecionado(modoAtrelarCliente);
            atualizarBotaoClientes();
            restaurarViewportMapa(viewport);
        }

        function abrirEscolhaMetodoAtrelamento() {
            const mapElement = document.getElementById('map');
            if (!mapElement || !modoAtrelarCliente) return;
            let escolha = document.getElementById('ctoLinkChoice');
            if (escolha) escolha.remove();
            escolha = document.createElement('div');
            escolha.id = 'ctoLinkChoice';
            escolha.className = 'cto-client-detail';
            escolha.style.left = '12px';
            escolha.style.top = '12px';
            escolha.innerHTML = `
                <button type="button" class="cto-panel-close" style="top:8px;right:8px;width:28px;height:28px;font-size:20px;" onclick="cancelarAtrelamentoCliente(true)">x</button>
                <h4>Como deseja atrelar?</h4>
                <p><strong>Cliente:</strong> ${escapeHtml(primeiroUltimoNome(modoAtrelarCliente.nome || 'Cliente'))}</p>
                <p><strong>CTO atual:</strong> ${escapeHtml(modoAtrelarCliente.caixa_herm || 'Sem CTO')}</p>
                <p><strong>Porta atual:</strong> ${escapeHtml(modoAtrelarCliente.porta || '-')}</p>
                <div class="cto-link-choice">
                    <button type="button" class="cto-small-btn save" onclick="abrirListaCtosAtrelamento()">Listar CTO</button>
                    <button type="button" class="cto-small-btn save" onclick="ativarCliqueCtoAtrelamento()">Clicar em CTO</button>
                </div>
            `;
            mapElement.appendChild(escolha);
            bloquearWheelMapa(escolha);
        }

        function ativarCliqueCtoAtrelamento() {
            const escolha = document.getElementById('ctoLinkChoice');
            if (escolha) escolha.remove();
            modoClicarCtoAtrelamento = true;
            prepararMapaParaEscolherCto();
            mostrarAvisoMapa('Clique na CTO destino. O mapa vai ficar no mesmo zoom.');
        }

        function abrirListaCtosAtrelamento() {
            const mapElement = document.getElementById('map');
            if (!mapElement || !modoAtrelarCliente) return;
            const escolha = document.getElementById('ctoLinkChoice');
            if (escolha) escolha.remove();
            modoClicarCtoAtrelamento = false;
            prepararMapaParaEscolherCto();
            let lista = document.getElementById('ctoLinkList');
            if (lista) lista.remove();
            lista = document.createElement('div');
            lista.id = 'ctoLinkList';
            lista.className = 'cto-client-detail';
            lista.style.left = '12px';
            lista.style.top = '12px';
            lista.innerHTML = `
                <button type="button" class="cto-panel-close" style="top:8px;right:8px;width:28px;height:28px;font-size:20px;" onclick="cancelarAtrelamentoCliente(true)">x</button>
                <h4>Escolher CTO destino</h4>
                <p><strong>Cliente:</strong> ${escapeHtml(primeiroUltimoNome(modoAtrelarCliente.nome || 'Cliente'))}</p>
                <p><strong>CTO atual:</strong> ${escapeHtml(modoAtrelarCliente.caixa_herm || 'Sem CTO')}</p>
                <p><strong>Porta atual:</strong> ${escapeHtml(modoAtrelarCliente.porta || '-')}</p>
                <input class="cto-link-search" id="ctoLinkSearch" placeholder="Buscar CTO, endereco ou OLT..." autocomplete="off">
                <div class="cto-link-list" id="ctoLinkRows"></div>
            `;
            mapElement.appendChild(lista);
            bloquearWheelMapa(lista);
            renderizarListaCtosAtrelamento('');
            const busca = document.getElementById('ctoLinkSearch');
            if (busca) busca.addEventListener('input', () => renderizarListaCtosAtrelamento(busca.value));
        }

        function renderizarListaCtosAtrelamento(termo) {
            const alvo = document.getElementById('ctoLinkRows');
            if (!alvo) return;
            const q = String(termo || '').trim().toLowerCase();
            const itens = ctosData
                .filter(cto => !isNaN(parseFloat(cto.latitude)) && !isNaN(parseFloat(cto.longitude)))
                .filter(cto => {
                    if (!q) return true;
                    return [cto.nome, cto.endereco, cto.olt, cto.tipo].some(v => String(v || '').toLowerCase().includes(q));
                })
                .map(cto => Object.assign({}, cto, {_distanciaCliente: distanciaClienteCto(modoAtrelarCliente, cto)}))
                .sort((a, b) => {
                    const da = a._distanciaCliente;
                    const db = b._distanciaCliente;
                    if (da === null && db === null) return String(a.nome || '').localeCompare(String(b.nome || ''));
                    if (da === null) return 1;
                    if (db === null) return -1;
                    return da - db;
                })
                .slice(0, 80);
            alvo.innerHTML = itens.map(cto => `
                <button type="button" class="cto-link-row" onmouseenter="destacarCtoLista('${String(cto.id)}')" onmouseleave="limparDestaqueCtoLista()" onclick="selecionarCtoDestinoAtrelamentoPorId('${String(cto.id)}')">
                    <strong>${escapeHtml(cto.nome || 'CTO')}</strong>
                    <span>${escapeHtml(cto.endereco || 'Sem endereco')}</span>
                    <span>${escapeHtml((cto.portas_livres || 0) + ' livres | ' + (cto.total_clientes || 0) + ' clientes')}</span>
                    ${cto._distanciaCliente !== null ? `<span><strong>Distancia:</strong> ${escapeHtml(formatarDistancia(cto._distanciaCliente))}</span>` : ''}
                </button>
            `).join('') || '<div class="cto-empty">Nenhuma CTO encontrada</div>';
            bloquearWheelMapa(alvo);
        }

        function limparDestaqueCtoLista() {
            removerOverlayMapa(marcadorCtoDestaqueLista);
            marcadorCtoDestaqueLista = null;
            if (!ctoDestinoAtrelamento) {
                removerOverlayMapa(linhaPreviewAtrelamento);
                removerOverlayMapa(linhaRotaAtrelamento);
                linhaPreviewAtrelamento = null;
                linhaRotaAtrelamento = null;
            }
        }

        function destacarCtoLista(id) {
            limparDestaqueCtoLista();
            const cto = ctosData.find(item => String(item.id) === String(id));
            if (!cto || !mapa) return;
            const lat = parseFloat(cto.latitude);
            const lng = parseFloat(cto.longitude);
            if (isNaN(lat) || isNaN(lng)) return;
            const svg = markerSvgComNumero('#f59e0b', cto.nome);
            if (MAP_PROVIDER === 'openstreet' && window.L) {
                marcadorCtoDestaqueLista = L.marker([lat, lng], {
                    interactive: false,
                    icon: L.divIcon({
                        className: '',
                        html: '<div class="cto-highlight-marker">' + svg + '</div>',
                        iconSize: [54, 46],
                        iconAnchor: [27, 43]
                    })
                }).addTo(mapa);
            } else if (window.google && google.maps) {
                marcadorCtoDestaqueLista = new google.maps.Marker({
                    position: {lat, lng},
                    map: mapa,
                    clickable: false,
                    zIndex: 999998,
                    icon: {
                        url: 'data:image/svg+xml;base64,' + btoa(svg),
                        scaledSize: new google.maps.Size(54, 46),
                        anchor: new google.maps.Point(27, 43)
                    }
                });
            }
            if (modoAtrelarCliente) desenharPreviewAtrelamento(modoAtrelarCliente, cto);
        }

        function abrirConfirmacaoAtrelamento(cto) {
            const mapElement = document.getElementById('map');
            if (!mapElement || !modoAtrelarCliente) return;
            let modal = document.getElementById('ctoLinkConfirm');
            if (modal) modal.remove();
            modal = document.createElement('div');
            modal.id = 'ctoLinkConfirm';
            modal.className = 'cto-client-detail';
            modal.style.left = '360px';
            modal.style.top = '12px';
            modal.innerHTML = `
                <button type="button" class="cto-panel-close" style="top:8px;right:8px;width:28px;height:28px;font-size:20px;" onclick="cancelarAtrelamentoCliente(true)">x</button>
                <h4>${escapeHtml(modoAtrelarCliente.caixa_herm ? 'Alterar cliente de CTO' : 'Atrelar cliente a CTO')}</h4>
                <div style="background:#f8fafc;border:1px solid #dbeafe;border-radius:8px;padding:9px;margin:8px 0 10px;">
                    <p><strong>Cliente:</strong> ${escapeHtml(modoAtrelarCliente.nome || 'Cliente')}</p>
                    <p><strong>Login:</strong> ${escapeHtml(modoAtrelarCliente.login || '-')}</p>
                    <p><strong>CTO atual:</strong> ${escapeHtml(modoAtrelarCliente.caixa_herm || 'Sem CTO')}</p>
                    <p><strong>Porta atual:</strong> ${escapeHtml(modoAtrelarCliente.porta || '-')}</p>
                </div>
                <p><strong>Nova CTO:</strong> ${escapeHtml(cto.nome || 'CTO')}</p>
                <p><strong>Portas:</strong> selecione uma porta livre e depois clique em Gravar.</p>
                ${montarGradePortasAtrelamento(cto)}
                <div id="portaSelecionadaAtrelamentoInfo" class="cto-selected-port-note" style="display:${portaSelecionadaAtrelamento ? 'block' : 'none'};">${portaSelecionadaAtrelamento ? 'Porta selecionada: ' + escapeHtml(portaSelecionadaAtrelamento) : ''}</div>
                <div class="cto-link-choice">
                    <button type="button" class="cto-small-btn cancel" onclick="cancelarAtrelamentoCliente(true)">Cancelar</button>
                    <button type="button" class="cto-small-btn save" id="btnGravarAtrelamentoCliente" onclick="confirmarAtrelamentoCliente()" ${portaSelecionadaAtrelamento ? '' : 'disabled'}>Gravar</button>
                </div>
            `;
            mapElement.appendChild(modal);
            bloquearWheelMapa(modal);
        }

        function confirmarAtrelamentoCliente(portaInformada) {
            if (!modoAtrelarCliente || !modoAtrelarCliente.id || !ctoDestinoAtrelamento) return;
            const cliente = modoAtrelarCliente;
            const porta = portaInformada || portaSelecionadaAtrelamento || (document.getElementById('portaDestinoAtrelamento') || {}).value || '';
            if (!porta) {
                alert('Escolha uma porta livre antes de gravar.');
                return;
            }
            enviarAcaoMapa({
                acao_mapa_cto: 'atribuir_cliente_cto',
                cliente_id: cliente.id,
                cliente_tipo: cliente.tipo || 'Cliente',
                cto_id: ctoDestinoAtrelamento.id,
                porta: porta
            }).then(ret => {
                if (!ret || !ret.ok) throw new Error((ret && ret.message) || 'Erro ao atrelar cliente.');
                mostrarAvisoMapa('Cliente atrelado a ' + (ctoDestinoAtrelamento.nome || 'CTO') + ' na porta ' + porta + '.');
                window.location.reload();
            }).catch(err => alert(err.message || 'Erro ao atrelar cliente.'));
        }

        function montarResumoClientesHover(cto) {
            const clientes = Array.isArray(cto.clientes) ? cto.clientes : [];
            if (!clientes.length) {
                return '<div class="cto-hover-empty">Nenhum cliente atribuido</div>';
            }
            const rows = clientes.map(cliente => {
                const statusClass = clienteDesativado(cliente) ? 'inactive' : (cliente.status === 'online' ? 'online' : 'offline');
                const tipo = cliente.tipo ? `<span class="cto-hover-type">${escapeHtml(cliente.tipo)}</span>` : '';
                return `<div class="cto-hover-client ${statusClass}">
                    <span class="cto-hover-dot"></span>
                    <span class="cto-hover-name ${clienteDesativado(cliente) ? 'inactive' : ''}">${escapeHtml(nomeClienteComStatus(cliente))}</span>
                    <span class="cto-hover-login">${escapeHtml(cliente.login)}</span>
                    ${tipo}
                </div>`;
            }).join('');
            return `<div class="cto-hover-clients">${rows}</div>`;
        }

        function montarPortasCto(cto) {
            const capacidade = parseInt(cto.capacidade, 10) || 0;
            const clientes = Array.isArray(cto.clientes) ? cto.clientes : [];
            if (!capacidade) return '<div class="cto-empty">Capacidade nao informada</div>';

            const portasUsadas = {};
            clientes.forEach((cliente, index) => {
                const porta = String(cliente.porta || cliente.porta_splitter || '').trim() || String(index + 1).padStart(2, '0');
                portasUsadas[parseInt(porta, 10)] = cliente;
            });

            let html = '<div class="cto-port-grid">';
            for (let i = 1; i <= capacidade; i++) {
                const cliente = portasUsadas[i];
                const porta = String(i).padStart(2, '0');
                html += `<div class="cto-port-pill ${cliente ? 'used' : ''} ${cliente && clienteDesativado(cliente) ? 'inactive' : ''}" title="${cliente ? escapeHtml(nomeClienteComStatus(cliente)) : 'Porta livre'}">${porta}${cliente ? '<br>Uso' : '<br>Livre'}</div>`;
            }
            html += '</div>';
            return html;
        }

        function clienteTemCoordenada(cliente) {
            const lat = parseFloat(cliente.latitude);
            const lng = parseFloat(cliente.longitude);
            return !isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0;
        }

        function corClienteMapa(cliente) {
            if (clienteDesativado(cliente)) return '#9ca3af';
            return cliente.status === 'online' ? '#2563eb' : '#ef4444';
        }

        function clienteCasaSvg(cor) {
            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28" width="28" height="28">
                <path d="M4 13.2 14 4l10 9.2v10.4a1.7 1.7 0 0 1-1.7 1.7H5.7A1.7 1.7 0 0 1 4 23.6V13.2z" fill="${cor}" stroke="white" stroke-width="2"/>
                <path d="M10.5 25.3v-8.1h7v8.1" fill="rgba(15,23,42,.18)" stroke="white" stroke-width="1.5"/>
                <path d="M2.7 14 14 3.4 25.3 14" fill="none" stroke="#111827" stroke-opacity=".45" stroke-width="2.2" stroke-linecap="round"/>
            </svg>`;
        }

        function criarLeafletIconCliente(cliente) {
            return L.divIcon({
                className: 'cto-leaflet-marker',
                html: clienteCasaSvg(corClienteMapa(cliente)),
                iconSize: [28, 28],
                iconAnchor: [14, 26],
                popupAnchor: [0, -26]
            });
        }

        function googleIconCliente(cliente) {
            return {
                url: 'data:image/svg+xml;base64,' + btoa(clienteCasaSvg(corClienteMapa(cliente))),
                scaledSize: new google.maps.Size(28, 28),
                anchor: new google.maps.Point(14, 26)
            };
        }

        function limparMarcadorAjusteCliente() {
            if (marcadorAjusteCliente) {
                if (typeof marcadorAjusteCliente.setMap === 'function') marcadorAjusteCliente.setMap(null);
                else if (typeof marcadorAjusteCliente.remove === 'function') marcadorAjusteCliente.remove();
            }
            marcadorAjusteCliente = null;
            ajusteClientePosicao = null;
            const editor = document.getElementById('ctoClientLocationEditor');
            if (editor) editor.remove();
        }

        function iniciarAjusteLocalizacaoCliente() {
            if (!clienteDetalheAtual || !clienteDetalheAtual.id) {
                mostrarAvisoMapa('Selecione um cliente antes de ajustar a localizacao.');
                return;
            }
            modoAjustarCliente = clienteDetalheAtual;
            bloqueiaAtualizacaoTempoReal = true;
            limparMarcadorAjusteCliente();
            fecharDetalheCliente();
            const temCoordenada = clienteTemCoordenada(modoAjustarCliente);
            if (temCoordenada) {
                criarMarcadorAjusteCliente(parseFloat(modoAjustarCliente.latitude), parseFloat(modoAjustarCliente.longitude));
                mostrarEditorLocalizacaoCliente();
                mostrarAvisoMapa('Arraste a casa do cliente para a posicao correta e clique em salvar.');
            } else {
                mostrarEditorLocalizacaoCliente();
                mostrarAvisoMapa('Clique no mapa para posicionar o cliente e depois arraste se precisar.');
            }
        }

        function criarMarcadorAjusteCliente(lat, lng) {
            ajusteClientePosicao = {lat: parseFloat(lat), lng: parseFloat(lng)};
            if (marcadorAjusteCliente) {
                if (typeof marcadorAjusteCliente.setMap === 'function') marcadorAjusteCliente.setMap(null);
                else if (typeof marcadorAjusteCliente.remove === 'function') marcadorAjusteCliente.remove();
            }
            if (MAP_PROVIDER === 'openstreet' && window.L) {
                marcadorAjusteCliente = L.marker([ajusteClientePosicao.lat, ajusteClientePosicao.lng], {
                    draggable: true,
                    icon: criarLeafletIconCliente(Object.assign({}, modoAjustarCliente || {}, {status: 'online'}))
                }).addTo(mapa);
                marcadorAjusteCliente.bindTooltip('Arraste para ajustar o cliente', {direction: 'top'}).openTooltip();
                marcadorAjusteCliente.on('dragend', event => {
                    const pos = event.target.getLatLng();
                    ajusteClientePosicao = {lat: pos.lat, lng: pos.lng};
                    atualizarEditorLocalizacaoCliente();
                });
            } else if (window.google && google.maps) {
                marcadorAjusteCliente = new google.maps.Marker({
                    position: ajusteClientePosicao,
                    map: mapa,
                    draggable: true,
                    title: 'Arraste para ajustar o cliente',
                    icon: googleIconCliente(Object.assign({}, modoAjustarCliente || {}, {status: 'online'}))
                });
                marcadorAjusteCliente.addListener('dragend', event => {
                    ajusteClientePosicao = {lat: event.latLng.lat(), lng: event.latLng.lng()};
                    atualizarEditorLocalizacaoCliente();
                });
            }
            atualizarEditorLocalizacaoCliente();
        }

        function mostrarEditorLocalizacaoCliente() {
            const mapElement = document.getElementById('map');
            if (!mapElement || !modoAjustarCliente) return;
            let editor = document.getElementById('ctoClientLocationEditor');
            if (!editor) {
                editor = document.createElement('div');
                editor.id = 'ctoClientLocationEditor';
                editor.className = 'cto-client-location-editor';
                mapElement.appendChild(editor);
            }
            editor.innerHTML = `
                <h4>Ajustar localizacao</h4>
                <p><strong>Cliente:</strong> ${escapeHtml(primeiroUltimoNome(modoAjustarCliente.nome || 'Cliente'))}</p>
                <p><strong>Login:</strong> ${escapeHtml(modoAjustarCliente.login || '-')}</p>
                <p id="ctoClientLocationCoords"><strong>Coordenadas:</strong> ${ajusteClientePosicao ? escapeHtml(ajusteClientePosicao.lat.toFixed(6) + ' / ' + ajusteClientePosicao.lng.toFixed(6)) : 'Clique no mapa'}</p>
                <button type="button" class="cto-small-btn save" style="width:100%;margin-top:8px;" onclick="salvarLocalizacaoCliente()" ${ajusteClientePosicao ? '' : 'disabled'}>Salvar localizacao</button>
                <button type="button" class="cto-small-btn cancel" style="width:100%;margin-top:8px;" onclick="cancelarAjusteLocalizacaoCliente()">Cancelar</button>
            `;
            bloquearWheelMapa(editor);
        }

        function atualizarEditorLocalizacaoCliente() {
            const coords = document.getElementById('ctoClientLocationCoords');
            if (coords && ajusteClientePosicao) {
                coords.innerHTML = `<strong>Coordenadas:</strong> ${escapeHtml(ajusteClientePosicao.lat.toFixed(6) + ' / ' + ajusteClientePosicao.lng.toFixed(6))}`;
            }
            const editor = document.getElementById('ctoClientLocationEditor');
            const botao = editor ? editor.querySelector('.cto-small-btn.save') : null;
            if (botao) botao.disabled = !ajusteClientePosicao;
        }

        function cancelarAjusteLocalizacaoCliente() {
            modoAjustarCliente = null;
            bloqueiaAtualizacaoTempoReal = false;
            limparMarcadorAjusteCliente();
        }

        function salvarLocalizacaoCliente() {
            if (!modoAjustarCliente || !ajusteClientePosicao) {
                mostrarAvisoMapa('Posicione o cliente no mapa antes de salvar.');
                return;
            }
            enviarAcaoMapa({
                acao_mapa_cto: 'atualizar_coordenadas_cliente',
                cliente_id: modoAjustarCliente.id,
                cliente_tipo: modoAjustarCliente.tipo || 'Cliente',
                latitude: ajusteClientePosicao.lat,
                longitude: ajusteClientePosicao.lng
            }).then(ret => {
                if (!ret || !ret.ok) throw new Error((ret && ret.message) || 'Erro ao salvar localizacao do cliente.');
                const viewportAtual = capturarViewportMapa();
                const clienteSalvo = modoAjustarCliente;
                const lat = ajusteClientePosicao.lat;
                const lng = ajusteClientePosicao.lng;
                const mesmoCliente = item => item && String(item.id) === String(clienteSalvo.id) && String(item.tipo || 'Cliente') === String(clienteSalvo.tipo || 'Cliente');
                (todosClientesData || []).forEach(item => {
                    if (mesmoCliente(item)) { item.latitude = lat; item.longitude = lng; }
                });
                (ctosData || []).forEach(cto => (cto.clientes || []).forEach(item => {
                    if (mesmoCliente(item)) { item.latitude = lat; item.longitude = lng; }
                }));
                if (clienteDetalheAtual && mesmoCliente(clienteDetalheAtual)) {
                    clienteDetalheAtual.latitude = lat;
                    clienteDetalheAtual.longitude = lng;
                }
                cancelarAjusteLocalizacaoCliente();
                limparMarcadoresClientesHover();
                adicionarMarcadores(false);
                if (todosClientesFixosAtivos) desenharTodosClientesNoMapa(false);
                else if (clientesFixosAtivos && ctoSelecionadaAtual) mostrarClientesCtoNoMapa(ctoSelecionadaAtual, true);
                restaurarViewportMapa(viewportAtual);
                mostrarAvisoMapa('Localizacao do cliente salva.');
            }).catch(err => alert(err.message || 'Erro ao salvar localizacao do cliente.'));
        }

        function mostrarAvisoMapa(texto) {
            const mapElement = document.getElementById('map');
            if (!mapElement) return;
            let toast = document.getElementById('mapToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'mapToast';
                toast.className = 'map-toast';
                mapElement.appendChild(toast);
            }
            toast.textContent = texto;
            toast.style.display = 'block';
            clearTimeout(toast._timer);
            toast._timer = setTimeout(() => { toast.style.display = 'none'; }, 3500);
        }

        function enviarAcaoMapa(dados) {
            const form = new FormData();
            Object.keys(dados).forEach(chave => form.append(chave, dados[chave]));
            return fetch(window.location.href, {
                method: 'POST',
                body: form,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then(resp => resp.json());
        }

        function salvarCoordenadasCto(cto, lat, lng) {
            bloqueiaAtualizacaoTempoReal = true;
            return enviarAcaoMapa({
                acao_mapa_cto: 'atualizar_coordenadas',
                id: cto.id,
                latitude: lat,
                longitude: lng
            }).then(ret => {
                if (!ret || !ret.ok) throw new Error((ret && ret.message) || 'Erro ao salvar coordenadas.');
                cto.latitude = parseFloat(lat);
                cto.longitude = parseFloat(lng);
                mostrarAvisoMapa('Coordenadas salvas para ' + (cto.nome || 'CTO'));
                if (painelCtoConteudoAtual) {
                    if (clientesFixosAtivos && ctoUnicaVisivelId && String(ctoUnicaVisivelId) === String(cto.id)) {
                        mostrarClientesCtoNoMapa(cto, true);
                    }
                    abrirPainelCto(montarPainelCto(cto));
                }
            }).catch(err => {
                alert(err.message || 'Erro ao salvar coordenadas.');
                adicionarMarcadores();
            }).finally(() => {
                bloqueiaAtualizacaoTempoReal = false;
            });
        }

        function limparMarcadorAjusteCto() {
            if (marcadorAjusteCto) {
                if (typeof marcadorAjusteCto.setMap === 'function') marcadorAjusteCto.setMap(null);
                else if (typeof marcadorAjusteCto.remove === 'function') marcadorAjusteCto.remove();
            }
            marcadorAjusteCto = null;
            ajusteCtoPosicao = null;
            const editor = document.getElementById('ctoLocationEditor');
            if (editor) editor.remove();
        }

        function iniciarAjusteLocalizacaoCto(id) {
            const cto = ctosData.find(item => String(item.id) === String(id));
            if (!cto || !mapa) return;
            if (!estadoMapaAntesAcao) estadoMapaAntesAcao = capturarEstadoVisual();
            modoAjustarCto = cto;
            bloqueiaAtualizacaoTempoReal = true;
            const viewport = capturarViewportMapa();
            ctoUnicaVisivelId = null;
            fecharPainelCto();
            adicionarMarcadores(false);
            if (todosClientesFixosAtivos) desenharTodosClientesNoMapa(false);
            criarMarcadorAjusteCto(parseFloat(cto.latitude), parseFloat(cto.longitude));
            mostrarEditorLocalizacaoCto();
            restaurarViewportMapa(viewport);
            mostrarAvisoMapa('Arraste a CTO e clique em salvar. Cancelar volta tudo como estava.');
        }

        function criarMarcadorAjusteCto(lat, lng) {
            limparMarcadorAjusteCto();
            if (isNaN(lat) || isNaN(lng)) return;
            ajusteCtoPosicao = {lat, lng};
            const svg = markerSvgComNumero('#f59e0b', modoAjustarCto ? modoAjustarCto.nome : 'CTO');
            if (MAP_PROVIDER === 'openstreet' && window.L) {
                marcadorAjusteCto = L.marker([lat, lng], {
                    draggable: true,
                    icon: L.divIcon({
                        className: '',
                        html: '<div class="cto-highlight-marker">' + svg + '</div>',
                        iconSize: [54, 46],
                        iconAnchor: [27, 43]
                    })
                }).addTo(mapa);
                marcadorAjusteCto.on('dragend', event => {
                    const pos = event.target.getLatLng();
                    ajusteCtoPosicao = {lat: pos.lat, lng: pos.lng};
                    atualizarEditorLocalizacaoCto();
                });
            } else if (window.google && google.maps) {
                marcadorAjusteCto = new google.maps.Marker({
                    position: {lat, lng},
                    map: mapa,
                    draggable: true,
                    zIndex: 999999,
                    icon: {
                        url: 'data:image/svg+xml;base64,' + btoa(svg),
                        scaledSize: new google.maps.Size(54, 46),
                        anchor: new google.maps.Point(27, 43)
                    }
                });
                marcadorAjusteCto.addListener('dragend', event => {
                    ajusteCtoPosicao = {lat: event.latLng.lat(), lng: event.latLng.lng()};
                    atualizarEditorLocalizacaoCto();
                });
            }
        }

        function mostrarEditorLocalizacaoCto() {
            const mapElement = document.getElementById('map');
            if (!mapElement || !modoAjustarCto) return;
            let editor = document.getElementById('ctoLocationEditor');
            if (!editor) {
                editor = document.createElement('div');
                editor.id = 'ctoLocationEditor';
                editor.className = 'cto-location-editor';
                mapElement.appendChild(editor);
            }
            editor.innerHTML = `
                <h4>Ajustar CTO</h4>
                <p><strong>CTO:</strong> ${escapeHtml(modoAjustarCto.nome || 'CTO')}</p>
                <p id="ctoLocationCoords"><strong>Coordenadas:</strong> ${ajusteCtoPosicao ? escapeHtml(ajusteCtoPosicao.lat.toFixed(6) + ' / ' + ajusteCtoPosicao.lng.toFixed(6)) : '-'}</p>
                <button type="button" class="cto-small-btn save" style="width:100%;margin-top:8px;" onclick="salvarLocalizacaoCto()">Salvar localizacao</button>
                <button type="button" class="cto-small-btn cancel" style="width:100%;margin-top:8px;" onclick="cancelarAjusteLocalizacaoCto(true)">Cancelar</button>
            `;
            bloquearWheelMapa(editor);
        }

        function atualizarEditorLocalizacaoCto() {
            const coords = document.getElementById('ctoLocationCoords');
            if (coords && ajusteCtoPosicao) {
                coords.innerHTML = `<strong>Coordenadas:</strong> ${escapeHtml(ajusteCtoPosicao.lat.toFixed(6) + ' / ' + ajusteCtoPosicao.lng.toFixed(6))}`;
            }
        }

        function cancelarAjusteLocalizacaoCto(restaurar) {
            modoAjustarCto = null;
            bloqueiaAtualizacaoTempoReal = false;
            limparMarcadorAjusteCto();
            if (restaurar !== false && estadoMapaAntesAcao) {
                const estado = estadoMapaAntesAcao;
                estadoMapaAntesAcao = null;
                restaurarEstadoVisual(estado);
            }
        }

        function salvarLocalizacaoCto() {
            if (!modoAjustarCto || !ajusteCtoPosicao) return;
            const cto = modoAjustarCto;
            enviarAcaoMapa({
                acao_mapa_cto: 'atualizar_coordenadas',
                id: cto.id,
                latitude: ajusteCtoPosicao.lat,
                longitude: ajusteCtoPosicao.lng
            }).then(ret => {
                if (!ret || !ret.ok) throw new Error((ret && ret.message) || 'Erro ao salvar coordenadas.');
                cto.latitude = parseFloat(ajusteCtoPosicao.lat);
                cto.longitude = parseFloat(ajusteCtoPosicao.lng);
                mostrarAvisoMapa('Coordenadas salvas para ' + (cto.nome || 'CTO'));
                cancelarAjusteLocalizacaoCto(true);
            }).catch(err => alert(err.message || 'Erro ao salvar coordenadas.'));
        }

        function limparMarcadoresClientesHover() {
            marcadoresClientesHover.forEach(marker => {
                if (marker && typeof marker.setMap === 'function') marker.setMap(null);
                else if (marker && typeof marker.remove === 'function') marker.remove();
            });
            marcadoresClientesHover = [];
        }

        function abrirDetalheClienteDaCto(ctoId, index) {
            const cto = ctosData.find(item => String(item.id) === String(ctoId));
            if (!cto || !Array.isArray(cto.clientes) || !cto.clientes[index]) return;
            ctoSelecionadaAtual = cto;
            const cliente = cto.clientes[index];
            abrirDetalheCliente(cliente);
            if (clienteTemCoordenada(cliente)) desenharPreviewAtrelamento(cliente, cto);
        }

        function mostrarClientesCtoNoMapa(cto, fixar, filtro) {
            if (fixar) {
                clientesFixosAtivos = true;
                todosClientesFixosAtivos = false;
                ctoUnicaVisivelId = String(cto.id);
            }
            const filtroUsado = filtro || filtroClientesCtoAtual || 'total';
            limparMarcadoresClientesHover();
            const clientes = clientesFiltradosDaCto(cto, filtroUsado).filter(clienteTemCoordenada);
            if (!clientes.length || !mapa) return;

            clientes.forEach(cliente => {
                const lat = parseFloat(cliente.latitude);
                const lng = parseFloat(cliente.longitude);
                const titulo = `${cliente.nome || ''} - ${cliente.login || ''}`;
                if (MAP_PROVIDER === 'openstreet' && window.L) {
                    const linha = L.polyline([[parseFloat(cto.latitude), parseFloat(cto.longitude)], [lat, lng]], {
                        color: corClienteMapa(cliente),
                        weight: 2,
                        opacity: 0.75
                    }).addTo(mapa);
                    const marker = L.marker([lat, lng], {icon: criarLeafletIconCliente(cliente)}).addTo(mapa);
                    marker.bindTooltip(escapeHtml(titulo), {direction: 'top'});
                    marker.on('click', () => abrirClienteNoMapa(cliente));
                    marcadoresClientesHover.push(linha);
                    marcadoresClientesHover.push(marker);
                } else if (window.google && google.maps) {
                    const linha = new google.maps.Polyline({
                        path: [
                            {lat: parseFloat(cto.latitude), lng: parseFloat(cto.longitude)},
                            {lat: lat, lng: lng}
                        ],
                        geodesic: true,
                        strokeColor: corClienteMapa(cliente),
                        strokeOpacity: 0.75,
                        strokeWeight: 2,
                        map: mapa
                    });
                    const marker = new google.maps.Marker({
                        position: {lat: lat, lng: lng},
                        map: mapa,
                        title: titulo,
                        icon: googleIconCliente(cliente)
                    });
                    marker.addListener('click', () => abrirClienteNoMapa(cliente));
                    marcadoresClientesHover.push(linha);
                    marcadoresClientesHover.push(marker);
                }
            });
        }

        function clientePassaFiltroTodos(cliente) {
            const temCto = String(cliente.caixa_herm || '').trim() !== '';
            if (filtroTodosClientesAtual === 'semcto') return !temCto;
            if (filtroTodosClientesAtual === 'comcto') return temCto;
            return true;
        }

        function mostrarTodosClientesNoMapa(forcarMostrar, filtro) {
            const deveForcar = forcarMostrar === true;
            if (filtro) filtroTodosClientesAtual = filtro;
            if (todosClientesFixosAtivos && !deveForcar && !filtro) {
                limparClientesMapa();
                return;
            }
            clientesFixosAtivos = true;
            todosClientesFixosAtivos = true;
            ctoUnicaVisivelId = null;
            atualizarBotaoClientes();
            desenharTodosClientesNoMapa(true);
        }

        function desenharTodosClientesNoMapa(mostrarToast) {
            limparMarcadoresClientesHover();
            const clientes = (Array.isArray(todosClientesData) ? todosClientesData : []).filter(clienteTemCoordenada).filter(clientePassaFiltroTodos);
            clientes.forEach(cliente => {
                const lat = parseFloat(cliente.latitude);
                const lng = parseFloat(cliente.longitude);
                const titulo = `${cliente.nome || ''} | CTO: ${cliente.caixa_herm || 'sem CTO'} | Porta: ${cliente.porta || '-'}`;
                if (MAP_PROVIDER === 'openstreet' && window.L) {
                    const marker = L.marker([lat, lng], {icon: criarLeafletIconCliente(cliente)}).addTo(mapa);
                    marker.bindTooltip(escapeHtml(titulo), {direction: 'top'});
                    marker.on('click', () => abrirClienteNoMapa(cliente));
                    marcadoresClientesHover.push(marker);
                } else if (window.google && google.maps) {
                    const marker = new google.maps.Marker({
                        position: {lat, lng},
                        map: mapa,
                        title: titulo,
                        icon: googleIconCliente(cliente)
                    });
                    marker.addListener('click', () => abrirClienteNoMapa(cliente));
                    marcadoresClientesHover.push(marker);
                }
            });
            if (mostrarToast !== false) mostrarAvisoMapa(clientes.length + ' clientes exibidos no mapa.');
        }

        function fixarClientesCto(ctoId, filtro) {
            const cto = ctosData.find(item => String(item.id) === String(ctoId));
            if (!cto) return;
            limparLinhasClienteCto();
            filtroClientesCtoAtual = filtro || filtroClientesCtoAtual || 'total';
            ctoUnicaVisivelId = String(cto.id);
            ctoSelecionadaAtual = cto;
            clientesFixosAtivos = true;
            todosClientesFixosAtivos = false;
            atualizarBotaoClientes();
            adicionarMarcadores();
            mostrarClientesCtoNoMapa(cto, true, filtroClientesCtoAtual);
            abrirPainelCto(montarPainelCto(cto));
            ajustarViewportCtoComClientes(cto, filtroClientesCtoAtual);
            mostrarAvisoMapa('Exibindo somente ' + (cto.nome || 'CTO') + ' e seus clientes.');
        }

        function abrirCtoSelecionadaNoMapa(cto) {
            if (!cto) return;
            if (modoAtrelarCliente) {
                selecionarCtoDestinoAtrelamento(cto);
                return;
            }
            fixarClientesCto(cto.id);
            try {
                destacarRadioCto(cto);
            } catch (erro) {
                if (window.console && console.warn) console.warn('Falha ao destacar vinculo de radio da CTO', erro);
            }
        }

        function encontrarCtoProximaDoClique(latlng, raioPx) {
            if (!mapa || !latlng || typeof mapa.latLngToContainerPoint !== 'function') return null;
            const pontoClique = mapa.latLngToContainerPoint(latlng);
            let melhor = null;
            let melhorDistancia = Number.POSITIVE_INFINITY;
            (Array.isArray(ctosData) ? ctosData : []).forEach(cto => {
                if (!ctoVisivelNoFiltro(cto)) return;
                const lat = parseFloat(cto.latitude);
                const lng = parseFloat(cto.longitude);
                if (isNaN(lat) || isNaN(lng)) return;
                const pontoCto = mapa.latLngToContainerPoint([lat, lng]);
                const dx = pontoClique.x - pontoCto.x;
                const dy = pontoClique.y - pontoCto.y;
                const distancia = Math.sqrt(dx * dx + dy * dy);
                if (distancia < melhorDistancia) {
                    melhorDistancia = distancia;
                    melhor = cto;
                }
            });
            return melhorDistancia <= (raioPx || 44) ? melhor : null;
        }

        function filtrarClientesPainelCto(filtro) {
            if (!ctoSelecionadaAtual) return;
            filtroClientesCtoAtual = filtro || 'total';
            mostrarClientesCtoNoMapa(ctoSelecionadaAtual, true, filtroClientesCtoAtual);
            abrirPainelCto(montarPainelCto(ctoSelecionadaAtual));
            ajustarViewportCtoComClientes(ctoSelecionadaAtual, filtroClientesCtoAtual);
        }

        function limparClientesMapa() {
            clientesFixosAtivos = false;
            todosClientesFixosAtivos = false;
            ctoUnicaVisivelId = null;
            ctoSelecionadaAtual = null;
            modoAtrelarCliente = null;
            limparPreviewAtrelamento();
            limparMarcadorClienteSelecionado();
            cancelarAjusteLocalizacaoCliente();
            cancelarAjusteLocalizacaoCto(false);
            limparMarcadoresClientesHover();
            adicionarMarcadores();
            atualizarBotaoClientes();
            mostrarAvisoMapa('Clientes ocultados.');
        }

        function corMarcadorCto(cto) {
            if (cto.total_clientes === 0) return '#9ca3af';
            if (cto.clientes_online > 0) return '#10b981';
            return '#ef4444';
        }

        function normalizarNomeCto(valor) {
            return String(valor || '').trim().toLowerCase();
        }

        function isRadioTipo(tipo) {
            return ['AP', 'STA', 'TO'].includes(String(tipo || '').toUpperCase());
        }

        function subtituloTipoCto(cto) {
            const tipo = String(cto.tipo || '').toUpperCase();
            if (tipo === 'AP') return 'AP - Access Point Radio';
            if (tipo === 'STA') return 'STA - Station Radio';
            if (tipo === 'TO') return 'TO - Torre de Radio';
            return 'CTO - Caixa de Terminacao Optica';
        }

        function equipamentoLabelCto(cto) {
            return isRadioTipo(cto.tipo) ? 'Painel/Equipamento' : 'OLT';
        }

        function vinculoLabelCto(cto) {
            const tipo = String(cto.tipo || '').toUpperCase();
            if (tipo === 'STA') return 'AP vinculado';
            if (tipo === 'AP') return 'Torre vinculada';
            if (tipo === 'TO') return 'Estrutura/Setor';
            return 'FSP';
        }

        function ctoVisivelNoFiltro(cto) {
            if (ctoUnicaVisivelId && String(cto.id) !== String(ctoUnicaVisivelId)) return false;
            if (filtroAtual === 'comclientes' && cto.total_clientes === 0) return false;
            if (filtroAtual === 'semclientes' && cto.total_clientes > 0) return false;
            return true;
        }

        function montarConteudoHover(cto) {
            return `
                <div class="cto-hover-tooltip">
                    <div class="cto-hover-title">${escapeHtml(cto.nome)}</div>
                    <div class="cto-hover-address">${escapeHtml(cto.endereco || 'Sem endereco')}</div>
                    <div class="cto-hover-counts">${cto.total_clientes} clientes | ${cto.clientes_online} online | ${cto.clientes_offline} offline</div>
                    ${montarResumoClientesHover(cto)}
                </div>
            `;
        }

        function markerSvg(cor) {
            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34 30" width="34" height="30">
                <rect x="4" y="5" width="26" height="18" rx="4" fill="${cor}" stroke="white" stroke-width="2"/>
                <rect x="10" y="23" width="14" height="4" rx="1.5" fill="${cor}" stroke="white" stroke-width="1"/>
                <circle cx="12" cy="14" r="2.2" fill="white"/>
                <circle cx="22" cy="14" r="2.2" fill="white"/>
            </svg>`;
        }

        function markerSvgComNumero(cor, nome) {
            const numero = numeroDaCto(nome);
            const texto = numero ? `<text x="17" y="18" text-anchor="middle" font-family="Arial" font-size="10" font-weight="700" fill="white">${escapeHtml(numero)}</text>` : '<circle cx="17" cy="14" r="4" fill="white"/>';
            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 32" width="38" height="32">
                <rect x="4" y="4" width="30" height="22" rx="5" fill="${cor}" stroke="white" stroke-width="2"/>
                <rect x="12" y="26" width="14" height="4" rx="1.5" fill="${cor}" stroke="white" stroke-width="1"/>
                ${texto}
            </svg>`;
        }

        function limparDestaquesRadio() {
            destaquesRadio.forEach(item => {
                if (item && typeof item.setMap === 'function') item.setMap(null);
                else if (item && typeof item.remove === 'function') item.remove();
            });
            destaquesRadio = [];
        }

        function limparMarcadores() {
            limparDestaquesRadio();
            if (!clientesFixosAtivos) limparMarcadoresClientesHover();
            marcadores.forEach(marker => {
                if (marker && typeof marker.setMap === 'function') marker.setMap(null);
                else if (marker && typeof marker.remove === 'function') marker.remove();
            });
            marcadores = [];
        }

        function limparMarcadorTemporario() {
            if (tempAddMarker) {
                if (typeof tempAddMarker.setMap === 'function') tempAddMarker.setMap(null);
                else if (typeof tempAddMarker.remove === 'function') tempAddMarker.remove();
            }
            tempAddMarker = null;
        }

        function ativarModoAdicionarCto() {
            modoAdicionarCto = true;
            bloqueiaAtualizacaoTempoReal = true;
            fecharPainelCto();
            limparMarcadoresClientesHover();
            mostrarAvisoMapa('Clique no mapa onde a nova CTO deve ficar.');
            const btn = document.getElementById('btnAdicionarCto');
            if (btn) btn.textContent = 'Clique no mapa para posicionar';
        }

        function desativarModoAdicionarCto() {
            modoAdicionarCto = false;
            bloqueiaAtualizacaoTempoReal = false;
            limparMarcadorTemporario();
            const modal = document.getElementById('ctoAddModal');
            if (modal) modal.remove();
            const btn = document.getElementById('btnAdicionarCto');
            if (btn) btn.textContent = 'Adicionar CTO no mapa';
        }

        function setTempMarkerPosition(lat, lng) {
            const latInput = document.getElementById('novaCtoLat');
            const lngInput = document.getElementById('novaCtoLng');
            if (latInput) latInput.value = Number(lat).toFixed(6);
            if (lngInput) lngInput.value = Number(lng).toFixed(6);
        }

        function criarMarcadorTemporario(lat, lng) {
            limparMarcadorTemporario();
            if (MAP_PROVIDER === 'openstreet' && window.L) {
                tempAddMarker = L.marker([lat, lng], {draggable: true}).addTo(mapa);
                tempAddMarker.on('dragend', event => {
                    const pos = event.target.getLatLng();
                    setTempMarkerPosition(pos.lat, pos.lng);
                });
            } else if (window.google && google.maps) {
                tempAddMarker = new google.maps.Marker({
                    position: {lat, lng},
                    map: mapa,
                    draggable: true,
                    title: 'Nova CTO'
                });
                tempAddMarker.addListener('dragend', event => setTempMarkerPosition(event.latLng.lat(), event.latLng.lng()));
            }
        }

        function abrirFormularioNovaCto(lat, lng) {
            modoAdicionarCto = false;
            criarMarcadorTemporario(lat, lng);
            const mapElement = document.getElementById('map');
            if (!mapElement) return;
            let modal = document.getElementById('ctoAddModal');
            if (modal) modal.remove();
            modal = document.createElement('div');
            modal.id = 'ctoAddModal';
            modal.className = 'cto-add-modal';
            modal.innerHTML = `
                <header>
                    <span>Adicionar CTO</span>
                    <button type="button" class="cto-small-btn cancel" onclick="desativarModoAdicionarCto()">x</button>
                </header>
                <div class="body">
                    <input id="novaCtoNome" placeholder="Nome da CTO" autocomplete="off">
                    <textarea id="novaCtoEndereco" placeholder="Endereco ou referencia" rows="2"></textarea>
                    <div class="grid">
                        <select id="novaCtoTipo">
                            <option value="CTO">CTO - Caixa de terminacao optica</option>
                            <option value="FTTH">FTTH - Fibra ate o domicilio</option>
                            <option value="AP">AP - Access Point Radio</option>
                            <option value="STA">STA - Station Radio</option>
                            <option value="TO">TO - Torre de Radio</option>
                        </select>
                        <input id="novaCtoCapacidade" type="number" min="0" value="8" placeholder="Capacidade">
                    </div>
                    <div class="grid">
                        <input id="novaCtoSinal" placeholder="Sinal">
                        <input id="novaCtoOlt" placeholder="OLT / Painel">
                    </div>
                    <input id="novaCtoFsp" placeholder="FSP / AP vinculado">
                    <div class="grid">
                        <input id="novaCtoLat" readonly>
                        <input id="novaCtoLng" readonly>
                    </div>
                </div>
                <footer>
                    <button type="button" class="cto-small-btn cancel" onclick="desativarModoAdicionarCto()">Cancelar</button>
                    <button type="button" class="cto-small-btn save" onclick="salvarNovaCtoMapa()">Gravar CTO</button>
                </footer>
            `;
            mapElement.appendChild(modal);
            setTempMarkerPosition(lat, lng);
            const btn = document.getElementById('btnAdicionarCto');
            if (btn) btn.textContent = 'Adicionar CTO no mapa';
            const nome = document.getElementById('novaCtoNome');
            if (nome) nome.focus();
        }

        function salvarNovaCtoMapa() {
            const nome = (document.getElementById('novaCtoNome') || {}).value || '';
            if (!nome.trim()) {
                alert('Informe o nome da CTO.');
                return;
            }
            enviarAcaoMapa({
                acao_mapa_cto: 'adicionar_cto',
                nome: nome,
                endereco: (document.getElementById('novaCtoEndereco') || {}).value || '',
                tipo: (document.getElementById('novaCtoTipo') || {}).value || 'CTO',
                capacidade: (document.getElementById('novaCtoCapacidade') || {}).value || '8',
                sinal: (document.getElementById('novaCtoSinal') || {}).value || '',
                olt: (document.getElementById('novaCtoOlt') || {}).value || '',
                fsp: (document.getElementById('novaCtoFsp') || {}).value || '',
                latitude: (document.getElementById('novaCtoLat') || {}).value || '',
                longitude: (document.getElementById('novaCtoLng') || {}).value || ''
            }).then(ret => {
                if (!ret || !ret.ok) throw new Error((ret && ret.message) || 'Erro ao cadastrar CTO.');
                mostrarAvisoMapa('CTO cadastrada.');
                window.location.reload();
            }).catch(err => alert(err.message || 'Erro ao cadastrar CTO.'));
        }

        function adicionarMarcadores(ajustarViewport) {
            const deveAjustar = ajustarViewport !== false;
            if (MAP_PROVIDER === 'openstreet') {
                adicionarMarcadoresOpenStreet(deveAjustar);
                return;
            }
            adicionarMarcadoresGoogle(deveAjustar);
        }

        // Inicializar o mapa
        function initializeMap() {
            if (MAP_PROVIDER === 'openstreet') {
                initializeOpenStreetMap();
                return;
            }
            if (!window.google || !window.google.maps) {
                document.getElementById('map').innerHTML = '<div style="padding:20px;color:#b91c1c;font-weight:700">Nao foi possivel carregar o Google Maps. Verifique a chave ou altere o recurso do sistema para OpenStreetMap.</div>';
                return;
            }
            initializeGoogleMap();
        }

        function initializeGoogleMap() {
            // Centro padrao (Brasil)
            const centro = { lat: -10.5, lng: -51.9 };

            mapa = new google.maps.Map(document.getElementById('map'), {
                zoom: 4,
                center: centro,
                mapTypeControl: true,
                mapTypeControlOptions: { mapTypeIds: ['roadmap', 'satellite'] },
                mapTypeId: getCaixasMapMode() === 'satelite' ? google.maps.MapTypeId.SATELLITE : google.maps.MapTypeId.ROADMAP,
                fullscreenControl: true,
                streetViewControl: true,
                scrollwheel: true,
                zoomControl: true,
                gestureHandling: 'auto'
            });

            mapa.addListener('maptypeid_changed', () => setCaixasMapMode(mapa.getMapTypeId() === google.maps.MapTypeId.SATELLITE ? 'satelite' : 'mapa'));
            mapa.addListener('click', event => {
                if (modoAjustarCliente && !marcadorAjusteCliente) {
                    criarMarcadorAjusteCliente(event.latLng.lat(), event.latLng.lng());
                    mostrarAvisoMapa('Agora arraste a casa se precisar e clique em salvar.');
                    return;
                }
                if (modoAdicionarCto) abrirFormularioNovaCto(event.latLng.lat(), event.latLng.lng());
            });

            // Adicionar marcadores para cada CTO
            adicionarMarcadores();

            // Atualizar estatisticas
            atualizarEstatisticas();
        }

        // Adicionar marcadores no mapa
        function criarLeafletIcon(cor) {
            return L.divIcon({
                className: 'cto-leaflet-marker',
                html: markerSvg(cor),
                iconSize: [34, 30],
                iconAnchor: [17, 28],
                popupAnchor: [0, -28]
            });
        }

        function criarLeafletIconCto(cto) {
            return L.divIcon({
                className: 'cto-leaflet-marker',
                html: markerSvgComNumero(corMarcadorCto(cto), cto.nome),
                iconSize: [38, 32],
                iconAnchor: [19, 30],
                popupAnchor: [0, -30]
            });
        }

        function initializeOpenStreetMap() {
            if (!window.L) {
                document.getElementById('map').innerHTML = '<div style="padding:20px;color:#b91c1c;font-weight:700">Nao foi possivel carregar o OpenStreetMap.</div>';
                return;
            }

            const centro = [-10.5, -51.9];
            const camadas = {
                mapa: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }),
                satelite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Tiles &copy; Esri'
                })
            };

            mapa = L.map('map', {
                center: centro,
                zoom: 4,
                layers: [getCaixasMapMode() === 'satelite' ? camadas.satelite : camadas.mapa],
                zoomControl: true
            });

            L.control.layers({'Mapa': camadas.mapa, 'Satelite': camadas.satelite}, null, {collapsed: false}).addTo(mapa);
            mapa.on('baselayerchange', event => setCaixasMapMode(event.name === 'Satelite' ? 'satelite' : 'mapa'));
            mapa.on('click', event => {
                if (modoAjustarCliente && !marcadorAjusteCliente) {
                    criarMarcadorAjusteCliente(event.latlng.lat, event.latlng.lng);
                    mostrarAvisoMapa('Agora arraste a casa se precisar e clique em salvar.');
                    return;
                }
                if (modoAdicionarCto) {
                    abrirFormularioNovaCto(event.latlng.lat, event.latlng.lng);
                    return;
                }
                if (!modoAdicionarCto && !modoAjustarCliente && !modoAtrelarCliente && !modoAjustarCto && (clientesFixosAtivos || todosClientesFixosAtivos)) {
                    const cto = encontrarCtoProximaDoClique(event.latlng, 52);
                    if (cto) abrirCtoSelecionadaNoMapa(cto);
                }
            });

            adicionarMarcadores();
            atualizarEstatisticas();
        }

        function adicionarMarcadoresGoogle(ajustarViewport) {
            // Limpar marcadores antigos
            limparMarcadores();

            let totalClientesVisiveis = 0;
            let totalOnlineVisiveis = 0;
            let totalOfflineVisiveis = 0;
            const hoverInfoWindow = new google.maps.InfoWindow({
                disableAutoPan: false,
                maxWidth: 760
            });
            ctosData.forEach(cto => {
                // Verificar filtro
                if (!ctoVisivelNoFiltro(cto)) {
                    return;
                }

                // Definir cor do marcador baseado em clientes online
                let cor = '#667eea'; // Padro
                if (cto.total_clientes === 0) {
                    cor = '#9ca3af'; // Cinza se sem clientes
                } else if (cto.clientes_online > 0) {
                    cor = '#10b981'; // Verde se tem online
                } else {
                    cor = '#ef4444'; // Vermelho se todos offline
                }

                // Criar SVG para o marcador
                const svgMarker = markerSvgComNumero(cor, cto.nome);

                const marker = new google.maps.Marker({
                    position: { lat: cto.latitude, lng: cto.longitude },
                    map: mapa,
                    title: cto.nome,
                    icon: {
                        url: 'data:image/svg+xml;base64,' + btoa(svgMarker),
                        scaledSize: new google.maps.Size(38, 32),
                        anchor: new google.maps.Point(19, 30)
                    }
                });

                marker.addListener('click', () => {
                    hoverInfoWindow.close();
                    abrirCtoSelecionadaNoMapa(cto);
                });

                marker.addListener('mouseover', () => {
                    if (ctoHoverTimer) clearTimeout(ctoHoverTimer);
                    ctoHoverAtivoId = String(cto.id);
                    if (!clientesFixosAtivos) mostrarClientesCtoNoMapa(cto, false);
                    hoverInfoWindow.setContent(`
                        <div class="cto-hover-tooltip">
                            <div class="cto-hover-title">${escapeHtml(cto.nome)}</div>
                            <div class="cto-hover-address">${escapeHtml(cto.endereco || 'Sem endereco')}</div>
                            <div class="cto-hover-counts">${cto.total_clientes} clientes | ${cto.clientes_online} online | ${cto.clientes_offline} offline</div>
                            ${montarResumoClientesHover(cto)}
                        </div>
                    `);
                    hoverInfoWindow.open({
                        anchor: marker,
                        map: mapa,
                        shouldFocus: false
                    });
                });

                marker.addListener('mouseout', () => {
                    ctoHoverTimer = setTimeout(() => {
                        if (ctoHoverAtivoId === String(cto.id)) return;
                        hoverInfoWindow.close();
                        if (!clientesFixosAtivos) limparMarcadoresClientesHover();
                    }, 1200);
                    ctoHoverAtivoId = null;
                });

                marcadores.push(marker);

                // Contar para estatsticas
                totalClientesVisiveis += parseInt(cto.total_clientes);
                totalOnlineVisiveis += parseInt(cto.clientes_online);
                totalOfflineVisiveis += parseInt(cto.clientes_offline);
            });

            // Ajustar view para mostrar todos os marcadores
            if (marcadores.length > 0 && ajustarViewport !== false) {
                const bounds = new google.maps.LatLngBounds();
                marcadores.forEach(marker => {
                    if (marker && typeof marker.getPosition === 'function') bounds.extend(marker.getPosition());
                });
                mapa.fitBounds(bounds);
            }

            // Atualizar contadores visveis
            document.getElementById('totalCtos').textContent = marcadores.length;
            document.getElementById('totalClientes').textContent = totalClientesVisiveis;
            document.getElementById('clientesOnline').textContent = totalOnlineVisiveis;
            document.getElementById('clientesOffline').textContent = totalOfflineVisiveis;
            adicionarVinculosRadioGoogle();
        }

        function montarPainelCto(cto) {
            const capacidade = parseInt(cto.capacidade) || 0;
            const portasUtilizadas = parseInt(cto.portas_utilizadas) || 0;
            const percentualUso = capacidade > 0 ? Math.min((portasUtilizadas / capacidade) * 100, 100) : 0;
            const filtroPainel = filtroClientesCtoAtual || 'total';
            const clientesPainel = clientesFiltradosDaCto(cto, filtroPainel);

            return `
                <div class="cto-popup">
                    <div class="cto-popup-header">
                        <h3>${escapeHtml(cto.nome)}</h3>
                        <p>${escapeHtml(subtituloTipoCto(cto))}</p>
                    </div>
                    <div class="cto-popup-body">
                        <div class="cto-section">
                            <div class="cto-label">Endereco</div>
                            <div class="cto-text">${escapeHtml(cto.endereco || 'N/A')}</div>
                        </div>
                        <div class="cto-section cto-tech-grid">
                            <div class="cto-tech-item"><strong>Tipo</strong><span>${escapeHtml(cto.tipo || 'N/A')}</span></div>
                            <div class="cto-tech-item"><strong>Sinal</strong><span>${escapeHtml(cto.sinal || 'N/A')}</span></div>
                            <div class="cto-tech-item"><strong>${escapeHtml(equipamentoLabelCto(cto))}</strong><span>${escapeHtml(cto.olt || 'N/A')}</span></div>
                            <div class="cto-tech-item"><strong>${escapeHtml(vinculoLabelCto(cto))}</strong><span>${escapeHtml(cto.fsp || 'N/A')}</span></div>
                        </div>
                        <div class="cto-section">
                            <div class="cto-label">Clientes Atribuidos</div>
                            <div class="cto-client-grid">
                                <button type="button" class="cto-count-card cto-count-total ${filtroPainel === 'total' ? 'active' : ''}" onclick="filtrarClientesPainelCto('total')"><strong>${cto.total_clientes}</strong><span>Total</span></button>
                                <button type="button" class="cto-count-card cto-count-online ${filtroPainel === 'online' ? 'active' : ''}" onclick="filtrarClientesPainelCto('online')"><strong>${cto.clientes_online}</strong><span>Online</span></button>
                                <button type="button" class="cto-count-card cto-count-offline ${filtroPainel === 'offline' ? 'active' : ''}" onclick="filtrarClientesPainelCto('offline')"><strong>${cto.clientes_offline}</strong><span>Offline</span></button>
                            </div>
                        </div>
                        ${clientesPainel && clientesPainel.length > 0 ? `
                        <div class="cto-section cto-client-list">
                            <div class="cto-label">Lista de Clientes</div>
                            <div class="cto-client-list-inner">
                                ${clientesPainel.map((cliente) => {
                                    const index = (Array.isArray(cto.clientes) ? cto.clientes : []).indexOf(cliente);
                                    const clienteInativo = clienteDesativado(cliente);
                                    return `
                                    <div class="cto-client-row ${clienteInativo ? 'inactive' : ''}">
                                        <button type="button" class="cto-client-name ${clienteInativo ? 'inactive' : ''}" onclick="abrirDetalheClienteDaCto('${String(cto.id)}', ${index})" title="${escapeHtml(nomeClienteComStatus(cliente))}" style="border:0;background:transparent;text-align:left;cursor:pointer;padding:0;font:inherit;color:#111827;"><strong>${escapeHtml(primeiroUltimoNome(cliente.nome))}</strong>${clienteInativo ? '<span class="cto-inactive-tag">Desativado</span>' : ''}</button>
                                        <span class="cto-client-login" title="Porta ${escapeHtml(cliente.porta || '-')} | ${escapeHtml(cliente.login || '')}">Porta ${escapeHtml(cliente.porta || '-')} | ${escapeHtml(cliente.login)}</span>
                                    </div>
                                `}).join('')}
                            </div>
                        </div>
                        ` : `<div class="cto-section cto-empty">Nenhum cliente neste filtro</div>`}
                        <div class="cto-section cto-section-full">
                            <div class="cto-label">Portas da CTO</div>
                            ${montarPortasCto(cto)}
                        </div>
                        <div class="cto-section cto-section-full">
                            <div class="cto-label">Capacidade de Portas</div>
                            <div class="cto-progress-head">
                                <span>${portasUtilizadas}/${capacidade} portas</span>
                                <span>${cto.portas_livres} livres</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill" style="width: ${percentualUso}%;"></div></div>
                        </div>
                        <div class="cto-section cto-section-full" style="margin-bottom:0;">
                            <button type="button" class="cto-edit" onclick="fixarClientesCto('${String(cto.id)}')" style="border:0;width:100%;cursor:pointer;">Mostrar clientes desta CTO</button>
                            <button type="button" class="cto-edit" onclick="iniciarAjusteLocalizacaoCto('${String(cto.id)}')" style="border:0;width:100%;cursor:pointer;margin-top:8px;background:#64748b;">Editar localizacao da CTO</button>
                        </div>
                    </div>
                </div>
            `;
        }

        function adicionarMarcadoresOpenStreet(ajustarViewport) {
            limparMarcadores();

            let totalClientesVisiveis = 0;
            let totalOnlineVisiveis = 0;
            let totalOfflineVisiveis = 0;
            const bounds = [];

            ctosData.forEach(cto => {
                if (!ctoVisivelNoFiltro(cto)) return;

                const lat = parseFloat(cto.latitude);
                const lng = parseFloat(cto.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                const marker = L.marker([lat, lng], {icon: criarLeafletIconCto(cto), title: cto.nome}).addTo(mapa);
                marker.bindTooltip(escapeHtml(cto.nome || ''), {direction: 'top', offset: [0, -48]});
                marker.bindPopup(montarConteudoHover(cto), {
                    maxWidth: 760,
                    autoPan: true,
                    closeButton: true,
                    className: 'cto-hover-popup'
                });
                marker.on('popupopen', event => {
                    const popup = event.popup && event.popup.getElement ? event.popup.getElement() : null;
                    if (!popup) return;
                    popup.addEventListener('mouseenter', () => {
                        if (ctoHoverTimer) clearTimeout(ctoHoverTimer);
                        ctoHoverAtivoId = String(cto.id);
                    });
                    popup.addEventListener('mouseleave', () => {
                        ctoHoverAtivoId = null;
                        ctoHoverTimer = setTimeout(() => {
                            marker.closePopup();
                            if (!clientesFixosAtivos) limparMarcadoresClientesHover();
                        }, 600);
                    });
                });
                marker.on('mouseover', () => {
                    ctoHoverAtivoId = String(cto.id);
                    marker.openPopup();
                });
                marker.on('mouseover', () => {
                    if (!clientesFixosAtivos) mostrarClientesCtoNoMapa(cto, false);
                });
                marker.on('mouseout', () => {
                    ctoHoverAtivoId = null;
                    ctoHoverTimer = setTimeout(() => {
                        if (ctoHoverAtivoId === String(cto.id)) return;
                        marker.closePopup();
                        if (!clientesFixosAtivos) limparMarcadoresClientesHover();
                    }, 1200);
                });
                marker.on('click', () => {
                    marker.closePopup();
                    abrirCtoSelecionadaNoMapa(cto);
                });

                marcadores.push(marker);
                bounds.push([lat, lng]);
                totalClientesVisiveis += parseInt(cto.total_clientes);
                totalOnlineVisiveis += parseInt(cto.clientes_online);
                totalOfflineVisiveis += parseInt(cto.clientes_offline);
            });

            if (bounds.length > 0 && ajustarViewport !== false) mapa.fitBounds(bounds, {padding: [30, 30]});

            document.getElementById('totalCtos').textContent = marcadores.length;
            document.getElementById('totalClientes').textContent = totalClientesVisiveis;
            document.getElementById('clientesOnline').textContent = totalOnlineVisiveis;
            document.getElementById('clientesOffline').textContent = totalOfflineVisiveis;
            adicionarVinculosRadioOpenStreet();
        }

        function ctosValidasVisiveis() {
            return ctosData.filter(cto => {
                const lat = parseFloat(cto.latitude);
                const lng = parseFloat(cto.longitude);
                return ctoVisivelNoFiltro(cto) && !isNaN(lat) && !isNaN(lng);
            });
        }

        function paresStaAp() {
            const visiveis = ctosValidasVisiveis();
            const aps = {};
            visiveis.forEach(cto => {
                if (String(cto.tipo || '').toUpperCase() === 'AP') {
                    aps[normalizarNomeCto(cto.nome)] = cto;
                }
            });
            return visiveis
                .filter(cto => String(cto.tipo || '').toUpperCase() === 'STA' && normalizarNomeCto(cto.fsp))
                .map(sta => ({sta: sta, ap: aps[normalizarNomeCto(sta.fsp)]}))
                .filter(par => !!par.ap);
        }

        function ctoPorNomeTipo(nome, tipo) {
            const alvo = normalizarNomeCto(nome);
            if (!alvo) return null;
            return (ctosData || []).find(cto => {
                return normalizarNomeCto(cto.nome) === alvo && (!tipo || String(cto.tipo || '').toUpperCase() === tipo);
            }) || null;
        }

        function latLngValidoCto(cto) {
            if (!cto) return false;
            const lat = parseFloat(cto.latitude);
            const lng = parseFloat(cto.longitude);
            return !isNaN(lat) && !isNaN(lng);
        }

        function addDestaqueRadioGoogle(origem, destino, options) {
            if (!window.google || !google.maps || !mapa || !latLngValidoCto(origem) || !latLngValidoCto(destino)) return;
            const line = new google.maps.Polyline({
                path: [
                    {lat: parseFloat(origem.latitude), lng: parseFloat(origem.longitude)},
                    {lat: parseFloat(destino.latitude), lng: parseFloat(destino.longitude)}
                ],
                geodesic: true,
                strokeColor: options && options.color ? options.color : '#0ea5e9',
                strokeOpacity: 0.95,
                strokeWeight: 4,
                map: mapa
            });
            destaquesRadio.push(line);
        }

        function addCirculoRadioGoogle(cto, raio, color) {
            if (!window.google || !google.maps || !mapa || !latLngValidoCto(cto)) return;
            const circle = new google.maps.Circle({
                strokeColor: color || '#10b981',
                strokeOpacity: 0.95,
                strokeWeight: 3,
                fillColor: color || '#10b981',
                fillOpacity: 0.12,
                map: mapa,
                center: {lat: parseFloat(cto.latitude), lng: parseFloat(cto.longitude)},
                radius: raio || 180
            });
            destaquesRadio.push(circle);
        }

        function addDestaqueRadioOpenStreet(origem, destino, options) {
            if (!window.L || !mapa || !latLngValidoCto(origem) || !latLngValidoCto(destino)) return;
            const line = L.polyline([
                [parseFloat(origem.latitude), parseFloat(origem.longitude)],
                [parseFloat(destino.latitude), parseFloat(destino.longitude)]
            ], {
                color: options && options.color ? options.color : '#0ea5e9',
                weight: 4,
                opacity: 0.95,
                dashArray: options && options.dash ? options.dash : null
            }).addTo(mapa);
            line.bindTooltip(escapeHtml((origem.nome || '') + ' -> ' + (destino.nome || '')), {direction: 'center'});
            destaquesRadio.push(line);
        }

        function addCirculoRadioOpenStreet(cto, raio, color) {
            if (!window.L || !mapa || !latLngValidoCto(cto)) return;
            const circle = L.circle([parseFloat(cto.latitude), parseFloat(cto.longitude)], {
                radius: raio || 180,
                color: color || '#10b981',
                weight: 3,
                opacity: 0.95,
                fillColor: color || '#10b981',
                fillOpacity: 0.12
            }).addTo(mapa);
            circle.bindTooltip(escapeHtml('Ponto: ' + (cto.nome || '')), {direction: 'top'});
            destaquesRadio.push(circle);
        }

        function destacarRadioCto(cto) {
            limparDestaquesRadio();
            const tipo = String(cto && cto.tipo || '').toUpperCase();
            if (!isRadioTipo(tipo)) return;
            if (tipo === 'STA') {
                const ap = ctoPorNomeTipo(cto.fsp, 'AP');
                if (!ap) {
                    mostrarAvisoMapa('STA sem AP vinculado.');
                    return;
                }
                if (MAP_PROVIDER === 'google') addDestaqueRadioGoogle(cto, ap, {color: '#0ea5e9'});
                else addDestaqueRadioOpenStreet(cto, ap, {color: '#0ea5e9'});
                return;
            }
            if (tipo === 'AP') {
                const torre = ctoPorNomeTipo(cto.fsp, 'TO');
                const ponto = torre || cto;
                if (MAP_PROVIDER === 'google') {
                    if (torre) addDestaqueRadioGoogle(cto, torre, {color: '#10b981'});
                    addCirculoRadioGoogle(ponto, 220, '#10b981');
                } else {
                    if (torre) addDestaqueRadioOpenStreet(cto, torre, {color: '#10b981'});
                    addCirculoRadioOpenStreet(ponto, 220, '#10b981');
                }
                if (!torre && normalizarNomeCto(cto.fsp)) mostrarAvisoMapa('Torre vinculada ao AP nao encontrada no mapa.');
            }
        }

        function adicionarVinculosRadioGoogle() {
            if (!window.google || !google.maps || !mapa) return;
            paresStaAp().forEach(par => {
                const linha = new google.maps.Polyline({
                    path: [
                        {lat: parseFloat(par.sta.latitude), lng: parseFloat(par.sta.longitude)},
                        {lat: parseFloat(par.ap.latitude), lng: parseFloat(par.ap.longitude)}
                    ],
                    geodesic: true,
                    strokeColor: '#f59e0b',
                    strokeOpacity: 0.9,
                    strokeWeight: 3,
                    icons: [{
                        icon: {path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3},
                        offset: '0',
                        repeat: '14px'
                    }],
                    map: mapa
                });
                marcadores.push(linha);
            });
        }

        function adicionarVinculosRadioOpenStreet() {
            if (!window.L || !mapa) return;
            paresStaAp().forEach(par => {
                const linha = L.polyline([
                    [parseFloat(par.sta.latitude), parseFloat(par.sta.longitude)],
                    [parseFloat(par.ap.latitude), parseFloat(par.ap.longitude)]
                ], {
                    color: '#f59e0b',
                    weight: 3,
                    opacity: 0.9,
                    dashArray: '8 8'
                }).addTo(mapa);
                linha.bindTooltip(escapeHtml(par.sta.nome + ' -> ' + par.ap.nome), {direction: 'center'});
                marcadores.push(linha);
            });
        }

        function abrirPainelCto(conteudo) {
            const mapElement = document.getElementById('map');
            if (!mapElement) return;

            const painelTarget = mapElement.querySelector('.gm-style') || mapElement;
            let painel = document.getElementById('ctoMapPanel');
            if (!painel) {
                painel = document.createElement('div');
                painel.id = 'ctoMapPanel';
                painel.className = 'cto-map-panel';
                painelTarget.appendChild(painel);
            } else if (painel.parentElement !== painelTarget) {
                painelTarget.appendChild(painel);
            }

            if (!painel) return;
            painelCtoConteudoAtual = conteudo;

            painel.innerHTML = `
                <button type="button" class="cto-panel-close" onclick="fecharPainelCto()" aria-label="Fechar">x</button>
                ${conteudo}
            `;
            painel.classList.add('is-open');
        }

        function fecharPainelCto(restaurarMapa) {
            const painel = document.getElementById('ctoMapPanel');
            const viewportAtual = capturarViewportMapa();
            if (painel) {
                painel.classList.remove('is-open');
                painel.innerHTML = '';
            }
            painelCtoConteudoAtual = '';
            fecharDetalheCliente();
            if (!modoAtrelarCliente) limparLinhasClienteCto();
            if (restaurarMapa !== false && ctoUnicaVisivelId && !modoAtrelarCliente && !modoAjustarCto) {
                clientesFixosAtivos = false;
                ctoUnicaVisivelId = null;
                ctoSelecionadaAtual = null;
                limparMarcadoresClientesHover();
                adicionarMarcadores(false);
                if (todosClientesFixosAtivos) desenharTodosClientesNoMapa(false);
                restaurarViewportMapa(viewportAtual);
                atualizarBotaoClientes();
                return;
            }
            if (!clientesFixosAtivos) limparMarcadoresClientesHover();
        }

        document.addEventListener('fullscreenchange', () => {
            if (painelCtoConteudoAtual) {
                setTimeout(() => abrirPainelCto(painelCtoConteudoAtual), 150);
            }
        });

        // Atualizar estatsticas gerais
        function atualizarEstatisticas() {
            let totalClientes = 0;
            let totalOnline = 0;
            let totalOffline = 0;

            ctosData.forEach(cto => {
                totalClientes += parseInt(cto.total_clientes);
                totalOnline += parseInt(cto.clientes_online);
                totalOffline += parseInt(cto.clientes_offline);
            });

            document.getElementById('totalCtos').textContent = ctosData.length;
            document.getElementById('totalClientes').textContent = totalClientes;
            document.getElementById('clientesOnline').textContent = totalOnline;
            document.getElementById('clientesOffline').textContent = totalOffline;
        }

        function tratarEscapeMapa(event) {
            if (!event || event.key !== 'Escape') return;
            if (modoAjustarCto) { event.preventDefault(); cancelarAjusteLocalizacaoCto(true); return; }
            if (modoAjustarCliente) { event.preventDefault(); cancelarAjusteLocalizacaoCliente(); return; }
            if (modoAtrelarCliente) { event.preventDefault(); cancelarAtrelamentoCliente(true); return; }
            if (document.getElementById('ctoClientSearchPanel')) { event.preventDefault(); fecharBuscaClienteMapa(); return; }
            if (modoAdicionarCto || tempAddMarker || document.getElementById('ctoAddModal')) { event.preventDefault(); desativarModoAdicionarCto(); return; }
            if (document.getElementById('ctoMapPanel')) { event.preventDefault(); fecharPainelCto(); }
        }

        document.addEventListener('keydown', tratarEscapeMapa);

        // Configurar filtros
        document.querySelectorAll('.filter-btn[data-filter]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn[data-filter]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filtroAtual = btn.getAttribute('data-filter');
                clientesFixosAtivos = false;
                todosClientesFixosAtivos = false;
                ctoUnicaVisivelId = null;
                ctoSelecionadaAtual = null;
                modoAtrelarCliente = null;
                limparMarcadorClienteSelecionado();
                cancelarAjusteLocalizacaoCliente();
                cancelarAjusteLocalizacaoCto(false);
                fecharPainelCto(false);
                adicionarMarcadores();
                atualizarBotaoClientes();
            });
        });

        const btnAdicionarCto = document.getElementById('btnAdicionarCto');
        if (btnAdicionarCto) btnAdicionarCto.addEventListener('click', ativarModoAdicionarCto);

        const btnMostrarTodosClientes = document.getElementById('btnMostrarTodosClientes');
        if (btnMostrarTodosClientes) btnMostrarTodosClientes.addEventListener('click', () => mostrarTodosClientesNoMapa(false));

        const btnBuscaClienteMapa = document.getElementById('btnBuscaClienteMapa');
        if (btnBuscaClienteMapa) btnBuscaClienteMapa.addEventListener('click', abrirBuscaClienteMapa);

        document.querySelectorAll('[data-client-filter]').forEach(btn => {
            btn.addEventListener('click', () => {
                const filtro = btn.getAttribute('data-client-filter') || 'todos';
                mostrarTodosClientesNoMapa(true, filtro);
            });
        });

        const btnLimparClientes = document.getElementById('btnLimparClientes');
        if (btnLimparClientes) btnLimparClientes.addEventListener('click', () => {
            limparClientesMapa();
        });

        // Inicializar ao carregar
        // Funo para recarregar dados em tempo real
        function atualizarDadosEmTempoReal() {
            if (bloqueiaAtualizacaoTempoReal || modoAdicionarCto || tempAddMarker || modoAjustarCto) return;
            fetch(window.location.href, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                // Extrair dados JSON da resposta
                const match = html.match(/const ctosData = (\[[\s\S]*?\]);/);
                if (match && match[1]) {
                    const novosDados = JSON.parse(match[1]);
                    
                    // Verificar se h mudanas
                    if (JSON.stringify(ctosData) !== JSON.stringify(novosDados)) {
                        // Atualizar dados
                        while (ctosData.length > 0) ctosData.pop();
                        ctosData.push(...novosDados);
                        
                        // Limpar marcadores antigos
                        limparMarcadores();
                        
                        // Re-renderizar mapa
                        adicionarMarcadores();
                        atualizarEstatisticas();
                        
                        console.log(' Mapa atualizado em tempo real');
                    }
                }
            })
            .catch(error => console.error('Erro ao atualizar:', error));
        }
        
        // Atualizar a cada 10 segundos
        setInterval(atualizarDadosEmTempoReal, 10000);
        window.addEventListener('load', initializeMap);
    </script>
</body>
</html>
