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
            padding: clamp(6px, 0.8vw, 12px);
        }

        .container {
            width: min(100%, 1450px);
            height: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .header {
            margin-bottom: clamp(6px, 0.9vw, 12px);
            color: white;
            flex: 0 0 auto;
        }

        .header h1 {
            font-size: clamp(1.25rem, 1.6vw, 1.7rem);
            margin-bottom: clamp(5px, 0.7vw, 8px);
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
            border-radius: 15px;
            padding: clamp(8px, 0.9vw, 12px);
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
            margin-bottom: clamp(8px, 0.8vw, 10px);
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

        .map-shell {
            position: relative;
            width: 100%;
            min-height: 520px;
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
            gap: clamp(8px, 0.8vw, 12px);
            margin-bottom: clamp(8px, 0.8vw, 10px);
            flex: 0 0 auto;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: clamp(8px, 0.9vw, 11px);
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .stat-card h3 {
            font-size: clamp(1.25rem, 1.7vw, 1.6rem);
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
            inset: 0;
            z-index: 9999;
            display: none;
            background: rgba(248, 250, 252, 0.98);
            padding: 10px;
            overflow: hidden;
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
        }

        .cto-hover-tooltip {
            color: #1f2937;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            max-width: 220px;
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
            min-height: 100%;
            height: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(30, 41, 59, 0.2);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
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
            overflow: hidden;
            min-height: 0;
            flex: 1 1 auto;
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
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
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 14px;
        }

        .cto-client-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
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
            overflow-wrap: anywhere;
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
        .cto-hover-tooltip{font-family:Arial,sans-serif;min-width:240px;max-width:340px;color:#1f2937}
        .cto-hover-title{font-weight:700;color:#4f63d8;margin-bottom:4px}
        .cto-hover-address{font-size:12px;color:#4b5563;margin-bottom:6px}
        .cto-hover-counts{font-size:12px;font-weight:700;margin-bottom:8px;color:#111827}
        .cto-hover-clients{display:grid;gap:4px;max-height:220px;overflow:auto}
        .cto-hover-client{display:grid;grid-template-columns:10px minmax(95px,1fr) minmax(70px,.8fr) auto;gap:5px;align-items:center;font-size:11px;border-top:1px solid #eef2f7;padding-top:4px}
        .cto-hover-dot{width:7px;height:7px;border-radius:50%;background:#ef4444}
        .cto-hover-client.online .cto-hover-dot{background:#10b981}
        .cto-hover-name{font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .cto-hover-login{color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .cto-hover-type{font-size:10px;color:#475569;background:#eef2ff;border-radius:999px;padding:1px 5px}
        .cto-hover-empty,.cto-hover-more{font-size:11px;color:#64748b;margin-top:4px}
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🌐 Gerenciador FTTH</h1>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="?_route=inicio" class="btn-voltar">← Voltar à Listagem</a>
                <a href="?_route=painel" class="btn-voltar" style="background: rgba(255, 107, 107, 0.2); border-color: #ff6b6b;">🏠 Painel Principal</a>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Estatísticas -->
            <div class="stats">
                <div class="stat-card">
                    <h3 id="totalCtos">0</h3>
                    <p>CTOs Cadastradas</p>
                </div>
                <div class="stat-card">
                    <h3 id="totalClientes">0</h3>
                    <p>Clientes Atribuídos</p>
                </div>
                <div class="stat-card">
                    <h3 id="clientesOnline">0</h3>
                    <p>Clientes Online</p>
                </div>
                <div class="stat-card">
                    <h3 id="clientesOffline">0</h3>
                    <p>Clientes Offline</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="controls">
                <button class="filter-btn active" data-filter="todos">Todas as CTOs</button>
                <button class="filter-btn" data-filter="comclientes">Com Clientes</button>
                <button class="filter-btn" data-filter="semclientes">Sem Clientes</button>
            </div>

            <!-- Mapa -->
            <div class="map-shell">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Google Maps API -->
    <?php
    require_once dirname(__FILE__) . '/../../config/api.php';
    $api_key = getGoogleMapsApiKey();
    ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($api_key); ?>"></script>

    <script>
        // Dados das CTOs
        const ctosData = <?php echo $ctos_json; ?>;
        let mapa = null;
        let marcadores = [];
        let filtroAtual = 'todos';
        let painelCtoConteudoAtual = '';
        const CAIXAS_MAP_LAYER_KEY = 'caixas_modo_visualizacao_mapa';
        function getCaixasMapMode() { return (localStorage.getItem(CAIXAS_MAP_LAYER_KEY) === 'satelite') ? 'satelite' : 'mapa'; }
        function setCaixasMapMode(mode) { localStorage.setItem(CAIXAS_MAP_LAYER_KEY, mode === 'satelite' ? 'satelite' : 'mapa'); }
        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (char) {
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[char];
            });
        }

        function montarResumoClientesHover(cto) {
            const clientes = Array.isArray(cto.clientes) ? cto.clientes : [];
            if (!clientes.length) {
                return '<div class="cto-hover-empty">Nenhum cliente atribuido</div>';
            }
            const rows = clientes.slice(0, 12).map(cliente => {
                const statusClass = cliente.status === 'online' ? 'online' : 'offline';
                const tipo = cliente.tipo ? `<span class="cto-hover-type">${escapeHtml(cliente.tipo)}</span>` : '';
                return `<div class="cto-hover-client ${statusClass}">
                    <span class="cto-hover-dot"></span>
                    <span class="cto-hover-name">${escapeHtml(cliente.nome)}</span>
                    <span class="cto-hover-login">${escapeHtml(cliente.login)}</span>
                    ${tipo}
                </div>`;
            }).join('');
            const resto = clientes.length > 12 ? `<div class="cto-hover-more">+${clientes.length - 12} cliente(s)</div>` : '';
            return `<div class="cto-hover-clients">${rows}${resto}</div>`;
        }

        // Inicializar o mapa
        function initializeMap() {
            // Centro padrão (Brasil)
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

            // Adicionar marcadores para cada CTO
            adicionarMarcadores();

            // Atualizar estatísticas
            atualizarEstatisticas();
        }

        // Adicionar marcadores no mapa
        function adicionarMarcadores() {
            // Limpar marcadores antigos
            marcadores.forEach(marker => marker.setMap(null));
            marcadores = [];

            let totalClientesVisiveis = 0;
            let totalOnlineVisiveis = 0;
            let totalOfflineVisiveis = 0;
            const hoverInfoWindow = new google.maps.InfoWindow({
                disableAutoPan: true,
                maxWidth: 240
            });
            ctosData.forEach(cto => {
                // Verificar filtro
                if (filtroAtual === 'comclientes' && cto.total_clientes === 0) {
                    return;
                }
                if (filtroAtual === 'semclientes' && cto.total_clientes > 0) {
                    return;
                }

                // Definir cor do marcador baseado em clientes online
                let cor = '#667eea'; // Padrão
                if (cto.total_clientes === 0) {
                    cor = '#9ca3af'; // Cinza se sem clientes
                } else if (cto.clientes_online > 0) {
                    cor = '#10b981'; // Verde se tem online
                } else {
                    cor = '#ef4444'; // Vermelho se todos offline
                }

                // Criar SVG para o marcador
                const svgMarker = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 60" width="40" height="60">
                    <path d="M20 0C8.95 0 0 8.95 0 20c0 11.05 20 40 20 40s20-28.95 20-40C40 8.95 31.05 0 20 0z" fill="${cor}"/>
                    <circle cx="20" cy="20" r="8" fill="white"/>
                </svg>`;

                const marker = new google.maps.Marker({
                    position: { lat: cto.latitude, lng: cto.longitude },
                    map: mapa,
                    title: cto.nome,
                    icon: {
                        url: 'data:image/svg+xml;base64,' + btoa(svgMarker),
                        scaledSize: new google.maps.Size(40, 60),
                        anchor: new google.maps.Point(20, 60)
                    }
                });

                const capacidade = parseInt(cto.capacidade) || 0;
                const portasUtilizadas = parseInt(cto.portas_utilizadas) || 0;
                const percentualUso = capacidade > 0 ? Math.min((portasUtilizadas / capacidade) * 100, 100) : 0;

                // Criar conteúdo do popup com design responsivo
                const infoContent = `
                    <div class="cto-popup">
                        <div class="cto-popup-header">
                            <h3>${cto.nome}</h3>
                            <p>CTO - Caixa de Terminação Óptica</p>
                        </div>
                        
                        <div class="cto-popup-body">
                            <div class="cto-section">
                                <div class="cto-label">Endereço</div>
                                <div class="cto-text">${cto.endereco || 'N/A'}</div>
                            </div>
                            
                            <div class="cto-section cto-tech-grid">
                                <div class="cto-tech-item">
                                    <strong>Tipo</strong>
                                    <span>${cto.tipo || 'N/A'}</span>
                                </div>
                                <div class="cto-tech-item">
                                    <strong>Sinal</strong>
                                    <span>${cto.sinal || 'N/A'}</span>
                                </div>
                                <div class="cto-tech-item">
                                    <strong>OLT</strong>
                                    <span>${cto.olt || 'N/A'}</span>
                                </div>
                            </div>
                            
                            <div class="cto-section">
                                <div class="cto-label">Clientes Atribuídos</div>
                                <div class="cto-client-grid">
                                    <div class="cto-count-card cto-count-total">
                                        <strong>${cto.total_clientes}</strong>
                                        <span>Total</span>
                                    </div>
                                    <div class="cto-count-card cto-count-online">
                                        <strong>${cto.clientes_online}</strong>
                                        <span>Online</span>
                                    </div>
                                    <div class="cto-count-card cto-count-offline">
                                        <strong>${cto.clientes_offline}</strong>
                                        <span>Offline</span>
                                    </div>
                                </div>
                            </div>
                            
                            ${cto.clientes && cto.clientes.length > 0 ? `
                            <div class="cto-section cto-client-list">
                                <div class="cto-label">Lista de Clientes</div>
                                <div class="cto-client-list-inner">
                                    ${cto.clientes.map(cliente => `
                                        <div class="cto-client-row">
                                            <span class="cto-client-name">
                                                ${cliente.status === 'online' ? '●' : '●'} 
                                                <strong>${cliente.nome}</strong>
                                            </span>
                                            <span class="cto-client-login">${cliente.login}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                            ` : `
                            <div class="cto-section cto-empty">Nenhum cliente atribuído</div>
                            `}
                            
                            <div class="cto-section cto-section-full">
                                <div class="cto-label">Capacidade de Portas</div>
                                <div class="cto-progress-head">
                                    <span>${portasUtilizadas}/${capacidade} portas</span>
                                    <span>${cto.portas_livres} livres</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${percentualUso}%;"></div>
                                </div>
                            </div>
                            
                            <div class="cto-section cto-section-full" style="margin-bottom: 0;">
                                <a href="?_route=editar&id=${cto.id}" class="cto-edit">Editar CTO</a>
                            </div>
                        </div>
                    </div>
                `;

                marker.addListener('click', () => {
                    hoverInfoWindow.close();
                    abrirPainelCto(infoContent);
                });

                marker.addListener('mouseover', () => {
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
                    hoverInfoWindow.close();
                });

                marcadores.push(marker);

                // Contar para estatísticas
                totalClientesVisiveis += parseInt(cto.total_clientes);
                totalOnlineVisiveis += parseInt(cto.clientes_online);
                totalOfflineVisiveis += parseInt(cto.clientes_offline);
            });

            // Ajustar view para mostrar todos os marcadores
            if (marcadores.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                marcadores.forEach(marker => {
                    bounds.extend(marker.getPosition());
                });
                mapa.fitBounds(bounds);
            }

            // Atualizar contadores visíveis
            document.getElementById('totalCtos').textContent = marcadores.length;
            document.getElementById('totalClientes').textContent = totalClientesVisiveis;
            document.getElementById('clientesOnline').textContent = totalOnlineVisiveis;
            document.getElementById('clientesOffline').textContent = totalOfflineVisiveis;
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
                <button type="button" class="cto-panel-close" onclick="fecharPainelCto()" aria-label="Fechar">×</button>
                ${conteudo}
            `;
            painel.classList.add('is-open');
        }

        function fecharPainelCto() {
            const painel = document.getElementById('ctoMapPanel');
            if (!painel) return;

            painel.classList.remove('is-open');
            painel.innerHTML = '';
            painelCtoConteudoAtual = '';
        }

        document.addEventListener('fullscreenchange', () => {
            if (painelCtoConteudoAtual) {
                setTimeout(() => abrirPainelCto(painelCtoConteudoAtual), 150);
            }
        });

        // Atualizar estatísticas gerais
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

        // Configurar filtros
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filtroAtual = btn.getAttribute('data-filter');
                fecharPainelCto();
                adicionarMarcadores();
            });
        });

        // Inicializar ao carregar
        // Função para recarregar dados em tempo real
        function atualizarDadosEmTempoReal() {
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
                    
                    // Verificar se há mudanças
                    if (JSON.stringify(ctosData) !== JSON.stringify(novosDados)) {
                        // Atualizar dados
                        while (ctosData.length > 0) ctosData.pop();
                        ctosData.push(...novosDados);
                        
                        // Limpar marcadores antigos
                        marcadores.forEach(marker => marker.setMap(null));
                        marcadores = [];
                        
                        // Re-renderizar mapa
                        adicionarMarcadores();
                        atualizarEstatisticas();
                        
                        console.log('✅ Mapa atualizado em tempo real');
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
