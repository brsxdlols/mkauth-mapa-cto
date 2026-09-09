<!DOCTYPE html>
<html lang="pt-BR" class="ftth-theme">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>MK - AUTH :: Viabilidade de Atendimento</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1580px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(430px, 0.85fr) minmax(720px, 1.15fr);
            gap: 0;
            min-height: 680px;
        }

        .sidebar {
            padding: 30px;
            border-right: 1px solid #e5e7eb;
            background: #f9fafb;
            overflow-y: auto;
            max-height: 680px;
        }

        .sidebar h2 {
            color: #1f2937;
            font-size: 1.3em;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #667eea;
        }

        .search-box {
            margin-bottom: 25px;
            position: relative;
        }

        .search-box label {
            display: block;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .search-input-group {
            position: relative;
            display: flex;
            gap: 8px;
        }

        #enderecoInput {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
        }

        #enderecoInput:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        #buscarBtn {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #buscarBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .address-suggestions {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 84px;
            z-index: 30;
            margin-top: 6px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            max-height: 260px;
            overflow-y: auto;
        }

        .address-suggestions.active {
            display: block;
        }

        .address-suggestion-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eef2f7;
            color: #374151;
            font-size: 0.9em;
            line-height: 1.35;
        }

        .address-suggestion-item:hover {
            background: #eef2ff;
            color: #312e81;
        }

        .address-suggestion-item:last-child {
            border-bottom: 0;
        }

        .address-suggestion-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .results-section {
            margin-bottom: 25px;
        }

        .results-section h3 {
            color: #374151;
            font-size: 1.1em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .selected-address {
            background: linear-gradient(135deg, #f3e8ff 0%, #ede9fe 100%);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #8b5cf6;
            margin-bottom: 15px;
            display: none;
        }

        .selected-address.active {
            display: block;
        }

        .selected-address p {
            color: #4b5563;
            font-size: 0.9em;
        }

        .ctos-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .cto-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cto-item:hover {
            border-color: #667eea;
            background: #f3f4f6;
            transform: translateX(4px);
        }

        .cto-item.selected {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-color: #0ea5e9;
        }

        .cto-item .cto-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .cto-item .cto-distance {
            color: #667eea;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .cto-item .cto-address {
            color: #6b7280;
            font-size: 0.85em;
        }

        .cto-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .cto-pill {
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 999px;
            padding: 3px 9px;
            font-size: 0.78em;
            font-weight: 700;
        }

        .cto-pill.free {
            background: #dcfce7;
            color: #047857;
        }

        .route-info {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 20px;
            margin-top: 15px;
            display: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .route-info.active {
            display: block;
        }

        .route-info h4 {
            color: #059669;
            margin-bottom: 15px;
            font-size: 1.1em;
            border-bottom: 2px solid #10b981;
            padding-bottom: 10px;
        }

        .route-detail {
            color: #047857;
            font-size: 0.95em;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .route-detail strong {
            font-weight: 600;
            color: #059669;
        }

        .route-detail span {
            background: white;
            padding: 6px 12px;
            border-radius: 6px;
            border-left: 3px solid #10b981;
            font-weight: 600;
            color: #047857;
        }

        .map-container {
            position: relative;
            height: 680px;
        }

        #mapa {
            width: 100%;
            height: 100%;
        }

        .voltar-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 5;
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            font-weight: 600;
            color: #667eea;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .voltar-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 1024px) {
            .content {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .sidebar {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
                max-height: none;
            }

            #mapa {
                min-height: 500px;
            }
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #667eea;
        }

        .error-message {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 12px;
            color: #991b1b;
            margin-bottom: 15px;
            display: none;
        }

        .error-message.show {
            display: block;
        }
    </style>
    <style data-ftth-theme="1.1.35"><?php readfile(__DIR__ . '/../../../../assets/css/ftth-theme.css'); ?></style>
</head>
<body class="ftth-theme ftth-viabilidade">
    <div class="container">
        <div class="header">
            <h1>🗺️ Viabilidade de Atendimento</h1>
            <p>Encontre a CTO mais próxima e visualize a rota até ela</p>
        </div>

        <div class="content">
            <div class="sidebar">
                <a href="?_route=painel" class="voltar-btn">← Voltar</a>
                
                <h2>Buscar Endereço</h2>

                <div class="error-message" id="errorMessage"></div>

                <div class="search-box">
                    <label for="enderecoInput">Digite o endereço:</label>
                    <div class="search-input-group">
                        <input 
                            type="text" 
                            id="enderecoInput" 
                            placeholder="Digite rua, número, cidade ou CEP"
                            autocomplete="off"
                        >
                        <button id="buscarBtn">Buscar</button>
                    </div>
                </div>

                <div class="selected-address" id="selectedAddress">
                    <p id="selectedAddressText"></p>
                </div>

                <div class="results-section">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3>CTOs Próximas</h3>
                        <button id="fecharListaBtn" onclick="fecharLista()" style="display: none; background: #ef4444; color: white; border: none; border-radius: 6px; padding: 6px 12px; cursor: pointer; font-weight: 600; font-size: 0.9em;">
                            ✕ Fechar
                        </button>
                    </div>
                    <div class="ctos-list" id="ctosList">
                        <div class="loading">Digite um endereço para encontrar CTOs próximas</div>
                    </div>
                </div>

                <div class="route-info" id="routeInfo">
                    <h4>✅ Informações da Rota</h4>
                    <div class="route-detail">
                        <strong>📏 Distância:</strong> 
                        <span id="routeDistance">-</span>
                    </div>
                    <div class="route-detail">
                        <strong>⏱️ Tempo estimado:</strong> 
                        <span id="routeDuration">-</span>
                    </div>
                    <div class="route-detail">
                        <strong>🚶 Modo:</strong> 
                        <span id="routeMode">A pé (Walking)</span>
                    </div>
                    <button id="verTodasBtn" onclick="verTodasCtos()" style="margin-top: 15px; width: 100%; padding: 10px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        ← Ver todas as CTOs
                    </button>
                </div>
            </div>

            <div class="map-container">
                <div id="mapa"></div>
            </div>
        </div>
    </div>

    <!-- Map provider API -->
    <?php
    // Estabelecer conexão com o banco de dados
    if (!isset($GLOBALS['connection'])) {
        $GLOBALS['connection'] = @mysqli_connect('localhost', 'root', 'rapnet@2024', 'mkradius');
    }
    
    require_once dirname(__FILE__) . '/../../config/api.php';
    $api_key = getGoogleMapsApiKey();
    $map_provider = function_exists('getSystemMapProvider') ? getSystemMapProvider() : (!empty($api_key) ? 'google' : 'openstreet');
    
    if ($map_provider === 'google' && empty($api_key)) {
        $map_provider = 'openstreet';
    }
    ?>
    <?php if ($map_provider === 'google' && !empty($api_key)): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($api_key); ?>&libraries=geometry,places"></script>
    <?php else: ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>

    <script>
        const MAP_PROVIDER = <?php echo json_encode($map_provider); ?>;
        console.log('Provedor de mapa da viabilidade:', MAP_PROVIDER);
        
        // Variáveis globais
        let mapa;
        let geocoder;
        let directionsService;
        let directionsRenderer;
        let userLocation = null;
        let userMarker = null;
        let ctoMarkers = [];
        let allCtos = [];
        let selectedCto = null;
        let routeLayer = null;
        let leafletLayers = null;

        function isOpenStreet() {
            return MAP_PROVIDER === 'openstreet';
        }

        function criarIconeLeaflet(cor, texto) {
            return L.divIcon({
                className: '',
                html: `<div style="background:${cor};color:white;border-radius:16px;padding:4px 8px;border:2px solid white;box-shadow:0 2px 8px rgba(0,0,0,.25);font-weight:700;font-size:11px;white-space:nowrap;">${texto || ''}</div>`,
                iconSize: [58, 28],
                iconAnchor: [29, 28],
                popupAnchor: [0, -28]
            });
        }

        function limparRotaAtual() {
            if (!routeLayer) return;
            if (isOpenStreet() && mapa && mapa.removeLayer) {
                mapa.removeLayer(routeLayer);
            } else if (routeLayer.setMap) {
                routeLayer.setMap(null);
            }
            routeLayer = null;
        }

        function limparMarcadoresCto() {
            ctoMarkers.forEach(marker => {
                if (isOpenStreet() && mapa && mapa.removeLayer) {
                    mapa.removeLayer(marker);
                } else if (marker.setMap) {
                    marker.setMap(null);
                }
            });
            ctoMarkers = [];
        }

        function distanciaMetrosEntre(a, b) {
            if (!a || !b) return 0;
            if (!isOpenStreet() && window.google && google.maps && google.maps.geometry) {
                return google.maps.geometry.spherical.computeDistanceBetween(
                    new google.maps.LatLng(a.lat, a.lng),
                    new google.maps.LatLng(b.lat, b.lng)
                );
            }
            const raio = 6371000;
            const rad = Math.PI / 180;
            const dLat = (b.lat - a.lat) * rad;
            const dLng = (b.lng - a.lng) * rad;
            const lat1 = a.lat * rad;
            const lat2 = b.lat * rad;
            const h = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) ** 2;
            return 2 * raio * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
        }

        function formatarDistancia(metros) {
            return metros >= 1000 ? (metros / 1000).toFixed(2) + ' km' : Math.round(metros) + ' m';
        }

        // Inicializar mapa
        function inicializarMapa() {
            console.log('Iniciando mapa...');

            if (isOpenStreet()) {
                if (!window.L) {
                    document.getElementById('mapa').innerHTML = '<div style="padding:20px;color:#b91c1c;font-weight:700">Nao foi possivel carregar o OpenStreetMap.</div>';
                    return;
                }

                leafletLayers = {
                    mapa: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }),
                    satelite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                        attribution: 'Tiles &copy; Esri'
                    })
                };

                mapa = L.map('mapa', {
                    center: [-14.2350, -51.9253],
                    zoom: 5,
                    layers: [leafletLayers.mapa],
                    zoomControl: true
                });
                L.control.layers({'Mapa': leafletLayers.mapa, 'Satelite': leafletLayers.satelite}, null, {collapsed: false}).addTo(mapa);
                adicionarEventos();
                carregarCtos();
                return;
            }
            
            if (typeof google === 'undefined' || !google.maps) {
                console.error('Google Maps API não carregada ainda');
                setTimeout(inicializarMapa, 1000);
                return;
            }
            
            const opcoesMapa = {
                center: { lat: -14.2350, lng: -51.9253 },
                zoom: 5,
                styles: [
                    {
                        featureType: 'water',
                        stylers: [{ color: '#b3d9ff' }]
                    },
                    {
                        featureType: 'transit',
                        stylers: [{ visibility: 'off' }]
                    }
                ]
            };

            mapa = new google.maps.Map(document.getElementById('mapa'), opcoesMapa);
            console.log('Mapa criado com sucesso');
            
            geocoder = new google.maps.Geocoder();
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: mapa,
                polylineOptions: {
                    strokeColor: '#667eea',
                    strokeWeight: 4
                }
            });

            adicionarEventos();
            carregarCtos();
        }

        function ctoCapacidade(cto) {
            return parseInt(cto.capacidade || 0, 10) || 0;
        }

        function ctoClientes(cto) {
            return parseInt(cto.clientes || 0, 10) || 0;
        }

        function ctoLivres(cto) {
            const livres = parseInt(cto.livres || 0, 10);
            return isNaN(livres) ? Math.max(ctoCapacidade(cto) - ctoClientes(cto), 0) : livres;
        }

        function ctoInfoHtml(cto) {
            return `
                <strong>${cto.nomecaixa}</strong><br>
                ${cto.endereco || 'Sem endereço'}<br>
                Portas: ${ctoCapacidade(cto)} total | ${ctoLivres(cto)} livres<br>
                Clientes: ${ctoClientes(cto)}
            `;
        }

        function ajustarMapaParaCtos(ctos) {
            if (!ctos || !ctos.length || userLocation) return;
            if (isOpenStreet()) {
                const pontos = [];
                ctos.forEach(cto => {
                    const lat = parseFloat(cto.latitude);
                    const lng = parseFloat(cto.longitude);
                    if (!isNaN(lat) && !isNaN(lng)) pontos.push([lat, lng]);
                });
                if (pontos.length === 1) {
                    mapa.setView(pontos[0], 15);
                } else if (pontos.length > 1) {
                    mapa.fitBounds(L.latLngBounds(pontos), {padding: [40, 40]});
                }
                return;
            }
            const bounds = new google.maps.LatLngBounds();
            let total = 0;
            ctos.forEach(cto => {
                const lat = parseFloat(cto.latitude);
                const lng = parseFloat(cto.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    bounds.extend(new google.maps.LatLng(lat, lng));
                    total++;
                }
            });
            if (total === 1) {
                mapa.setCenter(bounds.getCenter());
                mapa.setZoom(15);
            } else if (total > 1) {
                mapa.fitBounds(bounds);
            }
        }

        // Mostrar mensagem de erro
        function mostrarErro(mensagem) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = mensagem;
            errorDiv.classList.add('show');
        }

        // Limpar mensagem de erro
        function limparErro() {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.classList.remove('show');
        }

        // Carregar todas as CTOs do banco de dados via AJAX
        function carregarCtos() {
            const ctosList = document.getElementById('ctosList');
            ctosList.innerHTML = '<div class="loading">Carregando CTOs...</div>';
            
            // Usar o endpoint direto para carregar CTOs
            const urlCarregarCtos = '/admin/addons/caixas/src/cto/componente/viabilidade/carregarCtos.php';
            
            console.log('Buscando CTOs em:', urlCarregarCtos);
            
            fetch(urlCarregarCtos)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text.substring(0, 100));
                    try {
                        const data = JSON.parse(text);
                        if (data.erro) {
                            mostrarErro('Erro ao carregar CTOs: ' + (data.mensagem || data.detalhes));
                            console.error('Erro ao carregar CTOs:', data);
                            ctosList.innerHTML = '<div class="loading">Erro ao carregar CTOs</div>';
                            return;
                        }
                        allCtos = data;
                        exibirCtos(allCtos);
                        ajustarMapaParaCtos(allCtos);
                        
                        // Calcular distâncias apenas se houver localização do usuário
                        if (userLocation) {
                            calcularDistancias();
                        }
                    } catch (parseError) {
                        mostrarErro('Erro ao processar resposta do servidor: ' + parseError.message);
                        console.error('Erro ao parsear JSON:', parseError, 'texto:', text);
                        ctosList.innerHTML = '<div class="loading">Erro ao processar dados</div>';
                    }
                })
                .catch(error => {
                    mostrarErro('Erro ao carregar CTOs: ' + error.message);
                    console.error('Erro na requisição:', error);
                    ctosList.innerHTML = '<div class="loading">Erro de conexão</div>';
                });
        }

        // Exibir CTOs na lista e no mapa
        function exibirCtos(ctos) {
            console.log('Exibindo ' + ctos.length + ' CTOs');
            
            const ctosList = document.getElementById('ctosList');
            ctosList.innerHTML = '';

            if (ctos.length === 0) {
                ctosList.innerHTML = '<div class="loading">Nenhuma CTO encontrada com coordenadas válidas</div>';
                return;
            }

            // Limpar marcadores antigos
            limparMarcadoresCto();

            ctos.forEach(cto => {
                // Validar coordenadas
                const lat = parseFloat(cto.latitude);
                const lng = parseFloat(cto.longitude);
                
                if (isNaN(lat) || isNaN(lng)) {
                    console.warn('CTO sem coordenadas válidas:', cto);
                    return;
                }

                let marker;
                if (isOpenStreet()) {
                    marker = L.marker([lat, lng], {
                        title: `${cto.nomecaixa} - ${ctoCapacidade(cto)} portas / ${ctoLivres(cto)} livres`,
                        icon: criarIconeLeaflet('#2563eb', `${ctoLivres(cto)}/${ctoCapacidade(cto)}`)
                    }).addTo(mapa);
                    marker._ctoTexto = `${ctoLivres(cto)}/${ctoCapacidade(cto)}`;
                    marker.bindPopup(ctoInfoHtml(cto));
                    marker.on('click', () => {
                        marker.openPopup();
                        selecionarCto(cto);
                    });
                } else {
                    marker = new google.maps.Marker({
                        position: {
                            lat: lat,
                            lng: lng
                        },
                        map: mapa,
                        title: `${cto.nomecaixa} - ${ctoCapacidade(cto)} portas / ${ctoLivres(cto)} livres`,
                        icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                        label: {
                            text: `${ctoLivres(cto)}/${ctoCapacidade(cto)}`,
                            color: '#111827',
                            fontSize: '11px',
                            fontWeight: '700'
                        }
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: ctoInfoHtml(cto)
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(mapa, marker);
                        selecionarCto(cto);
                    });
                }

                ctoMarkers.push(marker);

                // Adicionar item à lista
                const ctoItem = document.createElement('div');
                ctoItem.className = 'cto-item';
                ctoItem.innerHTML = `
                    <div class="cto-name">${cto.nomecaixa}</div>
                    <div class="cto-distance">Distância: -</div>
                    <div class="cto-address">${cto.endereco || 'Sem endereço'}</div>
                    <div class="cto-meta">
                        <span class="cto-pill">${ctoCapacidade(cto)} portas</span>
                        <span class="cto-pill free">${ctoLivres(cto)} livres</span>
                        <span class="cto-pill">${ctoClientes(cto)} clientes</span>
                    </div>
                `;

                ctoItem.addEventListener('click', () => {
                    selecionarCto(cto);
                });

                ctosList.appendChild(ctoItem);
            });
        }

        // Buscar endereço
        function buscarEndereco() {
            console.log('Função buscarEndereco chamada');
            const endereco = document.getElementById('enderecoInput').value.trim();

            if (!endereco) {
                mostrarErro('Por favor, digite um endereço');
                return;
            }

            resolverEnderecoBusca(endereco).then(query => {
            if (isOpenStreet()) {
                buscarEnderecoOpenStreet(query);
                return;
            }
            geocoder.geocode({ address: query, componentRestrictions: { country: 'BR' } }, (results, status) => {
                if (status === 'OK' && results.length > 0) {
                    const location = results[0].geometry.location;
                    userLocation = {
                        lat: location.lat(),
                        lng: location.lng(),
                        endereco: results[0].formatted_address
                    };

                    // Limpar erro
                    limparErro();

                    // Mostrar endereço selecionado
                    const selectedAddr = document.getElementById('selectedAddress');
                    document.getElementById('selectedAddressText').textContent = userLocation.endereco;
                    selectedAddr.classList.add('active');

                    // Centralizar mapa
                    mapa.setCenter(userLocation);
                    mapa.setZoom(14);

                    // Remover marcador anterior
                    if (userMarker) {
                        userMarker.setMap(null);
                    }

                    // Adicionar marcador do usuário
                    userMarker = new google.maps.Marker({
                        position: userLocation,
                        map: mapa,
                        title: 'Sua localização',
                        icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                    });

                    if (allCtos.length > 0) {
                        calcularDistancias();
                    } else {
                        carregarCtos();
                    }
                } else {
                    mostrarErro('Endereço não encontrado. Tente novamente com informações mais precisas.');
                }
            });
            });
        }

        function buscarEnderecoOpenStreet(query) {
            const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&countrycodes=br&q=' + encodeURIComponent(query);
            fetch(url, { credentials: 'omit' })
                .then(resp => resp.ok ? resp.json() : [])
                .then(results => {
                    if (!results || !results.length) {
                        mostrarErro('Endereço não encontrado. Tente novamente com informações mais precisas.');
                        return;
                    }

                    userLocation = {
                        lat: parseFloat(results[0].lat),
                        lng: parseFloat(results[0].lon),
                        endereco: results[0].display_name || query
                    };
                    limparErro();
                    const selectedAddr = document.getElementById('selectedAddress');
                    document.getElementById('selectedAddressText').textContent = userLocation.endereco;
                    selectedAddr.classList.add('active');

                    mapa.setView([userLocation.lat, userLocation.lng], 15);

                    if (userMarker) {
                        mapa.removeLayer(userMarker);
                    }
                    userMarker = L.marker([userLocation.lat, userLocation.lng], {
                        title: 'Localizacao pesquisada',
                        icon: criarIconeLeaflet('#ef4444', 'A')
                    }).addTo(mapa);

                    if (allCtos.length > 0) {
                        calcularDistancias();
                    } else {
                        carregarCtos();
                    }
                })
                .catch(() => mostrarErro('Nao foi possivel buscar o endereco agora.'));
        }

        function aplicarEnderecoOpenStreet(item) {
            if (!item) return;
            userLocation = {
                lat: parseFloat(item.lat),
                lng: parseFloat(item.lon),
                endereco: item.display_name || item.label || ''
            };
            if (isNaN(userLocation.lat) || isNaN(userLocation.lng)) return;

            limparErro();
            document.getElementById('enderecoInput').value = userLocation.endereco;
            const selectedAddr = document.getElementById('selectedAddress');
            document.getElementById('selectedAddressText').textContent = userLocation.endereco;
            selectedAddr.classList.add('active');

            if (userMarker) {
                mapa.removeLayer(userMarker);
            }
            userMarker = L.marker([userLocation.lat, userLocation.lng], {
                title: 'Localizacao pesquisada',
                icon: criarIconeLeaflet('#ef4444', 'A')
            }).addTo(mapa);
            mapa.setView([userLocation.lat, userLocation.lng], 15);

            if (allCtos.length > 0) {
                calcularDistancias();
            } else {
                carregarCtos();
            }
        }

        function buscarSugestoesEnderecoOpenStreet(raw) {
            return resolverEnderecoBusca(raw).then(query => {
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=6&addressdetails=1&countrycodes=br&accept-language=pt-BR&q=' + encodeURIComponent(query);
                return fetch(url, { credentials: 'omit' })
                    .then(resp => resp.ok ? resp.json() : [])
                    .then(items => Array.isArray(items) ? items : []);
            });
        }

        function esconderSugestoesEndereco() {
            const box = document.getElementById('addressSuggestions');
            if (!box) return;
            box.classList.remove('active');
            box.innerHTML = '';
        }

        function renderizarSugestoesEndereco(items) {
            const box = document.getElementById('addressSuggestions');
            if (!box) return;
            box.innerHTML = '';
            if (!items || !items.length) {
                esconderSugestoesEndereco();
                return;
            }

            items.slice(0, 6).forEach(item => {
                const div = document.createElement('div');
                div.className = 'address-suggestion-item';
                const title = document.createElement('div');
                title.className = 'address-suggestion-title';
                title.textContent = item.display_name || 'Endereco encontrado';
                const meta = document.createElement('div');
                meta.textContent = [item.type, item.class].filter(Boolean).join(' / ');
                div.appendChild(title);
                div.appendChild(meta);
                div.addEventListener('mousedown', event => {
                    event.preventDefault();
                    esconderSugestoesEndereco();
                    aplicarEnderecoOpenStreet(item);
                });
                box.appendChild(div);
            });
            box.classList.add('active');
        }

        function resolverEnderecoBusca(raw) {
            const cepMatch = String(raw || '').match(/\b(\d{5})-?(\d{3})\b/);
            if (!cepMatch) {
                return Promise.resolve(raw);
            }
            const cep = cepMatch[1] + cepMatch[2];
            const numeroMatch = String(raw || '').replace(cepMatch[0], '').match(/\b(\d{1,6})\b/);
            const numero = numeroMatch ? numeroMatch[1] : '';
            return fetch('https://viacep.com.br/ws/' + cep + '/json/', { credentials: 'omit' })
                .then(resp => resp.ok ? resp.json() : null)
                .then(data => {
                    if (!data || data.erro) return raw;
                    const partes = [];
                    if (data.logradouro) partes.push(data.logradouro);
                    if (numero) partes.push(numero);
                    if (data.bairro) partes.push(data.bairro);
                    if (data.localidade) partes.push(data.localidade);
                    if (data.uf) partes.push(data.uf);
                    if (data.cep) partes.push(data.cep);
                    return partes.join(', ') || raw;
                })
                .catch(() => raw);
        }

        // Calcular distâncias para todas as CTOs
        function calcularDistancias() {
            if (!userLocation) return;

            const ctosList = document.getElementById('ctosList');
            const items = ctosList.querySelectorAll('.cto-item');

            const ctosComDistancia = allCtos.map((cto, index) => {
                const distance = distanciaMetrosEntre(userLocation, {
                    lat: parseFloat(cto.latitude),
                    lng: parseFloat(cto.longitude)
                });

                return {
                    ...cto,
                    distancia: distance,
                    index: index
                };
            }).sort((a, b) => a.distancia - b.distancia);

            // Atualizar lista com distâncias
            items.forEach((item, idx) => {
                const cto = ctosComDistancia[idx];
                item.querySelector('.cto-distance').textContent = `Distância: ${formatarDistancia(cto.distancia)}`;
                item.dataset.ctoId = cto.id;
                item.dataset.distancia = cto.distancia;
            });

            // Ordenar e reexibir
            exibirCtos(ctosComDistancia.sort((a, b) => a.distancia - b.distancia));

            // Auto-selecionar a CTO mais próxima
            if (ctosComDistancia.length > 0) {
                selecionarCto(ctosComDistancia[0]);
            }
        }

        // Fechar lista de CTOs (ocultar CTOs não selecionadas)
        function fecharLista() {
            console.log('Ocultando CTOs não selecionadas');
            document.querySelectorAll('.cto-item').forEach(item => {
                if (!item.classList.contains('selected')) {
                    item.style.display = 'none';
                }
            });
        }

        // Ver todas as CTOs
        function verTodasCtos() {
            console.log('Mostrando todas as CTOs');
            document.querySelectorAll('.cto-item').forEach(item => {
                item.style.display = 'block';
                item.classList.remove('selected');
            });
            document.getElementById('ctosList').style.display = 'block';
            document.getElementById('fecharListaBtn').style.display = 'inline-block';
            selectedCto = null;
        }

        // Selecionar CTO e traçar rota
        function selecionarCto(cto) {
            console.log('selecionarCto chamado com:', cto);
            console.log('userLocation:', userLocation);
            
            selectedCto = cto;

            // Ocultar todas as CTOs
            document.querySelectorAll('.cto-item').forEach(item => {
                item.classList.remove('selected');
                item.style.display = 'none';
            });

            // Mostrar apenas a CTO selecionada
            const ctosList = document.getElementById('ctosList');
            const items = ctosList.querySelectorAll('.cto-item');
            items.forEach(item => {
                if (item.querySelector('.cto-name').textContent === cto.nomecaixa) {
                    item.classList.add('selected');
                    item.style.display = 'block';
                }
            });

            // Mostrar botão de fechar
            document.getElementById('fecharListaBtn').style.display = 'inline-block';

            if (isOpenStreet()) {
                ctoMarkers.forEach(marker => {
                    if (marker.setIcon) marker.setIcon(criarIconeLeaflet('#2563eb', marker._ctoTexto || ''));
                });
                ctoMarkers.forEach(marker => {
                    const title = marker.options && marker.options.title ? marker.options.title : '';
                    if (String(title).indexOf(cto.nomecaixa) === 0 && marker.setIcon) {
                        marker.setIcon(criarIconeLeaflet('#f59e0b', `${ctoLivres(cto)}/${ctoCapacidade(cto)}`));
                    }
                });
            } else {
                ctoMarkers.forEach(marker => {
                    marker.setIcon('http://maps.google.com/mapfiles/ms/icons/blue-dot.png');
                });

                ctoMarkers.forEach(marker => {
                    if (String(marker.getTitle()).indexOf(cto.nomecaixa) === 0) {
                        marker.setIcon('http://maps.google.com/mapfiles/ms/icons/yellow-dot.png');
                    }
                });
            }

            // Traçar rota se houver localização do usuário
            if (userLocation) {
                console.log('Traçando rota...');
                tracarRota(userLocation, cto);
            } else {
                console.log('userLocation não está definido!');
            }
        }

        // Traçar rota
        function tracarRota(origem, destino) {
            console.log('tracarRota iniciando com origem:', origem, 'destino:', destino);
            limparRotaAtual();

            if (isOpenStreet()) {
                const destinoLatLng = {lat: parseFloat(destino.latitude), lng: parseFloat(destino.longitude)};
                const distancia = distanciaMetrosEntre(origem, destinoLatLng);
                routeLayer = L.polyline([[origem.lat, origem.lng], [destinoLatLng.lat, destinoLatLng.lng]], {
                    color: '#667eea',
                    weight: 5,
                    opacity: 0.85
                }).addTo(mapa);
                document.getElementById('routeDistance').textContent = formatarDistancia(distancia);
                document.getElementById('routeDuration').textContent = Math.max(Math.ceil(distancia / 80), 1) + ' min';
                document.getElementById('routeMode').textContent = 'Linha reta (OpenStreet)';
                document.getElementById('routeInfo').classList.add('active');
                mapa.fitBounds(routeLayer.getBounds(), {padding: [70, 70], maxZoom: 17});
                return;
            }
            
            const request = {
                origin: new google.maps.LatLng(origem.lat, origem.lng),
                destination: new google.maps.LatLng(
                    parseFloat(destino.latitude),
                    parseFloat(destino.longitude)
                ),
                travelMode: google.maps.TravelMode.WALKING
            };

            console.log('Request criado:', request);

            directionsService.route(request, (response, status) => {
                console.log('Callback tracarRota chamado. Status:', status);
                
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);

                    // Obter informações da rota
                    const route = response.routes[0];
                    const leg = route.legs[0];

                    // Extrair distância em metros e converter para km se necessário
                    const distanciaMetros = leg.distance.value;
                    let distanciaFormatada = '';
                    if (distanciaMetros >= 1000) {
                        distanciaFormatada = (distanciaMetros / 1000).toFixed(2) + ' km';
                    } else {
                        distanciaFormatada = distanciaMetros + ' m';
                    }

                    // Extrair tempo
                    const tempo = leg.duration.text;

                    console.log('Distância:', distanciaFormatada, 'Tempo:', tempo);

                    // Preenchendo os campos
                    document.getElementById('routeDistance').textContent = distanciaFormatada;
                    document.getElementById('routeDuration').textContent = tempo;
                    
                    // Mostrar informações da rota
                    const routeInfoDiv = document.getElementById('routeInfo');
                    routeInfoDiv.classList.add('active');
                    
                    // Scroll automático para mostrar o pop-up de rota
                    setTimeout(() => {
                        routeInfoDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);

                    // Ajustar zoom para mostrar rota completa
                    const bounds = new google.maps.LatLngBounds();
                    bounds.extend(leg.start_location);
                    bounds.extend(leg.end_location);
                    mapa.fitBounds(bounds, 70);
                    setTimeout(() => {
                        if (mapa.getZoom() > 17) mapa.setZoom(17);
                        if (mapa.getZoom() < 14) mapa.setZoom(14);
                    }, 150);
                    
                    console.log('Rota traçada com sucesso:', {
                        distancia: distanciaFormatada,
                        tempo: tempo,
                        cto: destino.nomecaixa
                    });
                } else {
                    console.log('Erro na rota. Status:', status);
                    mostrarErro('Erro ao calcular rota. Status: ' + status);
                }
            });
        }

        // Adicionar eventos
        function adicionarEventos() {
            console.log('Adicionando eventos...');
            
            const buscarBtn = document.getElementById('buscarBtn');
            const enderecoInput = document.getElementById('enderecoInput');
            
            if (!buscarBtn) {
                console.error('Botão buscar não encontrado!');
                return;
            }
            
            if (!enderecoInput) {
                console.error('Input endereço não encontrado!');
                return;
            }
            
            console.log('Botão encontrado:', buscarBtn);
            console.log('Input encontrado:', enderecoInput);

            let suggestionsBox = document.getElementById('addressSuggestions');
            if (!suggestionsBox) {
                suggestionsBox = document.createElement('div');
                suggestionsBox.id = 'addressSuggestions';
                suggestionsBox.className = 'address-suggestions';
                enderecoInput.parentNode.appendChild(suggestionsBox);
            }
            
            buscarBtn.addEventListener('click', () => {
                console.log('Botão clicado!');
                esconderSugestoesEndereco();
                buscarEndereco();
            });
            
            enderecoInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    console.log('Enter pressionado!');
                    esconderSugestoesEndereco();
                    buscarEndereco();
                }
            });

            let sugestaoTimer = null;
            let sugestaoSeq = 0;
            enderecoInput.addEventListener('input', () => {
                if (!isOpenStreet()) return;
                const raw = enderecoInput.value.trim();
                clearTimeout(sugestaoTimer);
                if (raw.length < 4) {
                    esconderSugestoesEndereco();
                    return;
                }
                const ticket = ++sugestaoSeq;
                sugestaoTimer = setTimeout(() => {
                    buscarSugestoesEnderecoOpenStreet(raw)
                        .then(items => {
                            if (ticket !== sugestaoSeq) return;
                            renderizarSugestoesEndereco(items);
                        })
                        .catch(() => {
                            if (ticket !== sugestaoSeq) return;
                            esconderSugestoesEndereco();
                        });
                }, 450);
            });

            document.addEventListener('click', event => {
                if (!suggestionsBox.contains(event.target) && event.target !== enderecoInput) {
                    esconderSugestoesEndereco();
                }
            });

            if (!isOpenStreet() && window.google && google.maps && google.maps.places && google.maps.places.Autocomplete) {
                const autocomplete = new google.maps.places.Autocomplete(enderecoInput, {
                    componentRestrictions: { country: 'br' },
                    fields: ['formatted_address', 'geometry', 'name']
                });
                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (!place || !place.geometry || !place.geometry.location) return;
                    enderecoInput.value = place.formatted_address || place.name || enderecoInput.value;
                    buscarEndereco();
                });
            }
            
            console.log('Eventos adicionados com sucesso');
        }

        // Função de callback para quando a API estiver pronta
        function inicializarAposAPI() {
            console.log('Callback da API Google Maps acionado');
            inicializarMapa();
        }

        // Inicializar quando o DOM estiver pronto
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM Carregado');
            setTimeout(() => {
                if (isOpenStreet()) {
                    if (window.L) {
                        console.log('Leaflet disponível');
                        inicializarMapa();
                    } else {
                        console.error('Leaflet não disponível');
                    }
                    return;
                }
                if (typeof google !== 'undefined' && google.maps) {
                    console.log('API disponível');
                    inicializarMapa();
                } else {
                    console.error('API não disponível');
                }
            }, 500);
        });
    </script>
</body>
</html>
