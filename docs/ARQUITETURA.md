# Arquitetura HECATE

## Definição

HECATE é a Plataforma Institucional de Governança e Controle de Impressão. O produto não administra o Samba AD da OM.

## Responsabilidades

- **Samba AD da OM:** identidade e autenticação; acesso somente leitura pelas contas técnicas.
- **Catálogo MB:** atributos funcionais e lotação, por exemplo `09051937 -> DCTIM-33`.
- **Keycloak:** SSO do portal e integração OIDC.
- **HECATE:** política, cotas, contratos, exceções, aprovações, auditoria administrativa, monitoramento e troubleshooting.
- **SavaPage:** retenção temporária, accounting e enforcement de impressão.
- **CUPS:** spool local e transporte para as impressoras.
- **PostgreSQL:** persistência, com databases separados para HECATE, SavaPage e Keycloak.
- **hecate-agent:** operações locais privilegiadas e controladas, descoberta de impressoras e diagnóstico.
- **Nexus:** distribuição institucional de RPMs e imagens OCI.

## Fluxo

`Usuário -> SavaPage -> validação HECATE -> liberação -> CUPS -> impressora`

O conteúdo do documento não é arquivado. Apenas metadados operacionais são preservados.

## Princípios

1. Nunca escrever diretamente no banco do SavaPage.
2. Nunca modificar usuários, grupos, GPO, OU ou DNS do Samba AD.
3. Monitoramento não pode impedir impressão.
4. P&B e colorida possuem contadores e cotas independentes.
5. Impressoras são descobertas automaticamente por padrões abertos antes de adapters específicos.
