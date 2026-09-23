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
- **HECATE da OM:** governança, políticas, cotas, contratos, aprovações, auditoria e operação; consome os dados necessários do Catálogo MB.
- **SavaPage:** retenção, contabilização e aplicação das regras de impressão.
- **CUPS:** filas físicas e transporte até o equipamento.
- **PostgreSQL:** persistência com isolamento lógico por componente.
- **hecate-agent:** operações locais privilegiadas, descoberta e diagnóstico.
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

O agente é um componente separado da aplicação web e concentra operações locais que exigem privilégio adicional. A interface deve oferecer apenas operações fechadas, controladas e auditáveis.

## 16. Implantação

- padrão inicial: uma VM dedicada por OM;
- CUPS, SavaPage, PostgreSQL e `hecate-agent` nativos no host;
- HECATE Web e Keycloak em Podman;
- uma instância PostgreSQL pode atender a OM, com databases e owners separados;
- distribuição institucional via Nexus;
- objetivo operacional: `dnf install hecate` seguido de `hecate-setup`.

## 17. Homologações pendentes

POCs, evidências e critérios pendentes são mantidos exclusivamente na [EAP](EAP.md#42-pocs-críticas). Decisão arquitetural aceita não significa integração homologada.

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

## 21. Instalação independente por OM

**Decisão:** cada OM opera uma instalação HECATE independente. Nexus distribui e versiona os artefatos genéricos.

**Motivo:** manter a arquitetura compatível com a operação efetiva por OM, sem criar serviço central inexistente.

**Consequência:** dados operacionais e indicadores permanecem na OM. Instalação, backup, atualização e recuperação são conduzidos por OM. Controles de leitura continuam dependentes de autorização e escopo.

## 22. Identificação institucional e Catálogo MB

**Decisão:** a instalação HECATE da OM consome diretamente o Catálogo MB. O domínio Samba AD é referência inicial; o Catálogo MB confirma a identidade oficial e a estrutura institucional da OM, com verificação pelo operador.

**Motivo:** evitar cadastro institucional paralelo e preservar o Catálogo MB como fonte autoritativa, sem depender de outra instância HECATE.

**Consequências:**

- endpoint e credencial do Catálogo MB são configurados e protegidos na instalação da OM, fora do pacote versionado;
- o técnico não deve precisar digitar código institucional da OM no fluxo normal; o domínio AD fornece a referência inicial e o operador confirma a OM encontrada;
- a instalação mantém cache técnico, somente leitura e descartável dos dados necessários à sua OM;
- o cache não é uma segunda fonte de verdade e não oferece edição dos dados recebidos;
- em cache miss ou atualização necessária, o HECATE consulta o Catálogo MB e grava o snapshot local;
- a sincronização deve admitir execução periódica e ação manual, reutilizando o mesmo caso de uso;
- indisponibilidade temporária do Catálogo MB não apaga o último snapshot válido; o estado deve indicar desatualização/falha de refresh;
- dados atualizados do Catálogo MB permitem correlacionar a estrutura institucional com usuários e grupos consultados no Samba AD, sem escrever no domínio.

**Premissa de segurança:** domínio AD validado, identificação oficial pelo Catálogo MB e confirmação pelo operador fundamentam o vínculo inicial. Divergências entre as fontes não devem ser corrigidas automaticamente; exigem verificação administrativa.

## 23. HECATE Demo

**Decisão:** manter **HECATE Demo** como variante instalável permanente baseada no mesmo código do HECATE Local, destinada a demonstração, treinamento e homologação visual/funcional.

**Motivo:** permitir demonstrações reproduzíveis com dados fictícios sem transformar a instalação local real em multi-OM nem introduzir regras de domínio exclusivas para apresentação.

**Consequências:**

- a invariável do HECATE Local permanece: uma instalação pertence a uma única OM;
- a edição demo pode oferecer seleção entre cenários, mas esse seletor é exclusivo da apresentação demo e não representa funcionalidade multi-OM do produto;
- os cenários iniciais são DCTIM, com contrato por franquia de 5.000 páginas P&B e 1.000 coloridas mais excedentes, e CTIM, com contrato por consumo e preço fixo por página P&B/colorida;
- cada cenário usa database PostgreSQL próprio (`hecate_demo_dctim` e `hecate_demo_ctim`), preservando isolamento equivalente a duas instalações locais distintas;
- migrations de produto permanecem comuns às edições; dados demonstrativos são carregados por seeds próprios e descartáveis, fora da migration funcional;
- `HECATE_EDITION=demo` habilita o comportamento demonstrativo; a edição normal não exibe seleção de cenário nem depende dos bancos demo;
- o HECATE Demo deve reutilizar as mesmas queries, regras e componentes do produto real sempre que o domínio correspondente existir;
- é proibido criar números hardcoded em dashboard ou tabelas exclusivas apenas para simular funcionalidades ainda não modeladas; novos indicadores devem esperar o schema funcional correspondente;
- dados demonstrativos devem ser determinísticos, explicitamente fictícios e recriáveis sem afetar a instalação normal.

## 24. BrandingAsset e URLs de identidade visual

**Decisão:** centralizar os assets institucionais em `BrandingAsset` e resolver suas URLs exclusivamente pelo `AssetManager` do Yii.

**Motivo:** o HECATE precisa funcionar na raiz do host, em subdiretório, multisite, reverse proxy e diferentes sistemas operacionais sem depender de caminhos absolutos ou de detalhes do servidor web.

**Consequências:**

- `BrandingAsset` usa `@public/branding` como `basePath` e `@baseUrl/branding` como `baseUrl`;
- `MainAsset` depende de `BrandingAsset`, preservando a separação entre identidade visual e CSS/JavaScript da aplicação;
- PHP usa `AssetManager::getUrl(BrandingAsset::class, $arquivo)` para logos, símbolos, favicons, backgrounds e demais assets institucionais;
- CSS recebe URLs resolvidas pelo Yii por custom properties e não contém `/branding/...` hardcoded;
- JavaScript não concatena host, porta, subdiretório ou caminho de branding;
- não criar variável específica por arquivo nem helper paralelo quando o `AssetManager` já atende ao caso;
- novos assets de branding devem aderir ao mesmo contrato;
- PHPDoc, JSDoc e CSSDoc devem documentar contratos, invariantes e integração entre camadas quando houver informação não expressa pela assinatura ou pelo próprio código, evitando comentários redundantes.
