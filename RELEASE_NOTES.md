Corrige a atualização do relatório rel_cto_cad_cli quando addons.class.php é um link simbólico para o carregador global do MK-AUTH. A cópia usa --remove-destination, substituindo o link local sem escrever no arquivo global.

Revisa também as cópias e o rollback, destaca addon.js antes da edição preservando seu link original para a reversão, substitui o link de VERSION antes de gravar e rejeita diretórios simbólicos nos caminhos atualizados. Os backups passam a usar diretórios exclusivos.

Teste de regressão: carregador global de exemplo e SHA256 permanecem intactos após instalação, reinstalação e rollback; inclui links de configuração, VERSION, menu e diretórios. Os testes de instalação, adicionais e filtros continuam passando.

Esta atualização evita novas sobrescritas. Não recupera automaticamente um carregador global já danificado pela v1.1.53; nesse caso, restaure o backup íntegro do MK-AUTH. A recuperação da Evolution foi informada como já concluída, sem alteração por esta entrega.
