# Identidade Visual HECATE

## 1. Nome e posicionamento

Nome do produto: **HECATE**.

Descrição institucional:

> **Plataforma Institucional de Governança e Controle de Impressão**

O nome provisório `APP-PRINT` foi abandonado e não deve aparecer na interface, documentação, arte ou nomenclatura técnica nova.

## 2. Conceito visual

A identidade combina referência mitológica a Hécate com elementos de controle, acesso, impressão e governança.

Elementos conceituais permitidos:

- Hécate em composições institucionais/hero;
- chave;
- tocha;
- lua tríplice;
- caminhos, portais e limiares;
- documentos e fluxo de impressão;
- controle de acesso;
- monitoramento e telemetria.

Evitar:

- excesso de fantasia sem relação com o produto;
- navios como elemento principal da marca;
- cães como elemento central da identidade;
- símbolos que remetam a Atena, como lança/escudo;
- textos técnicos excessivos dentro da arte;
- `APP-PRINT`.

## 3. Paleta

Direção visual inspirada em ambiente institucional naval:

- azul-marinho profundo como base;
- azul institucional/real para destaques;
- dourado para identidade, hierarquia e chamadas importantes;
- branco e cinzas claros para legibilidade;
- tons neutros escuros para superfícies e painéis.

O contraste deve priorizar legibilidade e acessibilidade. Dourado é cor de destaque, não de texto corrido em grandes blocos.

## 4. Assets oficiais

Local: `web/assets/branding/`

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
- `dashboard-background.jpg`
- `hecate-hero.jpg`

## 5. Uso dos assets

### Logo horizontal

Uso preferencial em:

- topbar;
- tela de login;
- README;
- apresentações;
- documentação.

### Logo vertical

Uso preferencial em:

- capas;
- splash institucional;
- páginas de apresentação;
- materiais gráficos verticais.

### Símbolo isolado

Uso preferencial em:

- sidebar recolhida;
- avatar do produto;
- loader;
- cards institucionais;
- favicon derivado.

### Favicons

Usar os tamanhos apropriados no `<head>` e em manifest/PWA quando aplicável.

### Background de login

É uma **imagem de fundo pura**, nunca um mockup de tela.

Requisitos:

- Hécate e elementos visuais concentrados em uma lateral/área principal;
- área de escape limpa e de baixo ruído visual para o formulário HTML real;
- sem campos de usuário/senha desenhados na imagem;
- sem botão de login desenhado;
- sem texto que concorra com o formulário;
- contraste suficiente para card translúcido ou sólido.

### Background do dashboard

Deve ser discreto e secundário.

- não reduzir legibilidade de cards/tabelas;
- usar baixa intensidade visual;
- não competir com alertas e indicadores;
- preferir aplicação parcial, overlay ou opacidade baixa.

### Hero HECATE

Uso em:

- apresentação da solução;
- páginas institucionais;
- documentação visual;
- onboarding.

Não usar como fundo permanente de telas de operação densa.

## 6. Layout da aplicação

O HECATE usa padrão de **admin dashboard**.

Estrutura de referência:

```text
+------------------------------------------------------+
| Topbar: HECATE | OM ativa | alertas | status | user |
+-------------+----------------------------------------+
| Sidebar     | Navbar/Breadcrumb + ações contextuais |
| retrátil    +----------------------------------------+
|             |                                        |
| menu        |             CONTEÚDO                  |
|             |                                        |
+-------------+----------------------------------------+
| Footerbar: versão | serviços | sincronização        |
+------------------------------------------------------+
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
  Vínculos Catálogo MB

Controle
  Políticas
  Cotas
  Contratos
  Transferências
  Autorizações

Operação
  Monitoramento
  Suprimentos
  Auditoria
  Logs

Sistema
  Stack
  Serviços
  Integrações
  Diagnóstico
  Configurações
```

A sidebar deve ser recolhível e manter o símbolo HECATE quando compactada.

### Topbar

Deve priorizar contexto operacional:

- marca HECATE;
- OM ativa;
- jobs pendentes;
- alertas;
- saúde resumida do stack;
- usuário autenticado.

### Footerbar

Pode exibir:

- versão do HECATE;
- versão/estado do ambiente;
- SavaPage/CUPS;
- última sincronização relevante.

## 7. Dashboard

A referência aprovada é um dashboard operacional, não decorativo.

Blocos desejados:

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

A hierarquia visual deve deixar evidente primeiro o que exige ação do operador.

## 8. Tela de login

A tela de login deve usar o background oficial, mas o formulário é renderizado pelo Yii2/Bootstrap.

Composição recomendada:

```text
arte HECATE / ambiente visual        área limpa
                                     +------------------+
                                     | HECATE           |
                                     | autenticação     |
                                     | [ Entrar ]       |
                                     +------------------+
```

Quando Keycloak estiver integrado, o fluxo de autenticação deve ser orientado por SSO/OIDC. A interface não deve induzir o usuário a acreditar que o HECATE armazena sua senha de domínio.

## 9. Tipografia e componentes

- priorizar fontes de sistema/web seguras e legíveis;
- headings fortes, sem excesso de caixa alta;
- usar Bootstrap 5 como base;
- cards compactos e funcionais;
- tabelas para dados densos;
- badges para status;
- ícones consistentes;
- animações discretas e apenas quando ajudarem a indicar estado.

## 10. Status e semântica visual

Estados operacionais devem ser inequívocos:

- OK / online;
- atenção;
- crítico;
- indisponível;
- pendente;
- bloqueado;
- expirado.

Cor nunca deve ser o único meio de comunicar estado: combinar cor, texto e ícone.

## 11. Princípio de design

O visual do HECATE deve transmitir:

**controle, rastreabilidade, segurança, governança e operação institucional**.

A mitologia sustenta a identidade do produto, mas a operação diária deve permanecer clara, sóbria e objetiva.
