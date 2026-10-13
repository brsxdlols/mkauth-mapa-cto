# Alterações

## 1.1.51 — 13/10/2026

O mapa de clientes respeita Clientes → Totais (`perm_numClientes`). Quando a permissão não está autorizada, omite os contadores Total, Online e Offline. Consulta `sis_perm` pela identidade administrativa atual, com suporte à identidade legada e consulta preparada. Permissão ausente ou falha de consulta mantém os contadores ocultos.

Marcadores e filtros Todos/Online/Offline permanecem disponíveis. A permissão controla a exibição dos contadores; a lista de clientes enviada ao navegador continua disponível para o mapa.

Validado no Visual Net: sintaxe PHP, hashes, permissão ativada/desativada/ausente, identidade atual e legada, renderização sintética e filtros JavaScript com e sem contadores. Testes SQL usam tabela temporária, sem modificar usuários ou clientes reais. Correção aplicada com backup e rollback antes da publicação.
