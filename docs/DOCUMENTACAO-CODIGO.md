# Documentação de Código — HECATE

## 1. Finalidade

Este documento define o padrão de documentação interna do código do HECATE. O objetivo é manter o código compreensível, auditável e sustentável sem transformar o projeto em uma base excessivamente comentada.

A regra geral é simples: **tipos e nomes devem explicar o óbvio; PHPDoc/JSDoc devem explicar contrato, intenção, restrições, efeitos colaterais e decisões que não são evidentes apenas pela assinatura do código**.

---

## 2. PHPDoc

O código PHP próprio do HECATE deve utilizar **PHPDoc** de forma consistente, especialmente em classes e pontos públicos/reutilizáveis.

### 2.1. Onde PHPDoc é obrigatório

PHPDoc deve existir em:

- classes próprias do HECATE;
- interfaces e traits;
- widgets reutilizáveis;
- adapters de integração;
- classes de infraestrutura e integração;
- métodos públicos cuja finalidade, contrato ou efeitos não sejam triviais;
- propriedades dinâmicas/mágicas relevantes ao Yii2 quando necessárias para análise estática/IDE;
- estruturas complexas que não possam ser expressas adequadamente apenas por tipos nativos;
- callbacks, arrays estruturados e formatos de retorno relevantes;
- métodos que lançam exceções relevantes ao contrato;
- métodos com efeitos colaterais administrativos, transacionais ou privilegiados.

### 2.2. Onde PHPDoc não deve ser usado apenas por formalidade

Evitar comentários redundantes como:

```php
/**
 * Retorna o nome.
 *
 * @return string
 */
public function getName(): string
{
    return $this->name;
}
```

Quando a assinatura já expressa completamente o contrato, o PHPDoc pode ser omitido, salvo exigência específica de ferramenta/framework.

### 2.3. Tipos nativos primeiro

PHPDoc não deve substituir tipos nativos disponíveis no PHP.

Preferir:

```php
public function findPrinter(int $id): Printer
```

em vez de depender apenas de:

```php
/**
 * @param int $id
 * @return Printer
 */
public function findPrinter($id)
```

PHPDoc complementa a tipagem nativa, não a substitui.

### 2.4. Tags recomendadas

Usar quando aplicável:

- `@param` para informação adicional que o tipo nativo não expresse;
- `@return` quando houver detalhes relevantes sobre o retorno;
- `@throws` para exceções que façam parte do contrato esperado;
- `@var` para propriedades mágicas, shapes ou inferência necessária;
- `@property`, `@property-read` e `@property-write` quando exigidos por comportamento dinâmico do Yii2;
- `@method` apenas quando realmente necessário para comportamento mágico;
- `@template`, `@extends`, `@implements` e tipos genéricos quando suportados pelo PHPStan e trouxerem ganho real;
- array shapes do PHPStan/PHPDoc para estruturas externas bem definidas.

Evitar tags sem finalidade operacional ou que apenas dupliquem a assinatura.

### 2.5. PHPDoc em ActiveRecord e SearchModel

Em modelos Yii2, documentar de forma suficiente:

- propósito do modelo;
- campos relevantes quando não forem evidentes;
- relações `ActiveQuery` relevantes;
- propriedades calculadas;
- scopes/queries customizadas;
- atributos virtuais;
- formatos de payload usados por integrações.

Comentários gerados automaticamente por Gii podem ser mantidos quando úteis, mas devem ser revisados para não carregar documentação incorreta ou obsoleta.

### 2.6. PHPDoc em adapters e integrações

Adapters de SavaPage, Catálogo MB, LDAP, Keycloak e `hecate-agent` devem documentar:

- sistema externo consumido;
- operação executada;
- parâmetros esperados;
- formato de retorno;
- timeouts quando relevantes;
- erros/exceções esperados;
- efeitos colaterais;
- garantias de segurança relevantes;
- se a operação é idempotente ou não;
- limites ou hipóteses da integração.

Não incluir secrets, tokens, credenciais reais ou exemplos sensíveis em comentários.

---

## 3. Comentários PHP

Comentários inline devem explicar principalmente **por que** determinada decisão existe.

Bom exemplo:

```php
// Reserva a cota antes da liberação para impedir consumo concorrente
// do mesmo saldo por dois jobs simultâneos.
```

Evitar:

```php
// Soma um ao contador.
$counter++;
```

Comentários de workaround devem informar, quando possível:

- motivo;
- limitação externa;
- referência técnica ou issue;
- condição para remoção futura.

Não deixar blocos de código comentado no repositório. Histórico pertence ao Git.

---

## 4. JavaScript: JSDoc

Para JavaScript próprio do HECATE, utilizar **JSDoc** como equivalente conceitual ao PHPDoc.

### 4.1. Onde JSDoc é obrigatório

JSDoc deve ser usado em:

- funções reutilizáveis;
- módulos/componentes compartilhados;
- classes JavaScript;
- callbacks de contrato não trivial;
- funções assíncronas relevantes;
- código de widgets/componentes que possua API pública;
- estruturas de dados complexas;
- funções que executem chamadas HTTP ou operações sensíveis;
- código que manipule estado compartilhado.

### 4.2. Tags recomendadas

Usar quando aplicável:

- `@param`;
- `@returns`;
- `@throws`;
- `@typedef`;
- `@property`;
- `@callback`;
- `@async` quando a natureza assíncrona não estiver suficientemente clara;
- `@deprecated` com orientação de substituição.

Exemplo:

```js
/**
 * Atualiza o estado visual de um componente de saúde do stack.
 *
 * @param {HTMLElement} element Elemento raiz do componente.
 * @param {'ok'|'warning'|'down'} status Estado operacional normalizado.
 * @param {string} message Mensagem segura para exibição ao operador.
 * @returns {void}
 */
function updateHealthStatus(element, status, message) {
    // ...
}
```

### 4.3. Documentação e segurança no JavaScript

JSDoc nunca deve incentivar ou normalizar APIs inseguras. Código frontend deve continuar obedecendo às regras de segurança do projeto:

- não usar `innerHTML` com dados não confiáveis;
- preferir `textContent` para texto;
- evitar `eval`, `new Function` e execução dinâmica;
- não construir código JavaScript por concatenação de entrada externa;
- não inserir tokens/secrets em comentários ou exemplos;
- documentar claramente quando uma função espera conteúdo já sanitizado.

---

## 5. Relação com PHPStan, IDE e SonarQube

PHPDoc deve ser escrito de forma compatível com a análise estática do projeto.

Objetivos:

- melhorar inferência do PHPStan;
- melhorar autocomplete e navegação da IDE;
- tornar contratos explícitos;
- reduzir uso injustificado de `mixed`;
- documentar shapes e generics onde a linguagem ainda não ofereça expressão nativa suficiente;
- evitar suppressions criadas apenas para compensar documentação incorreta.

A documentação deve ser atualizada junto com o código. PHPDoc/JSDoc obsoleto é considerado defeito de manutenção.

---

## 6. Linguagem e estilo

Para código próprio do HECATE:

- nomes de classes, métodos, propriedades e variáveis seguem convenções técnicas em inglês, salvo decisão contrária específica do projeto;
- documentação técnica interna pode ser escrita em português para facilitar sustentação pelas OM;
- termos de APIs, protocolos e bibliotecas devem manter a nomenclatura oficial;
- comentários devem ser objetivos;
- evitar textos longos quando o comportamento puder ser expresso por melhor nome, tipo ou extração de método.

---

## 7. Documentação de widgets reutilizáveis

Todo widget próprio deve documentar pelo menos:

- finalidade;
- propriedades públicas configuráveis;
- valores padrão relevantes;
- formatos aceitos;
- eventos/callbacks expostos;
- comportamento esperado;
- exemplo curto de uso quando não for óbvio.

Isso se aplica especialmente a componentes como `FlashAlert`, GridView institucional, indicadores de status, cota, suprimentos e confirmações de ações sensíveis.

---

## 8. Definition of Done de documentação de código

Uma alteração somente atende ao padrão documental quando:

- [ ] classes/interfaces/traits novas possuem documentação suficiente;
- [ ] APIs públicas reutilizáveis possuem PHPDoc/JSDoc quando não triviais;
- [ ] tipos nativos são usados antes de recorrer a PHPDoc;
- [ ] `@throws` relevante está documentado;
- [ ] array shapes/generics necessários ao PHPStan estão documentados;
- [ ] widgets reutilizáveis possuem contrato documentado;
- [ ] JavaScript reutilizável possui JSDoc adequado;
- [ ] comentários explicam intenção/decisão, não apenas repetem o código;
- [ ] não há documentação sabidamente obsoleta;
- [ ] não há secrets ou informações sensíveis em exemplos/comentários;
- [ ] mudança de contrato atualizou sua documentação no mesmo PR.

---

## 9. Regra final

O HECATE não adota a política de "comentar tudo". Adota a política de **documentar contratos, decisões e comportamentos que precisam sobreviver à troca de desenvolvedor e à replicação da solução entre OM**.

PHPDoc e JSDoc fazem parte da qualidade do produto e devem ser considerados durante code review e antes do merge.