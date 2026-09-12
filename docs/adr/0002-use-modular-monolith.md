# 0002 — Adotar monólito modular

Status: aceito como diretriz arquitetural.

## Contexto

Uma instância por OM deve ser simples de implantar e operar. Os módulos de governança precisam coordenar operações locais.

## Decisão

Manter uma aplicação modular, com um banco HECATE e organização por responsabilidade. Criar módulos apenas quando houver consumidores e regras reais.

## Consequências

Permite transações locais e distribuição simples. Não implica microserviços, bancos por módulo ou uma árvore completa de diretórios antecipada.

## Referências

- [Diretrizes detalhadas](../ddd.md)
- [Decisões consolidadas](../DECISOES.md)
- [Escopo e critérios de validação](../EAP.md)
