<?php
// Arquivo para carregar CTOs via AJAX
header('Content-Type: application/json; charset=utf-8');

try {
    // Incluir arquivo de configuração do banco de dados
    $db_file = dirname(__FILE__) . '/../../config/database.php';
    if (!file_exists($db_file)) {
        $db_file = dirname(__FILE__) . '/../../config/database.hhvm';
    }
    
    // Carregar configuração do banco
    if (file_exists($db_file)) {
        require_once $db_file;
        if (isset($connection)) {
            $conn = $connection;
        }
    }

    // Se não conseguiu pela inclusão, tentar conexão direta
    if (!isset($conn) || !$conn) {
        // Valores padrão
        $Host = 'localhost';
        $user = 'root';
        $pass = 'vertrigo';
        $db_name = 'mkradius';
        $socket = '/var/run/mysqld/mysqld.sock';

        // Verificar socket
        if (!file_exists($socket)) {
            $socket = null;
        }

        // Conectar com socket ou TCP
        if ($socket) {
            $conn = @mysqli_connect('localhost', $user, $pass, $db_name, 0, $socket);
        } else {
            $conn = @mysqli_connect($Host, $user, $pass, $db_name);
        }
    }

    if (!$conn) {
        throw new Exception('Erro ao conectar ao banco de dados: ' . mysqli_connect_error());
    }

    // Configurar charset
    mysqli_set_charset($conn, "utf8mb4");

    // Buscar CTOs com coordenadas válidas
    $sql = "SELECT id, nome, endereco, latitude, longitude, capacidade FROM mp_caixa 
            WHERE latitude IS NOT NULL AND longitude IS NOT NULL 
            AND CAST(latitude AS DECIMAL(10,6)) != 0
            AND CAST(longitude AS DECIMAL(10,6)) != 0
            ORDER BY nome";
    
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception('Erro ao buscar CTOs: ' . mysqli_error($conn));
    }

    $temAdicionalCaixa = false;
    if ($resTabela = mysqli_query($conn, "SHOW TABLES LIKE 'sis_adicional'")) {
        if (mysqli_num_rows($resTabela) > 0) {
            $resColuna = mysqli_query($conn, "SHOW COLUMNS FROM sis_adicional LIKE 'caixa_herm'");
            if ($resColuna) {
                $temAdicionalCaixa = mysqli_num_rows($resColuna) > 0;
            }
        }
    }

    $ctos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Validar dados
        if (!empty($row['latitude']) && !empty($row['longitude']) && !empty($row['nome'])) {
            $nomeEsc = mysqli_real_escape_string($conn, $row['nome']);
            $clientes = 0;
            if ($resClientes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sis_cliente WHERE caixa_herm = '" . $nomeEsc . "'")) {
                $dadosClientes = mysqli_fetch_assoc($resClientes);
                $clientes += intval($dadosClientes['total'] ?? 0);
            }
            if ($temAdicionalCaixa && $resAdicionais = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sis_adicional WHERE caixa_herm = '" . $nomeEsc . "'")) {
                $dadosAdicionais = mysqli_fetch_assoc($resAdicionais);
                $clientes += intval($dadosAdicionais['total'] ?? 0);
            }
            $capacidade = intval($row['capacidade'] ?? 0);
            $ctos[] = [
                'id' => $row['id'],
                'nomecaixa' => $row['nome'],
                'endereco' => $row['endereco'] ?? '',
                'latitude' => (float)$row['latitude'],
                'longitude' => (float)$row['longitude'],
                'capacidade' => $capacidade,
                'clientes' => $clientes,
                'livres' => max($capacidade - $clientes, 0)
            ];
        }
    }

    // Fechar conexão se foi criada localmente
    if (isset($conn) && !isset($GLOBALS['connection'])) {
        mysqli_close($conn);
    }

    // Retornar CTOs como JSON
    echo json_encode($ctos, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => true,
        'mensagem' => $e->getMessage()
    ]);
}
