<?php
/** Clientes > Totais: mesma chave nativa do MK-AUTH, sem liberar por perfil. */
function mapaClientesPodeMostrarTotais($connection, $session) {
    $usuario = !empty($session['MKA_Usuario']) ? $session['MKA_Usuario'] : (isset($session['MM_Usuario']) ? $session['MM_Usuario'] : '');
    if (!is_string($usuario) || $usuario === '' || !$connection) {
        return false;
    }
    $statement = null;
    try {
        $statement = $connection->prepare("SELECT 1 FROM sis_perm WHERE nome = 'perm_numClientes' AND usuario = ? AND permissao = 'sim' LIMIT 1");
        if (!$statement || !$statement->bind_param('s', $usuario) || !$statement->execute() || !$statement->store_result()) {
            return false;
        }
        return $statement->num_rows > 0;
    } catch (Exception $e) {
        error_log('Mapa de clientes: falha ao consultar perm_numClientes');
        return false;
    } finally {
        if ($statement) {
            $statement->close();
        }
    }
}
