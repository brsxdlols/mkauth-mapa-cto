Relatório de caixas com cards de CTOs, conexões, adicionais, portas livres e status. Clique na CTO para abrir o modal com portas e clientes; filtre por CTO, OLT, online, offline, adicionais ou conflitos. A opção Só listagem mantém busca e tabela sem os cards.

Adicionais sem porta não ocupam capacidade. Conexões na mesma porta são preservadas e sinalizadas como conflito. Totais respeitam perm_totais e os registros respeitam os grupos do operador; ocupações não verificáveis em perfis restritos não aparecem como portas livres.

Consultas de conexão e histórico agora são feitas em lote. Validado na KMNET com os mesmos 327 registros e 9 adicionais da consulta anterior, em 65–79 ms nas medições da carga de dados (não representa tempo HTTP completo).

Inclui os scripts nativos do topo e dos menus do MK-AUTH. Mantém a proteção de links simbólicos da v1.1.54, backup dos addons e do menu, preservação da configuração e rollback.

Homologação aplicada somente na KMNET. Nenhuma alteração na Uplink. Prévia testada no navegador, em desktop e mobile; testes SQL usam tabelas temporárias, sem alterar clientes reais.
