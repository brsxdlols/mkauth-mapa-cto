# Alterações

## 1.1.52 — 23/10/2026

Ícones diferentes para os 17 tipos de equipamento cadastrados, incluindo CTO, CEO, FTTH, FTTX e rádio. A prévia aparece abaixo do seletor de tipo no cadastro e na edição e muda automaticamente com a seleção. O mapa usa o mesmo desenho, preservando os números das caixas e as cores de status dos clientes.

Compatível com OpenStreetMap e Google Maps. Mantém filtros, seleção, clientes adicionais e as permissões existentes.

Validado: sintaxe PHP e JavaScript, geração dos 17 ícones, cores de status, numeração, codificação SVG para Google Maps e prévia dinâmica no navegador. Aplicado na Smart Telecom com backup e conferência de hashes, sem alterar registros de caixas ou clientes.

Para instalar, salve o arquivo `.run` no servidor MK-AUTH, confira o SHA256 e execute com `sh`. O instalador faz backup. Não use `curl | bash`.


## 1.1.51 — 13/10/2026

O mapa de clientes respeita Clientes → Totais (`perm_numClientes`). Quando a permissão não está autorizada, omite os contadores Total, Online e Offline. Consulta `sis_perm` pela identidade administrativa atual, com suporte à identidade legada e consulta preparada. Permissão ausente ou falha de consulta mantém os contadores ocultos.

Marcadores e filtros Todos/Online/Offline permanecem disponíveis. A permissão controla a exibição dos contadores; a lista de clientes enviada ao navegador continua disponível para o mapa.

Validado no Visual Net: sintaxe PHP, hashes, permissão ativada/desativada/ausente, identidade atual e legada, renderização sintética e filtros JavaScript com e sem contadores. Testes SQL usam tabela temporária, sem modificar usuários ou clientes reais. Correção aplicada com backup e rollback antes da publicação.
