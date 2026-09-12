# 0004 — Evitar ActiveRecord compartilhado

Status: aceito como diretriz arquitetural.

## Contexto

Models persistentes consumidos por telas, regras e integrações propagam alterações do schema entre responsabilidades distintas.

## Decisão

Não usar subclasses de ActiveRecord como modelo central ou universal. Permitir uso pontual na infraestrutura quando justificado.

## Consequências

Os models em src/Model são legado em transição. Sua remoção deve localizar consumidores e preservar comportamento com testes; esta decisão não exige retirar imediatamente a dependência Composer.

## Referências

- [Diretrizes detalhadas](../ddd.md)
- [Decisões consolidadas](../DECISOES.md)
- [Escopo e critérios de validação](../EAP.md)
