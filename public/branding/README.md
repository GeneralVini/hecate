# Assets de identidade visual HECATE

Esta pasta contém os assets oficiais de interface e identidade do HECATE.

## Arquivos atuais

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

## Conceito vigente

A identidade visual atual não apresenta o HECATE como inventário de ferramentas. A leitura deve evidenciar primeiro a **camada de governança**, composta por políticas, papéis, cotas, aprovações, auditoria e indicadores. Os componentes técnicos permanecem visualmente reconhecíveis como mecanismos de implementação.

Os três portais representam os domínios:

- **Identidade** — Keycloak e Samba AD;
- **Controle** — SavaPage e CUPS;
- **Dados** — PostgreSQL e Podman.

A composição institucional usa Hécate como elemento simbólico de passagem controlada, decisão e limiar. O cenário do Rio de Janeiro, com Cristo Redentor e Pão de Açúcar, reforça a identidade visual da solução sem substituir seu caráter técnico e institucional.

## Uso recomendado

### `logo-horizontal.png`

Topbar, login, README, apresentações e cabeçalhos institucionais.

### `logo-vertical.png`

Capas, splash, materiais verticais e páginas institucionais.

### `symbol.png`

Sidebar recolhida, avatar do produto, loader e componentes compactos.

### Favicons

Usar o tamanho adequado no `<head>`, atalhos e futuro manifest/PWA.

### `login-background.jpg` e `login-background.png`

São backgrounds da tela de autenticação. O formulário permanece HTML/Yii3, nunca desenhado na própria imagem. A composição deve preservar área de escape suficiente para usuário, senha e ação de entrada.

O rodapé institucional é renderizado em HTML/CSS para manter legibilidade e adaptação responsiva:

- estrela dourada;
- `PLATAFORMA INSTITUCIONAL DE GOVERNANÇA E CONTROLE DE IMPRESSÃO`;
- `CTIM - YYYY`;
- `CC(EN) HONORATO` discreto à direita.

### `dashboard-background.jpg`

Aplicar com overlay escuro e baixa interferência visual. O background deve sustentar a identidade sem prejudicar cards, tabelas, alertas ou métricas.

### `hecate-hero.jpg`

Imagem institucional de referência para README, apresentações, documentação visual e onboarding. É a peça que melhor expressa a relação entre governança e os três domínios técnicos.

## Direção visual

A identidade usa:

- azul-marinho profundo;
- dourado;
- branco e tons frios de apoio;
- Hécate;
- chave e tocha;
- lua tríplice;
- caminhos e portais;
- Rio de Janeiro como cenário institucional;
- governança acima dos componentes técnicos.

Evitar:

- tratar o HECATE como mero inventário de ferramentas;
- `APP-PRINT`;
- excesso de fantasia sem relação com a solução;
- excesso de texto nas imagens operacionais;
- formulários de login incorporados ao background;
- efeitos visuais que reduzam a legibilidade da interface.

Consulte `docs/IDENTIDADE-VISUAL.md` para as regras completas de interface e branding.
