BEGIN;

TRUNCATE TABLE audit_log, quota, printer, location, contract, division RESTART IDENTITY CASCADE;

INSERT INTO division (code, name) VALUES
    ('CTIM-ADM', 'Divisão Administrativa'),
    ('CTIM-OPS', 'Divisão de Operações'),
    ('CTIM-SUP', 'Divisão de Suporte');

INSERT INTO location (name, description) VALUES
    ('Secretaria', 'Atendimento administrativo da CTIM'),
    ('Operações', 'Área de operação e acompanhamento'),
    ('Suporte', 'Área de suporte aos usuários'),
    ('Almoxarifado', 'Área logística e suprimentos');

INSERT INTO printer (name, host, vendor, model, location_id, is_color, is_duplex, monitor_source, last_seen_at) VALUES
    ('CTIM-ADM-01', '10.20.10.21', 'HP', 'LaserJet Enterprise M611', 1, FALSE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '4 minutes'),
    ('CTIM-OPS-01', '10.20.20.31', 'Xerox', 'VersaLink C7030', 2, TRUE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '2 minutes'),
    ('CTIM-SUP-01', '10.20.30.41', 'Brother', 'MFC-L6902DW', 3, FALSE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '7 minutes'),
    ('CTIM-ALM-01', '10.20.40.51', 'Lexmark', 'CX625adhe', 4, TRUE, TRUE, 'snmpv3', CURRENT_TIMESTAMP - INTERVAL '55 minutes');

INSERT INTO contract (
    name, type, bw_quota, color_quota, bw_unit_price, color_unit_price, bw_overage_price, color_overage_price
) VALUES (
    'Contrato Demo CTIM — Consumo',
    'consumption',
    NULL,
    NULL,
    0.0900,
    0.5000,
    NULL,
    NULL
);

WITH months AS (
    SELECT generate_series(DATE '2025-10-01', DATE '2026-09-01', INTERVAL '1 month')::date AS month
), allocations AS (
    SELECT * FROM (VALUES
        (1, 2200, 500),
        (2, 2800, 800),
        (3, 1800, 350)
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
    round(a.bw_allocated * (0.52 + ((extract(month FROM m.month)::int + a.division_id) % 6) * 0.08))::int,
    0,
    a.color_allocated,
    round(a.color_allocated * (0.40 + ((extract(month FROM m.month)::int + a.division_id) % 5) * 0.09))::int,
    0
FROM months m
CROSS JOIN allocations a;

INSERT INTO audit_log (actor, action, entity, entity_id, details) VALUES
    ('hecate-demo', 'demo.seed', 'environment', 'CTIM', 'Cenário demonstrativo CTIM carregado; dados fictícios.');

COMMIT;
