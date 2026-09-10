# Integrações HECATE

## Samba AD

Somente leitura. Contas técnicas separadas são recomendadas para Keycloak e SavaPage. HECATE não cria ou altera objetos de domínio.

## Catálogo MB

Fonte preferencial para nome, posto/graduação, telefone, função, departamento e divisão. O vínculo organizacional é enriquecido pela API REST documentada em Swagger. O HECATE deve manter cache e registrar divergências/ausências.

## SavaPage

Integração encapsulada em código padrão do Yii2, sem criar estrutura paralela ao template. Priorizar CLI e interfaces documentadas. REST somente onde homologado. Nunca acessar tabelas internas diretamente.

## Impressoras

Sequência de descoberta: IPP/IPPS -> SNMPv3 -> SNMPv2c read-only -> EWS/API HTTP -> parser específico -> cadastro manual. A ausência de telemetria não bloqueia o equipamento.

## Keycloak

Autenticação do portal via OIDC. O PHP não recebe credenciais do Samba AD.
