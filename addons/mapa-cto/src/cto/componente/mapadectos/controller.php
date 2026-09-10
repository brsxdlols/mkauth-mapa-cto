<?php
/**
 * Controller - Componente MAPA DE CTOs
 * Busca dados das CTOs para exibição no mapa
 */

$component_base = dirname(__FILE__);
$ctos_data = array();
$todos_clientes_data = array();
$todos_clientes_index = array();

function mapa_cto_json_response($data) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function mapa_cto_coluna_existe($connection, $tabela, $coluna) {
    $tabela = mysqli_real_escape_string($connection, $tabela);
    $coluna = mysqli_real_escape_string($connection, $coluna);
    $result = mysqli_query($connection, "SHOW COLUMNS FROM `" . $tabela . "` LIKE '" . $coluna . "'");
    return $result && mysqli_num_rows($result) > 0;
}

function mapa_cto_tabela_existe($connection, $tabela) {
    $tabela = mysqli_real_escape_string($connection, $tabela);
    $result = mysqli_query($connection, "SHOW TABLES LIKE '" . $tabela . "'");
    return $result && mysqli_num_rows($result) > 0;
}

function mapa_cto_cliente_tem_coord($cliente) {
    $lat = isset($cliente['latitude']) ? floatval($cliente['latitude']) : 0;
    $lng = isset($cliente['longitude']) ? floatval($cliente['longitude']) : 0;
    return !empty($lat) && !empty($lng) && $lat != 0 && $lng != 0;
}

function mapa_cto_normalizar_coord_cliente($cliente) {
    $lat = isset($cliente['latitude']) ? trim((string)$cliente['latitude']) : '';
    $lng = isset($cliente['longitude']) ? trim((string)$cliente['longitude']) : '';
    if (($lat === '' || $lng === '' || floatval($lat) == 0 || floatval($lng) == 0) && !empty($cliente['coordenadas'])) {
        $coordenadas = trim((string)$cliente['coordenadas']);
        if (preg_match('/(-?\d+(?:[\.,]\d+)?)\s*[,; ]\s*(-?\d+(?:[\.,]\d+)?)/', $coordenadas, $matches)) {
            $lat = str_replace(',', '.', $matches[1]);
            $lng = str_replace(',', '.', $matches[2]);
        }
    }
    $cliente['latitude'] = $lat;
    $cliente['longitude'] = $lng;
    return $cliente;
}

function mapa_cto_adicionar_todos_clientes(&$todos_clientes_data, &$todos_clientes_index, $cliente) {
    if (empty($cliente['id'])) return;
    $cliente = mapa_cto_normalizar_coord_cliente($cliente);
    $tipo = !empty($cliente['tipo']) ? $cliente['tipo'] : 'Cliente';
    $key = $tipo . ':' . $cliente['id'];
    if (!isset($todos_clientes_index[$key])) {
        $todos_clientes_index[$key] = count($todos_clientes_data);
        $todos_clientes_data[] = $cliente;
        return;
    }

    $pos = $todos_clientes_index[$key];
    if (!mapa_cto_cliente_tem_coord($todos_clientes_data[$pos]) && mapa_cto_cliente_tem_coord($cliente)) {
        $todos_clientes_data[$pos] = $cliente;
    }
}

if (isset($connection) && $connection && isset($_POST['acao_mapa_cto'])) {
    $acao_mapa_cto = isset($_POST['acao_mapa_cto']) ? (string)$_POST['acao_mapa_cto'] : '';

    if ($acao_mapa_cto === 'atribuir_cliente_cto') {
        $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
        $cliente_tipo = isset($_POST['cliente_tipo']) ? trim((string)$_POST['cliente_tipo']) : 'Cliente';
        $cto_id = isset($_POST['cto_id']) ? intval($_POST['cto_id']) : 0;
        $porta = isset($_POST['porta']) ? trim((string)$_POST['porta']) : '';

        if ($cliente_id <= 0 || $cto_id <= 0 || $porta === '') {
            mapa_cto_json_response(array('ok' => false, 'message' => 'Cliente, CTO ou porta invalida.'));
        }

        $cto_result = mysqli_query($connection, "SELECT id, nome FROM mp_caixa WHERE id = " . $cto_id . " LIMIT 1");
        $cto = $cto_result ? mysqli_fetch_assoc($cto_result) : null;
        if (!$cto) {
            mapa_cto_json_response(array('ok' => false, 'message' => 'CTO nao encontrada.'));
        }

        $cto_nome = mysqli_real_escape_string($connection, $cto['nome']);
        $porta_db = mysqli_real_escape_string($connection, $porta);
        $is_adicional = strtolower($cliente_tipo) === 'adicional';

        if ($is_adicional) {
            if (!mapa_cto_tabela_existe($connection, 'sis_adicional') || !mapa_cto_coluna_existe($connection, 'sis_adicional', 'caixa_herm')) {
                mapa_cto_json_response(array('ok' => false, 'message' => 'Tabela/campo de adicional nao encontrado.'));
            }
            $set = "caixa_herm = '" . $cto_nome . "'";
            if (mapa_cto_coluna_existe($connection, 'sis_adicional', 'porta_splitter')) {
                $set .= ", porta_splitter = '" . $porta_db . "'";
            }
            $ok = mysqli_query($connection, "UPDATE sis_adicional SET " . $set . " WHERE id = " . $cliente_id . " LIMIT 1");
        } else {
            if (!mapa_cto_coluna_existe($connection, 'sis_cliente', 'caixa_herm')) {
                mapa_cto_json_response(array('ok' => false, 'message' => 'Campo caixa_herm nao encontrado em sis_cliente.'));
            }
            $set = "caixa_herm = '" . $cto_nome . "'";
            if (mapa_cto_coluna_existe($connection, 'sis_cliente', 'porta_splitter')) {
                $set .= ", porta_splitter = '" . $porta_db . "'";
            }
            if (mapa_cto_coluna_existe($connection, 'sis_cliente', 'cto_id')) {
                $set .= ", cto_id = " . $cto_id;
            }
            $ok = mysqli_query($connection, "UPDATE sis_cliente SET " . $set . " WHERE id = " . $cliente_id . " LIMIT 1");
        }

        if (!$ok) {
            mapa_cto_json_response(array('ok' => false, 'message' => 'Erro ao gravar: ' . mysqli_error($connection)));
        }

        mapa_cto_json_response(array('ok' => true, 'message' => 'Cliente atrelado com sucesso.'));
    }

    if ($acao_mapa_cto === 'remover_cliente_cto') {
        $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
        $cliente_tipo = isset($_POST['cliente_tipo']) ? trim((string)$_POST['cliente_tipo']) : 'Cliente';

        if ($cliente_id <= 0) {
            mapa_cto_json_response(array('ok' => false, 'message' => 'Cliente invalido.'));
        }

        $is_adicional = strtolower($cliente_tipo) === 'adicional';
        if ($is_adicional) {
            if (!mapa_cto_tabela_existe($connection, 'sis_adicional') || !mapa_cto_coluna_existe($connection, 'sis_adicional', 'caixa_herm')) {
                mapa_cto_json_response(array('ok' => false, 'message' => 'Tabela/campo de adicional nao encontrado.'));
            }
            $set = "caixa_herm = ''";
            if (mapa_cto_coluna_existe($connection, 'sis_adicional', 'porta_splitter')) {
                $set .= ", porta_splitter = ''";
            }
            $ok = mysqli_query($connection, "UPDATE sis_adicional SET " . $set . " WHERE id = " . $cliente_id . " LIMIT 1");
        } else {
            if (!mapa_cto_coluna_existe($connection, 'sis_cliente', 'caixa_herm')) {
                mapa_cto_json_response(array('ok' => false, 'message' => 'Campo caixa_herm nao encontrado em sis_cliente.'));
            }
            $set = "caixa_herm = ''";
            if (mapa_cto_coluna_existe($connection, 'sis_cliente', 'porta_splitter')) {
                $set .= ", porta_splitter = ''";
            }
            if (mapa_cto_coluna_existe($connection, 'sis_cliente', 'cto_id')) {
                $set .= ", cto_id = NULL";
            }
            $ok = mysqli_query($connection, "UPDATE sis_cliente SET " . $set . " WHERE id = " . $cliente_id . " LIMIT 1");
        }

        if (!$ok) {
            mapa_cto_json_response(array('ok' => false, 'message' => 'Erro ao remover: ' . mysqli_error($connection)));
        }

        mapa_cto_json_response(array('ok' => true, 'message' => 'Cliente removido da CTO e porta.'));
    }
}

if (isset($connection) && $connection) {
    $has_cto_id = false;
    $column_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_cliente LIKE 'cto_id'");
    if ($column_check && mysqli_num_rows($column_check) > 0) {
        $has_cto_id = true;
    }
    $has_sis_adicional = false;
    $has_sis_adicional_caixa = false;
    $has_sis_adicional_porta = false;
    $has_sis_adicional_latitude = false;
    $has_sis_adicional_longitude = false;
    $has_cliente_porta = false;
    $has_cliente_latitude = false;
    $has_cliente_longitude = false;
    $has_cliente_coordenadas = false;
    $cliente_porta_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_cliente LIKE 'porta_splitter'");
    $has_cliente_porta = $cliente_porta_check && mysqli_num_rows($cliente_porta_check) > 0;
    $cliente_latitude_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_cliente LIKE 'latitude'");
    $has_cliente_latitude = $cliente_latitude_check && mysqli_num_rows($cliente_latitude_check) > 0;
    $cliente_longitude_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_cliente LIKE 'longitude'");
    $has_cliente_longitude = $cliente_longitude_check && mysqli_num_rows($cliente_longitude_check) > 0;
    $cliente_coordenadas_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_cliente LIKE 'coordenadas'");
    $has_cliente_coordenadas = $cliente_coordenadas_check && mysqli_num_rows($cliente_coordenadas_check) > 0;
    $table_check = mysqli_query($connection, "SHOW TABLES LIKE 'sis_adicional'");
    if ($table_check && mysqli_num_rows($table_check) > 0) {
        $has_sis_adicional = true;
        $adicional_caixa_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_adicional LIKE 'caixa_herm'");
        $has_sis_adicional_caixa = $adicional_caixa_check && mysqli_num_rows($adicional_caixa_check) > 0;
        $adicional_porta_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_adicional LIKE 'porta_splitter'");
        $has_sis_adicional_porta = $adicional_porta_check && mysqli_num_rows($adicional_porta_check) > 0;
        $adicional_latitude_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_adicional LIKE 'latitude'");
        $has_sis_adicional_latitude = $adicional_latitude_check && mysqli_num_rows($adicional_latitude_check) > 0;
        $adicional_longitude_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_adicional LIKE 'longitude'");
        $has_sis_adicional_longitude = $adicional_longitude_check && mysqli_num_rows($adicional_longitude_check) > 0;
    }

    // Load associations in bulk; preserve SQL collation and ID-or-name matching.
    $map_join_principal = "(sc.caixa_herm = c.nome AND sc.caixa_herm IS NOT NULL AND sc.caixa_herm != '')";
    if ($has_cto_id) $map_join_principal = "((sc.cto_id = c.id AND sc.cto_id > 0) OR " . $map_join_principal . ")";
    $map_principais = array(); $map_adicionais = array();
            $cliente_porta_select = $has_cliente_porta ? "sc.porta_splitter" : "''";
            $cliente_latitude_select = $has_cliente_latitude ? "sc.latitude" : "''";
            $cliente_longitude_select = $has_cliente_longitude ? "sc.longitude" : "''";
            $cliente_coordenadas_select = $has_cliente_coordenadas ? "sc.coordenadas" : "''";
            $clientes_sql = "SELECT c.id as map_cto_id, (ra.radacctid IS NOT NULL) as map_online, sc.id, sc.nome, sc.login,
                            " . $cliente_porta_select . " as porta_splitter,
                            " . $cliente_latitude_select . " as latitude,
                            " . $cliente_longitude_select . " as longitude,
                            " . $cliente_coordenadas_select . " as coordenadas,
                            sc.cli_ativado,
                            'Cliente' as tipo_cliente,
                            CASE
                                WHEN LOWER(COALESCE(sc.cli_ativado, '')) <> 's' THEN 'desativado'
                                WHEN ra.radacctid IS NOT NULL THEN 'online'
                                ELSE 'offline'
                            END as status
                            FROM sis_cliente sc INNER JOIN mp_caixa c ON " . $map_join_principal . "
                            LEFT JOIN (SELECT username, MAX(radacctid) AS radacctid FROM radacct WHERE acctstoptime IS NULL GROUP BY username) ra ON ra.username = sc.login
                            
                            ORDER BY sc.nome";

    $bulk = mysqli_query($connection, $clientes_sql);
    if (!$bulk) throw new Exception('Falha ao carregar clientes do mapa');
    while ($item = mysqli_fetch_assoc($bulk)) $map_principais[$item['map_cto_id']][] = $item;
    if ($has_sis_adicional && $has_sis_adicional_caixa) {
                $adicional_porta_select = $has_sis_adicional_porta ? "sa.porta_splitter" : "''";
                $adicional_latitude_select = $has_sis_adicional_latitude ? "sa.latitude" : "''";
                $adicional_longitude_select = $has_sis_adicional_longitude ? "sa.longitude" : "''";
                $adicionais_sql = "SELECT c.id as map_cto_id, (ra.radacctid IS NOT NULL) as map_online, sa.id,
                                COALESCE(NULLIF(sa.nome, ''), sa.username, sa.login) as nome,
                                sa.username as login,
                                " . $adicional_porta_select . " as porta_splitter,
                                " . $adicional_latitude_select . " as latitude,
                                " . $adicional_longitude_select . " as longitude,
                                'Adicional' as tipo_cliente,
                                CASE WHEN ra.radacctid IS NOT NULL THEN 'online' ELSE 'offline' END as status
                                FROM sis_adicional sa INNER JOIN mp_caixa c ON sa.caixa_herm = c.nome AND sa.caixa_herm IS NOT NULL AND sa.caixa_herm != ''
                                LEFT JOIN (SELECT username, MAX(radacctid) AS radacctid FROM radacct WHERE acctstoptime IS NULL GROUP BY username) ra ON ra.username = sa.username
                                LEFT JOIN sis_cliente scp ON scp.login = sa.login
                                WHERE 1=1
                                AND (scp.id IS NULL OR scp.cli_ativado = 's')
                                ORDER BY nome";

        $bulk = mysqli_query($connection, $adicionais_sql);
        if (!$bulk) throw new Exception('Falha ao carregar adicionais do mapa');
        while ($item = mysqli_fetch_assoc($bulk)) $map_adicionais[$item['map_cto_id']][] = $item;
    }

    // Buscar todas as CTOs com dados de clientes
    $sql = "SELECT 
                c.id,
                c.nome,
                c.endereco,
                c.latitude,
                c.longitude,
                c.capacidade,
                c.tipo,
                c.sinal,
                c.olt,
                c.fsp
            FROM mp_caixa c
            ORDER BY c.nome";
    
    $result = mysqli_query($connection, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cto_id = (int)$row['id'];
            $clientes_list = array();
            $map_ids = array(); $map_online_ids = array();
            foreach (($map_principais[$cto_id] ?? array()) as $cliente) {
                $map_ids['p:' . $cliente['id']] = true;
                if ($cliente['map_online']) $map_online_ids['p:' . $cliente['id']] = true;
                    $clientes_list[] = mapa_cto_normalizar_coord_cliente(array(
                        'id' => $cliente['id'],
                        'nome' => $cliente['nome'],
                        'login' => $cliente['login'],
                        'porta' => $cliente['porta_splitter'] ?? '',
                        'latitude' => $cliente['latitude'] ?? '',
                        'longitude' => $cliente['longitude'] ?? '',
                        'coordenadas' => $cliente['coordenadas'] ?? '',
                        'status' => $cliente['status'],
                        'desativado' => (strtolower($cliente['cli_ativado'] ?? '') !== 's') ? 1 : 0,
                        'tipo' => $cliente['tipo_cliente']
                    ));

            }
            foreach (($map_adicionais[$cto_id] ?? array()) as $cliente) {
                $map_ids['a:' . $cliente['id']] = true;
                if ($cliente['map_online']) $map_online_ids['a:' . $cliente['id']] = true;
                        $clientes_list[] = array(
                            'id' => $cliente['id'],
                            'nome' => $cliente['nome'],
                            'login' => $cliente['login'],
                            'porta' => $cliente['porta_splitter'] ?? '',
                            'latitude' => $cliente['latitude'] ?? '',
                            'longitude' => $cliente['longitude'] ?? '',
                            'status' => $cliente['status'],
                            'desativado' => 0,
                            'tipo' => $cliente['tipo_cliente']
                        );

            }
            $total_clientes = count($map_ids);
            $total_online = count($map_online_ids);
            $total_offline = max(0, $total_clientes - $total_online);

            // Calcular portas livres
            $portas_utilizadas = $total_clientes;
            $portas_livres = $row['capacidade'] - $portas_utilizadas;
            
            // Validar coordenadas
            $lat = floatval($row['latitude']);
            $lng = floatval($row['longitude']);
            
            if (empty($lat) || empty($lng) || $lat == 0 || $lng == 0) {
                continue; // Pular CTOs sem coordenadas válidas
            }
            
            foreach ($clientes_list as $cliente_mapa) {
                $cliente_mapa['caixa_herm'] = $row['nome'];
                mapa_cto_adicionar_todos_clientes($todos_clientes_data, $todos_clientes_index, $cliente_mapa);
            }

            $ctos_data[] = array(
                'id' => $cto_id,
                'nome' => $row['nome'],
                'endereco' => $row['endereco'],
                'latitude' => $lat,
                'longitude' => $lng,
                'capacidade' => intval($row['capacidade']),
                'tipo' => $row['tipo'],
                'sinal' => $row['sinal'],
                'olt' => $row['olt'],
                'fsp' => $row['fsp'],
                'total_clientes' => intval($total_clientes),
                'clientes_online' => intval($total_online),
                'clientes_offline' => intval($total_offline),
                'portas_utilizadas' => intval($portas_utilizadas),
                'portas_livres' => intval($portas_livres),
                'clientes' => $clientes_list
            );
        }
    }

    $todos_cliente_porta_select = $has_cliente_porta ? "sc.porta_splitter" : "''";
    $todos_cliente_latitude_select = $has_cliente_latitude ? "sc.latitude" : "''";
    $todos_cliente_longitude_select = $has_cliente_longitude ? "sc.longitude" : "''";
    $todos_cliente_coordenadas_select = $has_cliente_coordenadas ? "sc.coordenadas" : "''";
    $todos_clientes_sql = "SELECT sc.id, sc.nome, sc.login, sc.caixa_herm,
                        " . $todos_cliente_porta_select . " as porta_splitter,
                        " . $todos_cliente_latitude_select . " as latitude,
                        " . $todos_cliente_longitude_select . " as longitude,
                        " . $todos_cliente_coordenadas_select . " as coordenadas,
                        sc.cli_ativado,
                        CASE
                            WHEN LOWER(COALESCE(sc.cli_ativado, '')) <> 's' THEN 'desativado'
                            WHEN ra.radacctid IS NOT NULL THEN 'online'
                            ELSE 'offline'
                        END as status
                        FROM sis_cliente sc
                        LEFT JOIN (SELECT username, MAX(radacctid) AS radacctid FROM radacct WHERE acctstoptime IS NULL GROUP BY username) ra ON ra.username = sc.login
                        ORDER BY sc.nome
                        LIMIT 5000";
    $todos_clientes_result = mysqli_query($connection, $todos_clientes_sql);
    if ($todos_clientes_result) {
        while ($cliente = mysqli_fetch_assoc($todos_clientes_result)) {
            mapa_cto_adicionar_todos_clientes($todos_clientes_data, $todos_clientes_index, array(
                'id' => $cliente['id'],
                'nome' => $cliente['nome'],
                'login' => $cliente['login'],
                'caixa_herm' => $cliente['caixa_herm'] ?? '',
                'porta' => $cliente['porta_splitter'] ?? '',
                'latitude' => $cliente['latitude'] ?? '',
                'longitude' => $cliente['longitude'] ?? '',
                'coordenadas' => $cliente['coordenadas'] ?? '',
                'status' => $cliente['status'],
                'desativado' => (strtolower($cliente['cli_ativado'] ?? '') !== 's') ? 1 : 0,
                'tipo' => 'Cliente'
            ));
        }
    }
}

// Converter para JSON para uso no JavaScript
$ctos_json = json_encode($ctos_data);
$todos_clientes_json = json_encode($todos_clientes_data);

// Renderizar a view
require_once __DIR__ . '/mapadectos.view.php';
