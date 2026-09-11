# Documentação de Código — HECATE

## 1. Finalidade

Este documento define o padrão de documentação interna do código do HECATE. O objetivo é manter o código compreensível, auditável e sustentável sem excesso de comentários.

A regra geral é: tipos e nomes explicam o óbvio; PHPDoc e JSDoc explicam contratos, restrições, formatos, efeitos colaterais e decisões que não são evidentes apenas pela assinatura.

## 2. PHPDoc

PHPDoc deve ser utilizado quando acrescentar informação que os tipos nativos não conseguem expressar adequadamente.

É especialmente útil em:

- interfaces e contratos próprios;
- adapters de SavaPage, Catálogo MB, LDAP, Keycloak e `hecate-agent`;
- estruturas complexas e array shapes;
- generics utilizados por PHPStan/Psalm;
- callbacks não triviais;
- métodos que lançam exceções relevantes ao contrato;
- operações administrativas, transacionais ou privilegiadas;
- pontos em que bibliotecas externas dependam de metadados documentais.

Não é obrigatório adicionar PHPDoc redundante a toda classe ou método quando assinatura, nome e tipos nativos já forem suficientes.

## 3. Tipos nativos primeiro

Preferir tipos nativos de propriedades, parâmetros e retornos.

Exemplo:

```php
public function findPrinter(int $id): Printer
```

Evitar substituir tipagem nativa por PHPDoc:

```php
/** @param int $id */
public function findPrinter($id)
```

PHPDoc complementa a tipagem; não deve substituí-la.

## 4. Tags recomendadas

Usar quando houver informação adicional real:

- `@param`;
- `@return`;
- `@throws`;
- `@var`;
- `@template`;
- `@extends`;
- `@implements`;
- array shapes e generics aceitos por PHPStan/Psalm.

`@property` e `@method` devem ser usados apenas quando uma biblioteca realmente expuser comportamento dinâmico não representável de forma melhor por tipos nativos.

## 5. Yii3 e ActiveRecord

Os modelos persistentes do HECATE utilizam `yiisoft/active-record` e ficam em `src/Model`.

Documentar quando necessário:

- finalidade do modelo;
- relações relevantes;
- tipos que não possam ser inferidos corretamente;
- regras ou invariantes de domínio associadas ao dado;
- formatos usados em integrações;
- diferenças entre representação persistente e regra de negócio.

Não reproduzir documentação obsoleta oriunda de Yii2, Gii ou propriedades mágicas apenas por compatibilidade histórica.

## 6. Actions, handlers e middleware

Na arquitetura Yii3 do HECATE, endpoints web ficam em `src/Web` como actions/handlers invocáveis e usam dependências explícitas via DI.

Documentar quando houver:

- pré-condições relevantes;
- autorização necessária;
- side effects;
- contratos de payload;
- códigos de resposta não triviais;
- integração externa;
- transação ou alteração persistente;
- requisito de idempotência.

Não comentar boilerplate óbvio do PSR-7/PSR-15.

## 7. Adapters e integrações

Adapters de SavaPage, Catálogo MB, LDAP, Keycloak e `hecate-agent` devem documentar, quando aplicável:

- sistema externo consumido;
- operação executada;
- formato de entrada e saída;
- timeouts;
- erros esperados;
- idempotência;
- efeitos colaterais;
- limites técnicos;
- requisitos de segurança.

Nunca incluir secrets, tokens, senhas, communities SNMP ou credenciais reais em comentários.

## 8. Comentários inline

Comentários devem explicar principalmente o motivo da implementação.

Exemplo adequado:

```php
// Reserva a cota antes da liberação para impedir que dois jobs
// concorrentes consumam o mesmo saldo disponível.
```

Evitar comentários que apenas traduzam o código:

```php
// Incrementa o contador.
$counter++;
```

Workarounds devem indicar, quando possível, a limitação externa, referência técnica e condição para remoção futura.

Código comentado não deve permanecer no repositório; histórico pertence ao Git.

## 9. `#[Override]`

Quando um método sobrescrever um método herdado ou implementar contrato aplicável e a versão de PHP homologada permitir, utilizar `#[Override]` quando isso melhorar validação estática e tornar o contrato explícito.

O atributo não substitui os tipos da assinatura nem documentação de efeitos relevantes.

## 10. Arrays estruturados

Evitar `array` sem shape conhecido nas fronteiras importantes.

Quando um value object ou DTO não se justificar, documentar o shape:

```php
/**
 * @param array{
 *     host: string,
 *     port: int,
 *     timeout: float
 * } $target
 */
```

O objetivo é permitir que PHPStan, Psalm e IDE validem o contrato real.

## 11. JavaScript e JSDoc

JavaScript reutilizável deve utilizar JSDoc quando possuir API ou contrato não trivial.

Usar principalmente em:

- funções compartilhadas;
- módulos reutilizáveis;
- funções assíncronas relevantes;
- callbacks de contrato complexo;
- estruturas de dados não triviais;
- código que execute chamadas HTTP ou manipule estado compartilhado.

Tags usuais:

- `@param`;
- `@returns`;
- `@throws`;
- `@typedef`;
- `@property`;
- `@callback`;
- `@deprecated`.

Exemplo:

```js
/**
 * Atualiza o estado visual de um componente de saúde.
 *
 * @param {HTMLElement} element Elemento raiz.
 * @param {'ok'|'warning'|'down'} status Estado normalizado.
 * @param {string} message Texto seguro para exibição.
 * @returns {void}
 */
function updateHealthStatus(element, status, message) {
    // ...
}
```

## 12. Segurança na documentação

Comentários e documentação não devem:

- expor secrets;
- recomendar concatenação insegura em SQL;
- recomendar `innerHTML` com dados não confiáveis;
- recomendar shell arbitrário;
- registrar ou exemplificar PIN real;
- sugerir manipulação direta de banco/spool interno do SavaPage;
- documentar endpoints internos sensíveis com credenciais reais.

Documentação de segurança deve descrever controles e contratos, não material sensível.

## 13. Relação com PHPStan e Psalm

PHPDoc deve ser compatível com os analisadores estáticos do projeto.

Objetivos:

- melhorar inferência real;
- explicitar generics e shapes;
- reduzir `mixed` injustificado;
- evitar suppressions decorrentes de documentação incorreta;
- manter contratos consistentes entre implementação, testes e IDE.

Quando PHPStan e Psalm divergirem por limitação conhecida de uma ferramenta, a solução deve ser localizada e justificada, nunca uma exclusão ampla do código próprio.

## 14. Critério de qualidade

Uma documentação interna adequada deve:

- explicar decisões não óbvias;
- permanecer coerente com o código;
- não duplicar assinaturas sem necessidade;
- evitar termos e convenções Yii2 na branch `yii3`;
- não conter informação sensível;
- ajudar análise estática, manutenção e auditoria.
