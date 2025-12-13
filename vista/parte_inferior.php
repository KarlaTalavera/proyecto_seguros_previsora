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

</body>

</html>