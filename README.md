# MK-AUTH Mapa CTO

Addon separado para instalar o Gerenciador FTTH / Mapa de CTOs no MK-AUTH.

## O que instala

- Addon em `/opt/mk-auth/admin/addons/caixas`.
- Menu no arquivo `addon.js` do MK-AUTH apontando para `admin/addons/caixas/?_route=painel`.
- Backup automatico antes de sobrescrever arquivos.
- Rollback por backup gerado em `/root/backups`.

## Recursos incluidos

- Mapa de CTOs responsivo.
- Selecao automatica entre Google Maps e OpenStreet conforme configuracao do MK-AUTH.
- Fallback para OpenStreet quando Google/chave falhar.
- Alternancia Mapa/Satelite com ultima escolha salva no navegador.
- Busca de endereco com sugestoes enquanto digita.
- Consulta ViaCEP quando CEP for informado.
- Marcador arrastavel para ajuste fino de latitude/longitude.
- Painel de informacoes da CTO em tela cheia dentro do mapa.

## Instalacao direta

No servidor MK-AUTH, execute como `root`:

```bash
curl -fsSL https://raw.githubusercontent.com/brsxdlols/mkauth-mapa-cto/main/installers/github-install.sh | sh
```

## Instalacao por arquivo `.run`

No Windows, gere o pacote:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\installers\build-release.ps1
```

Copie o arquivo gerado de `dist/` para o MK-AUTH e execute:

```bash
sh /tmp/mkauth-mapa-cto-1.0.0.run
```

## Rollback

Use o backup informado no final da instalacao:

```bash
sh installers/rollback.sh /root/backups/mkauth-mapa-cto-AAAAMMDD-HHMMSS-v1.0.0
```

