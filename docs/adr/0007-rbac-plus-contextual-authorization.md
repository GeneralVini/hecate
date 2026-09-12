# 0007 — Combinar RBAC e autorização contextual

Status: aceito como diretriz arquitetural.

## Contexto

Um papel institucional não informa sozinho a divisão competente, ownership do job, alçada ou segregação de funções.

## Decisão

Autenticar por integração institucional e aplicar permissões HECATE com políticas contextuais no servidor. Usar Catálogo MB para atributos organizacionais preferenciais e overrides locais auditados.

## Consequências

Middleware pode exigir identidade/permissão geral; casos de uso validam contexto e recurso. Ator e contagem de páginas não podem ser aceitos como dados confiáveis do cliente. OIDC, PIN e políticas completas continuam sujeitos aos critérios da EAP.

## Referências

- [Diretrizes detalhadas](../ddd.md)
- [Decisões consolidadas](../DECISOES.md)
- [Escopo e critérios de validação](../EAP.md)
