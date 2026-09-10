# HECATE

**Plataforma Institucional de Governança e Controle de Impressão**

HECATE é a plataforma institucional destinada a padronizar, controlar e auditar o serviço de impressão nas OM. O produto centraliza identidade, autorização, políticas, cotas P&B/colorida, contratos, liberação segura, auditoria, monitoramento da pilha de impressão e telemetria das impressoras.

O HECATE não é apenas um frontend para SavaPage. Ele é o **plano de governança** do serviço de impressão. SavaPage, CUPS, Keycloak, PostgreSQL e os demais componentes fazem parte da solução, cada um com responsabilidade delimitada.

## Objetivos

- centralizar o fluxo de impressão;
- autenticar usuários com identidade institucional;
- controlar acesso por OM, divisão, grupo e exceção autorizada;
- aplicar cotas independentes para P&B e colorida;
- registrar quem imprimiu, quando, onde e quantas páginas;
- controlar contratos por consumo ou franquia mensal;
- permitir transferência auditada de quotas entre divisões;
- exigir liberação deliberada de jobs retidos;
- impedir, sempre que possível, impressão direta sem rastreabilidade;
- monitorar serviços, filas, impressoras e suprimentos;
- permitir replicação simples em outras OM.

## Arquitetura de referência

```text
Samba AD da OM ----LDAPS----> Keycloak
       |                         |
       |                         v
       +---------------------> HECATE <------ Catálogo MB
                                 |
                                 | políticas, quotas,
                                 | contratos, auditoria,
                                 | autorização e release
                                 v
Cliente ---------------------> SavaPage
                                 |
                                 v
                               CUPS
                                 |
                                 v
                             Impressora
```

### Responsabilidades

- **Samba AD:** identidade institucional; somente leitura.
- **Catálogo MB:** atributos funcionais e organizacionais.
- **Keycloak:** SSO/OIDC e base para MFA futuro.
- **HECATE:** fonte de verdade para política, organização, quota, contrato, aprovação, auditoria e operação.
- **SavaPage:** engine de impressão, retenção, accounting e enforcement.
- **CUPS:** spool local e transporte do job.
- **PostgreSQL:** persistência das aplicações, com databases separados.
- **hecate-agent:** operações privilegiadas locais, descoberta, monitoramento e troubleshooting.
- **Nexus:** distribuição institucional de RPMs, imagens OCI e artefatos homologados.

## Princípios de segurança

- O HECATE não altera usuários, grupos, OUs, GPOs, DNS ou senhas do Samba AD.
- Integração com diretório é somente leitura, preferencialmente via LDAPS.
- O PHP não recebe `sudo` genérico.
- Ações privilegiadas passam pelo `hecate-agent`, com conjunto fechado de operações.
- Nunca escrever diretamente no banco ou spool interno do SavaPage.
- Filas físicas do CUPS não devem ser publicadas diretamente aos clientes.
- Conteúdo de documentos não é arquivado permanentemente; somente metadados operacionais e de auditoria são preservados.

## Liberação segura

Todo job deve passar por retenção e liberação deliberada. O fluxo de referência é:

```text
Usuário envia -> SavaPage retém -> usuário acessa HECATE
-> autenticação + PIN -> validação de política/quota/impressora
-> liberação -> SavaPage -> CUPS -> impressora
```

O PIN pertence ao HECATE, não à impressora. A solução não exige release station e suporta impressoras simples. Sem hardware de proximidade, a liberação comprova autorização deliberada do usuário, não presença física junto ao equipamento.

## Cotas

P&B e colorida são contabilizadas separadamente. Cada quota mantém:

```text
alocado
reservado
consumido
disponível = alocado - reservado - consumido
```

A reserva antecede a liberação para evitar concorrência entre jobs simultâneos.

## Contratos

O HECATE deve suportar:

- contrato por consumo, com valor unitário P&B e colorido;
- contrato por franquia mensal, com volumes incluídos e excedentes P&B/colorido.

A franquia contratual da OM e a distribuição interna de quota por divisão são controles distintos.

## Impressoras e telemetria

O cadastro deve exigir apenas o mínimo necessário, como nome lógico, IP/FQDN e localização. O `hecate-agent` tenta detectar automaticamente fabricante, modelo, serial, capacidades, protocolos, status, contadores e suprimentos.

Ordem preferencial:

```text
IPP/IPPS -> SNMPv3 -> SNMPv2c read-only -> EWS/API -> parser específico -> manual
```

A ausência de telemetria nunca deve bloquear impressão.

## Plataforma

- Oracle Linux conforme matriz homologada pela OM;
- Yii2 Basic;
- Bootstrap 5;
- SavaPage;
- CUPS;
- PostgreSQL;
- Keycloak;
- Podman;
- Samba AD / LDAP;
- Catálogo MB via API REST/Swagger;
- Nexus Repository para distribuição institucional.

## Implantação prevista

Modelo inicial: uma VM dedicada por OM.

```text
Host nativo:
  CUPS
  SavaPage
  PostgreSQL
  hecate-agent

Podman:
  HECATE Web
  Keycloak
```

Experiência de instalação pretendida:

```bash
dnf install hecate
hecate-setup
```

O instalador deve detectar automaticamente hostname, FQDN, IP e DNS e perguntar apenas os parâmetros de infraestrutura realmente necessários.

## Desenvolvimento

O código segue o padrão do `yii2-app-basic`, evitando camadas ou estruturas paralelas desnecessárias.

```text
controllers/
models/
views/
config/
migrations/
assets/
web/
docs/
```

Para ambiente local de desenvolvimento:

```bash
composer install
export HECATE_DB_DSN='pgsql:host=127.0.0.1;port=5432;dbname=hecate'
export HECATE_DB_USER='hecate'
export HECATE_DB_PASSWORD='senha'
php yii migrate
php yii serve
```

## Documentação

- `docs/ARQUITETURA.md` — arquitetura e fluxos.
- `docs/DECISOES.md` — decisões técnicas consolidadas.
- `docs/INTEGRACOES.md` — Samba AD, Catálogo MB, Keycloak, SavaPage e impressoras.
- `docs/SEGURANCA.md` — controles de segurança e auditoria.
- `docs/IMPLANTACAO.md` — modelo de instalação e distribuição.
- `docs/IDENTIDADE-VISUAL.md` — identidade visual e uso dos assets.
- `docs/MVP.md` — escopo do MVP e POCs pendentes.

## Estado atual

O MVP estrutural já está na `main`. As integrações críticas ainda passam por POC/homologação antes de serem consideradas prontas para produção.
