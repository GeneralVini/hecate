# HECATE

**Plataforma Institucional de Governança e Controle de Impressão**

HECATE é uma solução para padronizar o controle de impressão nas OM, com foco em identidade, autorização, cotas P&B/colorida, contratos, auditoria, monitoramento do stack e descoberta/telemetria de impressoras.

## Stack proposto

- Oracle Linux conforme versão suportada pela OM
- Yii2 Basic + Bootstrap 5
- SavaPage
- CUPS
- PostgreSQL
- Keycloak
- Podman
- Samba AD da OM, somente leitura
- Catálogo MB via API REST/Swagger
- Nexus para distribuição institucional

## Princípios

- O HECATE não altera usuários, grupos, GPO, OU ou DNS do Samba AD.
- O SavaPage permanece como motor de impressão/accounting/enforcement.
- O CUPS fica no fluxo interno de spool e transporte.
- O conteúdo dos documentos não é arquivado; somente metadados operacionais são preservados.
- P&B e colorida possuem cotas e contabilização independentes.
- A liberação de job será deliberada pelo usuário via HECATE, com PIN, sem release station.
- O monitoramento de impressoras tenta automaticamente IPP/IPPS, SNMP e EWS/API, sem impedir impressão em caso de falha de telemetria.

## Estrutura do MVP

O código segue o padrão do `yii2-app-basic`, evitando criar camadas e diretórios paralelos ao template.

- `controllers/`
- `models/`
- `views/`
- `config/`
- `migrations/`
- `assets/`
- `web/`
- `docs/`

## Executar localmente

```bash
composer install
export HECATE_DB_DSN='pgsql:host=127.0.0.1;port=5432;dbname=hecate'
export HECATE_DB_USER='hecate'
export HECATE_DB_PASSWORD='senha'
php yii migrate
php yii serve
```

## Branch do MVP

O primeiro MVP está sendo desenvolvido em `feature/mvp`.

Consulte `docs/ARQUITETURA.md`, `docs/MVP.md` e `docs/INTEGRACOES.md` para o desenho atual.
