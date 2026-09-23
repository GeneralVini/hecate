# Prompt consolidado para revisão global do HECATE

Revise integralmente o projeto **HECATE**, considerando todas as decisões arquiteturais já consolidadas no repositório e as decisões mais recentes registradas abaixo.

Este prompt é **incremental, consolidativo e corretivo**.

Não reabra decisões que já estão fechadas neste prompt.

Não recrie componentes anteriormente descartados.

Não crie novos arquivos `.md` sem necessidade.

Não transforme a revisão em uma nova arquitetura paralela.

Antes de modificar qualquer arquivo:

1. leia `README.md`;
2. leia `AGENTS.md`;
3. leia todos os documentos canônicos em `docs/`;
4. examine a estrutura atual do código;
5. identifique decisões anteriores que foram superadas;
6. identifique contradições entre documentos;
7. identifique contradições entre documentação e código;
8. identifique funcionalidades descritas como entregues sem evidência na EAP;
9. somente então faça as alterações.

Ao final, faça obrigatoriamente uma segunda leitura global do projeto já consolidado.

---

## 1. Princípio arquitetural central

Preservar:

> **A complexidade deve ser justificada pelo domínio.**

Não introduzir:

- microserviços;
- CQRS formal;
- command bus;
- query bus;
- event sourcing;
- message broker;
- repositories genéricos;
- abstrações antecipadas;
- camadas sem necessidade concreta;
- novos serviços centrais sem requisito real.

O HECATE deve continuar sendo uma solução simples, replicável, auditável e administrável pelas equipes das OM.

---

## 2. HECATE como superfície central de governança

O **HECATE é o componente central de governança e administração funcional da solução**.

Os componentes subjacentes não devem ser expostos ao usuário final ou ao administrador funcional quando a operação puder ser realizada de forma controlada pelo HECATE.

Aplicar esse princípio a:

- SavaPage;
- CUPS;
- Keycloak;
- Samba AD;
- PostgreSQL;
- impressoras;
- `hecate-agent`.

O acesso administrativo direto a esses componentes deve ficar restrito à equipe técnica e ser usado somente para manutenção, diagnóstico, homologação ou contingência.

Exemplo:

```text
Usuário / administrador funcional
            ↓
          HECATE
            ↓
        SavaPage
            ↓
          CUPS
            ↓
       Impressora
```

O usuário não deve precisar acessar a interface administrativa do SavaPage para operar o serviço normal.

O HECATE governa o processo; os demais componentes executam responsabilidades específicas.

---

## 3. HECATE Master — decisão superada

O **HECATE Master deve ser removido da arquitetura atual**.

Não avaliar se ele deve permanecer.

Não propor mantê-lo reduzido.

Não renomeá-lo para Governance, Central, Hub ou equivalente.

Não preservar federação apenas por possibilidade futura.

A necessidade anteriormente atribuída ao Master foi revista e deixou de justificar um componente próprio.

Foram eliminadas as seguintes necessidades:

- bootstrap institucional via Master;
- intermediação do Catálogo MB;
- cache institucional central;
- API DPM intermediada;
- phpIPAM intermediado;
- enrollment de instalações;
- identidade federada das instalações;
- comunicação Local → Master;
- heartbeat federado;
- push de agregados;
- armazenamento central de métricas;
- governança consolidada entre OM;
- Federation API;
- credenciais M2M entre Local e Master;
- registro de instância no Master.

Remover das decisões arquiteturais vigentes referências a:

```text
HECATE Master
Master
federation
federated
Federation API
enrollment
instance registration
installation_uuid usado para Master
instance_id federado
HECATE_MASTER_URL
heartbeat
Local -> Master
push de agregados
schema_version federado
sequence federado
governança central
cache institucional no Master
M2M Local ↔ Master
OAuth2 Client Credentials para federação
mTLS de federação
```

Quando necessário para preservar contexto histórico, registrar apenas que essa arquitetura foi **superada pela integração direta das OM com o Catálogo MB e pela eliminação do requisito de governança central**.

Caso futuramente surja requisito institucional concreto de consolidação nacional, isso será objeto de nova decisão arquitetural.

Não projetar essa capacidade agora.

---

## 4. Catálogo MB — fonte institucional autoritativa

Adotar como decisão:

> **O Catálogo MB é a fonte autoritativa dos dados institucionais, organizacionais e dos vínculos funcionais utilizados pelo HECATE.**

Isso inclui, conforme disponíveis no Catálogo:

- identificação da OM;
- estrutura organizacional;
- Elementos Organizacionais;
- lotação;
- vínculos institucionais;
- chefias;
- ajudantes;
- pessoas;
- NIP;
- nome;
- nome de guerra;
- RETELMA;
- e-mail;
- demais atributos institucionais necessários.

Quando houver divergência entre o Catálogo MB e outra fonte institucional secundária:

```text
Catálogo MB prevalece.
```

Não criar mecanismo permanente para decidir:

```text
Catálogo diz A
outra API diz B
qual vale?
```

A resposta institucional é:

```text
Catálogo MB é a referência.
```

Se o dado estiver incorreto:

```text
corrigir o Catálogo MB
```

e não:

```text
corrigir localmente no HECATE
```

O objetivo também é induzir as OM a manterem o Catálogo MB atualizado.

---

## 5. Eliminar dependência da API da DPM

A API da DPM não deve ser utilizada pelo HECATE como segunda fonte para corrigir ou complementar silenciosamente dados que pertencem ao Catálogo MB.

Se os dados institucionais necessários já são responsabilidade do Catálogo MB:

```text
Catálogo MB
= única fonte autoritativa
```

Evitar:

```text
Catálogo MB
+
DPM
+
regra de reconciliação
```

Localizar referências à API DPM no código e documentação.

Classificar:

- já implementada;
- apenas planejada;
- obsoleta;
- ainda necessária por algum motivo não relacionado ao cadastro institucional.

Se não houver outra responsabilidade concreta, retirar da arquitetura.

---

## 6. Acesso direto das OM ao Catálogo MB

As OM conseguem acessar diretamente a API do Catálogo MB.

A API não exige autenticação.

Portanto, não existe necessidade de intermediário central para essa integração.

Modelo desejado:

```text
HECATE da OM
      |
      +------> Catálogo MB
```

Não reintroduzir:

```text
HECATE Local
     ↓
HECATE Master
     ↓
Catálogo MB
```

A indisponibilidade do Catálogo MB não deve interromper autenticação, impressão ou operação normal do HECATE.

Quando necessário, utilizar snapshot/cache local derivado, somente leitura e descartável.

Esse cache não é uma segunda fonte de verdade.

---

## 7. Identidade da OM

O Catálogo MB possui:

```text
COD_OM
SIGLA
INDICATIVO_NAVAL
```

O domínio Samba da OM segue a convenção:

```text
{INDICATIVO_NAVAL}.AD
```

Exemplo:

```text
INDICATIVO_NAVAL = COMTIM
domínio Samba = COMTIM.AD
```

A descoberta da OM pode utilizar:

```text
COMTIM.AD
   ↓
COMTIM
   ↓
consulta Catálogo MB
   ↓
OM correspondente
```

Usar preferencialmente `COD_OM` como identificador institucional estável da OM dentro do HECATE.

Tratar:

- nome;
- SIGLA;
- INDICATIVO_NAVAL;
- domínio Samba;

como atributos.

Não usar nome textual como identidade da OM.

---

## 8. NIP — identidade da pessoa

O NIP é o identificador institucional estável da pessoa.

Exemplo:

```text
09051937
```

O login do usuário no Samba da OM também é o próprio NIP.

Portanto:

```text
Catálogo MB.NIP
=
Samba login
```

A correlação Catálogo MB ↔ Samba é determinística.

Obrigatoriamente tratar NIP como:

```text
string
```

Nunca como inteiro.

Zeros à esquerda são significativos para sua representação.

Não usar como chave principal de correlação:

- nome;
- nome de guerra;
- e-mail;
- comparação aproximada;
- login derivado;

quando o NIP estiver disponível.

---

## 9. Elementos Organizacionais

O Catálogo MB possui estrutura organizacional hierárquica.

Exemplo:

```text
10      Departamento A
11      Divisão do Departamento A
11.1    Setor da Divisão 11
```

O código do Elemento Organizacional deve ser tratado como:

```text
string
```

Nunca como inteiro ou decimal.

Valores como:

```text
11.1
```

são identificadores organizacionais, não números matemáticos.

Não inferir a estrutura pelo nome.

Não recriar manualmente no HECATE a hierarquia que já existe no Catálogo MB.

---

## 10. Chefias e atributos institucionais

Elementos Organizacionais podem possuir informações como:

```text
chefe
ajudante
NIP
nome de guerra
RETELMA
e-mail
```

Essas informações pertencem ao Catálogo MB.

O HECATE pode consumi-las para:

- contexto organizacional;
- autorização;
- políticas;
- exibição;
- auditoria;

quando necessário.

Não criar automaticamente grupos Samba específicos para cada função apenas porque o atributo existe.

Exemplo:

```text
CHEFE_DEP_10
AJUDANTE_DIV_11
```

só deve existir se houver necessidade operacional real.

---

## 11. Catálogo MB e Samba AD

Adotar conceitualmente:

```text
Catálogo MB
= estado institucional autoritativo

Samba AD
= representação operacional local
```

Para os dados institucionais sob responsabilidade do Catálogo, o Samba não é uma segunda fonte de verdade.

Quando houver divergência:

```text
Catálogo = referência correta
Samba = estado divergente a corrigir
```

O HECATE deve ser capaz de:

1. consultar o Catálogo MB;
2. consultar o Samba AD;
3. comparar estado institucional desejado com estado atual;
4. apresentar divergências;
5. gerar plano de correção;
6. gerar comandos administrativos determinísticos;
7. permitir que o administrador execute esses comandos manualmente;
8. validar posteriormente o resultado.

---

## 12. Integração inicial HECATE ↔ Samba

Nesta fase, **o HECATE não deve escrever automaticamente no Samba AD**.

Não conceder ao `hecate-agent` conta de escrita no domínio como requisito inicial.

Não conceder Domain Admin.

Não automatizar alteração de grupos/memberships antes de POC e homologação.

O fluxo inicial deve ser:

```text
Catálogo MB
     ↓
   HECATE
     ↓
consulta Samba por LDAP/LDAPS
     ↓
compara estado desejado x estado atual
     ↓
gera plano de alteração
     ↓
administrador da OM revisa
     ↓
HECATE gera comandos
     ↓
administrador executa no shell
     ↓
HECATE consulta novamente o Samba
     ↓
valida o resultado
```

O HECATE permanece dono do **processo de reconciliação**.

A execução privilegiada no domínio permanece sob ação explícita do administrador.

---

## 13. HECATE deve gerar plano, não comandos soltos

A interface deve primeiro apresentar o plano de reconciliação em linguagem operacional.

Exemplo:

```text
Sincronização Catálogo MB → Samba

+ Criar grupo institucional do Elemento 11.1
+ Adicionar NIP 09051937 ao Elemento 11.1
- Remover NIP 09051937 do Elemento 10

Nenhum grupo técnico local será alterado.

ADMINS: ignorado
USB: ignorado
```

Depois o administrador poderá solicitar:

```text
Gerar comandos
```

Os comandos gerados devem corresponder somente às operações previstas no plano.

---

## 14. Comandos Samba gerados pelo HECATE

Avaliar preferencialmente o uso das ferramentas administrativas suportadas pelo Samba, como `samba-tool`, quando adequadas ao ambiente.

Exemplos conceituais:

```bash
samba-tool group add "ORG-11.1"
samba-tool group addmembers "ORG-11.1" "09051937"
samba-tool group removemembers "ORG-10" "09051937"
```

Os nomes acima são apenas exemplos.

Não definir ainda nomenclatura final dos grupos.

Também pode ser avaliado LDAP administrativo equivalente, mas a primeira implementação deve priorizar simplicidade operacional e comandos auditáveis.

O HECATE não deve gerar shell arbitrário.

As operações geráveis devem pertencer a uma lista fechada, por exemplo:

```text
CREATE_MANAGED_GROUP
RENAME_MANAGED_GROUP
ADD_MANAGED_MEMBERSHIP
REMOVE_MANAGED_MEMBERSHIP
```

Não permitir ao usuário fornecer comandos livres.

---

## 15. Validação após execução manual

Após o administrador executar os comandos:

```text
HECATE
   ↓
consulta Samba novamente
   ↓
compara com Catálogo MB
   ↓
resultado
```

Exemplo:

```text
0 divergências
Sincronização validada
```

ou:

```text
2 divergências restantes
```

A execução manual não deve ser considerada concluída apenas porque o HECATE gerou comandos.

---

## 16. `memberOf`

Não tratar `memberOf` como atributo diretamente escrito pelo HECATE.

O administrador altera a associação do usuário ao grupo usando os comandos gerados.

O Samba/AD reflete isso em `memberOf`.

Exemplo:

```text
Catálogo:
NIP 09051937
Elemento 11.1

        ↓

HECATE:
gera comando de associação

        ↓

Administrador:
executa no Samba

        ↓

Samba:
memberOf passa a refletir o vínculo
```

---

## 17. Grupos institucionais versus grupos técnicos locais

Distinguir explicitamente:

```text
grupos institucionais gerenciados
```

de:

```text
grupos técnicos locais
```

Hoje existem grupos técnicos locais como:

```text
ADMINS
USB
```

`USB` representa usuários autorizados a utilizar mídias removíveis.

Esses grupos não pertencem à autoridade do Catálogo MB.

O HECATE nunca deve gerar remoção ou alteração desses grupos apenas porque eles não existem no Catálogo.

Exemplo conceitual:

```text
Institucional
→ Departamento
→ Divisão
→ Setor

Local
→ ADMINS
→ USB
→ outros grupos técnicos
```

---

## 18. Não automatizar ainda o ciclo completo da conta Samba

A decisão atual NÃO é:

```text
Catálogo cria usuário Samba
Catálogo exclui usuário Samba
Catálogo desabilita usuário Samba
```

A capacidade inicial deve se concentrar em:

```text
estrutura institucional
grupos institucionais
memberships
```

Criação, desativação ou exclusão de contas requer decisão específica posterior.

---

## 19. Ciclo de vida e movimentação

Uma pessoa pode:

- sair de uma OM;
- levar até cerca de 30 dias para assumir nova função;
- estar em processo de movimentação;
- estar em reposicionamento dentro da mesma OM;
- ficar temporariamente sem lotação definitiva.

Portanto:

> **Ausência temporária de lotação não significa desligamento da pessoa.**

Separar:

```text
identidade
lotação
estado funcional
conta Samba
```

Nunca implementar:

```text
sumiu do Catálogo
→ apagar usuário
```

ou:

```text
sem lotação atual
→ desabilitar usuário
```

sem uma regra institucional específica.

---

## 20. Outra OM versus transição

Distinguir:

```text
lotado explicitamente em outra OM
```

de:

```text
temporariamente sem lotação definida
```

Se o Catálogo indicar claramente que o usuário pertence a outra OM, os vínculos organizacionais da OM anterior podem precisar ser removidos.

Se estiver em transição, não assumir remoção imediata.

Registrar a política exata de remoção de memberships como ponto de homologação enquanto não estiver definida.

---

## 21. Fases da reconciliação Samba

Adotar progressão conservadora:

```text
Fase 1
dry-run / relatório de divergências

Fase 2
HECATE gera plano de correção

Fase 3
HECATE gera comandos
administrador executa manualmente

Fase 4
HECATE valida o resultado

Fase futura
automação direta somente se houver justificativa e homologação
```

Não introduzir escrita automática no domínio agora.

---

## 22. Proteção contra alterações em massa

Mesmo no modo de geração de comandos, detectar alterações anormais.

Exemplo:

```text
mudança anormal de grande parte dos usuários
→ não gerar automaticamente lote executável
→ alertar administrador
→ exigir análise
```

Avaliar:

- limite percentual;
- limite absoluto;
- detecção de resposta incompleta;
- último snapshot válido;
- comparação com execução anterior;
- destaque de alterações destrutivas.

---

## 23. Segurança da API do Catálogo

Embora a API não exija autenticação, manter:

- HTTPS;
- validação de certificado;
- endpoint institucional conhecido;
- timeout;
- retry controlado;
- validação de schema;
- proteção contra payload inesperado;
- logs operacionais;
- tratamento explícito de indisponibilidade.

“Sem autenticação” não significa “sem validação”.

---

## 24. Elemento Organizacional ↔ Local

Manter como decisão:

> **Elemento Organizacional e Local são conceitos diferentes.**

Catálogo MB:

```text
Departamento
Divisão
Setor
```

HECATE:

```text
Local
```

`Local` representa organização física/lógica da infraestrutura de impressão.

Relacionamento:

```text
Elemento Organizacional N:N Local
```

Exemplos válidos:

```text
Divisão A
→ Local 1
→ Local 2

Local 3
→ Divisão A
→ Divisão B
```

Não copiar nomes organizacionais para `Local` como substituto do relacionamento.

---

## 25. Local ↔ Impressora

Manter:

```text
Local N:N Impressora
```

Não modelar obrigatoriamente:

```text
uma impressora → apenas um Local
```

Uma impressora pode atender múltiplos locais lógicos quando necessário.

---

## 26. Cadeia organizacional, políticas e cotas de impressão

O HECATE deve relacionar a estrutura institucional vinda do Catálogo MB com os recursos e regras próprias do domínio de impressão.

Modelo conceitual:

```text
Pessoa
NIP string
    ↓
Elemento Organizacional
    N:N
     ↓
   Local
    N:N
     ↓
 Impressora
```

Essa estrutura não determina sozinha o direito de imprimir.

O HECATE deve aplicar também:

```text
Pessoa / Elemento Organizacional
        ↓
      Política
        ↓
       Cota
        ↓
Local / Impressora autorizados
        ↓
Contrato aplicável
        ↓
Release / Accounting / Auditoria
```

As cotas continuam sendo capacidade própria do HECATE.

Avaliar e preservar, conforme o domínio já documentado:

```text
cota por usuário
cota por Elemento Organizacional
cota por grupo/setor
cota derivada de política
```

Não assumir antecipadamente uma única origem de cota.

A decisão de impressão pode considerar:

- identidade;
- Elemento Organizacional;
- Local;
- Impressora;
- política;
- cota disponível;
- contrato;
- exceção/autorização;
- estado operacional.

As cotas P&B e colorida permanecem independentes.

Preservar:

```text
disponível = alocado - consumido - reservado
```

A reserva ocorre antes da liberação do job.

A cadeia organizacional serve para determinar escopo e aplicabilidade das regras, mas não substitui o mecanismo de cotas do HECATE.

---

## 27. Fonte de verdade por domínio

Consolidar claramente:

```text
Catálogo MB
→ pessoa
→ OM
→ Elementos Organizacionais
→ estrutura
→ lotação
→ chefias
→ vínculos institucionais

Samba AD
→ autenticação local
→ memberships
→ representação operacional da estrutura institucional
→ grupos técnicos locais

Keycloak
→ SSO/OIDC
→ identidade para aplicações
→ roles/grupos quando necessários

HECATE
→ governança central da solução
→ administração funcional dos componentes
→ governança de impressão
→ Local
→ Impressora
→ políticas
→ cotas
→ contratos
→ release
→ auditoria
→ indicadores
→ reconciliação Catálogo MB ↔ Samba
→ geração de plano/comandos administrativos

SavaPage
→ retenção
→ accounting
→ enforcement
→ fluxo de impressão

CUPS
→ spool/transporte final

hecate-agent
→ executor local privilegiado apenas quando necessário
→ descoberta
→ telemetria
→ diagnóstico
→ operações fechadas de sistema operacional

PostgreSQL
→ persistência HECATE/SavaPage/Keycloak conforme databases próprios

Nexus
→ distribuição/versionamento de artefatos
```

O `hecate-agent` não é o componente de governança.

Ele é subordinado ao HECATE e executa operações locais privilegiadas quando necessário.

---

## 28. Fluxo de impressão

Preservar:

```text
Cliente
   ↓
SavaPage
   ↓
job retido
   ↓
HECATE valida identidade/contexto/política/cota
   ↓
reserva
   ↓
confirmação/release
   ↓
SavaPage
   ↓
CUPS
   ↓
Impressora
   ↓
accounting/reconciliação
```

Não expor SavaPage como interface funcional normal do usuário.

Não fazer o HECATE manipular banco interno ou spool não suportado do SavaPage.

---

## 29. Cotas

Preservar:

```text
disponível = alocado - consumido - reservado
```

P&B e colorido são independentes.

Reserva ocorre antes da liberação.

Resultado incerto exige reconciliação antes de devolver cota ou reenviar job.

---

## 30. Contratos

Preservar os modelos já definidos:

- contrato por consumo;
- contrato por franquia;
- valores P&B/colorido;
- excedentes;
- consumo contratual.

Não confundir:

```text
franquia contratual da OM
```

com:

```text
distribuição interna de cotas
```

---

## 31. Read Path / Write Path

Preservar explicitamente:

```text
WRITE PATH

Request
  ↓
Use Case
  ↓
Authorization / Policies
  ↓
Business Rules
  ↓
Persistence
  ↓
Audit
```

```text
READ PATH

Request
  ↓
Read Authorization / Scope
  ↓
Query específica
  ↓
SQL otimizado
  ↓
DTO / Read Model
```

Não transformar isso em CQRS formal.

Não criar infraestrutura adicional sem necessidade.

---

## 32. SQL no Read Path

Preservar SQL explícito e parametrizado para:

- dashboards;
- grids;
- relatórios;
- indicadores;
- inventário;
- contratos;
- acompanhamento;
- consultas administrativas.

O PostgreSQL deve executar:

- joins;
- filtros;
- agregações;
- ordenações;
- cálculos relacionais.

Não forçar ActiveRecord/entidade/repository para produzir projeções de tela.

O SQL deve permanecer encapsulado em componentes identificáveis de consulta.

---

## 33. ActiveRecord

Preservar:

> **ActiveRecord não é o modelo compartilhado da aplicação.**

Pode existir pontualmente na infraestrutura quando houver justificativa concreta.

Não basear domínio, APIs, apresentação e integrações no mesmo modelo ActiveRecord.

---

## 34. DTOs e boundaries

Preservar:

> **Duplicação entre boundaries pode ser mais barata que acoplamento entre boundaries.**

DTOs/read models podem ser específicos de:

- tela;
- relatório;
- integração;
- caso de uso;
- boundary.

Não criar modelo universal para evitar duplicação mecânica.

---

## 35. Repository

Não criar repositories automaticamente apenas porque existe DDD.

Usar somente quando houver benefício arquitetural concreto.

Evitar:

```text
GenericRepository
AbstractRepository
BaseRepository<T>
BaseEntity
```

sem necessidade real.

---

## 36. DDD pragmático

Preservar:

```text
Yii3
+
monólito modular
+
DDD pragmático
```

DDD não significa:

- criar Value Object para todo campo;
- criar interface para toda classe;
- criar aggregate sem regra real;
- criar Domain Event antecipadamente;
- separar diretórios vazios apenas para seguir livro.

O domínio real determina a estrutura.

---

## 37. RBAC e autorização contextual

Preservar:

```text
RBAC
+
políticas contextuais
```

Keycloak pode fornecer:

- identidade;
- roles;
- groups;
- atributos.

HECATE continua responsável por autorização específica do domínio.

Exemplo:

```text
permission
+
Elemento Organizacional
+
Local
+
contrato
+
estado do recurso
+
alçada
```

Não confiar apenas no nome da role para decisões de domínio.

---

## 38. Autorização de leitura

Preservar:

```text
Request
  ↓
RBAC / Permission
  ↓
Resolve escopo autorizado
  ↓
Query
  ↓
SQL restrito
```

Separar:

```text
pode acessar endpoint
```

de:

```text
quais dados pode visualizar
```

Escopo ausente não significa acesso global.

---

## 39. Keycloak

Keycloak continua responsável por:

- SSO;
- OIDC;
- autenticação da aplicação;
- base para MFA futuro.

Não armazenar senha do domínio no HECATE.

Samba continua sendo a origem operacional de autenticação institucional da OM.

---

## 40. Frontend

Manter a escolha aberta.

Não fechar automaticamente:

- React;
- Vue;
- SPA;
- Yii3 server-rendered;
- híbrido.

API-first não significa obrigatoriamente SPA.

Views Yii3 continuam possíveis.

A camada de apresentação não deve conter regras de domínio.

---

## 41. API-first

Preservar API-first como direção do backend quando houver consumidores reais:

- frontend;
- agente;
- integrações;
- possível aplicativo móvel;
- sistemas futuros.

Não transformar toda operação interna em HTTP sem necessidade.

Não existe mais Federation API porque o Master foi eliminado.

---

## 42. HECATE Demo

Preservar decisões existentes sobre HECATE Demo, desde que não dependam do Master eliminado.

HECATE Demo deve continuar, se já consolidado, como variante baseada no mesmo código para:

- demonstração;
- treinamento;
- homologação visual/funcional;
- dados fictícios.

Não transformar Demo em multi-OM real nem reintroduzir federação por causa dele.

---

## 43. Nexus

Preservar:

```text
Nexus
→ distribuição institucional
→ RPM/DNF
→ imagens OCI
→ artefatos homologados
→ versionamento
```

Nexus não é:

- CI/CD;
- identidade;
- Catálogo MB;
- HECATE Master;
- registro de OM;
- enrollment.

---

## 44. Implantação

Modelo inicial continua sendo uma VM por OM.

Manter, salvo decisão posterior explícita:

```text
Oracle Linux

Nativo:
  CUPS
  SavaPage
  PostgreSQL
  hecate-agent

Podman:
  HECATE Web
  Keycloak
```

Objetivo:

```bash
dnf install hecate
hecate-setup
```

Remover do `hecate-setup` qualquer dependência de:

```text
HECATE Master
enrollment
Master URL
credencial federada
registration
```

O setup pode usar o domínio Samba local para identificar a OM no Catálogo MB.

---

## 45. Segurança do hecate-agent

Preservar:

- lista fechada de operações;
- sem shell arbitrário;
- sem sudo genérico para aplicação web;
- auditoria das operações sensíveis.

Não transformar o `hecate-agent` em administrador do Samba nesta fase.

A reconciliação Catálogo MB ↔ Samba pertence ao HECATE, mas a execução das alterações de domínio permanece manual pelo administrador através dos comandos gerados.

---

## 46. Impressoras e telemetria

Preservar a ordem preferencial:

```text
IPP/IPPS
→ SNMPv3
→ SNMPv2c read-only
→ EWS/API
→ parser específico
→ manual
```

Ausência de telemetria não deve bloquear impressão.

---

## 47. Impressão direta

Preservar:

- clientes apontam para fluxo controlado;
- filas físicas CUPS não são caminho normal;
- acesso direto ao IP das impressoras deve ser restringido quando possível;
- HECATE não depende do firewall como componente interno do produto.

---

## 48. Qualidade de código

Preservar o baseline atual do projeto.

Conferir no repositório a configuração efetiva, incluindo quando aplicável:

- Lefthook;
- Rector;
- ECS / PSR-12;
- PHPStan;
- Psalm;
- PHPUnit;
- Composer Audit;
- Psalm Taint;
- Semgrep CE;
- OWASP ZAP.

Preservar os comandos definidos no projeto, incluindo:

```bash
composer qa
composer security
composer check
composer fix
```

conforme estado atual do repositório.

---

## 49. Segurança de aplicação

Preservar preocupação explícita com:

- SQL Injection;
- XSS;
- CSRF;
- command injection;
- SSRF;
- path traversal;
- autenticação;
- autorização;
- exposição de segredos;
- dependências vulneráveis;
- sanitização;
- validação;
- auditabilidade.

SQL explícito nunca significa SQL concatenado inseguramente.

Usar parâmetros.

A geração de comandos Samba também deve proteger contra command injection.

Nunca interpolar texto institucional não validado diretamente no shell.

---

## 50. UI e componentes reutilizáveis

Preservar a decisão de reaproveitar componentes de interface.

Evitar CSS/JS específico por página salvo justificativa real.

Reutilizar:

- layouts;
- grids;
- alerts;
- componentes;
- assets;
- padrões de tela.

---

## 51. Documentação enxuta

Preservar os documentos canônicos atuais.

Não criar novos `.md` quando o conteúdo couber claramente em um documento existente.

Estrutura esperada:

```text
README.md
AGENTS.md

docs/
  EAP.md
  ARQUITETURA.md
  DECISOES.md
  DESENVOLVIMENTO.md
  SEGURANCA.md
  IMPLANTACAO.md
  IDENTIDADE-VISUAL.md
```

Não recriar `docs/adr/` apenas para registrar estas decisões.

---

## 52. Responsabilidade de cada documento

Manter:

```text
README.md
→ apresentação e síntese

AGENTS.md
→ regras para agentes

DECISOES.md
→ decisões aceitas e motivos

ARQUITETURA.md
→ estrutura e fluxos resultantes

SEGURANCA.md
→ trust boundaries, privilégios e controles

IMPLANTACAO.md
→ instalação, operação e rollout

DESENVOLVIMENTO.md
→ ambiente, qualidade e práticas de código

EAP.md
→ escopo, POCs, critérios, evidências e pendências

IDENTIDADE-VISUAL.md
→ identidade visual e UI
```

Evitar duplicação entre eles.

---

## 53. EAP continua sendo autoridade sobre estado de implementação

Não declarar uma funcionalidade como implementada apenas porque foi decidida arquiteturalmente.

Especial atenção para:

```text
Catálogo MB direto
reconciliação Catálogo ↔ Samba
relatório de divergências
geração de comandos Samba
validação pós-execução
N:N Elemento ↔ Local
N:N Local ↔ Impressora
```

Se não estiverem implementados/testados:

```text
decisão arquitetural aceita
≠
implementação concluída
```

Registrar POCs e critérios na EAP.

---

## 54. Procurar explicitamente resíduos da arquitetura superada

Pesquisar em todo o projeto por:

```text
Master
HECATE Master
federation
federado
federada
enrollment
heartbeat
instance_id
installation_uuid
HECATE_MASTER_URL
Federation API
Local -> Master
Master -> Catálogo
Master/Catálogo
Master cache
governança central
push de métricas
M2M
client_credentials
mTLS de federação
phpIPAM via Master
DPM
```

Para cada ocorrência:

```text
remover
reescrever
manter apenas como histórico
ou justificar concretamente
```

A regra padrão é remover do desenho atual.

---

## 55. Revisão do modelo de dados

Verificar especialmente:

```text
NIP
```

deve ser `string`.

Verificar:

```text
Elemento Organizacional
```

código deve aceitar:

```text
10
11
11.1
```

como strings.

Verificar cardinalidades:

```text
Elemento Organizacional N:N Local
Local N:N Impressora
```

Não aceitar silenciosamente modelo 1:N se contrariar essas decisões.

---

## 56. Não criar dependência direta desnecessária do Catálogo durante cada request

O Catálogo é autoritativo, mas isso não significa:

```text
cada request web
→ consulta API Catálogo
```

Avaliar cache/snapshot local quando necessário para:

- resiliência;
- desempenho;
- operação durante indisponibilidade.

Esse cache é:

```text
derivado
somente leitura
descartável
```

Não é uma segunda fonte de verdade.

---

## 57. Critério para novas abstrações

Antes de criar qualquer:

- classe base;
- interface;
- repository;
- service;
- DTO;
- Value Object;
- evento;
- módulo;

responder:

1. qual problema concreto resolve?
2. qual boundary protege?
3. qual acoplamento reduz?
4. qual comportamento ou teste exige sua existência?

Se a justificativa for apenas:

```text
DDD
SOLID
boa prática
pode ser útil depois
```

não criar.

---

## 58. Revisão do código

Depois de consolidar a documentação, revisar o código apenas para identificar incompatibilidades.

Não realizar refatoração grande automaticamente.

Apresentar para cada problema:

```text
arquivo
trecho/responsabilidade
decisão afetada
impacto
alteração mínima sugerida
```

Pode corrigir inconsistências triviais e de baixo risco.

Não implementar automaticamente sincronização Samba, remoção de estruturas complexas ou migrações de alto impacto sem primeiro avaliar o código existente.

---

## 59. Segunda revisão global obrigatória

Após as alterações documentais, releia novamente:

```text
README.md
AGENTS.md
docs/*.md
```

e confronte com a estrutura atual do código.

Confirme explicitamente:

- HECATE Master não permanece na arquitetura;
- não existe federação residual;
- não existe governança central como requisito;
- Catálogo MB é a fonte autoritativa;
- DPM não aparece como segunda fonte institucional;
- NIP é string;
- login Samba corresponde ao NIP;
- `COD_OM` é referência estável da OM;
- domínio utiliza o indicativo naval;
- código de Elemento Organizacional é string;
- estrutura organizacional não é duplicada manualmente no HECATE;
- Elemento Organizacional ↔ Local é N:N;
- Local ↔ Impressora é N:N;
- cotas continuam fazendo parte da decisão de impressão;
- grupos `ADMINS` e `USB` continuam locais;
- `memberOf` não é tratado como atributo escrito diretamente;
- usuário em transição não é automaticamente apagado/desabilitado;
- HECATE governa a reconciliação com Samba;
- HECATE apenas gera plano/comandos nesta fase;
- administrador executa os comandos no shell;
- HECATE valida o resultado;
- `hecate-agent` não recebeu indevidamente autoridade de escrita no domínio;
- HECATE continua sendo a superfície administrativa central;
- SavaPage/CUPS/Keycloak não são expostos como interfaces concorrentes ao usuário;
- implantação não exige Master;
- EAP não confunde decisão com implementação;
- Nexus continua apenas como distribuição;
- read/write paths continuam válidos;
- frontend continua aberto;
- ActiveRecord não voltou a ser modelo compartilhado;
- nenhuma nova complexidade foi introduzida sem necessidade.

---

## 60. Resultado final esperado

Ao terminar, apresentar:

### A. Arquivos modificados

Listar todos.

### B. Decisões superadas

Especialmente:

```text
HECATE Master
federação
governança central
DPM como segunda fonte
Samba absolutamente read-only
automação direta de escrita no Samba pelo agente
```

explicando pelo que foram substituídas.

### C. Decisões preservadas

Incluindo:

- HECATE como superfície central de governança;
- Yii3;
- monólito modular;
- DDD pragmático;
- SQL explícito;
- DTO/read models;
- read/write paths;
- RBAC contextual;
- Nexus;
- SavaPage/CUPS;
- agente privilegiado subordinado ao HECATE;
- cotas;
- contratos;
- qualidade;
- segurança.

### D. Pontos ainda não implementados

Extraídos da EAP e do código real.

### E. Pontos ainda abertos

Não inventar respostas para:

- política final de remoção de membership;
- contas Samba em movimentação;
- nested groups versus memberships diretos;
- nomenclatura final dos grupos institucionais;
- comandos exatos por distribuição/versão do Samba;
- política futura de automação direta;
- limites de proteção em massa.

### F. Conflitos encontrados

Documentação × documentação.

Documentação × código.

Decisão × implementação.

---

# Arquitetura alvo resultante

A revisão deve convergir para este modelo conceitual:

```text
                         Catálogo MB
                  fonte institucional oficial
                              |
                              v
                            HECATE
             governança e administração da solução
                              |
          +-------------------+-------------------+
          |                   |                   |
          v                   v                   v
       Keycloak            SavaPage           PostgreSQL
          |                   |
          |                   v
          |                  CUPS
          |                   |
          |                   v
          |              Impressoras
          |
          +-----> Samba AD
          |       leitura/reconciliação
          |
          +-----> plano/comandos administrativos
                    |
                    v
             Administrador da OM
                    |
                    v
                  Shell
                    |
                    v
                Samba AD

HECATE
   |
   +-----> hecate-agent
           executor local privilegiado para
           operações fechadas de sistema,
           descoberta, telemetria e diagnóstico
```

Sem HECATE Master.

Sem federação.

Sem governança central obrigatória.

Sem DPM como segunda fonte institucional.

O Catálogo MB define a estrutura institucional.

O HECATE governa a solução e compara Catálogo MB com Samba.

Nesta fase, o HECATE gera o plano e os comandos para correção no Samba.

O administrador executa as alterações no shell.

O HECATE valida posteriormente o estado obtido.

O relacionamento de impressão permanece:

```text
Pessoa
NIP string
    ↓
Elemento Organizacional
    N:N
     ↓
   Local
    N:N
     ↓
 Impressora

+
Política
+
Cota
+
Contrato
+
Autorização
```

O objetivo da revisão é **simplificar o HECATE removendo responsabilidades e componentes que deixaram de ser necessários, mantendo o HECATE como ponto central de governança e administração da solução, sem automatizar prematuramente alterações privilegiadas no Samba AD**.
