Instala o relatório de caixas junto com o Mapa de CTO, em `/admin/addons/rel_cto_cad_cli/`, incluindo clientes adicionais e a identificação das portas ocupadas. Cria o atalho Relatório de Caixas no menu Provedor pelo addon.js, sem duplicá-lo em reinstalações.

Preserva o config.php de um relatório já instalado. Instalações novas reutilizam a conexão do Mapa; nenhuma credencial da Uplink é incluída no pacote. Faz backup dos dois addons e do menu; o rollback restaura também o relatório e funciona com addons/addon.js.

Validação: instalação inicial, atualização, reinstalação, preservação de configuração e rollback em ambiente temporário, PHP e testes isolados dos adicionais. Nenhum cadastro de cliente é modificado pelo instalador.
