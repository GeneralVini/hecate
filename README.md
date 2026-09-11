# HECATE

**Plataforma Institucional de Governança e Controle de Impressão**

HECATE é a plataforma institucional destinada a padronizar, controlar e auditar o serviço de impressão nas OM. A solução centraliza identidade, autorização, políticas, cotas P&B/colorida, contratos, liberação segura, auditoria, monitoramento da pilha de impressão e telemetria das impressoras.

O HECATE é o plano de governança do serviço de impressão. SavaPage, CUPS, Keycloak, PostgreSQL e os demais componentes têm responsabilidades delimitadas e são integrados sem acoplamento indevido.

## Arquitetura de referência

```text
Samba AD da OM ----LDAPS----> Keycloak
       |                         |
       |                         v
       +---------------------> HECATE <------ Catálogo MB
                                 |
                                 | políticas, cotas,
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

Responsabilidades principais:

- **Samba AD:** identidade institucional; acesso somente leitura.
- **Catálogo MB:** atributos funcionais e organizacionais.
- **Keycloak:** SSO/OIDC e base para MFA/federação futura.
- **HECATE:** fonte de verdade para política, organização, cotas, contratos, aprovação, auditoria e operação.
- **SavaPage:** engine de impressão, retenção, accounting e enforcement.
- **CUPS:** spool local e transporte do job.
- **PostgreSQL:** persistência das aplicações, com databases e owners separados.
- **hecate-agent:** operações privilegiadas locais, descoberta, monitoramento e troubleshooting.
- **Nexus:** distribuição institucional de RPMs, imagens OCI e artefatos homologados; não é CI/CD.

## Plataforma web

A branch `yii3` utiliza o template oficial **Yii3 Web Application** (`yiisoft/app`) como referência estrutural. A aplicação usa PHP 8.2–8.5, PSR-7/PSR-17 para HTTP, middleware PSR-15, container DI, roteamento explícito e componentes desacoplados.

Estrutura principal:

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
  Model/                ActiveRecord do domínio persistente
  Shared/               componentes compartilhados
  Web/                  actions, templates e layout da aplicação web
tests/                   testes automatizados
runtime/                 arquivos temporários em execução
yii                      entry point console
```

Não são utilizados controllers, models e views no formato Yii2. Cada endpoint web é implementado como action/handler invocável em `src/Web`, com dependências recebidas pelo container.

## Banco de dados

O HECATE usa PostgreSQL pelo `yiisoft/db-pgsql` e `yiisoft/active-record`. A conexão é resolvida pelo container através de `Yiisoft\Db\Connection\ConnectionInterface`.

Variáveis locais:

```bash
export HECATE_DB_HOST=127.0.0.1
export HECATE_DB_PORT=5432
export HECATE_DB_NAME=hecate
export HECATE_DB_USER=hecate
export HECATE_DB_PASSWORD='senha'
```

As migrations ficam em `src/Migration` e usam `yiisoft/db-migration`.

## Primeira execução da branch Yii3

```bash
git clone https://github.com/GeneralVini/hecate.git
cd hecate
git switch yii3
make setup
```

O `composer.lock` é obrigatório e está versionado. O bootstrap executa `composer install` exclusivamente a partir do lockfile; ausência do arquivo interrompe a preparação do ambiente para evitar resolução não reproduzível de dependências.

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

## Segurança

- integração LDAP/AD somente leitura, preferencialmente por LDAPS;
- PHP sem `sudo` genérico;
- ações privilegiadas somente pelo `hecate-agent`, com operações fechadas;
- sem escrita direta no banco ou spool interno do SavaPage;
- filas físicas do CUPS não expostas diretamente aos clientes sempre que possível;
- autorização validada no servidor;
- CSRF no fluxo web;
- dependências explícitas via DI em vez de service locator global;
- logs e trilhas de auditoria sem conteúdo de documentos ou segredos.

## Liberação segura

```text
Usuário envia -> SavaPage retém -> usuário acessa HECATE
-> autenticação + PIN -> validação de política/cota/impressora
-> liberação -> SavaPage -> CUPS -> impressora
```

A integração de release deverá usar interface suportada pelo SavaPage. O HECATE não manipula diretamente banco, spool ou interfaces internas não suportadas.

## Cotas

P&B e colorida são contabilizadas separadamente:

```text
disponível = alocado - reservado - consumido
```

A reserva antecede a liberação para impedir estouro por concorrência entre jobs simultâneos.

## Contratos

O HECATE suporta como modelo de negócio:

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

- `docs/ARQUITETURA.md` — arquitetura e fluxos.
- `docs/DECISOES.md` — decisões técnicas consolidadas.
- `docs/EAP.md` — checklist de entrega completa do produto.
- `docs/QUALIDADE-CODIGO.md` — qualidade, análise estática, segurança e compliance técnico.
- `docs/AMBIENTE-DESENVOLVIMENTO.md` — ambiente de desenvolvimento.
- `docs/DOCUMENTACAO-CODIGO.md` — convenções de documentação.
- `docs/INTEGRACOES.md` — integrações institucionais e de impressão.
- `docs/SEGURANCA.md` — controles de segurança e auditoria.
- `docs/IMPLANTACAO.md` — instalação e distribuição.
- `docs/IDENTIDADE-VISUAL.md` — identidade visual e uso dos assets.
- `docs/MVP.md` — escopo do MVP e POCs pendentes.

## Estado da branch Yii3

A branch `yii3` é a linha de modernização do HECATE baseada no template oficial `yiisoft/app`. O lockfile está versionado e o pipeline integral de QA está aprovado. Antes de promovê-la a `main`, permanecem como validações principais a execução da migration PostgreSQL e a validação funcional local da aplicação.
