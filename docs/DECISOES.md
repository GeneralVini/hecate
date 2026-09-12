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
- **Catálogo MB:** fonte autoritativa dos dados institucionais e organizacionais utilizados pelo HECATE.
- **Keycloak:** SSO/OIDC e base para autenticação reforçada futura.
- **HECATE Local:** governança, políticas, cotas, contratos, aprovações, auditoria e operação da OM.
- **HECATE Master:** federação, registro das instalações e consumidor exclusivo das integrações corporativas usadas no bootstrap e sincronização institucional.
- **SavaPage:** retenção, contabilização e aplicação das regras de impressão.
- **CUPS:** filas físicas e transporte até o equipamento.
- **PostgreSQL:** persistência com isolamento lógico por componente.
- **hecate-agent:** operações locais privilegiadas, descoberta, diagnóstico e comunicação periódica com o Master.
- **Nexus:** distribuição institucional de pacotes, imagens e artefatos homologados; não é CI/CD.

## 9. Identidade e organização

- integração com o domínio é somente leitura;
- preferir LDAPS;
- usar contas técnicas de privilégio mínimo;
- o Samba AD permanece fonte de identidade/autenticação da OM;
- o Catálogo MB é a fonte autoritativa de OM, código, indicativo, dados empresariais, estrutura organizacional e vínculos institucionais que forem efetivamente necessários ao HECATE;
- dados oriundos do Catálogo MB são somente leitura no HECATE e devem ser corrigidos na fonte institucional;
- dados operacionais próprios do HECATE, como locais físicos de impressão, impressoras, políticas, cotas e contratos, permanecem sob administração da solução;
- identificadores técnicos, chaves e códigos internos do HECATE não dependem de digitação do usuário.

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

O agente é um componente separado da aplicação web e concentra operações locais que exigem privilégio adicional. A interface deve oferecer apenas operações fechadas, controladas e auditáveis. O agente também pode executar a comunicação periódica Local -> Master, inclusive heartbeat e verificação de atualização dos dados institucionais.

## 16. Implantação

- padrão inicial: uma VM dedicada por OM;
- CUPS, SavaPage, PostgreSQL e `hecate-agent` nativos no host;
- HECATE Web e Keycloak em Podman;
- uma instância PostgreSQL pode atender a OM, com databases e owners separados;
- distribuição institucional via Nexus;
- objetivo operacional: `dnf install hecate` seguido de `hecate-setup`.

## 17. Homologações pendentes

POCs, evidências e critérios pendentes são mantidos exclusivamente na [EAP](EAP.md#42-pocs-críticas), incluindo o [incremento federado](EAP.md#5-incremento-de-leitura-e-federação). Decisão arquitetural aceita não significa integração homologada.

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

Decisões aceitas em chat devem ser incorporadas à fonte canônica correspondente antes de orientar mudanças posteriores. Propostas e dúvidas permanecem identificadas como abertas; memória/chat não substituem o registro versionado. Mudanças posteriores devem registrar o que foi superado e o motivo. README apresenta o projeto e remete às fontes; AGENTS orienta o trabalho sem duplicar os contratos técnicos.

## 20. Leitura otimizada e escrita protegida

**Decisão:** separar conceitualmente leitura e escrita, mantendo SQL encapsulado em queries específicas e autorização de leitura composta por permissão e escopo obrigatório dos dados.

**Motivo:** projeções de governança e contratos exigem agregações eficientes e isolamento de dados, enquanto operações de negócio exigem invariantes, persistência e auditoria confiáveis.

**Consequência:** não exigir entidades/ActiveRecord/repository para apresentação nem introduzir CQRS formal ou infraestrutura adicional. A direção API-first preserva as views atuais e a escolha aberta de frontend. Fluxos em [ARQUITETURA.md](ARQUITETURA.md#61-caminhos-de-escrita-e-leitura); perfis e escopos em [SEGURANCA.md](SEGURANCA.md#6-autorização).

## 21. Federação por agregados e identidade por instância

**Decisão:** HECATE Local envia agregados por push ao Master, consumidor de governança sem administração da operação local. Nexus distribui/versiona artefatos genéricos; o Master registra instâncias e sua associação à OM após a instalação.

**Motivo:** permitir governança central sem copiar o banco operacional, exigir acesso de entrada nas OMs ou distribuir credenciais compartilhadas no software.

**Consequência:** a conexão federada parte da OM para o Master; sincronização deve ser automática, idempotente, versionável e recuperável. A identidade própria da instância e o mecanismo M2M permanente serão definidos/homologados sem depender de credencial corporativa embutida no pacote. O bootstrap institucional descrito na seção 22 substitui a premissa anterior de exigir token manual como passo obrigatório de identificação da OM. Contrato e questões abertas em [ARQUITETURA.md](ARQUITETURA.md#13-federação-e-apis); controles em [SEGURANCA.md](SEGURANCA.md#17-segurança-da-federação); ciclo operacional em [IMPLANTACAO.md](IMPLANTACAO.md#18-enrollment-e-operação-federada).

## 22. Bootstrap institucional e Catálogo MB

**Decisão:** somente o HECATE Master consome o Catálogo MB. Durante a instalação, o HECATE Local informa e valida o domínio Samba AD da OM; o domínio é usado como referência inicial para descoberta, enquanto a identidade oficial da OM é confirmada pelo Catálogo MB por intermédio do Master.

**Motivo:** eliminar cadastro institucional duplicado nas OM, concentrar credenciais e dependências corporativas no Master e preservar o Catálogo MB como única fonte autoritativa desses dados.

**Consequências:**

- o `hecate-setup` não solicita endpoint nem credencial do Catálogo MB na OM;
- o técnico não deve precisar digitar código institucional da OM no fluxo normal; o domínio AD fornece a referência inicial e o operador confirma a OM encontrada;
- o Master mantém cache técnico, somente leitura e descartável dos snapshots das OM que efetivamente possuem HECATE instalado;
- o cache não é uma segunda fonte de verdade e não oferece edição dos dados recebidos;
- em cache miss ou atualização necessária, o Master consulta o Catálogo MB, grava o snapshot e o entrega ao HECATE Local;
- somente OM federadas entram no refresh periódico; instalações locais consultam exclusivamente o Master;
- a sincronização deve admitir execução periódica e ação manual, reutilizando o mesmo caso de uso;
- indisponibilidade temporária do Catálogo MB não apaga o último snapshot válido; o estado deve indicar desatualização/falha de refresh;
- quando houver acesso autorizado ao phpIPAM, o Master poderá verificar de forma complementar a compatibilidade do IP de origem com as redes da OM. Essa integração é opcional e não é requisito de bootstrap;
- CatalogoMB e eventual phpIPAM são integrações exclusivas do Master.

**Premissa de segurança:** o bootstrap ocorre em ambiente institucional controlado e considera cooperação entre as OM. Na primeira versão, não é requisito resistir a tentativa deliberada de personificação de outra OM por agente interno. A combinação entre domínio AD validado, identificação oficial pelo Catálogo MB e confirmação pelo operador é considerada suficiente para o bootstrap inicial. Eventual validação via phpIPAM é apenas evidência complementar. Divergências entre as fontes não devem ser corrigidas automaticamente e devem impedir o vínculo automático ou exigir verificação administrativa.
