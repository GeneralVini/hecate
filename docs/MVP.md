# MVP HECATE

## Objetivo

Validar o produto institucional antes da integração completa com o ambiente de produção.

## Incluído nesta primeira entrega

- Yii2 Basic com Bootstrap 5.
- Layout admin dashboard com menu lateral, topbar, navbar contextual e footerbar.
- Dashboard inicial.
- Cadastro básico de impressoras.
- Base de dados para divisões `DCTIM-xx`, impressoras, cotas P&B/colorida, contratos e auditoria.
- Botão de detecção preparado para integração futura com `hecate-agent`.
- Documentação da arquitetura e integrações.

## POCs críticas seguintes

1. Windows/Ubuntu -> SavaPage com username, documento, IP, páginas e cor.
2. Hold e liberação web por PIN sem release station.
3. HECATE -> ACL do SavaPage por divisão e exceção temporária.
4. Accounting P&B/colorida e reserva transacional de cotas.
5. Descoberta HP/Epson/Xerox via IPP, SNMP e EWS.
