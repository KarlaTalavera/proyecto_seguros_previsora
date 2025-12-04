<?php
// Estilos compartidos para las vistas de siniestros (agente y administrador)
?>
<style>
  .siniestros-toolbar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
  }

  .siniestros-toolbar .btn-neo {
    min-width: 150px;
  }

  .siniestros-table {
    border-spacing: 0;
    border-collapse: separate;
    border-radius: 1rem;
    overflow: hidden;
  }

  .siniestros-table thead th {
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: 0.05em;
    background: transparent;
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-muted);
  }

  .siniestros-table tbody tr {
    border-bottom: 1px solid var(--color-border);
    transition: background 0.18s ease, transform 0.18s ease;
  }

  .siniestros-table tbody tr:hover {
    background: var(--color-accent-soft);
    transform: translateY(-1px);
  }

  html[data-theme="dark"] .siniestros-table tbody tr:hover {
    background: rgba(97, 83, 255, 0.12);
  }

  .siniestros-table th,
  .siniestros-table td {
    vertical-align: middle;
    border-top: none;
    padding: 0.85rem 1rem;
  }

  .siniestros-table td .badge-soft {
    font-size: 0.78rem;
    font-weight: 600;
  }

  .siniestro-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
  }

  .btn-neo--icon {
    width: 2.35rem;
    height: 2.35rem;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.85rem;
    border: 1px solid var(--color-border);
    background: var(--color-bg-card);
    color: var(--color-accent);
    transition: transform 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
  }

  .btn-neo--icon[data-variant="info"] {
    color: #0f6fbf;
  }

  .btn-neo--icon[data-variant="warn"] {
    color: #d97706;
  }

  .btn-neo--icon[data-variant="success"] {
    color: #0f9f6e;
  }

  .btn-neo--icon[data-variant="danger"] {
    color: #d34141;
  }

  .btn-neo--icon:hover {
    transform: translateY(-1px);
    background: var(--color-accent-soft);
    box-shadow: 0 10px 25px rgba(10, 18, 45, 0.15);
  }

  html[data-theme="dark"] .btn-neo--icon {
    border-color: var(--color-border-strong);
    background: rgba(28, 34, 62, 0.92);
  }

  html[data-theme="dark"] .btn-neo--icon:hover {
    background: rgba(97, 83, 255, 0.18);
  }

  .modal-consistent .form-control,
  .modal-consistent .custom-select,
  .modal-consistent textarea {
    border-radius: 0.75rem;
  }

  .modal-consistent .modal-footer {
    gap: 0.5rem;
  }

  .toast {
    border-radius: 0.75rem;
  }

  .poliza-accion {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--chip-bg);
    margin-right: 6px;
    transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
    cursor: pointer;
  }

  html[data-theme="dark"] .poliza-accion {
    background-color: rgba(114, 94, 255, 0.18);
  }

  .poliza-accion:last-child {
    margin-right: 0;
  }

  .poliza-accion i {
    font-size: 14px;
  }

  .poliza-accion[data-action="eliminar"] i {
    color: #e74a3b;
  }

  .poliza-accion[data-action="editar"] i {
    color: #1cc88a;
  }

  .poliza-accion[data-action="detalle"] i {
    color: #4e73df;
  }

  .poliza-accion[data-action="pago"] i {
    color: #0f9f6e;
  }

  .poliza-accion:hover {
    background-color: var(--submenu-hover-bg);
    transform: translateY(-1px);
  }

  .poliza-accion:hover[data-action="eliminar"] i {
    color: #c0392b;
  }

  .poliza-accion:hover[data-action="editar"] i {
    color: #17a673;
  }

  .poliza-accion:hover[data-action="detalle"] i {
    color: #2e59d9;
  }

  .poliza-accion:hover[data-action="pago"] i {
    color: #0c7b52;
  }
</style>
