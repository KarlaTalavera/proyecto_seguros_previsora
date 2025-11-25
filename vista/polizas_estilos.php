<?php
// Estilos compartidos para los modales de pólizas (admin y agente)
?>
<style>
:root {
    --poliza-modal-header-bg: #93BFC7;
    --poliza-modal-header-color: #ffffff;
    --poliza-modal-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
}

#modalPoliza .modal-dialog {
    max-width: 900px;
}

#modalPoliza .modal-content {
    border-radius: 1rem;
    overflow: hidden;
    border: none;
    box-shadow: var(--poliza-modal-shadow);
}

#modalPoliza .modal-header {
    background-color: var(--poliza-modal-header-bg);
    color: var(--poliza-modal-header-color);
    border-bottom: none;
}

#modalPoliza .modal-header .modal-title {
    font-weight: 500;
}

#modalPoliza .modal-header .close {
    color: var(--poliza-modal-header-color);
    opacity: 0.85;
}

#modalPoliza .modal-header .close:hover,
#modalPoliza .modal-header .close:focus {
    opacity: 1;
}

#modalPoliza .modal-body {
    padding: 1.5rem;
    max-height: calc(100vh - 220px);
    overflow-y: auto;
    background-color: #f8f9fa;
}

#modalPoliza .modal-footer {
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

#modalPoliza .neu-button {
    background-color: #e0e0e0;
    border-radius: 50px;
    box-shadow: inset 4px 4px 10px #bcbcbc, inset -4px -4px 10px #ffffff;
    color: #4d4d4d;
    cursor: pointer;
    font-size: 16px;
    padding: 12px 32px;
    transition: all 0.2s ease-in-out;
    border: 2px solid #cecece;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
}

#modalPoliza .neu-button:hover {
    box-shadow: inset 2px 2px 5px #bcbcbc, inset -2px -2px 5px #ffffff,
        2px 2px 5px #bcbcbc, -2px -2px 5px #ffffff;
}

#modalPoliza .neu-button:focus {
    outline: none;
    box-shadow: inset 2px 2px 5px #bcbcbc, inset -2px -2px 5px #ffffff,
        2px 2px 5px #bcbcbc, -2px -2px 5px #ffffff;
}

#modalPoliza .neu-button.neu-primary {
    color: #1d4855;
    font-weight: 600;
}

#modalPoliza .neu-button[disabled] {
    cursor: not-allowed;
    opacity: 0.7;
    box-shadow: inset 2px 2px 5px #bcbcbc, inset -2px -2px 5px #ffffff;
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

#modalPoliza #coberturasContainer {
    background: #ffffff;
    border: 1px solid #dfe4ea;
}

.poliza-accion {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #f0f3f6;
    margin-right: 6px;
    transition: background-color 0.2s ease-in-out;
    cursor: pointer;
}

.poliza-accion:last-child {
    margin-right: 0;
}

.poliza-accion i {
    font-size: 14px;
}

.poliza-accion:hover {
    background-color: #e0e7ec;
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
