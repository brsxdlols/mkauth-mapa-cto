<?php
header('Content-Type: application/json; charset=utf-8');

$config = dirname(__DIR__) . '/config/database.php';
if (!file_exists($config)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Configuração do banco não encontrada']);
    exit;
}

require_once $config;

if (!isset($connection) || !$connection) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Sem conexão com o banco']);
    exit;
}

function norm_porta($porta) {
    $porta = trim((string)$porta);
    if ($porta === '') {
        return '';
    }
    if (ctype_digit($porta)) {
        return (string)intval($porta);
    }
    return strtolower($porta);
}

function out_porta($porta, $capacidade) {
    return str_pad((string)$porta, ($capacidade >= 10 ? 2 : 1), '0', STR_PAD_LEFT);
}

$usadas = [];
$sqlUsadas = "
    SELECT caixa_herm, porta_splitter
    FROM sis_cliente
    WHERE caixa_herm IS NOT NULL
      AND TRIM(caixa_herm) <> ''
      AND porta_splitter IS NOT NULL
      AND TRIM(porta_splitter) <> ''
";

if ($rs = $connection->query($sqlUsadas)) {
    while ($row = $rs->fetch_assoc()) {
        $cto = trim((string)$row['caixa_herm']);
        $porta = norm_porta($row['porta_splitter']);
        if ($cto !== '' && $porta !== '') {
            $usadas[$cto][$porta] = true;
        }
    }
    $rs->free();
}

if ($rsTab = $connection->query("SHOW TABLES LIKE 'sis_adicional'")) {
    if ($rsTab->num_rows > 0) {
        $sqlAdicionais = "
            SELECT caixa_herm, porta_splitter
            FROM sis_adicional
            WHERE caixa_herm IS NOT NULL
              AND TRIM(caixa_herm) <> ''
              AND porta_splitter IS NOT NULL
              AND TRIM(porta_splitter) <> ''
        ";
        if ($rs = $connection->query($sqlAdicionais)) {
            while ($row = $rs->fetch_assoc()) {
                $cto = trim((string)$row['caixa_herm']);
                $porta = norm_porta($row['porta_splitter']);
                if ($cto !== '' && $porta !== '') {
                    $usadas[$cto][$porta] = true;
                }
            }
            $rs->free();
        }
    }
    $rsTab->free();
}

$ctos = [];
$sqlCtos = "
    SELECT id, nome, endereco, capacidade, olt, tipo, sinal
    FROM mp_caixa
    ORDER BY nome
";

if ($rs = $connection->query($sqlCtos)) {
    while ($row = $rs->fetch_assoc()) {
        $nome = trim((string)$row['nome']);
        $capacidade = max(0, intval($row['capacidade']));
        $portasLivres = [];
        $usadasCto = isset($usadas[$nome]) ? $usadas[$nome] : [];

        for ($i = 1; $i <= $capacidade; $i++) {
            if (!isset($usadasCto[(string)$i])) {
                $portasLivres[] = out_porta($i, $capacidade);
            }
        }

        $ctos[] = [
            'id' => intval($row['id']),
            'nome' => $nome,
            'endereco' => (string)$row['endereco'],
            'capacidade' => $capacidade,
            'usadas' => max(0, $capacidade - count($portasLivres)),
            'livres' => count($portasLivres),
            'portas_livres' => $portasLivres,
            'olt' => (string)$row['olt'],
            'tipo' => (string)$row['tipo'],
            'sinal' => (string)$row['sinal'],
        ];
    }
    $rs->free();
}

echo json_encode(['ok' => true, 'ctos' => $ctos], JSON_UNESCAPED_UNICODE);
