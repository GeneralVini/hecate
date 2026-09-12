# Qualidade, Compliance e Padrões de Código — HECATE

## 1. Finalidade

Este documento define os padrões mínimos de qualidade e compliance técnico do HECATE. O objetivo é manter o código verificável, reproduzível, seguro e coerente com a arquitetura Yii3 adotada.

A validação deve ocorrer localmente e no CI, sem depender apenas de revisão manual.

## 2. Padrões PHP adotados

São obrigatórios:

- PSR-1 para regras básicas de código PHP;
- PSR-4 para autoload via Composer;
- PSR-12 para estilo e formatação;
- PSR-7 para mensagens HTTP;
- PSR-11 para interoperabilidade do container;
- PSR-15 para middleware HTTP;
- PSR-17 para factories HTTP.

O projeto deve acompanhar o PER Coding Style quando compatível com a versão de PHP homologada.

PSR-3, PSR-6/16 e PSR-18 podem ser adotados quando houver benefício concreto de interoperabilidade.

## 3. Estrutura Yii3

A aplicação web segue o template oficial `yiisoft/app`.

Estrutura principal:

```text
assets/
config/
public/
src/
  Migration/
  Printing/
  Quota/
  IdentityAccess/
  Audit/
  Monitoring/
  Model/  # legado ActiveRecord em transição
  Shared/
  Web/
tests/
```

Endpoints web devem ser implementados como actions/handlers invocáveis em `src/Web`, com dependências fornecidas pelo container. Não utilizar controllers, models, views e widgets no formato Yii2 como referência para novo código.

## 4. Composer e dependências

`composer.json` é a fonte de declaração de dependências e autoload. `composer.lock` é obrigatório e deve permanecer versionado e sincronizado.

O fluxo normal é:

```bash
composer install --no-interaction --prefer-dist
composer validate --no-interaction
```

O bootstrap não deve executar `composer require`, `composer update`, instalar pacotes do sistema ou mascarar falhas.

## 5. Baseline obrigatório de QA

O comando principal é:

```bash
composer qa
```

O baseline atual contém:

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
```

Uma falha em qualquer etapa deve produzir código de saída diferente de zero.

## 6. PHPStan

PHPStan nível 8 é gate mínimo obrigatório. O nível não deve ser reduzido para aprovar uma alteração.

Objetivo de evolução:

```text
nível 8 -> nível 9 -> nível máximo
```

Erros devem ser corrigidos pela melhoria da tipagem, contrato ou implementação real. Exclusões e suppressions somente são aceitáveis para falso positivo ou limitação comprovada, de forma localizada e justificada.

## 7. Psalm

Psalm é análise estática complementar obrigatória. O código próprio em `src/`, `config/` e entry points aplicáveis deve permanecer analisável sem ignorar diretórios apenas para obter resultado verde.

Não criar baseline amplo para ocultar erros novos. Atributos, tipos e contratos apontados pelo Psalm devem ser corrigidos na origem sempre que possível.

## 8. PHPCS

PHPCS utiliza PSR-12 como padrão mínimo.

```bash
composer lint
```

PHPCBF pode ser utilizado para correções mecânicas, mas não substitui revisão de código.

Código próprio não deve ser removido do escopo do ruleset para contornar violações.

## 9. PHPUnit

PHPUnit é obrigatório no pipeline.

O conjunto de testes deve evoluir para incluir:

- testes unitários de regras de negócio;
- integração com PostgreSQL;
- migrations;
- autorização e segregação de acesso;
- cotas e concorrência;
- integração com adapters externos;
- fluxos críticos de liberação de impressão.

Testes smoke isolados não são critério suficiente de maturidade funcional.

## 10. SonarQube

SonarQube não é requisito para desenvolvimento local, `make setup` ou pipeline básico atual.

Quando houver instância institucional disponível, poderá ser adicionado como camada complementar para:

- dashboard centralizado;
- histórico de qualidade;
- dívida técnica;
- duplicação;
- cobertura;
- security hotspots;
- Quality Gate institucional.

A futura adoção do SonarQube não substitui PHPCS, PHPStan, Psalm ou PHPUnit.

## 11. Reutilização de interface

A interface deve evitar duplicação de marcação, estilo e comportamento. Em Yii3, reutilização deve ser feita por templates compartilhados, componentes, helpers ou classes de apresentação compatíveis com a estrutura `src/Web`.

Padrões candidatos a reutilização incluem:

- mensagens de status;
- badges;
- cards de saúde;
- indicadores de cota;
- indicadores de suprimentos;
- confirmações de ações sensíveis;
- filtros recorrentes;
- tabelas institucionais.

Não criar abstração apenas porque um trecho de HTML existe. A reutilização deve reduzir duplicação real ou consolidar uma regra institucional de UX.

## 12. CSS e JavaScript

Estilos compartilhados devem permanecer em assets comuns. Evitar por padrão CSS e JavaScript específicos por página.

Regras:

- evitar CSS inline;
- evitar JavaScript inline;
- evitar `style=` e `onclick=`;
- preferir `data-*` para parametrização de comportamento reutilizável;
- evitar `eval` e `new Function`;
- não inserir dados não confiáveis com `innerHTML` sem tratamento adequado.

## 13. Segurança de código

Segurança é requisito de implementação.

### XSS

Todo dado não confiável exibido deve ser escapado no contexto correto. Entrada validada não elimina a necessidade de escaping de saída.

### SQL Injection

Preferir SQL explícito com comandos parametrizados do Yii DB; Query Builder pode ser usado quando trouxer clareza. ActiveRecord permanece restrito a usos pontuais de infraestrutura justificados. Nunca concatenar dados de requisição diretamente em SQL.

Identificadores dinâmicos, como nomes de coluna ou ordenação, devem vir de allowlist.

### Validação de entrada

Payloads HTTP devem ser validados antes de alcançar regras de negócio. Campos de privilégios, owner, OM, divisão, saldo, aprovação e status não devem ser controláveis livremente pelo cliente.

### CSRF e métodos HTTP

Operações web autenticadas devem utilizar a proteção CSRF fornecida pela pilha Yii3 e métodos HTTP coerentes. Não desativar proteção globalmente para resolver uma integração específica.

### Autorização e IDOR/BOLA

Autenticação não implica autorização. Toda operação sensível deve validar no servidor o direito do usuário sobre o recurso, incluindo OM, divisão, impressora, job, cota e ação administrativa.

### `hecate-agent`

O PHP web não deve executar shell arbitrário nem possuir `sudo` genérico. Operações privilegiadas devem ser encaminhadas ao `hecate-agent` através de uma interface fechada, com argumentos tipados, allowlist e auditoria.

### SSRF

Consultas a impressoras, EWS, IPP, Catálogo MB e integrações externas devem validar destino, esquema, timeout e redirects. O HECATE não deve funcionar como proxy para destinos arbitrários.

### Arquivos e path traversal

Caminhos de filesystem não devem ser construídos diretamente a partir de entrada do usuário. Uploads, quando introduzidos, devem validar tipo real, tamanho, nome, destino e permissões.

### Secrets

Senhas, tokens, secrets OIDC, communities SNMP e chaves privadas não podem ser versionados ou registrados em log. Utilizar variáveis de ambiente ou mecanismo institucional de secrets.

### Sessão e autenticação

Produção deve utilizar TLS. Cookies de sessão devem ser configurados com atributos seguros adequados. PIN do HECATE deve ser armazenado somente como hash e sujeito a rate limiting/bloqueio.

### Headers

Devem ser homologados Content-Security-Policy, X-Content-Type-Options, Referrer-Policy, frame-ancestors e HSTS quando aplicável.

### Logs

Não registrar senha, token, cookie, PIN ou conteúdo de documentos. Trilhas de auditoria devem registrar identidade, ação, objeto, resultado e timestamp.

## 14. Regras de arquitetura e manutenibilidade

Código próprio deve preferir:

- `declare(strict_types=1);`;
- tipos nativos de propriedades, parâmetros e retornos;
- `readonly` quando representar corretamente o objeto;
- `#[Override]` quando um método sobrescrever contrato herdado ou interface e a versão de PHP permitir;
- dependências explícitas via DI;
- baixo acoplamento entre camada HTTP, domínio e infraestrutura;
- arrays tipados ou value objects em fronteiras relevantes;
- ausência de service locator global;
- actions pequenas e focadas;
- regras de negócio fora de templates.

Não criar camadas ou abstrações sem ganho claro de coesão, teste, interoperabilidade ou segurança.

## 15. Banco de dados

PostgreSQL é o banco de referência. Migrations ficam em `src/Migration` e utilizam `yiisoft/db-migration`.

Mudanças de schema devem ser versionadas por migration e possuir caminho de reversão quando tecnicamente seguro.

Regras de concorrência, principalmente reserva e consumo de cotas, devem ser protegidas no banco e não apenas na interface.

## 16. CI

O pipeline deve executar, no mínimo:

```bash
composer install --no-interaction --prefer-dist
composer validate --no-interaction
composer qa
```

O CI não deve gerar lockfile, executar `composer update` nem alterar o repositório.

## 17. Critério de aceite

Uma alteração só deve ser considerada tecnicamente apta quando:

- [ ] `composer.json` e `composer.lock` estiverem sincronizados;
- [ ] PHPCS estiver aprovado;
- [ ] PHPStan nível 8 estiver aprovado;
- [ ] Psalm estiver aprovado;
- [ ] PHPUnit estiver aprovado;
- [ ] não houver suppressions adicionadas apenas para esconder erro real;
- [ ] controles de segurança aplicáveis tiverem sido considerados;
- [ ] migrations e integrações alteradas tiverem validação funcional quando aplicável;
- [ ] o CI estiver verde.
