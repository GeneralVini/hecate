BEGIN;

TRUNCATE TABLE audit_log, quota, printer, location, contract, division RESTART IDENTITY CASCADE;

INSERT INTO division (code, name) VALUES
    ('DCTIM-ADM', 'Divisão Administrativa'),
    ('DCTIM-INFRA', 'Divisão de Infraestrutura'),
    ('DCTIM-SIS', 'Divisão de Sistemas'),
    ('DCTIM-SEG', 'Divisão de Segurança da Informação');

INSERT INTO location (name, description) VALUES
    ('Gabinete', 'Área administrativa da DCTIM'),
    ('Secretaria', 'Atendimento e expediente'),
    ('CPD', 'Centro de processamento de dados'),
    ('Infraestrutura', 'Área técnica de infraestrutura'),
    ('Sistemas', 'Área de desenvolvimento e sustentação');

INSERT INTO printer (name, host, vendor, model, location_id, is_color, is_duplex, monitor_source, last_seen_at) VALUES
    ('DCTIM-ADM-01', '10.10.10.21', 'HP', 'LaserJet Enterprise M611', 1, FALSE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '2 minutes'),
    ('DCTIM-SEC-01', '10.10.10.22', 'Brother', 'MFC-L6902DW', 2, FALSE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '5 minutes'),
    ('DCTIM-CPD-01', '10.10.20.31', 'Xerox', 'VersaLink C7030', 3, TRUE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '3 minutes'),
    ('DCTIM-INFRA-01', '10.10.20.32', 'HP', 'Color LaserJet Enterprise M555', 4, TRUE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '8 minutes'),
    ('DCTIM-SIS-01', '10.10.30.41', 'Brother', 'HL-L6412DW', 5, FALSE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '4 minutes'),
    ('DCTIM-SIS-02', '10.10.30.42', 'Lexmark', 'MS823dn', 5, FALSE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '3 hours');

INSERT INTO contract (
    name, type, bw_quota, color_quota, bw_unit_price, color_unit_price, bw_overage_price, color_overage_price
) VALUES (
    'Contrato Demo DCTIM — Franquia',
    'franchise',
    5000,
    1000,
    NULL,
    NULL,
    0.0800,
    0.4500
);

WITH months AS (
    SELECT generate_series(DATE '2025-10-01', DATE '2026-09-01', INTERVAL '1 month')::date AS month
), allocations AS (
    SELECT * FROM (VALUES
        (1, 1800, 400),
        (2, 1400, 300),
        (3, 1000, 200),
        (4,  800, 100)
    ) AS a(division_id, bw_allocated, color_allocated)
)
INSERT INTO quota (
    division_id, period,
    bw_allocated, bw_used, bw_reserved,
    color_allocated, color_used, color_reserved
)
SELECT
    a.division_id,
    to_char(m.month, 'YYYY-MM'),
    a.bw_allocated,
    round(a.bw_allocated * (0.68 + ((extract(month FROM m.month)::int + a.division_id) % 5) * 0.09))::int,
    0,
    a.color_allocated,
    round(a.color_allocated * (0.55 + ((extract(month FROM m.month)::int + a.division_id) % 6) * 0.10))::int,
    0
FROM months m
CROSS JOIN allocations a;

INSERT INTO audit_log (actor, action, entity, entity_id, details) VALUES
    ('hecate-demo', 'demo.seed', 'environment', 'DCTIM', 'Cenário demonstrativo DCTIM carregado; dados fictícios.');

COMMIT;
