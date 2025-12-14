<?php
require_once dirname(__DIR__) . '/config/asset_paths.php';

$jquerySrc = resolveAssetPath('vendor/jquery/jquery.min.js', 'https://code.jquery.com/jquery-3.6.4.min.js');
$bootstrapBundleSrc = resolveAssetPath(
    'vendor/bootstrap/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js'
);
$jqueryEasingSrc = resolveAssetPath(
    'vendor/jquery-easing/jquery.easing.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js'
);
$chartJsSrc = resolveAssetPath(
    'vendor/chart.js/Chart.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js'
);
$sbAdminJs = resolveAssetPath('js/sb-admin-2.min.js', 'js/sb-admin-2.min.js');
?>

</div>
            <!-- end of main content -->

            <!-- footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>copyright &copy; Seguros la Previsora 2025</span>
                    </div>
                </div>
            </footer>
            <!-- end of footer -->

        </div>
        <!-- end of content wrapper -->

    </div>
    <!-- end of page wrapper -->

    <!-- scroll to top button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- logout modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">¿Listo para salir?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Selecciona «Cerrar sesión» para finalizar tu sesión actual.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                    <a class="btn btn-primary" href="logout.php">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?php echo htmlspecialchars($jquerySrc, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script src="<?php echo htmlspecialchars($bootstrapBundleSrc, ENT_QUOTES, 'UTF-8'); ?>"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?php echo htmlspecialchars($jqueryEasingSrc, ENT_QUOTES, 'UTF-8'); ?>"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?php echo htmlspecialchars($sbAdminJs, ENT_QUOTES, 'UTF-8'); ?>"></script>

    <!-- Page level plugins -->
    <script src="<?php echo htmlspecialchars($chartJsSrc, ENT_QUOTES, 'UTF-8'); ?>"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

    <?php
    // Si la vista definió scripts extra en la variable $extra_scripts, imprimirlos aquí.
    if (isset($extra_scripts) && is_string($extra_scripts)) {
        echo $extra_scripts;
    }
    ?>
    
    <script>
// Sistema de notificaciones en tiempo real
class SistemaNotificaciones {
    constructor() {
        this.intervalo = null;
        this.ultimaActualizacion = null;
        this.intervaloTiempo = 30000; // 30 segundos
        this.sonidoNotificacion = null;
        
        this.inicializar();
    }
    
    inicializar() {
        // Cargar notificaciones al inicio
        this.cargarNotificaciones();
        
        // Configurar intervalo de actualización
        this.intervalo = setInterval(() => this.cargarNotificaciones(), this.intervaloTiempo);
        
        // Configurar sonido de notificación
        this.sonidoNotificacion = new Audio('assets/sounds/notification.mp3');
        
        // Configurar eventos
        this.configurarEventos();
    }
    
    configurarEventos() {
        // Marcar como leída al hacer clic en notificación
        $(document).on('click', '.dropdown-notificacion', (e) => {
            const id = $(e.currentTarget).data('id');
            const enlace = $(e.currentTarget).data('enlace');
            this.marcarComoLeida(id, enlace);
        });
        
        // Marcar todas como leídas
        $('#marcar-todas-leidas').on('click', (e) => {
            e.preventDefault();
            this.marcarTodasLeidas();
        });
        
        // Ver todas las notificaciones
        $('#ver-todas-notificaciones').on('click', (e) => {
            e.preventDefault();
            window.location.href = 'index.php?vista=notificaciones';
        });
    }
    
    async cargarNotificaciones() {
        try {
            const response = await fetch('controladores/controladorNotificacion.php?accion=obtener_notificaciones&solo_no_leidas=false&limit=5');
            const data = await response.json();
            
            if (data.success) {
                this.actualizarInterfaz(data.notificaciones, data.total_no_leidas);
                
                // Reproducir sonido si hay nuevas notificaciones
                if (this.ultimaActualizacion !== null && data.total_no_leidas > this.ultimaActualizacion) {
                    this.reproducirSonido();
                }
                
                this.ultimaActualizacion = data.total_no_leidas;
            }
        } catch (error) {
            console.error('Error cargando notificaciones:', error);
        }
    }
    
    actualizarInterfaz(notificaciones, totalNoLeidas) {
        // Actualizar contador en la campana
        const contador = $('#notificacion-counter');
        const contadorMenu = $('.badge-counter');
        
        contador.text(totalNoLeidas);
        contadorMenu.text(totalNoLeidas);
        
        if (totalNoLeidas > 0) {
            contador.show();
            contadorMenu.show();
            contadorMenu.addClass('pulse');
        } else {
            contador.hide();
            contadorMenu.hide();
            contadorMenu.removeClass('pulse');
        }
        
        // Actualizar lista dropdown
        this.actualizarDropdown(notificaciones);
    }
    
    actualizarDropdown(notificaciones) {
        const lista = $('#lista-notificaciones');
        
        if (notificaciones.length === 0) {
            lista.html(`
                <div class="text-center py-4">
                    <i class="fas fa-bell-slash fa-2x text-gray-300 mb-2"></i>
                    <p class="small text-muted">No hay notificaciones</p>
                </div>
            `);
            return;
        }
        
        let html = '';
        notificaciones.forEach(notif => {
            const icono = this.obtenerIcono(notif.tipo);
            const color = this.obtenerColor(notif.tipo);
            const noLeidaClass = notif.leida == 0 ? 'unread' : '';
            const nuevaBadge = notif.leida == 0 ? '<span class="badge badge-danger badge-sm ml-2">Nueva</span>' : '';
            
            html += `
                <a href="#" class="dropdown-item dropdown-notificacion ${noLeidaClass}" 
                   data-id="${notif.id_notificacion}" 
                   data-enlace="${notif.enlace || '#'}">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon ${color}">
                            <i class="${icono}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">${notif.titulo} ${nuevaBadge}</h6>
                                <small class="notification-time">${notif.fecha_formateada}</small>
                            </div>
                            <p class="mb-0 small text-gray-600">${notif.mensaje}</p>
                        </div>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
            `;
        });
        
        lista.html(html);
    }
    
    obtenerIcono(tipo) {
        const iconos = {
            'info': 'fas fa-info-circle',
            'success': 'fas fa-check-circle',
            'warning': 'fas fa-exclamation-triangle',
            'danger': 'fas fa-times-circle',
            'primary': 'fas fa-bell'
        };
        return iconos[tipo] || 'fas fa-bell';
    }
    
    obtenerColor(tipo) {
        const colores = {
            'info': 'info',
            'success': 'success',
            'warning': 'warning',
            'danger': 'danger',
            'primary': 'primary'
        };
        return colores[tipo] || 'info';
    }
    
    async marcarComoLeida(id, enlace) {
        try {
            const formData = new FormData();
            formData.append('accion', 'marcar_leida');
            formData.append('id_notificacion', id);
            
            await fetch('controladores/controladorNotificacion.php', {
                method: 'POST',
                body: formData
            });
            
            // Redirigir si hay enlace
            if (enlace && enlace !== '#') {
                setTimeout(() => {
                    window.location.href = enlace;
                }, 300);
            }
            
            // Recargar notificaciones
            this.cargarNotificaciones();
        } catch (error) {
            console.error('Error marcando notificación:', error);
        }
    }
    
    async marcarTodasLeidas() {
        try {
            const formData = new FormData();
            formData.append('accion', 'marcar_todas_leidas');
            
            const response = await fetch('controladores/controladorNotificacion.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.cargarNotificaciones();
                this.mostrarToast('Todas las notificaciones marcadas como leídas', 'success');
            }
        } catch (error) {
            console.error('Error marcando todas las notificaciones:', error);
        }
    }
    
    reproducirSonido() {
        if (this.sonidoNotificacion) {
            this.sonidoNotificacion.play().catch(e => console.log('Error reproduciendo sonido:', e));
        }
    }
    
    mostrarToast(mensaje, tipo = 'info') {
        // Usar toast de Bootstrap si está disponible
        if (typeof bootstrap !== 'undefined') {
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${tipo} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${mensaje}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            document.body.appendChild(toastEl);
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
            
            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        } else {
            console.log(`[${tipo.toUpperCase()}] ${mensaje}`);
        }
    }
}

// Inicializar cuando el DOM esté listo
$(document).ready(() => {
    window.sistemaNotificaciones = new SistemaNotificaciones();
    
    // Forzar actualización al hacer clic en la campana
    $('#alertsDropdown').on('click', () => {
        window.sistemaNotificaciones.cargarNotificaciones();
    });
});
</script>

<style>
.badge-counter.pulse {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.dropdown-notificacion.unread {
    background-color: rgba(97, 83, 255, 0.05);
}
</style>

</body>

</html>