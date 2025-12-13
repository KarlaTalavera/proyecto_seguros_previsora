<?php
// Estilos compartidos para los módulos de pagos de cuotas (cliente y gestión)
?>
<style>
.pagos-cuotas-table {
    width: 100% !important;
    border-radius: 0.75rem;
    overflow: hidden;
    border: 1px solid var(--color-border);
    background-color: var(--color-bg-card);
}

.pagos-cuotas-card .card-header {
    background-color: var(--color-bg-card);
    color: var(--color-text-primary);
    border-bottom: 1px solid var(--color-border);
}

.pagos-cuotas-card .card-header small,
.pagos-cuotas-card .card-header .text-muted {
    color: var(--color-text-muted) !important;
}

html[data-theme="dark"] .pagos-cuotas-card .card-header {
    background: linear-gradient(145deg, rgba(24, 28, 51, 0.94), rgba(16, 19, 39, 0.92));
    border-bottom-color: rgba(167, 139, 250, 0.28);
    color: rgba(231, 235, 255, 0.94);
}

html[data-theme="dark"] .pagos-cuotas-card .card-header small,
html[data-theme="dark"] .pagos-cuotas-card .card-header .text-muted {
    color: rgba(188, 195, 245, 0.85) !important;
}

.pagos-cuotas-table thead th {
    background: rgba(97, 83, 255, 0.08);
    border-bottom: 1px solid var(--color-border-strong);
    color: var(--color-text-primary);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.02em;
}

html[data-theme="dark"] .pagos-cuotas-table {
    background: linear-gradient(140deg, rgba(33, 38, 71, 0.95), rgba(20, 24, 52, 0.94));
    border-color: rgba(167, 139, 250, 0.18);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

html[data-theme="dark"] .pagos-cuotas-table thead th {
    background: rgba(167, 139, 250, 0.18);
    color: rgba(229, 233, 255, 0.92);
    border-bottom-color: rgba(167, 139, 250, 0.24);
}

.pagos-cuotas-table tbody td {
    vertical-align: middle;
}

.pagos-cuotas-table .badge {
    border-radius: 999px;
    padding: 0.35rem 0.75rem;
    font-weight: 600;
    letter-spacing: 0.01em;
}

.pagos-cuotas-table a.btn-outline-primary {
    border-radius: 999px;
    font-weight: 600;
    border-width: 2px;
    padding: 0.3rem 0.85rem;
    transition: all 0.2s ease;
}

.pagos-cuotas-table a.btn-outline-primary:hover {
    background: var(--btn-main-bg);
    border-color: transparent;
    color: #ffffff;
}

html[data-theme="dark"] .pagos-cuotas-table a.btn-outline-primary {
    color: rgba(224, 229, 255, 0.92);
    border-color: rgba(167, 139, 250, 0.45);
}

html[data-theme="dark"] .pagos-cuotas-table a.btn-outline-primary:hover {
    color: #f9f9ff;
}

.pagos-cuotas-table .js-reportar-pago {
    border-radius: 999px;
    border: none;
    font-weight: 600;
    padding: 0.35rem 1rem;
    display: inline-flex;
    align-items: center;
    background: var(--btn-main-bg);
    color: #ffffff;
    box-shadow: 0 10px 20px rgba(67, 56, 202, 0.15);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.pagos-cuotas-table .js-reportar-pago:hover {
    transform: translateY(-1px);
    background: var(--btn-main-bg-hover);
    box-shadow: 0 16px 28px rgba(67, 56, 202, 0.2);
}

.pagos-cuotas-table .js-reportar-pago:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(97, 83, 255, 0.25);
}

html[data-theme="dark"] .pagos-cuotas-table .js-reportar-pago {
    color: #f8fbff;
    box-shadow: 0 18px 30px rgba(8, 12, 40, 0.65);
}

.pagos-acciones.btn-group .btn {
    border-radius: 999px !important;
    border: none;
    padding: 0.35rem 0.55rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.pagos-acciones.btn-group .btn i {
    font-size: 0.85rem;
}

.pagos-acciones.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(67, 56, 202, 0.15);
}

html[data-theme="dark"] .pagos-acciones.btn-group .btn.btn-info {
    background: linear-gradient(135deg, #60a5fa, #2563eb);
    color: #f8fbff;
}

html[data-theme="dark"] .pagos-acciones.btn-group .btn.btn-success {
    background: linear-gradient(135deg, #34d399, #059669);
    color: #f0fff7;
}

html[data-theme="dark"] .pagos-acciones.btn-group .btn.btn-danger {
    background: linear-gradient(135deg, #f87171, #dc2626);
    color: #fffaf5;
}

.custom-file-input:focus ~ .custom-file-label {
    border-color: rgba(97, 83, 255, 0.45);
    box-shadow: 0 0 0 0.2rem rgba(97, 83, 255, 0.18);
}

.custom-file-label {
    background-color: var(--input-bg);
    border: 1px solid var(--input-border);
    color: var(--color-text-muted);
    transition: background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease;
}

.custom-file-label::after {
    background-color: var(--color-accent);
    color: #ffffff;
    border-radius: 0 0.35rem 0.35rem 0;
    border-left: none;
    font-weight: 600;
}

html[data-theme="dark"] .custom-file-label {
    background-color: rgba(26, 30, 55, 0.92);
    color: rgba(213, 220, 255, 0.86);
    border-color: rgba(167, 139, 250, 0.3);
}

html[data-theme="dark"] .custom-file-label::after {
    background: linear-gradient(135deg, #8b5cf6, #5b21b6);
    color: #f8f5ff;
}

.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select {
    border-radius: 0.65rem;
    border: 1px solid var(--input-border);
    background-color: var(--input-bg);
    color: var(--color-text-primary);
    padding: 0.35rem 0.75rem;
}

html[data-theme="dark"] .dataTables_wrapper .dataTables_filter input,
html[data-theme="dark"] .dataTables_wrapper .dataTables_length select {
    border-color: rgba(167, 139, 250, 0.35);
    background-color: rgba(28, 32, 58, 0.9);
    color: rgba(229, 233, 255, 0.9);
}

html[data-theme="dark"] .dataTables_wrapper .dataTables_filter input:focus,
html[data-theme="dark"] .dataTables_wrapper .dataTables_length select:focus {
    border-color: rgba(167, 139, 250, 0.55);
    box-shadow: 0 0 0 0.2rem rgba(167, 139, 250, 0.25);
}

.pagos-cuotas-resumen .badge {
    font-size: 0.85rem;
    padding: 0.45rem 0.9rem;
}

.pagos-cuotas-resumen .card {
    border-radius: 1rem;
    border: 1px solid var(--color-border);
    box-shadow: var(--shadow-soft);
}

html[data-theme="dark"] .pagos-cuotas-resumen .card {
    border-color: rgba(167, 139, 250, 0.24);
    box-shadow: 0 22px 40px rgba(8, 12, 40, 0.65);
}

</style>
