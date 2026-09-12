# 0003 — Definir fronteiras pelo domínio

Status: aceito como diretriz arquitetural.

## Contexto

Impressão, contratos, cotas, identidade e telemetria têm responsabilidades distintas, mas seus limites ainda precisam ser exercitados pelas POCs.

## Decisão

Aplicar DDD pragmático: linguagem, invariantes e fluxos reais orientam os limites. A complexidade deve ser justificada pelo domínio.

## Consequências

Separar regras de HTTP e integrações quando isso melhora testes e coesão. Entidades, interfaces e Value Objects exigem problema concreto; independência total de framework não é objetivo isolado.

## Referências

- [Diretrizes detalhadas](../ddd.md)
- [Decisões consolidadas](../DECISOES.md)
- [Escopo e critérios de validação](../EAP.md)
