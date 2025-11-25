<?php
// Estilos compartidos de modales basados en el registro de agentes
?>
<style>
:root {
    --modal-principal-header-bg: #93BFC7;
    --modal-principal-header-color: #ffffff;
    --modal-principal-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15);
    --modal-primary-btn-bg: #1d4855;
    --modal-primary-btn-border: #1d4855;
    --modal-secondary-btn-color: #1d4855;
}

.modal.modal-alineada .modal-content {
    border-radius: 1rem;
    overflow: hidden;
    border: none;
    box-shadow: var(--modal-principal-shadow);
}

.modal.modal-alineada .modal-header {
    background-color: var(--modal-principal-header-bg);
    color: var(--modal-principal-header-color);
    border-bottom: none;
}

.modal.modal-alineada .modal-header .modal-title {
    font-weight: 500;
}

.modal.modal-alineada .modal-header .close {
    color: var(--modal-principal-header-color);
    opacity: 0.85;
}

.modal.modal-alineada .modal-header .close:hover,
.modal.modal-alineada .modal-header .close:focus {
    opacity: 1;
}

.modal.modal-alineada .modal-body {
    padding: 1.5rem;
}

.modal.modal-alineada .modal-footer {
    padding: 1rem 1.5rem;
}

.modal.modal-alineada .btn-primary {
    background-color: var(--modal-primary-btn-bg);
    border-color: var(--modal-primary-btn-border);
    font-weight: 500;
}

.modal.modal-alineada .btn-primary:hover,
.modal.modal-alineada .btn-primary:focus {
    background-color: #153640;
    border-color: #153640;
}

.modal.modal-alineada .btn-secondary {
    color: var(--modal-secondary-btn-color);
    background-color: #ffffff;
    border-color: #d0d7dd;
    font-weight: 500;
}

.modal.modal-alineada .btn-secondary:hover,
.modal.modal-alineada .btn-secondary:focus {
    color: #ffffff;
    background-color: #1d4855;
    border-color: #1d4855;
}

.modal.modal-alineada .btn-outline-primary {
    color: var(--modal-primary-btn-bg);
    border-color: var(--modal-primary-btn-bg);
}

.modal.modal-alineada .btn-outline-primary:hover,
.modal.modal-alineada .btn-outline-primary:focus {
    color: #ffffff;
    background-color: var(--modal-primary-btn-bg);
    border-color: var(--modal-primary-btn-bg);
}
</style>
