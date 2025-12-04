<?php
// Estilos compartidos para los modales de pólizas (admin y agente)
?>
<style>
#modalPoliza .modal-dialog {
    max-width: 900px;
}

#modalPoliza .modal-body {
    max-height: calc(100vh - 220px);
    overflow-y: auto;
}

#modalPoliza .form-group {
    margin-bottom: 1rem;
}

#modalPoliza .custom-control-label {
    cursor: pointer;
}

#modalPoliza .custom-control-input:checked ~ .custom-control-label {
    font-weight: 500;
}

#coberturasContainer {
    background: var(--color-bg-card);
    border: 1px solid var(--color-border);
    border-radius: 0.75rem;
    min-height: 80px;
    transition: background 0.3s ease, border-color 0.3s ease;
    padding: 1rem;
}

#coberturasContainer p {
    margin-bottom: 0;
    color: var(--color-text-muted);
}

html[data-theme="dark"] #coberturasContainer {
    background: linear-gradient(152deg, rgba(36, 42, 82, 0.9), rgba(20, 25, 54, 0.9));
    border-color: rgba(129, 108, 255, 0.35);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

html[data-theme="dark"] #coberturasContainer p {
    color: rgba(203, 212, 255, 0.65);
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

.poliza-accion:hover {
    background-color: var(--submenu-hover-bg);
    transform: translateY(-1px);
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

.poliza-accion:hover[data-action="eliminar"] i {
    color: #c0392b;
}

.poliza-accion:hover[data-action="editar"] i {
    color: #17a673;
}

.poliza-accion:hover[data-action="detalle"] i {
    color: #2e59d9;
}

#tablaPolizas {
    width: 100% !important;
}

.dataTables_wrapper .dataTables_scroll {
    overflow: hidden;
}

.dataTables_wrapper .dataTables_scrollBody {
    overflow-x: auto !important;
}

@media (max-width: 767.98px) {
    #modalPoliza .modal-dialog {
        margin: 0.5rem;
    }
    #modalPoliza .modal-body {
        max-height: calc(100vh - 160px);
    }
}
</style>
