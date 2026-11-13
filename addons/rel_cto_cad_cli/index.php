<?php
// Relatório de caixas. Créditos originais: Aleff Meykson.
require_once __DIR__.'/addons.class.php';
if(session_status()===PHP_SESSION_NONE){session_name('mka');session_start();}
if(empty($_SESSION['MKA_Logado'])){http_response_code(403);exit('Acesso negado.');}
require __DIR__.'/config.php';
require_once __DIR__.'/report-model.php';
mysqli_set_charset($link,'utf8mb4');
$reportError='';$reportData=null;
try{
    $scope=relCtoScope($link,$_SESSION);
    if(!$scope['valid']){http_response_code(403);exit('Perfil administrativo não identificado.');}
    $started=microtime(true);$reportData=relCtoLoadReport($link,$scope);
    $reportData['signalMode']=!empty($cfg_port_olt);
    $reportData['clientExtension']=is_file(__DIR__.'/../../cliente_alt.hhvm')?'hhvm':'php';
    $reportData['elapsedMs']=(int)round((microtime(true)-$started)*1000);
}catch(Exception $e){error_log('Relatório CTO: '.$e->getMessage());$reportError='Não foi possível carregar o relatório. Tente atualizar a página.';}
header('Cache-Control: no-store');
?>
<!doctype html><html lang="pt-BR" class="has-navbar-fixed-top"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Relatório de Caixas</title><link rel="stylesheet" href="../../estilos/mk-auth.css"><link rel="stylesheet" href="report.css?v=1.1.56">
<script src="js/jquery-3.6.0.min.js"></script>
<script src="../../scripts/mk-auth.js"></script>
</head><body>
<?php include __DIR__.'/../../topo.php'; ?>
<main id="cto-report" class="report-shell" aria-busy="true">
<header class="report-heading"><div><p class="report-eyebrow">REDE ÓPTICA</p><h1>Relatório de caixas</h1><p>CTOs, portas e conexões em um só lugar.</p></div><nav class="report-actions" aria-label="Ações do relatório"><a class="report-button" href="../caixas/">← Voltar ao painel</a><a class="report-button" href="../caixas/?_route=mapadectos">Abrir mapa</a><button class="report-button" type="button" id="report-reload">Atualizar</button><button class="report-button light" type="button" id="report-print">Imprimir</button></nav></header>
<section class="report-toolbar" aria-label="Busca e filtros">
<label class="report-search">Buscar CTO, cliente ou login<input id="report-search" type="search" placeholder="Ex.: CAIXA 20 ou login do adicional" autocomplete="off"></label>
<label>CTO<select id="report-cto"><option value="">Todas as CTOs</option></select></label>
<label>OLT<select id="report-olt"><option value="">Todas as OLTs</option></select></label>
<div class="report-view" role="group" aria-label="Modo de exibição"><button type="button" data-view="overview" aria-pressed="true">Visão geral</button><button type="button" data-view="list" aria-pressed="false">Só listagem</button></div>
</section>
<div class="report-filterbar"><div class="report-chips" role="group" aria-label="Filtrar conexões"><button data-filter="all" aria-pressed="true">Todos</button><button data-filter="online" aria-pressed="false">Online</button><button data-filter="offline" aria-pressed="false">Offline</button><button data-filter="additional" aria-pressed="false">Adicionais</button><button data-filter="free" aria-pressed="false">Portas livres</button><button data-filter="conflict" aria-pressed="false">Conflitos de porta</button></div><button type="button" class="report-clear" id="report-clear">Limpar filtros</button></div>
<p id="report-scope" class="report-scope" aria-live="polite"></p>
<section id="report-summary" class="report-summary" aria-label="Resumo clicável"></section>
<section id="report-boxes-section" class="report-section"><div class="report-section-title"><h2>Suas CTOs</h2><p>Clique em uma caixa para ver portas e clientes.</p></div><div id="report-boxes" class="report-boxes"></div></section>
<section class="report-section report-list-section"><div class="report-section-title"><h2>Listagem de portas e clientes</h2><p>Principais e adicionais, com a ocupação física de cada porta.</p></div><div id="report-list"></div></section>
<button type="button" id="report-more" class="report-button report-more" hidden>Mostrar mais CTOs</button>
<div id="report-empty" class="report-empty" hidden><h2>Nenhum resultado</h2><p>Tente outro nome de CTO ou limpe os filtros.</p><button type="button" class="report-button" data-clear>Limpar filtros</button></div>
<footer id="report-updated" class="report-updated"></footer></main>
<dialog id="report-dialog" class="report-dialog" aria-labelledby="report-dialog-title"><header class="report-dialog-heading"><div><p class="report-eyebrow">DETALHES DA CTO</p><h2 id="report-dialog-title"></h2><p id="report-dialog-description"></p></div><button type="button" id="report-dialog-close" aria-label="Fechar detalhes">×</button></header><div id="report-dialog-body"></div></dialog>
<?php if($reportError): ?><p class="report-error" role="alert"><?php echo htmlspecialchars($reportError,ENT_QUOTES,'UTF-8'); ?></p><?php endif; ?>
<noscript><p class="report-error">Ative o JavaScript para usar os filtros e a listagem interativa.</p></noscript>
<script id="report-data" type="application/json"><?php echo json_encode($reportData,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE); ?></script><script src="report.js?v=1.1.56"></script>
<?php include __DIR__.'/../../baixo.php'; ?>
<script src="../../menu.js.php"></script>
</body></html>
