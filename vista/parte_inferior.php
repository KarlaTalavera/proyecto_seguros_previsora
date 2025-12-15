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

    <!-- Core plugin JavaScript-->

    <!-- Note: Bootstrap and other plugins are loaded after we ensure jQuery is available below -->


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
    // Fallback loader: si jQuery o el script de solicitudes no están disponibles,
    // cargarlos dinámicamente para asegurar que la tabla y acciones funcionen.
    $localJquery = htmlspecialchars($jquerySrc, ENT_QUOTES, 'UTF-8');
    $solicitudesJs = htmlspecialchars(resolveAssetPath('js/solicitudesGestion.js', 'js/solicitudesGestion.js'), ENT_QUOTES, 'UTF-8');
    ?>
    <script>
    (function(){
        function loadScript(src, cb){
            var s = document.createElement('script');
            s.src = src;
            s.async = false;
            s.onload = function(){ if(cb) cb(null); };
            s.onerror = function(e){ if(cb) cb(e); };
            document.head.appendChild(s);
        }

        function ensureSolicitudes(){
            try{
                if (document.querySelector('#tablaSolicitudesGestion') && !document.querySelector('script[src*="solicitudesGestion.js"]')){
                    loadScript('<?php echo $solicitudesJs; ?>', function(err){ if(err) console.error('No se pudo cargar solicitudesGestion.js', err); });
                }
            }catch(e){ console.error(e); }
        }

        var bootstrapSrc = '<?php echo htmlspecialchars($bootstrapBundleSrc, ENT_QUOTES, 'UTF-8'); ?>';
        var jqueryEasing = '<?php echo htmlspecialchars($jqueryEasingSrc, ENT_QUOTES, 'UTF-8'); ?>';
        var sbAdmin = '<?php echo htmlspecialchars($sbAdminJs, ENT_QUOTES, 'UTF-8'); ?>';
        var chartJs = '<?php echo htmlspecialchars($chartJsSrc, ENT_QUOTES, 'UTF-8'); ?>';
        var demoArea = 'js/demo/chart-area-demo.js';
        var demoPie = 'js/demo/chart-pie-demo.js';

        function loadBootstrapAndPlugins(){
            // Load in order: bootstrap -> easing -> sb-admin -> chartjs -> demos -> ensure solicitudes
            loadScript(bootstrapSrc, function(err){
                if (err) console.error('Error cargando Bootstrap:', err);
                loadScript(jqueryEasing, function(){
                    loadScript(sbAdmin, function(){
                        loadScript(chartJs, function(){
                            loadScript(demoArea, function(){
                                loadScript(demoPie, function(){
                                    ensureSolicitudes();
                                });
                            });
                        });
                    });
                });
            });
        }

        if (!window.jQuery){
            loadScript('<?php echo $localJquery; ?>', function(err){
                if (err) {
                    console.error('No se pudo cargar jQuery desde fallback:', err);
                    // still attempt to load bootstrap after (will likely error), but try to recover
                } else {
                    console.log('jQuery cargado por fallback');
                }
                loadBootstrapAndPlugins();
            });
        } else {
            loadBootstrapAndPlugins();
        }
    })();
    </script>
    <script>
    // Move user dropdown to body on open to avoid clipping by parent stacking contexts
    (function($){
        var $toggle = $('#userDropdown');
        if (!$toggle || !$toggle.length) return;
        $toggle.on('show.bs.dropdown', function () {
            var $menu = $toggle.closest('.nav-item').find('.dropdown-menu');
            if (!$menu || !$menu.length) return;
            $menu.data('orig-parent', $menu.parent());
            $menu.appendTo('body');
            setTimeout(function(){
                var rect = $toggle[0].getBoundingClientRect();
                var menuW = $menu.outerWidth();
                var left = rect.right - menuW;
                left = Math.max(8, left + window.scrollX);
                var top = rect.bottom + window.scrollY;
                // Use a very large z-index and position fixed to escape stacking contexts
                $menu.css({position: 'fixed', top: top + 'px', left: left + 'px', zIndex: 2147483647});
                $menu.addClass('dropdown-menu--moved');
                // Also raise the toggle's nav-item stacking context as fallback
                var $navItem = $toggle.closest('.nav-item');
                if ($navItem && $navItem.length) {
                    $navItem.data('orig-z', $navItem.css('z-index'));
                    $navItem.css('z-index', 2147483646);
                }
            }, 10);
        });

        $toggle.on('hide.bs.dropdown', function () {
            var $menu = $('body').find('.dropdown-menu.dropdown-menu--moved').first();
            if (!$menu || !$menu.length) return;
            var $orig = $menu.data('orig-parent');
            $menu.removeClass('dropdown-menu--moved');
            $menu.css({position: '', top: '', left: '', zIndex: ''}).detach().appendTo($orig).removeData('orig-parent');
            // restore nav-item z-index if changed
            var $navItem = $toggle.closest('.nav-item');
            if ($navItem && $navItem.length) {
                var origZ = $navItem.data('orig-z');
                if (typeof origZ !== 'undefined') {
                    $navItem.css('z-index', origZ);
                    $navItem.removeData('orig-z');
                } else {
                    $navItem.css('z-index', '');
                }
            }
        });
    })(jQuery);
    </script>
    <style>
    /* Asegurar que el menú movido esté siempre por encima */
    .dropdown-menu--moved {
        position: fixed !important;
        z-index: 2147483647 !important;
    }
    </style>
    
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
        
        // Configurar sonido de notificación (comprobar existencia)
        this.sonidoNotificacion = null;
        (async () => {
            try {
                const resp = await fetch('assets/sounds/notification.mp3', { method: 'HEAD' });
                if (resp.ok) {
                    this.sonidoNotificacion = new Audio('assets/sounds/notification.mp3');
                }
            } catch (e) {
                // no sound available or blocked
                console.log('Notificación sonora no disponible:', e);
            }
        })();
        
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
            const response = await fetch('controlador/controladorNotificacion.php?accion=obtener_notificaciones&solo_no_leidas=false&limit=5');
            const contentType = response.headers.get('content-type') || '';
            if (!response.ok) {
                const txt = await response.text().catch(() => '');
                console.error('Error fetch notificaciones - status:', response.status, txt);
                return;
            }

            if (contentType.indexOf('application/json') === -1) {
                // server returned HTML or other content (likely a PHP error). Log it.
                const txt = await response.text().catch(() => '');
                console.error('Respuesta no JSON al obtener notificaciones:', txt);
                return;
            }

            const data = await response.json();

            if (data && data.success) {
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
            
            await fetch('controlador/controladorNotificacion.php', {
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
            
            const response = await fetch('controlador/controladorNotificacion.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const txt = await response.text().catch(() => '');
                console.error('Error marcarTodasLeidas - status:', response.status, txt);
                return;
            }

            const ct = response.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) {
                const txt = await response.text().catch(() => '');
                console.error('Respuesta no JSON en marcarTodasLeidas:', txt);
                return;
            }

            const data = await response.json();
            if (data && data.success) {
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

    /* Ensure moved dropdown is top-most and pointer-events enabled */
    .dropdown-menu--moved {
        position: fixed !important;
        z-index: 2147483647 !important;
        pointer-events: auto !important;
        transform: none !important;
    }
</style>

<script>
(function(){
    function analyzeDropdownOverlay() {
        var $toggle = $('#userDropdown');
        if (!$toggle.length) return;
        var observer = function(){
            var $menu = $('body').find('.dropdown-menu.dropdown-menu--moved').first();
            if (!$menu.length) return;
            var rect = $menu[0].getBoundingClientRect();
            var points = [
                 {x: rect.left + 8, y: rect.top + 8},
                 {x: rect.left + rect.width/2, y: rect.top + rect.height/2},
                 {x: rect.right - 8, y: rect.bottom -8}
            ];
            console.group('dropdown-overlay-diagnostic');
            points.forEach(function(p, idx){
                var el = document.elementFromPoint(p.x, p.y);
                console.group('point ' + idx + ' (' + Math.round(p.x) + ',' + Math.round(p.y) + ')');
                if (!el) { console.log('no element at point'); console.groupEnd(); return; }
                console.log('top element:', el);
                var elems = [];
                var node = el;
                while (node && node !== document) {
                     var cs = window.getComputedStyle(node);
                     elems.push({
                         node: node.tagName + (node.id?('#'+node.id):'') + (node.className?('.'+node.className.split(' ').join('.')):''),
                         zIndex: cs.zIndex,
                         position: cs.position,
                         transform: cs.transform,
                         opacity: cs.opacity,
                         overflow: cs.overflow,
                         pointerEvents: cs.pointerEvents
                     });
                     node = node.parentNode;
                }
                console.log(elems);
                console.groupEnd();
            });
            console.groupEnd();
        };

        $toggle.on('shown.bs.dropdown', function(){
            setTimeout(observer, 30);
        });
    }
    if (window.jQuery) {
        $(document).ready(analyzeDropdownOverlay);
    } else {
        document.addEventListener('DOMContentLoaded', analyzeDropdownOverlay);
    }
})();
</script>

</body>

</html>