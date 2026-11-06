# Alterações

## 1.1.55 — 05/11/2026

Relatório de caixas com cards de CTOs, conexões, adicionais, portas livres e status. Clique na CTO para abrir o modal com portas e clientes; filtre por CTO, OLT, online, offline, adicionais ou conflitos. A opção Só listagem mantém busca e tabela sem os cards.

Adicionais sem porta não ocupam capacidade. Conexões na mesma porta são preservadas e sinalizadas como conflito. Totais respeitam perm_totais e os registros respeitam os grupos do operador; ocupações não verificáveis em perfis restritos não aparecem como portas livres.

Consultas de conexão e histórico agora são feitas em lote. Validado na KMNET com os mesmos 327 registros e 9 adicionais da consulta anterior, em 65–79 ms nas medições da carga de dados (não representa tempo HTTP completo).

Inclui os scripts nativos do topo e dos menus do MK-AUTH. Mantém a proteção de links simbólicos da v1.1.54, backup dos addons e do menu, preservação da configuração e rollback.

Homologação aplicada somente na KMNET. Nenhuma alteração na Uplink. Prévia testada no navegador, em desktop e mobile; testes SQL usam tabelas temporárias, sem alterar clientes reais.


## 1.1.54 — 05/11/2026

Corrige a atualização do relatório rel_cto_cad_cli quando addons.class.php é um link simbólico para o carregador global do MK-AUTH. A cópia usa --remove-destination, substituindo o link local sem escrever no arquivo global.

Revisa também as cópias e o rollback, destaca addon.js antes da edição preservando seu link original para a reversão, substitui o link de VERSION antes de gravar e rejeita diretórios simbólicos nos caminhos atualizados. Os backups passam a usar diretórios exclusivos.

Teste de regressão: carregador global de exemplo e SHA256 permanecem intactos após instalação, reinstalação e rollback; inclui links de configuração, VERSION, menu e diretórios. Os testes de instalação, adicionais e filtros continuam passando.

Esta atualização evita novas sobrescritas. Não recupera automaticamente um carregador global já danificado pela v1.1.53; nesse caso, restaure o backup íntegro do MK-AUTH. A recuperação da Evolution foi informada como já concluída, sem alteração por esta entrega.


## 1.1.53 — 05/11/2026

Instala o relatório de caixas junto com o Mapa de CTO, em `/admin/addons/rel_cto_cad_cli/`, incluindo clientes adicionais e a identificação das portas ocupadas. Cria o atalho Relatório de Caixas no menu Provedor pelo addon.js, sem duplicá-lo em reinstalações.

Preserva o config.php de um relatório já instalado. Instalações novas reutilizam a conexão do Mapa; nenhuma credencial da Uplink é incluída no pacote. Faz backup dos dois addons e do menu; o rollback restaura também o relatório e funciona com addons/addon.js.

Validação: instalação inicial, atualização, reinstalação, preservação de configuração e rollback em ambiente temporário, PHP e testes isolados dos adicionais. Nenhum cadastro de cliente é modificado pelo instalador.


## 1.1.52 — 23/10/2026

Ícones diferentes para os 17 tipos de equipamento cadastrados, incluindo CTO, CEO, FTTH, FTTX e rádio. A prévia aparece abaixo do seletor de tipo no cadastro e na edição e muda automaticamente com a seleção. O mapa usa o mesmo desenho, preservando os números das caixas e as cores de status dos clientes.

Compatível com OpenStreetMap e Google Maps. Mantém filtros, seleção, clientes adicionais e as permissões existentes.

Validado: sintaxe PHP e JavaScript, geração dos 17 ícones, cores de status, numeração, codificação SVG para Google Maps e prévia dinâmica no navegador. Aplicado na Smart Telecom com backup e conferência de hashes, sem alterar registros de caixas ou clientes.

Para instalar, salve o arquivo `.run` no servidor MK-AUTH, confira o SHA256 e execute com `sh`. O instalador faz backup. Não use `curl | bash`.


## 1.1.51 — 13/10/2026

O mapa de clientes respeita Clientes → Totais (`perm_numClientes`). Quando a permissão não está autorizada, omite os contadores Total, Online e Offline. Consulta `sis_perm` pela identidade administrativa atual, com suporte à identidade legada e consulta preparada. Permissão ausente ou falha de consulta mantém os contadores ocultos.

Marcadores e filtros Todos/Online/Offline permanecem disponíveis. A permissão controla a exibição dos contadores; a lista de clientes enviada ao navegador continua disponível para o mapa.

Validado no Visual Net: sintaxe PHP, hashes, permissão ativada/desativada/ausente, identidade atual e legada, renderização sintética e filtros JavaScript com e sem contadores. Testes SQL usam tabela temporária, sem modificar usuários ou clientes reais. Correção aplicada com backup e rollback antes da publicação.
