# Decisões Técnicas do HECATE

Este documento consolida as decisões já fechadas para o HECATE e substitui a necessidade de ADRs individuais para as decisões atuais. A EAP permanece a fonte única de escopo, POCs, critérios de aceite e acompanhamento.

## 1. Produto

- Nome oficial: **HECATE**.
- Descrição: **Plataforma Institucional de Governança e Controle de Impressão**.
- O HECATE governa políticas, organização, cotas, contratos, aprovações, auditoria, indicadores e operação do serviço.

**Motivo:** separar governança institucional dos mecanismos de execução mantém o produto independente da implementação específica do spool e do controle de impressão.

## 2. Yii3

**Decisão:** utilizar Yii3 como base do backend da aplicação.

**Motivo:** DI, padrões PSR, middleware, modularidade, testabilidade e integração com o ecossistema PHP moderno.

**Consequência:** Yii3 é ferramenta da aplicação, não o modelo arquitetural do domínio. A tecnologia do frontend permanece aberta; views nativas podem ser usadas quando adequadas, mas um frontend separado por API também é compatível com a arquitetura.

## 3. Monólito modular e DDD pragmático

**Decisão:** utilizar monólito modular com DDD pragmático.

**Motivo:** o domínio exige limites claros entre responsabilidades, mas não justifica microserviços ou uma estrutura acadêmica antecipada.

**Consequência:** módulos e abstrações surgem somente quando regras, integrações ou acoplamentos concretos os justificarem.

> **A complexidade deve ser justificada pelo domínio.**

## 4. Boundaries

**Decisão:** preservar limites de responsabilidade mesmo quando isso exigir duplicação localizada.

**Motivo:** um modelo universal compartilhado aumenta o acoplamento conceitual entre módulos.

**Consequência:** módulos podem possuir DTOs e read models próprios para a mesma origem de dados.

> **Duplication between boundaries may be cheaper than coupling across boundaries.**

## 5. ActiveRecord

**Decisão:** ActiveRecord não será o modelo compartilhado da aplicação.

**Motivo:** um modelo persistente global tende a misturar responsabilidades de persistência, apresentação, integração e domínio.

**Consequência:** uso pontual fica restrito à infraestrutura e deve ser justificado.

## 6. Persistência e DTOs

**Decisão:** preferir SQL explícito e parametrizado via Yii DB quando tornar a intenção mais clara, com DTOs/read models específicos onde houver fronteira real.

**Motivo:** manter consultas compreensíveis e evitar modelos universais ou abstrações genéricas sem ganho concreto.

**Consequência:** SQL fica em componentes de consulta/persistência; repositories são criados somente quando houver benefício demonstrável.

## 7. Autorização

**Decisão:** combinar RBAC com políticas contextuais.

**Motivo:** perfis institucionais não representam sozinhos regras de escopo organizacional, alçada, estado do recurso e segregação de funções.

**Consequência:** Keycloak pode fornecer identidade e roles, enquanto o HECATE controla permissões específicas do domínio.

## 8. Responsabilidades dos componentes

- **Samba AD:** identidade institucional, somente leitura.
- **Catálogo MB:** atributos funcionais e organizacionais.
- **Keycloak:** SSO/OIDC e base para autenticação reforçada futura.
- **HECATE:** governança, políticas, cotas, contratos, aprovações e auditoria.
- **SavaPage:** retenção, contabilização e aplicação das regras de impressão.
- **CUPS:** filas físicas e transporte até o equipamento.
- **PostgreSQL:** persistência com isolamento lógico por componente.
- **hecate-agent:** operações locais privilegiadas, descoberta e diagnóstico.
- **Nexus:** distribuição institucional de pacotes, imagens e artefatos homologados; não é CI/CD.

## 9. Identidade e organização

- integração com o domínio é somente leitura;
- preferir LDAPS;
- usar contas técnicas de privilégio mínimo;
- o HECATE mantém seu próprio modelo de OM, divisão, usuários/grupos e impressoras;
- Catálogo MB é a fonte preferencial para atributos organizacionais;
- correções locais devem ser controladas e auditáveis.

## 10. Fluxo de impressão

```text
Cliente -> SavaPage -> retenção -> HECATE valida -> liberação -> CUPS -> impressora
```

- filas físicas do CUPS não são o caminho normal dos usuários;
- acesso direto às impressoras deve ser restringido pela infraestrutura sempre que possível;
- endereço IP não substitui identidade ou lotação.

## 11. Liberação e cotas

- toda impressão passa por liberação deliberada;
- o PIN pertence ao HECATE;
- cotas P&B e colorida são independentes;
- `disponível = alocado - consumido - reservado`;
- reserva ocorre antes da liberação e deve ser protegida contra concorrência;
- aceite de release não comprova impressão concluída;
- resultados incertos exigem reconciliação antes de devolver saldo ou repetir operação.

## 12. Contratos e exceções

- contratos podem ser por consumo ou franquia mensal;
- franquia contratual e distribuição interna de cotas são conceitos distintos;
- transferências de cotas devem ser autorizadas e auditadas;
- exceções de acesso devem possuir justificativa, validade e auditoria.

## 13. SavaPage e CUPS

- HECATE usa interfaces suportadas do SavaPage;
- detalhes internos do SavaPage não fazem parte do contrato do HECATE;
- o método exato de liberação de job retido permanece sujeito a POC/homologação;
- CUPS é responsável pelo transporte final ao equipamento.

## 14. Impressoras e telemetria

Ordem preferencial:

```text
IPP/IPPS -> SNMPv3 -> SNMPv2c somente leitura -> EWS/API -> parser específico -> manual
```

A ausência de telemetria não deve bloquear impressão. Dados detectados devem registrar fonte e momento da coleta.

## 15. hecate-agent

O agente é um componente separado da aplicação web e concentra operações locais que exigem privilégio adicional. A interface deve oferecer apenas operações fechadas, controladas e auditáveis.

## 16. Implantação

- padrão inicial: uma VM dedicada por OM;
- CUPS, SavaPage, PostgreSQL e `hecate-agent` nativos no host;
- HECATE Web e Keycloak em Podman;
- uma instância PostgreSQL pode atender a OM, com databases e owners separados;
- distribuição institucional via Nexus;
- objetivo operacional: `dnf install hecate` seguido de `hecate-setup`.

## 17. Homologações pendentes

Permanecem sujeitos a POC ou validação técnica:

- liberação de job retido no SavaPage por interface suportada;
- regras dinâmicas de acesso no SavaPage;
- contabilização P&B/colorida em diferentes drivers e fabricantes;
- atribuição confiável de usuário nos clientes;
- telemetria multi-fabricante;
- fallback para Catálogo MB indisponível ou desatualizado.

## 18. Qualidade e hooks de desenvolvimento

**Decisão:** adotar Lefthook, Rector, ECS, PHPStan, Psalm e PHPUnit como baseline de qualidade.

**Motivo:** separar claramente orquestração de hooks, autofix/refatoração, coding standard, análise estática e testes, mantendo o fluxo válido mesmo se o repositório passar a incluir frontend separado ou outras stacks além de PHP.

**Consequências:**

- Lefthook é o orquestrador de hooks Git;
- Rector aplica refatorações automáticas homologadas;
- ECS é a fonte do coding standard PHP e do autofix de estilo;
- PHPStan é a análise estática principal;
- Psalm permanece como análise complementar;
- PHPUnit valida comportamento;
- `composer qa` é o comando de validação integral para desenvolvimento e CI;
- `composer fix` executa apenas correções automáticas determinísticas;
- PHPCS/PHPCBF deixam de ser ferramentas diretas do projeto;
- hooks locais não substituem a validação no CI.

## 19. Regra documental

Não criar novo arquivo Markdown quando o conteúdo puder ser incorporado claramente a um documento canônico existente.

Documentos canônicos em `docs/`:

- `EAP.md` — escopo, POCs, aceite e acompanhamento;
- `ARQUITETURA.md` — arquitetura, boundaries, fluxos e integrações;
- `DECISOES.md` — decisões e respectivos motivos;
- `DESENVOLVIMENTO.md` — ambiente, qualidade e documentação de código;
- `SEGURANCA.md` — controles de segurança e auditoria;
- `IMPLANTACAO.md` — instalação, operação e replicação;
- `IDENTIDADE-VISUAL.md` — identidade e UI institucional.
