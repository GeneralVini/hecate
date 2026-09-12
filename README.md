# HECATE

**Plataforma Institucional de Governança e Controle de Impressão**

![HECATE — Governança e Controle de Impressão](public/branding/hecate-hero.jpg)

HECATE é a plataforma institucional destinada a padronizar, governar, controlar e auditar o serviço de impressão nas OM. O escopo previsto abrange identidade, autorização, políticas, papéis, cotas P&B/colorida, contratos, aprovações, liberação segura, auditoria, indicadores, monitoramento e telemetria. Essas capacidades estão em desenvolvimento; sua validação é acompanhada na [EAP](docs/EAP.md).

O HECATE não é um inventário de ferramentas. A governança está acima dos componentes técnicos e define **quem decide, quais regras se aplicam, quem pode alterar essas regras e como verificar se o que foi definido está funcionando**.

```text
HECATE
Governança e Controle de Impressão
        ↓
Políticas · Papéis · Cotas · Aprovações · Auditoria · Indicadores
        ↓
Identidade | Controle | Dados
        ↓
Keycloak/Samba AD | SavaPage/CUPS | PostgreSQL/Podman
```

Keycloak, Samba AD, SavaPage, CUPS, PostgreSQL, Podman e demais componentes são mecanismos de implementação. O HECATE organiza a decisão, a política, a rastreabilidade e o acompanhamento do serviço.

## Estado atual

A branch `yii3` está em estágio de **POC**, sem prontidão demonstrada para o MVP ou produção. Há base HTTP/DI, cadastro e consultas de inventário com SQL e DTOs, além de um esqueleto de reserva e solicitação de release.

O login permanece demonstrativo. O gateway de release configurado é indisponível por padrão; não há integração SavaPage homologada. A suíte versionada contém dois smoke tests e não comprova os fluxos HTTP, a persistência, a concorrência ou a recuperação de falhas.

A prioridade é demonstrar uma operação autenticada, autorizada, persistida e auditada, seguida de um job retido liberado com reserva e accounting reconciliados. Consulte o [resumo executivo](docs/RESUMO-EXECUTIVO.md) e os critérios da [EAP](docs/EAP.md), fonte única de acompanhamento do MVP.

## Direção arquitetural

**Yii3 + monólito modular + DDD pragmático. A complexidade deve ser justificada pelo domínio.**

Organizar por responsabilidade, preferir SQL parametrizado via Yii DB/Command e usar DTOs específicos nas fronteiras que os justifiquem. ActiveRecord não deve ser o modelo compartilhado da aplicação; uso pontual fica restrito à infraestrutura e exige justificativa. Não criar camadas, repositories genéricos ou entidades duplicadas por convenção.

As decisões e seus motivos estão em [DECISOES.md](docs/DECISOES.md) e nos [ADRs](docs/adr/README.md). As orientações operacionais ficam em [AGENTS.md](AGENTS.md).

## Arquitetura de referência

```text
Samba AD da OM ----LDAPS----> Keycloak
       |                         |
       |                         v
       +---------------------> HECATE <------ Catálogo MB
                                 |
                                 | políticas, papéis, cotas,
                                 | contratos, aprovações,
                                 | auditoria e indicadores
                                 v
Cliente ---------------------> SavaPage
                                 |
                                 v
                               CUPS
                                 |
                                 v
                             Impressora
```

Responsabilidades principais:

- **Samba AD:** identidade institucional; acesso somente leitura.
- **Catálogo MB:** atributos funcionais e organizacionais.
- **Keycloak:** SSO/OIDC e base para MFA/federação futura.
- **HECATE:** fonte de verdade para política, organização, papéis, cotas, contratos, aprovação, auditoria, indicadores e operação.
- **SavaPage:** engine de impressão, retenção, accounting e enforcement.
- **CUPS:** spool local e transporte do job.
- **PostgreSQL:** persistência das aplicações, com databases e owners separados.
- **Podman:** execução conteinerizada dos componentes definidos para essa camada.
- **hecate-agent:** operações privilegiadas locais, descoberta, monitoramento e troubleshooting.
- **Nexus:** distribuição institucional de RPMs, imagens OCI e artefatos homologados; não é CI/CD.

## Identidade visual

A linguagem visual atual usa azul-marinho profundo, dourado, Hécate, chave, tocha, lua tríplice e três portais que representam os domínios **Identidade**, **Controle** e **Dados**. O cenário do Rio de Janeiro, com Cristo Redentor e Pão de Açúcar, integra a composição institucional.

Os assets oficiais ficam em `public/branding/`:

```text
logo-horizontal.png
logo-vertical.png
symbol.png
favicon-16x16.png
favicon-32x32.png
favicon-48x48.png
favicon-180x180.png
favicon-192x192.png
favicon-512x512.png
login-background.jpg
login-background.png
dashboard-background.jpg
hecate-hero.jpg
```

A interface Yii3 segue a mesma concepção: superfícies escuras, realces dourados, sidebar retrátil, topbar operacional, camada de governança visível e rodapé institucional com estrela, slogan, `CTIM - YYYY` e crédito discreto.

Consulte `docs/IDENTIDADE-VISUAL.md` e `public/branding/README.md`.

## Plataforma web

A branch `yii3` utiliza o template oficial **Yii3 Web Application** (`yiisoft/app`) como referência estrutural. O CI está configurado para PHP 8.5. O Composer declara PHP 8.2–8.5, mas a compatibilidade da faixa completa depende do lockfile e de validação específica; não representa homologação automática. A aplicação usa HTTP PSR-7/PSR-17, middleware PSR-15, container DI e roteamento explícito.

Estrutura presente no repositório (a organização modular está em transição):

```text
assets/                 assets-fonte da aplicação
config/
  common/               parâmetros, rotas e DI compartilhados
  console/              configuração da aplicação console
  environments/         parâmetros dev/test/prod
  web/                  pipeline e dependências HTTP
public/                 document root e branding público
src/
  Migration/            migrations Yii3
  Model/                models ActiveRecord legados; não são padrão para código novo
  Printing/             cadastro, consultas e esqueleto de release
  Quota/                regra inicial e persistência de reserva
  IdentityAccess/       representação de ator; não implementa login OIDC
  Audit/                escrita de eventos do esqueleto
  Monitoring/           consultas e DTOs dos indicadores
  Shared/               componentes compartilhados
  Web/                  actions, templates e layouts da aplicação web
tests/                   testes automatizados
runtime/                 arquivos temporários em execução
yii                      entry point console
```

Não são utilizados controllers, models e views no formato Yii2. Cada endpoint web é implementado como action/handler invocável em `src/Web`, com dependências recebidas pelo container.

## Banco de dados

O HECATE usa PostgreSQL pelos componentes `yiisoft/db` e `yiisoft/db-pgsql`, com `ConnectionInterface` injetada nos componentes de consulta e persistência. A evolução prioriza SQL explícito com bindings. A dependência `yiisoft/active-record` e os models em `src/Model` ainda existem como legado em transição; sua presença não altera a decisão arquitetural.

Variáveis locais:

```bash
export HECATE_DB_HOST=127.0.0.1
export HECATE_DB_PORT=5432
export HECATE_DB_NAME=hecate
export HECATE_DB_USER=hecate
export HECATE_DB_PASSWORD='senha'
```

As migrations ficam em `src/Migration` e usam `yiisoft/db-migration`. Existem a migration inicial e a do esqueleto de release. Presença no código não comprova aplicação ou validação em uma instância PostgreSQL; esses critérios permanecem pendentes na EAP.

## Primeira execução da branch Yii3

```bash
git clone https://github.com/GeneralVini/hecate.git
cd hecate
git switch yii3
make setup
```

O `composer.lock` é obrigatório e está versionado. O bootstrap executa `composer install` exclusivamente a partir do lockfile; ausência do arquivo interrompe a preparação do ambiente para evitar resolução não reproduzível de dependências.

Após configurar um banco de desenvolvimento, aplicar as migrations pendentes:

```bash
./yii migrate:up
```

Para iniciar o servidor de desenvolvimento:

```bash
APP_ENV=dev APP_DEBUG=1 composer serve
```

## Qualidade

O baseline de qualidade permanece obrigatório na branch Yii3:

```bash
composer qa
```

Executa:

```text
PHPCS / PSR-12
PHPStan nível 8
Psalm
PHPUnit
```

Comandos individuais:

```bash
composer lint
composer stan
composer psalm
composer test
make qa
```

O SonarQube não é requisito para o desenvolvimento local nem para o pipeline básico. Pode ser incorporado futuramente como dashboard centralizado, histórico, dívida técnica e Quality Gate.

## Requisitos de segurança

Os itens abaixo são requisitos de entrega, não uma declaração de controles integralmente implementados. CSRF está conectado ao fluxo web; autenticação institucional e autorização integrada ainda precisam ser demonstradas.

- integração LDAP/AD somente leitura, preferencialmente por LDAPS;
- PHP sem `sudo` genérico;
- ações privilegiadas somente pelo `hecate-agent`, com operações fechadas;
- sem escrita direta no banco ou spool interno do SavaPage;
- filas físicas do CUPS não expostas diretamente aos clientes sempre que possível;
- autorização validada no servidor;
- CSRF no fluxo web;
- dependências explícitas via DI em vez de service locator global;
- logs e trilhas de auditoria sem conteúdo de documentos ou segredos.

## Fluxo previsto de liberação segura

```text
Usuário envia -> SavaPage retém -> usuário acessa HECATE
-> autenticação + PIN -> validação de política/cota/impressora
-> liberação -> SavaPage -> CUPS -> impressora
```

A integração de release deverá usar interface suportada pelo SavaPage. O HECATE não manipula diretamente banco, spool ou interfaces internas não suportadas.

## Cotas

A regra de negócio prevê contabilização separada de P&B e colorida:

```text
disponível = alocado - reservado - consumido
```

A reserva deve anteceder a liberação e ser protegida por transação e controle de concorrência. O esqueleto atual ainda exige testes PostgreSQL e integração com accounting. Timeout de release representa resultado desconhecido: não autoriza devolver reserva nem repetir o envio automaticamente.

## Contratos

O escopo de contratos prevê:

- contrato por consumo, com valor unitário P&B e colorido;
- contrato por franquia mensal, com volumes incluídos e excedentes P&B/colorido.

A franquia contratual da OM e a distribuição interna de cota por divisão são controles distintos.

## Impressoras e telemetria

O cadastro exige apenas os dados mínimos. O `hecate-agent` fará a descoberta técnica utilizando, conforme disponibilidade:

```text
IPP/IPPS -> SNMPv3 -> SNMPv2c read-only -> EWS/API -> parser específico -> manual
```

A ausência de telemetria não deve bloquear impressão.

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

## Documentação

- [RESUMO-EXECUTIVO.md](docs/RESUMO-EXECUTIVO.md) — direção, estado atual e bloqueios.
- [ddd.md](docs/ddd.md) — diretrizes detalhadas.
- [ADRs](docs/adr/README.md) — decisões e racional.
- [AGENTS.md](AGENTS.md) — regras para alterações no projeto.
- `docs/ARQUITETURA.md` — arquitetura e fluxos.
- `docs/DECISOES.md` — decisões técnicas consolidadas.
- `docs/EAP.md` — fonte única de acompanhamento da entrega, incluindo escopo, POCs e critérios de aceite do MVP.
- `docs/QUALIDADE-CODIGO.md` — qualidade, análise estática, segurança e compliance técnico.
- `docs/AMBIENTE-DESENVOLVIMENTO.md` — ambiente de desenvolvimento.
- `docs/DOCUMENTACAO-CODIGO.md` — convenções de documentação.
- `docs/INTEGRACOES.md` — integrações institucionais e de impressão.
- `docs/SEGURANCA.md` — controles de segurança e auditoria.
- `docs/IMPLANTACAO.md` — instalação e distribuição.
- `docs/IDENTIDADE-VISUAL.md` — identidade visual, governança da composição e uso dos assets.

## Estado da branch Yii3

O lockfile está versionado. A consolidação documental não conclui homologações técnicas: validação das migrations, fluxos HTTP, autenticação, autorização, integração e concorrência deve ter evidência registrada na [EAP](docs/EAP.md). Um resultado de QA anterior não certifica automaticamente alterações posteriores.
