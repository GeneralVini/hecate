# Identidade Visual HECATE

## 1. Nome e posicionamento

Nome do produto: **HECATE**.

Descrição institucional:

> **Plataforma Institucional de Governança e Controle de Impressão**

O HECATE não deve ser apresentado como simples agregador de ferramentas. Sua identidade precisa comunicar que existe uma camada de governança acima dos componentes técnicos.

## 2. Conceito visual

A identidade combina referência mitológica a Hécate com governança, controle, acesso, impressão e rastreabilidade.

A leitura visual deve seguir esta hierarquia:

```text
HECATE
Governança e Controle de Impressão
        ↓
Políticas · Papéis · Cotas · Aprovações · Auditoria · Indicadores
        ↓
Identidade | Controle | Dados
        ↓
Keycloak/Samba AD | SavaPage/CUPS | PostgreSQL/Podman
```

Os três portais continuam como elemento central da linguagem visual:

- **Identidade** — autenticação, origem do usuário e vínculo institucional;
- **Controle** — aplicação das regras de impressão, retenção, contabilização e liberação;
- **Dados** — persistência, auditoria e serviços da aplicação.

Os portais representam uma passagem controlada: o usuário não alcança diretamente o recurso final; ele atravessa identidade, regras e controles definidos pela governança.

## 3. Elementos visuais

Elementos permitidos:

- Hécate em composições institucionais e hero;
- chave;
- tocha;
- lua tríplice;
- caminhos, portais e limiares;
- Rio de Janeiro, incluindo Cristo Redentor e Pão de Açúcar, como cenário institucional;
- documentos e fluxo de impressão;
- controle de acesso;
- monitoramento e telemetria;
- estrela dourada como elemento de assinatura institucional.

Os logos dos componentes técnicos devem permanecer reconhecíveis e, quando utilizados, preferencialmente em suas versões oficiais. Não há necessidade de reduzir sua presença visual; a distinção entre governança e implementação deve ocorrer pela composição e pela hierarquia da informação.

Evitar:

- excesso de fantasia sem relação com o produto;
- tratar ferramentas como se fossem a própria governança;
- navios como elemento principal da marca;
- cães como elemento central da identidade;
- símbolos que remetam a Atena, como lança/escudo;
- textos técnicos excessivos dentro das artes operacionais;
- `APP-PRINT`.

## 4. Paleta

Direção visual principal:

- azul-marinho profundo como base;
- azul institucional para superfícies secundárias;
- dourado para identidade, hierarquia, bordas e chamadas importantes;
- branco e cinzas azulados para legibilidade;
- fundos translúcidos escuros para cards e painéis.

O dourado é uma cor de destaque. Deve ser usado em títulos, estados selecionados, ícones, divisores e elementos institucionais, evitando grandes blocos de texto corrido.

## 5. Assets oficiais

Local correto: `public/branding/`.

Assets atuais:

- `logo-horizontal.png`
- `logo-vertical.png`
- `symbol.png`
- `favicon-16x16.png`
- `favicon-32x32.png`
- `favicon-48x48.png`
- `favicon-180x180.png`
- `favicon-192x192.png`
- `favicon-512x512.png`
- `login-background.jpg`
- `login-background.png`
- `dashboard-background.jpg`
- `hecate-hero.jpg`

## 6. Uso dos assets

### Logo horizontal

Uso preferencial em topbar, tela de login, README, apresentações e documentação.

### Logo vertical

Uso preferencial em capas, splash institucional, páginas de apresentação e materiais verticais.

### Símbolo isolado

Uso preferencial em sidebar recolhida, avatar do produto, loader, cards institucionais e favicons derivados.

### Favicons

Usar os tamanhos apropriados no `<head>` e em futuro manifest/PWA quando aplicável.

### Background de login

A imagem é o cenário visual; o formulário continua sendo renderizado pela aplicação Yii3.

Requisitos:

- preservar área de escape para usuário, senha e ação de entrada;
- não desenhar campos ou botão na própria imagem;
- manter leitura limpa em resoluções diferentes;
- usar overlay apenas quando necessário para casar arte e formulário;
- manter o rodapé institucional em HTML/CSS.

Rodapé aprovado:

```text
                         ✦
PLATAFORMA INSTITUCIONAL DE GOVERNANÇA E CONTROLE DE IMPRESSÃO
CTIM - YYYY                                      CC(EN) HONORATO
```

### Background do dashboard

Deve sustentar a ambientação sem competir com o conteúdo operacional. Usar overlay escuro, painéis translúcidos e contraste alto nas informações.

### Hero HECATE

É a referência institucional principal para apresentar o conceito completo: Hécate, camada de governança, três portais, componentes técnicos e cenário do Rio de Janeiro.

Uso recomendado em README, apresentações, documentação visual e onboarding. Não usar como fundo permanente de tabelas ou telas densas.

## 7. Layout da aplicação

O HECATE usa padrão de **admin dashboard**, com linguagem visual escura, institucional e orientada à governança.

Estrutura de referência:

```text
+-------------------------------------------------------------+
| Topbar: HECATE | OM ativa | jobs | governança | usuário   |
+---------------+---------------------------------------------+
| Sidebar       | Breadcrumb / contexto                     |
| retrátil      +---------------------------------------------+
|               | Governança: políticas, papéis, cotas...   |
|               |                                             |
|               | Conteúdo operacional                       |
|               |                                             |
+---------------+---------------------------------------------+
| CTIM - YYYY | ✦ slogan institucional | CC(EN) HONORATO   |
+-------------------------------------------------------------+
```

### Sidebar

Agrupamentos de referência:

```text
Dashboard

Impressão
  Impressoras
  Filas
  Jobs pendentes
  Liberação

Organização
  Divisões
  Usuários

Governança
  Políticas
  Papéis e aprovações
  Cotas
  Contratos
  Indicadores

Operação
  Monitoramento
  Auditoria

Sistema
  Integrações
  Configurações
```

A sidebar deve ser recolhível e manter o símbolo HECATE quando compactada.

### Topbar

Deve priorizar contexto operacional e institucional:

- marca HECATE;
- OM ativa;
- jobs pendentes;
- estado resumido da governança/stack;
- usuário autenticado.

### Footerbar

A assinatura visual adotada é:

- `CTIM - YYYY` à esquerda ou no eixo institucional definido pelo layout;
- estrela dourada e slogan da plataforma em destaque central;
- `CC(EN) HONORATO` de forma singela e discreta à direita.

## 8. Dashboard

O dashboard deve ser operacional, não decorativo.

A camada de governança deve aparecer antes dos indicadores técnicos, explicitando:

- políticas;
- papéis;
- cotas;
- aprovações;
- auditoria;
- indicadores.

Blocos operacionais desejados:

- fila de impressão;
- jobs pendentes/liberação;
- consumo P&B/colorido;
- consumíveis/suprimentos;
- atividade recente;
- estado do stack;
- impressoras online/offline;
- fluxo de autorização;
- impressão por divisão;
- consumo contratual;
- alertas.

A hierarquia visual deve deixar evidente primeiro o que exige decisão ou ação do operador.

## 9. Tela de login

A tela de login usa `login-background.jpg` como cenário e `logo-horizontal.png` no painel de autenticação.

O formulário é HTML/Yii3. A interface não deve induzir o usuário a acreditar que o HECATE armazena sua senha de domínio quando o fluxo SSO/OIDC estiver implementado.

## 10. Tipografia e componentes

- fontes de sistema legíveis para interface;
- serifada apenas em títulos institucionais quando adequada;
- headings fortes sem excesso de caixa alta;
- cards translúcidos escuros e compactos;
- tabelas para dados densos;
- badges para status;
- bordas e realces dourados discretos;
- animações somente quando ajudarem a indicar estado.

## 11. Status e semântica visual

Estados operacionais devem ser inequívocos:

- OK / online;
- atenção;
- crítico;
- indisponível;
- pendente;
- bloqueado;
- expirado.

Cor nunca deve ser o único meio de comunicar estado: combinar cor, texto e ícone.

## 12. Princípio de design

O visual do HECATE deve transmitir:

**governança, controle, rastreabilidade, segurança e operação institucional**.

A mitologia e o cenário sustentam a identidade visual. As ferramentas demonstram a implementação. A governança, porém, precisa permanecer como a mensagem principal da solução.
