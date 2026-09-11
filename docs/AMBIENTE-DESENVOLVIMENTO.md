# Ambiente de Desenvolvimento — HECATE

## 1. Finalidade

O ambiente de desenvolvimento do HECATE deve reproduzir, com o mínimo de divergência possível, as validações executadas no CI. A referência atual da aplicação web é o template oficial `yiisoft/app`, com PHP 8.2–8.5, Composer, PostgreSQL e ferramentas de qualidade versionadas no projeto.

O fluxo local deve priorizar diagnóstico antecipado, correção na causa e builds reproduzíveis.

## 2. IDE de referência

O Visual Studio Code é o IDE de referência. Extensões podem facilitar navegação, análise PHP, PHPCS, PHPStan, testes e edição de arquivos auxiliares, mas não substituem os comandos versionados no repositório.

A fonte de verdade para dependências e autoload é o Composer. Configurações locais do IDE não devem contradizer `composer.json`, `phpstan.neon`, `phpcs.xml`, `psalm.xml` ou `phpunit.xml`.

## 3. Primeira execução

```bash
git clone https://github.com/GeneralVini/hecate.git
cd hecate
git switch yii3
make setup
```

O `composer.lock` é obrigatório e deve estar versionado. O bootstrap não instala pacotes do sistema, não executa `composer require` e não resolve uma nova árvore de dependências. A instalação é realizada com `composer install` a partir do lockfile.

Pré-requisitos locais:

```text
PHP
Composer
Git
ext-pdo_pgsql
PostgreSQL acessível para testes funcionais/migrations
```

## 4. Qualidade local

O comando principal é:

```bash
composer qa
```

Ele executa o baseline obrigatório:

```text
PHPCS / PSR-12
PHPStan nível 8
Psalm
PHPUnit
```

Também podem ser usados:

```bash
composer lint
composer stan
composer psalm
composer test
make qa
```

O CI executa as mesmas configurações versionadas. Não deve existir uma configuração local mais permissiva.

## 5. Regra para código novo e código gerado

Código novo, inclusive código gerado por ferramentas automáticas ou assistência por IA, deve ser tratado como código de produção.

Antes de considerar uma alteração concluída, deve-se revisar o código, executar os checks aplicáveis, corrigir a causa dos diagnósticos e confirmar que não foram introduzidas suppressions apenas para aprovar a alteração.

O código deve seguir a arquitetura Yii3 adotada no HECATE: actions/handlers invocáveis, dependências por DI, HTTP PSR-7/PSR-17, middleware PSR-15, configuração em `config/` e código da aplicação em `src/`.

## 6. Não silenciar ferramentas

Quando PHPCS, PHPStan, Psalm ou PHPUnit apontarem problema, a ação padrão é corrigir o código.

Não utilizar como atalho:

- redução do nível do PHPStan;
- expansão artificial de baseline;
- `@phpstan-ignore-*` sem falso positivo comprovado;
- `// phpcs:ignore` genérico;
- suppressions amplas no Psalm;
- `@` para esconder erro PHP;
- exclusão de código próprio do escopo das ferramentas;
- casts ou verificações redundantes somente para satisfazer o analisador.

Supressões só são aceitáveis quando houver limitação técnica ou falso positivo comprovado, de forma localizada e com justificativa objetiva.

## 7. PHPStan

PHPStan nível 8 é gate mínimo atual. O objetivo é aumentar rigor sem reduzir o nível para aprovar entregas.

O desenvolvedor deve executar:

```bash
composer stan
```

A configuração utilizada localmente é a mesma do CI.

## 8. Psalm

Psalm complementa o PHPStan e permanece obrigatório no baseline.

```bash
composer psalm
```

Erros encontrados pelo Psalm devem ser corrigidos no código ou na tipagem real do projeto. Baselines e suppressions amplas não devem ser usados para ocultar problemas novos.

## 9. PHPCS e formatação

PHPCS aplica PSR-12 ao código próprio do projeto.

```bash
composer lint
```

PHPCBF pode ser usado para correções mecânicas quando apropriado, mas não substitui revisão de código.

## 10. Testes

PHPUnit é a ferramenta de testes automatizados do projeto.

```bash
composer test
```

O baseline atual contém testes mínimos e deve evoluir com testes unitários, integração PostgreSQL e validações dos fluxos críticos do HECATE.

## 11. SonarQube

SonarQube não é requisito para `make setup`, desenvolvimento local nem para o pipeline básico atual.

Quando houver servidor institucional disponível, ele poderá ser integrado como camada adicional para dashboard centralizado, histórico, dívida técnica, cobertura, duplicação e Quality Gate. A indisponibilidade do SonarQube não deve impedir o ambiente local nem substituir PHPCS, PHPStan, Psalm e PHPUnit.

Integração do SonarQube for IDE no VS Code é opcional e pode ser utilizada quando houver instância institucional configurada.

## 12. Estrutura relevante

```text
assets/                 assets-fonte
config/                 configuração e DI
public/                 document root
src/                    código da aplicação
tests/                  testes
runtime/                artefatos temporários de execução
composer.json           dependências e scripts
composer.lock           versões exatas das dependências
phpstan.neon            análise estática
phpcs.xml               estilo PSR-12
psalm.xml               análise estática complementar
phpunit.xml             testes
```

Não utilizar convenções Yii2 como `controllers/`, `models/`, `views/`, `widgets/` ou service locator global como referência arquitetural para código novo na branch `yii3`.

## 13. Configuração local do PostgreSQL

Exemplo de variáveis:

```bash
export HECATE_DB_HOST=127.0.0.1
export HECATE_DB_PORT=5432
export HECATE_DB_NAME=hecate
export HECATE_DB_USER=hecate
export HECATE_DB_PASSWORD='senha'
```

As migrations ficam em `src/Migration` e utilizam `yiisoft/db-migration`.

## 14. Fluxo recomendado

```text
editar código
    ↓
revisar diagnostics no IDE
    ↓
composer qa
    ↓
testes funcionais/migration quando aplicável
    ↓
commit / push
    ↓
GitHub Actions
    ↓
merge
```

O objetivo é que o CI confirme uma alteração já validada localmente, em vez de ser a primeira etapa a descobrir problemas básicos.

## 15. Critério de conclusão local

Antes de abrir ou atualizar um PR:

- [ ] `composer.lock` permanece sincronizado com `composer.json`;
- [ ] `composer qa` está aprovado;
- [ ] não foram adicionadas suppressions para contornar erros reais;
- [ ] PHPDoc foi usado apenas onde agrega contrato ou informação não expressável pelos tipos nativos;
- [ ] configuração e código seguem a estrutura Yii3 adotada;
- [ ] migrations e consultas PostgreSQL alteradas foram testadas quando aplicável;
- [ ] código gerado ou assistido por IA passou pelos mesmos checks do código escrito manualmente.
