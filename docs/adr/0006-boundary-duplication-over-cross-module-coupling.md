# 0006 — Aceitar duplicação localizada entre fronteiras

Status: aceito como diretriz arquitetural.

## Contexto

Um registro pode atender necessidades diferentes de auditoria, operação e apuração contratual. Compartilhar a mesma classe pode acoplar essas necessidades.

## Decisão

Aceitar modelos de dados específicos quando reduzem acoplamento conceitual entre módulos. Avaliar compartilhamento pelo significado, não apenas pela igualdade dos campos.

## Consequências

Não duplicar regras financeiras ou de autorização sem controle. Não criar DTOs distintos para funções próximas sem ganho concreto. Mudanças de schema devem localizar todos os consumidores relevantes.

## Referências

- [Diretrizes detalhadas](../ddd.md)
- [Decisões consolidadas](../DECISOES.md)
- [Escopo e critérios de validação](../EAP.md)
