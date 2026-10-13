<?php
if (PHP_SAPI !== 'cli') exit(1);
require $argv[1] . '/totals_permission.php';
require '/opt/mk-auth/admin/addons/caixas/src/cto/config/database.php';
$db = new mysqli($Host, $user, $pass, $db_name, 0, $socket);
function checkTotals($ok, $label) {
    if (!$ok) throw new RuntimeException('FAIL: '.$label);
    echo 'PASS: '.$label."\n";
}
// A temporary table shadows the real table only in this isolated connection.
$db->query('CREATE TEMPORARY TABLE sis_perm(nome VARCHAR(50), usuario VARCHAR(50), permissao VARCHAR(3))');
$db->query("INSERT INTO sis_perm VALUES('perm_numClientes','fixture_allowed','sim'),('perm_numClientes','fixture_denied','nao'),('perm_totais','fixture_other','sim'),('perm_numClientes','fixture_literal%','sim')");
checkTotals(mapaClientesPodeMostrarTotais($db,array('MKA_Usuario'=>'fixture_allowed')), 'permission enabled');
checkTotals(!mapaClientesPodeMostrarTotais($db,array('MKA_Usuario'=>'fixture_denied')), 'permission disabled');
checkTotals(!mapaClientesPodeMostrarTotais($db,array('MKA_Usuario'=>'fixture_missing')), 'permission missing');
checkTotals(!mapaClientesPodeMostrarTotais($db,array('MKA_Usuario'=>'fixture_other')), 'general totals does not authorize client totals');
checkTotals(mapaClientesPodeMostrarTotais($db,array('MM_Usuario'=>'fixture_allowed')), 'legacy identity');
checkTotals(!mapaClientesPodeMostrarTotais($db,array('MKA_Usuario'=>'fixture_denied','MM_Usuario'=>'fixture_allowed')), 'current identity has priority');
checkTotals(!mapaClientesPodeMostrarTotais($db,array()), 'identity missing');
checkTotals(!mapaClientesPodeMostrarTotais(null,array('MKA_Usuario'=>'fixture_allowed')), 'connection missing');
checkTotals(!mapaClientesPodeMostrarTotais($db,array('MKA_Usuario'=>'fixture_%')), 'identity has exact matching');
checkTotals(!mapaClientesPodeMostrarTotais($db,array('MKA_Usuario'=>"' OR 1=1 --")), 'identity cannot inject SQL');
function renderTotalsFixture($view, $mostrar_totais_clientes) {
    $clientes_data=array(); $total_online=0; $total_offline=0; $api_key='fixture';
    $source=file_get_contents($view);
    $source=preg_replace('/<\?php readfile\(.*?\); \?>/', '', $source);
    ob_start(); eval('?>'.$source); return ob_get_clean();
}
foreach (array(false,true) as $allowed) {
    $html=renderTotalsFixture($argv[1].'/maps.view.php',$allowed);
    foreach (array('total-clientes','total-online','total-offline') as $id) {
        checkTotals((strpos($html,'id="'.$id.'"')!==false)===$allowed,'render '.$id.' permission '.($allowed?'on':'off'));
    }
    checkTotals(strpos($html,'id="btn-online"')!==false && strpos($html,'id="btn-offline"')!==false,'filters preserved');
}
$db->close();
