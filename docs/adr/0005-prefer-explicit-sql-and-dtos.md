# 0005 — Preferir SQL explícito e DTOs específicos

Status: aceito como diretriz arquitetural.

## Contexto

Consultas, cadastro e reserva precisam de projeções claras, bindings e garantias transacionais visíveis.

## Decisão

Preferir SQL parametrizado via Yii DB/Command em componentes específicos. Usar DTOs nas fronteiras de entrada, saída, consulta e integração que os justifiquem.

## Consequências

Sem SQL em actions ou templates, sem DTO universal e sem repository genérico. Mapeamento e tipos devem ser testados; SQL explícito não garante sozinho autorização, concorrência ou idempotência.

## Referências

- [Diretrizes detalhadas](../ddd.md)
- [Decisões consolidadas](../DECISOES.md)
- [Escopo e critérios de validação](../EAP.md)
