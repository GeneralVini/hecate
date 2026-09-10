# Qualidade, Compliance e Padrões de Código — HECATE

## 1. Finalidade

Este documento define os padrões mínimos de qualidade de código e os controles de compliance técnico do HECATE. O objetivo é impedir que o produto evolua apenas por validação funcional, sem critérios objetivos de qualidade, manutenibilidade, segurança e aderência a padrões PHP.

A qualidade deve ser verificável no pipeline de CI e não depender apenas de revisão manual.

---

## 2. Padrões PHP adotados

### Obrigatórios

- **PSR-1 — Basic Coding Standard**: regras básicas de arquivos, namespaces, classes, constantes e métodos.
- **PSR-4 — Autoloading Standard**: autoload via Composer e namespaces coerentes com o código do projeto.
- **PSR-12 — Extended Coding Style**: padrão principal de estilo e formatação do código PHP.

O PSR-12 substitui o PSR-2 e deve ser tratado como baseline mínimo do projeto.

### Evolução recomendada

O projeto deve acompanhar o **PER Coding Style** da PHP-FIG, atualmente a evolução moderna do guia de estilo para versões mais recentes do PHP. A adoção de regras do PER deve ocorrer quando forem compatíveis com a versão mínima de PHP homologada para o HECATE e com Yii2.

### PSRs aplicáveis por integração

- **PSR-3 — Logger Interface**: recomendado em componentes próprios que precisem expor logging interoperável. Não é obrigatório substituir o mecanismo nativo de log do Yii2; adapters podem ser usados quando houver benefício.
- **PSR-11 — Container Interface**: recomendado apenas em pontos de integração que exijam interoperabilidade de container. O HECATE não deve forçar uma abstração paralela ao container do Yii2.
- **PSR-6 / PSR-16 — Cache**: opcionais para bibliotecas ou adapters que realmente precisem interoperar com cache externo. Não devem ser impostos apenas por conformidade formal.
- **PSR-18 — HTTP Client**: útil em clientes reutilizáveis de APIs externas, mas não obrigatório se a integração já estiver adequadamente encapsulada com o cliente HTTP homologado do projeto.

### PSRs que não devem ser impostos ao núcleo Yii2 sem necessidade

- PSR-7 e PSR-15 não devem ser introduzidos apenas para declarar conformidade, pois alterariam o modelo HTTP/middleware natural do Yii2 e aumentariam complexidade sem ganho direto para o HECATE.

**Decisão:** para o código próprio do HECATE, o conjunto obrigatório é **PSR-1 + PSR-4 + PSR-12**, com acompanhamento do PER Coding Style e adoção seletiva dos demais PSRs em interfaces de integração.

---

## 3. Lint e estilo

Todo código PHP deve passar por verificação automática antes de merge.

### PHP syntax lint

Executar `php -l` nos arquivos PHP modificados ou em todo o código próprio do projeto.

Falhas de sintaxe bloqueiam o pipeline.

### PHP_CodeSniffer

Utilizar **PHP_CodeSniffer (PHPCS)** com ruleset baseado em PSR-12.

Objetivos:

- identificar violações de estilo;
- garantir consistência de indentação, imports, espaços e estrutura;
- impedir divergência progressiva entre módulos;
- permitir correção automática com PHPCBF quando segura.

Comandos de referência:

```bash
vendor/bin/phpcs
vendor/bin/phpcbf
```

O ruleset do projeto deve excluir apenas diretórios de terceiros, artefatos gerados e runtime. Código próprio não deve ser ignorado para contornar violações.

---

## 4. Análise estática com PHPStan

PHPStan é obrigatório no pipeline.

### Estratégia de níveis

O HECATE é um projeto novo, mas utiliza Yii2, que possui ActiveRecord, propriedades mágicas e service locator. Por isso, a evolução deve ser agressiva sem gerar uma quantidade artificial de falsos positivos.

Meta recomendada:

1. iniciar com **nível 8** como gate mínimo;
2. corrigir tipagem e modelagem até atingir nível 9;
3. atingir **nível 10 / max** como objetivo de maturidade do produto;
4. nunca reduzir o nível para aprovar uma entrega.

O PHPStan possui níveis de 0 a 10, sendo o 10 o mais estrito. `max` acompanha automaticamente o nível máximo disponível na versão instalada.

### Integração com Yii2

Utilizar extensão de PHPStan compatível com a versão homologada de Yii2/PHPStan para melhorar inferência de:

- ActiveRecord;
- ActiveQuery;
- `Yii::$app`;
- componentes do service locator;
- propriedades e métodos mágicos do framework.

A extensão deve ser fixada por versão e homologada antes de atualização.

### Baseline

Um baseline PHPStan pode ser usado temporariamente em código legado ou em fase de elevação de nível, mas:

- não deve esconder erros novos;
- novas violações não podem ser adicionadas ao baseline sem justificativa;
- o baseline deve diminuir ao longo do tempo;
- não deve ser usado como substituto de correção de código próprio recém-criado.

**Gate:** nenhuma nova violação PHPStan no código alterado e análise global aprovada no nível mínimo homologado.

---

## 5. SonarQube

O HECATE deve possuir análise contínua no SonarQube institucional quando disponível.

A análise deve abranger, no mínimo:

- bugs/reliability;
- vulnerabilidades/security;
- security hotspots;
- maintainability/code smells;
- duplicação de código;
- complexidade;
- cobertura de testes quando integrada;
- dívida técnica.

### Quality Gate

O merge para `main` deve ser bloqueado quando o Quality Gate falhar.

Política recomendada para código novo:

- zero vulnerabilidades novas críticas/altas;
- zero bugs novos críticos/altos;
- security hotspots revisados;
- nenhuma piora deliberada de maintainability sem justificativa;
- duplicação sob limite institucional definido;
- cobertura mínima definida para código crítico;
- nenhuma issue `BLOCKER` ou `CRITICAL` aberta em código novo.

Os limites numéricos devem ser formalizados quando o SonarQube institucional for definido, evitando números arbitrários antes de conhecer a política vigente da organização.

---

## 6. Testes automatizados

A qualidade não será considerada atendida apenas com lint e análise estática.

O pipeline deve evoluir para incluir:

- testes unitários;
- testes de integração;
- testes de migrations;
- testes de autorização;
- testes de reserva/consumo de cotas;
- testes de adapters SavaPage, Catálogo MB e `hecate-agent` com doubles/mocks onde necessário;
- testes dos fluxos críticos de liberação de impressão.

Funções críticas de segurança, autorização, cotas e transferência devem possuir cobertura superior ao restante da aplicação.

---

## 7. Dependency e supply-chain compliance

O pipeline deve incluir controles sobre dependências Composer.

Obrigatório:

- `composer validate`;
- `composer audit`;
- `composer.lock` versionado;
- versões de dependências controladas;
- atualização deliberada e testada;
- proibição de dependências abandonadas ou sem justificativa técnica;
- análise de vulnerabilidades antes do release.

Imagens OCI e pacotes RPM distribuídos via Nexus devem ser rastreáveis por versão e origem.

---

## 8. Convenções de código HECATE

Além dos PSRs:

- preferir `declare(strict_types=1);` no código próprio PHP quando compatível com o arquivo e o framework;
- utilizar tipos nativos de parâmetro, retorno e propriedades sempre que possível;
- evitar `mixed` sem necessidade;
- evitar arrays sem shape conhecido em fronteiras de integração;
- usar DTOs/Value Objects apenas quando justificarem clareza, sem criar arquitetura paralela ao Yii2 Basic;
- controllers devem permanecer pequenos;
- regras de negócio críticas não devem ficar embutidas em views;
- queries devem ser parametrizadas por mecanismos do Yii2;
- evitar SQL concatenado;
- não registrar senhas, tokens, PINs ou conteúdo de documentos em logs;
- exceções devem preservar causa técnica sem expor segredo ao usuário;
- toda ação privilegiada deve ser auditável.

---

## 9. Pipeline mínimo de CI

Ordem sugerida:

```text
composer validate
      ↓
composer install
      ↓
php -l
      ↓
PHPCS / PSR-12
      ↓
PHPStan
      ↓
testes automatizados
      ↓
composer audit
      ↓
SonarQube
      ↓
Quality Gate
      ↓
merge/release
```

Qualquer etapa obrigatória com falha deve bloquear merge/release.

---

## 10. Definition of Done técnica

Uma entrega de código somente pode ser considerada concluída quando:

- [ ] segue PSR-1, PSR-4 e PSR-12;
- [ ] passa em `php -l`;
- [ ] passa no PHPCS;
- [ ] passa no PHPStan no nível mínimo homologado;
- [ ] não adiciona suppressions/baselines sem justificativa;
- [ ] possui testes adequados ao risco da alteração;
- [ ] `composer validate` passa;
- [ ] `composer audit` não apresenta vulnerabilidade não aceita formalmente;
- [ ] SonarQube Quality Gate está aprovado, quando disponível;
- [ ] não introduz segredos ou dados sensíveis no repositório/logs;
- [ ] documentação técnica é atualizada quando a alteração muda arquitetura, integração ou operação;
- [ ] revisão de código foi realizada antes do merge.

---

## 11. Meta de maturidade

O objetivo é que a versão 1.0 do HECATE seja entregue com:

- PSR-12 aplicado automaticamente;
- PHPStan mínimo nível 8, com plano de elevação para 10/max;
- lint de sintaxe obrigatório;
- testes automatizados dos fluxos críticos;
- Composer audit;
- SonarQube integrado ao CI;
- Quality Gate bloqueante;
- zero segredo versionado;
- critérios de qualidade reproduzíveis por qualquer OM ou pipeline institucional.
