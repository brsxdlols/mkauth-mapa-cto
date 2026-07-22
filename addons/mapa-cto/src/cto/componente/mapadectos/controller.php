<?php
/**
 * Controller - Componente MAPA DE CTOs
 * Busca dados das CTOs para exibição no mapa
 */

$component_base = dirname(__FILE__);
$ctos_data = array();

if (isset($connection) && $connection) {
    $has_cto_id = false;
    $column_check = mysqli_query($connection, "SHOW COLUMNS FROM sis_cliente LIKE 'cto_id'");
    if ($column_check && mysqli_num_rows($column_check) > 0) {
        $has_cto_id = true;
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
            $cto_nome = mysqli_real_escape_string($connection, $row['nome']);

            $cliente_where = "(caixa_herm = '" . $cto_nome . "' AND caixa_herm IS NOT NULL AND caixa_herm != '')";
            $cliente_where_sc = "(sc.caixa_herm = '" . $cto_nome . "' AND sc.caixa_herm IS NOT NULL AND sc.caixa_herm != '')";
            $adicional_where_sa = "(sa.caixa_herm = '" . $cto_nome . "' AND sa.caixa_herm IS NOT NULL AND sa.caixa_herm != '')";

            if ($has_cto_id) {
                $cliente_where = "((cto_id = " . $cto_id . " AND cto_id IS NOT NULL AND cto_id > 0) OR " . $cliente_where . ")";
                $cliente_where_sc = "((sc.cto_id = " . $cto_id . " AND sc.cto_id IS NOT NULL AND sc.cto_id > 0) OR " . $cliente_where_sc . ")";
            }
            
            $count_sql = "SELECT COUNT(DISTINCT id) as total FROM sis_cliente WHERE " . $cliente_where;
            $count_result = mysqli_query($connection, $count_sql);
            $count_row = $count_result ? mysqli_fetch_assoc($count_result) : ['total' => 0];
            $total_clientes_principal = intval($count_row['total'] ?? 0);

            $count_adicional_sql = "SELECT COUNT(DISTINCT sa.id) as total
                          FROM sis_adicional sa
                          LEFT JOIN sis_cliente scp ON scp.login = sa.login
                          WHERE " . $adicional_where_sa . "
                          AND (scp.id IS NULL OR scp.cli_ativado = 's')";
            $count_adicional_result = mysqli_query($connection, $count_adicional_sql);
            $count_adicional_row = $count_adicional_result ? mysqli_fetch_assoc($count_adicional_result) : ['total' => 0];
            $total_adicionais = intval($count_adicional_row['total'] ?? 0);
            $total_clientes = $total_clientes_principal + $total_adicionais;
            
            $online_sql = "SELECT COUNT(DISTINCT sc.id) as total FROM sis_cliente sc
                          INNER JOIN radacct ra ON ra.username = sc.login 
                          WHERE ra.acctstoptime IS NULL
                          AND " . $cliente_where_sc;
            $online_result = mysqli_query($connection, $online_sql);
            $online_row = $online_result ? mysqli_fetch_assoc($online_result) : ['total' => 0];
            $total_online_principal = intval($online_row['total'] ?? 0);

            $online_adicional_sql = "SELECT COUNT(DISTINCT sa.id) as total
                          FROM sis_adicional sa
                          INNER JOIN radacct ra ON ra.username = sa.username AND ra.acctstoptime IS NULL
                          LEFT JOIN sis_cliente scp ON scp.login = sa.login
                          WHERE " . $adicional_where_sa . "
                          AND (scp.id IS NULL OR scp.cli_ativado = 's')";
            $online_adicional_result = mysqli_query($connection, $online_adicional_sql);
            $online_adicional_row = $online_adicional_result ? mysqli_fetch_assoc($online_adicional_result) : ['total' => 0];
            $total_online = $total_online_principal + intval($online_adicional_row['total'] ?? 0);
            
            $total_offline = max(0, $total_clientes - $total_online);
            
            $clientes_sql = "SELECT sc.id, sc.nome, sc.login, 'Cliente' as tipo_cliente,
                            CASE WHEN ra.radacctid IS NOT NULL THEN 'online' ELSE 'offline' END as status
                            FROM sis_cliente sc
                            LEFT JOIN radacct ra ON ra.username = sc.login AND ra.acctstoptime IS NULL
                            WHERE " . $cliente_where_sc . "
                            ORDER BY sc.nome";
            $clientes_result = mysqli_query($connection, $clientes_sql);
            $clientes_list = array();
            
            if ($clientes_result) {
                while ($cliente = mysqli_fetch_assoc($clientes_result)) {
                    $clientes_list[] = array(
                        'id' => $cliente['id'],
                        'nome' => $cliente['nome'],
                        'login' => $cliente['login'],
                        'status' => $cliente['status'],
                        'tipo' => $cliente['tipo_cliente']
                    );
                }
            }

            $adicionais_sql = "SELECT sa.id,
                            COALESCE(NULLIF(sa.nome, ''), sa.username, sa.login) as nome,
                            sa.username as login,
                            'Adicional' as tipo_cliente,
                            CASE WHEN ra.radacctid IS NOT NULL THEN 'online' ELSE 'offline' END as status
                            FROM sis_adicional sa
                            LEFT JOIN radacct ra ON ra.username = sa.username AND ra.acctstoptime IS NULL
                            LEFT JOIN sis_cliente scp ON scp.login = sa.login
                            WHERE " . $adicional_where_sa . "
                            AND (scp.id IS NULL OR scp.cli_ativado = 's')
                            ORDER BY nome";
            $adicionais_result = mysqli_query($connection, $adicionais_sql);

            if ($adicionais_result) {
                while ($cliente = mysqli_fetch_assoc($adicionais_result)) {
                    $clientes_list[] = array(
                        'id' => $cliente['id'],
                        'nome' => $cliente['nome'],
                        'login' => $cliente['login'],
                        'status' => $cliente['status'],
                        'tipo' => $cliente['tipo_cliente']
                    );
                }
            }
            
            // Calcular portas livres
            $portas_utilizadas = $total_clientes;
            $portas_livres = $row['capacidade'] - $portas_utilizadas;
            
            // Validar coordenadas
            $lat = floatval($row['latitude']);
            $lng = floatval($row['longitude']);
            
            if (empty($lat) || empty($lng) || $lat == 0 || $lng == 0) {
                continue; // Pular CTOs sem coordenadas válidas
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
}

// Converter para JSON para uso no JavaScript
$ctos_json = json_encode($ctos_data);

// Renderizar a view
require_once __DIR__ . '/mapadectos.view.php';
