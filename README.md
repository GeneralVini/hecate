# HECATE

Plataforma Institucional de Governança e Controle de Impressão

![HECATE — Governança e Controle de Impressão](public/branding/hecate-hero.jpg)

HECATE é a plataforma institucional destinada a padronizar, governar, controlar e auditar o serviço de impressão nas OM. O produto organiza políticas, papéis, cotas, contratos, aprovações, liberação, auditoria, indicadores e operação do serviço.

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

## Estado atual

A branch `yii3` está em estágio de **POC**. Há base HTTP/DI, persistência PostgreSQL, cadastro e consultas iniciais de inventário, além de componentes preliminares para reserva e liberação.

Ainda precisam ser demonstrados de ponta a ponta os fluxos de autenticação institucional, autorização, integração SavaPage, contabilização, concorrência e recuperação de falhas.

A [EAP](docs/EAP.md) é a fonte única de acompanhamento da entrega, incluindo escopo, POCs, critérios de aceite e pendências do MVP.

## Direção arquitetural

A direção adotada é **Yii3 + monólito modular + DDD pragmático**.

> **A complexidade deve ser justificada pelo domínio.**

Organizar por responsabilidade, preferir SQL explícito e parametrizado via Yii DB quando apropriado e usar DTOs/read models específicos nas fronteiras que os justifiquem. ActiveRecord não deve funcionar como modelo compartilhado da aplicação.

As decisões e seus motivos estão em [DECISOES.md](docs/DECISOES.md). A arquitetura e as integrações estão em [ARQUITETURA.md](docs/ARQUITETURA.md).

## Arquitetura de referência

```text
Samba AD da OM ----LDAPS----> Keycloak
       |                         |
       |                         v
       +---------------------> HECATE <------ Catálogo MB
                                 |
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

- **Samba AD:** identidade institucional;
- **Catálogo MB:** atributos funcionais e organizacionais;
- **Keycloak:** SSO/OIDC;
- **HECATE:** governança, políticas, cotas, contratos, aprovações e auditoria;
- **SavaPage:** retenção, contabilização e controle do fluxo de impressão;
- **CUPS:** filas físicas e transporte ao equipamento;
- **PostgreSQL:** persistência;
- **hecate-agent:** operações locais, descoberta e diagnóstico;
- **Nexus:** distribuição institucional de artefatos homologados.

## Plataforma web

A branch `yii3` utiliza o template oficial **Yii3 Web Application** (`yiisoft/app`) como referência estrutural do backend, com HTTP PSR-7/PSR-17, middleware PSR-15, DI e roteamento explícito.

A tecnologia do frontend permanece uma decisão aberta. O HECATE pode usar views nativas do Yii3 onde forem suficientes ou evoluir para frontend separado consumindo APIs do backend. A arquitetura não deve acoplar regras de domínio à camada de apresentação.

Estrutura principal atual:

```text
assets/
config/
public/
src/
tests/
runtime/
yii
```

A organização modular está em evolução e deve acompanhar o domínio real, sem criação antecipada de camadas vazias.

## Primeira execução

```bash
git clone https://github.com/GeneralVini/hecate.git
cd hecate
git switch yii3
make setup
```

Após configurar o PostgreSQL:

```bash
./yii migrate:up
```

Para iniciar o ambiente de desenvolvimento:

```bash
APP_ENV=dev APP_DEBUG=1 composer serve
```

## Qualidade

O baseline de qualidade é composto por:

```text
Lefthook
Rector
ECS / PSR-12
PHPStan
Psalm
PHPUnit
```

Validação integral:

```bash
composer qa
```

Autocorreções determinísticas de código e estilo:

```bash
composer fix
```

O Lefthook aplica autofix em PHP no `pre-commit` e executa as verificações completas no `pre-push`. PHPStan, Psalm e PHPUnit permanecem validadores: problemas sem correção determinística exigem alteração consciente de código.

Detalhes de ambiente, instalação do Lefthook, qualidade e documentação de código estão em [DESENVOLVIMENTO.md](docs/DESENVOLVIMENTO.md).

## Fluxo previsto de impressão

```text
Usuário envia -> SavaPage retém -> HECATE valida
-> liberação -> SavaPage -> CUPS -> impressora
-> contabilização e reconciliação
```

As cotas P&B e colorida são independentes:

```text
disponível = alocado - consumido - reservado
```

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

Objetivo de instalação:

```bash
dnf install hecate
hecate-setup
```

A distribuição institucional de pacotes e imagens homologadas será feita via Nexus.

## Identidade visual

Os assets oficiais ficam em `public/branding/`. A referência de identidade e uso está em [IDENTIDADE-VISUAL.md](docs/IDENTIDADE-VISUAL.md).

## Documentação

A documentação do projeto é deliberadamente enxuta. Não criar novo `.md` quando o conteúdo puder ser incorporado a um documento canônico existente.

- [EAP.md](docs/EAP.md) — escopo, POCs, aceite e acompanhamento;
- [ARQUITETURA.md](docs/ARQUITETURA.md) — arquitetura, boundaries, fluxos e integrações;
- [DECISOES.md](docs/DECISOES.md) — decisões técnicas e respectivos motivos;
- [DESENVOLVIMENTO.md](docs/DESENVOLVIMENTO.md) — ambiente, qualidade e documentação de código;
- [SEGURANCA.md](docs/SEGURANCA.md) — controles de segurança e auditoria;
- [IMPLANTACAO.md](docs/IMPLANTACAO.md) — instalação, operação e replicação;
- [IDENTIDADE-VISUAL.md](docs/IDENTIDADE-VISUAL.md) — identidade visual e UI institucional;
- [AGENTS.md](AGENTS.md) — regras para agentes e alterações no repositório.

O estado de implementação deve ser comprovado pela EAP e por evidências de teste; documentação arquitetural não substitui validação funcional.
