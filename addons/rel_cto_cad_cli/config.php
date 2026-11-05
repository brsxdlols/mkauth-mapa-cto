<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('mka');
    session_start();
}
if (!isset($_SESSION['MKA_Logado'])) {
    http_response_code(403);
    exit('Acesso negado.');
}
require_once __DIR__ . '/../caixas/src/cto/config/database.php';
$link = isset($connection) ? $connection : null;
if (!$link) {
    http_response_code(503);
    exit('Falha ao conectar ao banco de dados.');
}
$cfg_port_olt = false;
