<?php
/** Unified report source. The additional username is its RADIUS identity. */
function relCtoClientesQuery($link, $busca) {
    $busca = mysqli_real_escape_string($link, (string)$busca);
    return "SELECT c.* FROM (
        SELECT 'cliente' AS tipo_registro, p.id AS registro_id,
               p.uuid_cliente, p.nome, p.caixa_herm, p.login,
               p.porta_olt, p.porta_splitter, p.endereco, p.numero,
               p.bairro, p.armario_olt, p.login AS titular_login, p.nome AS titular_nome
        FROM sis_cliente p
        WHERE p.cli_ativado = 's' AND p.caixa_herm IS NOT NULL
        UNION ALL
        SELECT 'adicional' AS tipo_registro, a.id AS registro_id,
               p.uuid_cliente, COALESCE(NULLIF(a.nome, ''), p.nome) AS nome,
               a.caixa_herm, a.username AS login,
               a.porta_olt, a.porta_splitter,
               COALESCE(NULLIF(a.end_endereco, ''), p.endereco) AS endereco,
               COALESCE(NULLIF(a.end_numero, ''), p.numero) AS numero,
               COALESCE(NULLIF(a.end_bairro, ''), p.bairro) AS bairro,
               a.armario_olt, p.login AS titular_login, p.nome AS titular_nome
        FROM sis_adicional a
        INNER JOIN sis_cliente p ON p.login = a.login
        WHERE p.cli_ativado = 's' AND a.caixa_herm IS NOT NULL
    ) c
    WHERE c.nome LIKE '%$busca%' OR c.caixa_herm LIKE '%$busca%'
       OR c.login LIKE '%$busca%' OR c.porta_olt LIKE '%$busca%'
       OR c.porta_splitter LIKE '%$busca%' OR c.endereco LIKE '%$busca%'
       OR c.bairro LIKE '%$busca%' OR c.armario_olt LIKE '%$busca%'
       OR c.titular_login LIKE '%$busca%' OR c.titular_nome LIKE '%$busca%'
    ORDER BY c.caixa_herm ASC, CAST(TRIM(c.porta_splitter) AS UNSIGNED) ASC,
             c.tipo_registro ASC, c.registro_id ASC";
}
