<?php
// === LICENCIAMENTO DESATIVADO PELO PROPRIETARIO ===
$licenseStatus = [
    'instalada' => true,
    'expirada' => false,
    'mensagem' => 'Licenciamento desativado pelo proprietario'
];

// === ROTEAMENTO ANTECIPADO DOS COMPONENTES ===
// Evita carregar dependencias do dashboard quando a URL ja pediu uma tela interna.
$addon_base = dirname(__FILE__);
$route = isset($_GET['_route']) ? $_GET['_route'] : '';
$component_routes = ['inicio', 'adicionar', 'editar', 'backup', 'maps', 'mapadeclientes', 'mapadectos', 'configurar', 'viabilidade'];

if (!empty($route) && in_array($route, $component_routes)) {
    $app_file = $addon_base . '/src/cto/componente/' . $route . '/index.php';
    if (file_exists($app_file)) {
        include_once $app_file;
        exit();
    }
}

// === CONFIGURAÇÃO DO SERVIDOR ===
require_once dirname(__FILE__) . '/src/ServerConfig.php';
$serverConfig = new ServerConfig();
$admin_url = $serverConfig->getAdminUrl();

// === CONFIGURAÇÃO INICIAL ===
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error.log');

// === VERIFICAÇÃO DE AUTENTICAÇÃO FLEXÍVEL ===
// Usa gestor que detecta qualquer variável de sessão do mk-auth
require_once dirname(__FILE__) . '/src/auth_handler.php';
// AuthHandler::requireAuth(); // Desabilitado para funcionar em novos servidores sem sessão

// === CARREGAR DEPENDÊNCIAS ===
$addon_base = dirname(__FILE__);
require_once $addon_base . '/addons.class.php';

// === CARREGAR CONFIGURAÇÃO DE BANCO DE DADOS ===
$ctos_cadastradas = 0;
$portas_totais = 0;
$portas_livres = 0;
$portas_ativas = 0;
$portas_utilizadas = 0;

// Tentar carregar estatísticas do banco
if (file_exists($addon_base . '/src/cto/config/database.php')) {
    require_once $addon_base . '/src/cto/config/database.php';
    
    if (isset($connection) && $connection) {
        // Contar CTOs cadastradas
        $query_ctos = "SELECT COUNT(*) as total FROM mp_caixa";
        $result_ctos = @mysqli_query($connection, $query_ctos);
        if ($result_ctos) {
            $row = mysqli_fetch_assoc($result_ctos);
            $ctos_cadastradas = intval($row['total']);
        }
        
        // Calcular portas totais (soma das capacidades)
        $query_portas = "SELECT SUM(capacidade) as total FROM mp_caixa";
        $result_portas = @mysqli_query($connection, $query_portas);
        if ($result_portas) {
            $row = mysqli_fetch_assoc($result_portas);
            $portas_totais = intval($row['total']) ?: 0;
        }
        
        // Calcular portas livres por CTO (usando caixa_herm como referência)
        $query_livres = "SELECT SUM(mp.capacidade - COALESCE(cliente_count.total, 0)) as total
                        FROM mp_caixa mp
                        LEFT JOIN (
                            SELECT caixa_herm, COUNT(*) as total 
                            FROM sis_cliente 
                            WHERE caixa_herm IS NOT NULL AND caixa_herm != ''
                            GROUP BY caixa_herm
                        ) cliente_count ON mp.nome = cliente_count.caixa_herm";
        $result_livres = @mysqli_query($connection, $query_livres);
        if ($result_livres) {
            $row = mysqli_fetch_assoc($result_livres);
            $portas_livres = max(0, intval($row['total']) ?: 0);
        }
        
        // Contar clientes online (portas ativas) - usando caixa_herm
        $query_ativas = "SELECT COUNT(*) as total FROM sis_cliente sc
                        INNER JOIN radacct ra ON ra.username = sc.login 
                        WHERE ra.acctstoptime IS NULL
                        AND sc.caixa_herm IS NOT NULL AND sc.caixa_herm != ''";
        $result_ativas = @mysqli_query($connection, $query_ativas);
        if ($result_ativas) {
            $row = mysqli_fetch_assoc($result_ativas);
            $portas_ativas = intval($row['total']) ?: 0;
        }
    }
}


// === CONTROLAR ROTEAMENTO ===
$route = isset($_GET['_route']) ? $_GET['_route'] : '';

// === INCLUIR APLICAÇÃO SE HOUVER ROTA ===
if (!empty($route) && in_array($route, ['inicio', 'adicionar', 'editar', 'backup', 'maps', 'mapadeclientes', 'mapadectos', 'configurar', 'viabilidade'])) {
    $app_file = $addon_base . '/src/cto/componente/' . $route . '/index.php';
    if (file_exists($app_file)) {
        include_once $app_file;
        exit();
    }
}

// === RENDERIZAR DASHBOARD PADRÃO ===
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>MK - AUTH :: <?php echo $Manifest->name; ?></title>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { min-height: 100%; background: #f5f7fb; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; color: #17243b; padding: 32px; min-height: 100vh; }
        button, input { font: inherit; }
        .dashboard-container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 28px; padding: 24px; background: #fff; border: 1px solid #e3e8f0; border-radius: 16px; }
        .header-brand { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .brand-icon { display: grid; place-items: center; width: 52px; height: 52px; flex-shrink: 0; border-radius: 14px; color: #fff; background: #4f46e5; }
        svg { width: 24px; height: 24px; }
        .header h1 { font-size: 26px; line-height: 1.25; letter-spacing: -.6px; font-weight: 700; }
        .header p { margin-top: 5px; color: #64748b; font-size: 14px; }
        .header-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .header-button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px; padding: 10px 14px; border: 1px solid #dde3ee; border-radius: 9px; color: #475569; background: #fff; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .header-button:hover { color: #4338ca; border-color: #b9b5f4; background: #f6f5ff; }
        .header-button.icon-only { width: 42px; padding: 10px; }
        .header-button svg { width: 18px; height: 18px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 30px; }
        .stat-card { position: relative; min-width: 0; padding: 20px; border: 1px solid #e3e8f0; border-radius: 12px; background: #fff; }
        .stat-card .icon { position: absolute; right: 18px; top: 18px; width: 34px; height: 34px; display: grid; place-items: center; border-radius: 9px; color: #6366f1; background: #f0f0ff; }
        .stat-card .icon svg { width: 19px; height: 19px; }
        .stat-card h3 { color: #64748b; font-size: 13px; line-height: 1.4; font-weight: 500; padding-right: 36px; }
        .stat-card .value { margin-top: 14px; color: #24334d; font-size: 32px; line-height: 1; font-weight: 700; letter-spacing: -.6px; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
        .stat-card:nth-child(3) .icon, .stat-card:nth-child(4) .icon { color: #15836b; background: #eaf7f1; }
        .section-heading { font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 12px; }
        .actions-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .action-card { --accent: #4f46e5; --tint: #eeedff; position: relative; display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; gap: 20px; min-height: 148px; min-width: 0; padding: 22px 24px; border: 1px solid #e0e6ef; border-radius: 14px; background: #fff; color: #24334d; text-decoration: none; transition: border-color .15s, box-shadow .15s, background .15s; }
        .action-card:hover { border-color: var(--accent); background: #fdfdff; box-shadow: 0 4px 16px #2533540a; }
        .action-icon { display: grid; place-items: center; width: 44px; height: 44px; background: var(--tint); color: var(--accent); border-radius: 12px; }
        .action-title { font-size: 17px; line-height: 1.4; font-weight: 600; padding-right: 8px; overflow-wrap: anywhere; }
        .action-arrow { position: absolute; right: 24px; top: 30px; color: #94a3b8; font-size: 22px; line-height: 1; }
        .action-card:hover .action-arrow { color: var(--accent); }
        .action-card.clients { --accent: #167bad; --tint: #eaf5fb; }
        .action-card.map { --accent: #b47719; --tint: #fdf4e5; }
        .action-card.viability { --accent: #168367; --tint: #e8f7f0; }
        .action-card.backup { --accent: #526787; --tint: #edf1f7; }
        .action-card.settings { --accent: #8553c0; --tint: #f3ecfb; }
        a:focus-visible, button:focus-visible, input:focus-visible { outline: 3px solid #818cf8; outline-offset: 4px; }
        @media (max-width: 900px) { body { padding: 24px; } .header { align-items: flex-start; } .header h1 { font-size: 23px; } .header-actions { flex-wrap: wrap; justify-content: flex-end; } .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .actions-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 600px) { body { padding: 16px; } .header { flex-direction: column; gap: 18px; padding: 20px; margin-bottom: 20px; } .header h1 { font-size: 21px; } .header p { font-size: 13px; } .brand-icon { width: 44px; height: 44px; } .header-actions { justify-content: flex-start; } .stats-grid { gap: 10px; margin-bottom: 24px; } .stat-card { padding: 16px; } .stat-card .icon { display: none; } .stat-card h3 { padding: 0; font-size: 12px; } .stat-card .value { font-size: 28px; } .actions-grid { grid-template-columns: 1fr; gap: 12px; } .action-card { min-height: 84px; flex-direction: row; align-items: center; justify-content: flex-start; padding: 18px; gap: 16px; } .action-icon { flex-shrink: 0; } .action-title { padding-right: 24px; font-size: 16px; } .action-arrow { right: 18px; top: calc(50% - 11px); } }
        @media (prefers-reduced-motion: reduce) { .action-card { transition: none; } }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <header class="header">
            <div class="header-brand">
                <span class="brand-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span>
                <div><h1>Gerenciamento FTTH</h1><p>Caixas de terminação óptica</p></div>
            </div>
            <div class="header-actions">
                <a href="<?php echo htmlspecialchars($admin_url); ?>" id="btn-voltar" class="header-button">← Voltar ao MK-AUTH</a>
                <button type="button" class="header-button icon-only" onclick="abrirEditorUrl()" title="Editar URL do MK-AUTH" aria-label="Editar URL do MK-AUTH"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M4 17h16"/><circle cx="9" cy="7" r="3" fill="currentColor"/><circle cx="15" cy="17" r="3" fill="currentColor"/></svg></button>
            </div>
        </header>

        <!-- Modal de Edição de URL -->
        <div id="modal-url" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
            <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px; width: 90%;">
                <h2 style="color: #333; margin-bottom: 20px;">Editar URL do MK-AUTH</h2>
                <input type="text" id="input-url" placeholder="https://seu-dominio.com/admin" style="
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #ddd;
                    border-radius: 6px;
                    font-size: 1em;
                    margin-bottom: 20px;
                " value="<?php echo htmlspecialchars($admin_url); ?>">
                <div style="color: #999; font-size: 0.9em; margin-bottom: 20px;">
                    Exemplos: https://seu-dominio.com.br/admin ou http://seu-servidor/admin
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="salvarUrl()" style="
                        background: #10b981;
                        color: white;
                        padding: 12px 24px;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        flex: 1;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                        💾 Salvar
                    </button>
                    <button onclick="fecharEditorUrl()" style="
                        background: #ef4444;
                        color: white;
                        padding: 12px 24px;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        flex: 1;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                        ✕ Cancelar
                    </button>
                </div>
            </div>
        </div>

        <script>
        function abrirEditorUrl() {
            document.getElementById('modal-url').style.display = 'flex';
        }
        
        function fecharEditorUrl() {
            document.getElementById('modal-url').style.display = 'none';
        }
        
        function salvarUrl() {
            var url = document.getElementById('input-url').value.trim();
            
            if (!url) {
                alert('Por favor, digite uma URL');
                return;
            }
            
            // Validar URL básica
            if (!url.includes('://')) {
                alert('URL inválida. Use https:// ou http://');
                return;
            }
            
            // Enviar para servidor
            fetch('<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/src/config_server.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'admin_url=' + encodeURIComponent(url)
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    alert('URL salva com sucesso!');
                    // Atualizar botão
                    document.getElementById('btn-voltar').href = data.url;
                    fecharEditorUrl();
                } else {
                    alert('Erro ao salvar: ' + data.mensagem);
                }
            })
            .catch(error => {
                alert('Erro ao conectar com servidor');
                console.error(error);
            });
        }
        
        // Fechar modal ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fecharEditorUrl();
            }
        });
        </script>
<!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></div>
                <h3>CTOs Cadastradas</h3>
                <div class="value"><?php echo $ctos_cadastradas; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3v5M16 3v5M6 8h12v3a6 6 0 0 1-12 0V8ZM12 17v5"/></svg></div>
                <h3>Portas Totais</h3>
                <div class="value"><?php echo $portas_totais; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></div>
                <h3>Portas Livres</h3>
                <div class="value"><?php echo $portas_livres; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12h5l3-8 4 16 3-8h5"/></svg></div>
                <h3>Portas Ativas</h3>
                <div class="value"><?php echo $portas_ativas; ?></div>
            </div>
        </div>

        <h2 class="section-heading">Acesso rápido</h2>
        <nav class="actions-grid" aria-label="Ferramentas FTTH">
            <a href="?_route=inicio" class="action-card list">
                <span class="action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h4"/></svg></span>
                <span class="action-title">Listar CTOs</span>
                <span class="action-arrow" aria-hidden="true">↗</span>
            </a>
            <a href="?_route=maps" class="action-card clients">
                <span class="action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 4v2"/></svg></span>
                <span class="action-title">Mapa de Clientes</span>
                <span class="action-arrow" aria-hidden="true">↗</span>
            </a>
            <a href="?_route=mapadectos" class="action-card map">
                <span class="action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6ZM9 3v15M15 6v15"/></svg></span>
                <span class="action-title">Mapa de CTOs</span>
                <span class="action-arrow" aria-hidden="true">↗</span>
            </a>
            <a href="?_route=viabilidade" class="action-card viability">
                <span class="action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                <span class="action-title">Viabilidade de Atendimento</span>
                <span class="action-arrow" aria-hidden="true">↗</span>
            </a>
            <a href="?_route=backup" class="action-card backup">
                <span class="action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v7c0 4 16 4 16 0V5M4 12v7c0 4 16 4 16 0v-7"/></svg></span>
                <span class="action-title">Backup de Dados</span>
                <span class="action-arrow" aria-hidden="true">↗</span>
            </a>
            <a href="?_route=configurar" class="action-card settings">
                <span class="action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M4 17h16"/><circle cx="9" cy="7" r="3" fill="currentColor"/><circle cx="15" cy="17" r="3" fill="currentColor"/></svg></span>
                <span class="action-title">Configurações</span>
                <span class="action-arrow" aria-hidden="true">↗</span>
            </a>
        </nav>
    </div>
</body>
</html>
