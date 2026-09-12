# 0001 — Utilizar Yii3

Status: aceito como diretriz arquitetural.

## Contexto

O projeto já possui HTTP PSR-7/PSR-17, middleware PSR-15, DI e configuração Yii3.

## Decisão

Manter Yii3 e seus componentes oficiais. O framework fornece infraestrutura e apresentação; a modelagem segue as responsabilidades do HECATE.

## Consequências

Preserva o investimento existente e evita migração de framework sem benefício demonstrado. Não retornar ao Yii2. A versão PHP e a matriz operacional precisam de homologação própria.

## Referências

- [Diretrizes detalhadas](../ddd.md)
- [Decisões consolidadas](../DECISOES.md)
- [Escopo e critérios de validação](../EAP.md)
