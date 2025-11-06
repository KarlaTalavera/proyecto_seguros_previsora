<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Seguros la Previsora</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- evitar overflow del logo -->
      <style>
        /* Forzar color del sidebar a #2c657fff */
        #accordionSidebar {
            background-color: #1d4855ff !important;
            background-image: none !important;
            /* si el tema usa gradientes, reemplazamos por un único color */
            background: linear-gradient(180deg, #60a5d3ff 0%, #212b61ff 100%) !important;
        }
        /* Asegurar que los iconos/textos mantengan buena visibilidad */
        .sidebar .nav-item .nav-link,
        .sidebar .sidebar-brand .sidebar-brand-text,
        .sidebar .sidebar-brand .sidebar-brand-icon {
            color: #ffffff !important;
        }
        /* Ajuste opcional de iconos pequeños con baja opacidad (si aplica) */
        .sidebar .fas, .sidebar .fa {
            color: rgba(255,255,255,0.95) !important;
        }

    </style>

	<style>
	/* Safety CSS: asegurar que el footer ocupe el ancho correcto y no quede como barra lateral
	   Esto es un parche visual que evita que un DOM mal cerrado o reglas colapsadas muevan el footer
	   fuera de su sitio. Si la raíz del problema es HTML mal formado, lo ideal es corregirlo,
	   pero esta regla evita el efecto visible mientras se valida. */
	.sticky-footer {
		position: relative !important;
		width: 100% !important;
		z-index: 2;
		clear: both;
	}
	#content-wrapper {
		min-height: 80vh;
	}
	</style>

</head>

<body id="page-top">

	<!-- page wrapper -->
	<div id="wrapper">

		<!-- sidebar -->
		<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

			<!-- Sidebar - Brand -->
			<li class="nav-item active align-items-center justify-content-center">
                    <div class="sidebar-brand-icon">
                        <img src="img/iconos-17.svg" alt="logo" class="sidebar-logo">
                    </div>
            </li>

			<!-- Divider -->
			<hr class="sidebar-divider my-0">
			<?php if (isset($_SESSION['datos_usuario']) && $_SESSION['datos_usuario']->getNombreRol() === 'agente'): ?>
			<!-- Nav Item - Dashboard -->
			 <li class="nav-item active">
				<a class="nav-link" href="index.php?vista=estadisticasAgente">					<!-- icono: dashboard -->
					<i class="fas fa-fw fa-tachometer-alt"></i>
					<span>Dashboard</span>
				</a>
			</li>
			<?php endif; ?>

			<?php if (isset($_SESSION['datos_usuario']) && $_SESSION['datos_usuario']->getNombreRol() === 'administrador'): ?>
			            <li class="nav-item active">
			                <a class="nav-link" href="index.php?vista=estadisticasAdmin">
			                    <i class="fas fa-fw fa-tachometer-alt"></i>
			                    <span>Dashboard</span></a>
			            </li>			
			<?php endif; ?>

			<?php if (isset($_SESSION['datos_usuario']) && $_SESSION['datos_usuario']->getNombreRol() === 'asegurado'): ?>
					<li class="nav-item active">
						<a class="nav-link" href="index.php?vista=polizasCliente">
							<i class="fas fa-fw fa-file-alt"></i>
							<span>Mis Pólizas</span></a>
					</li>
			<?php endif; ?>

			<!-- Divider -->

			<?php if (isset($_SESSION['datos_usuario']) && $_SESSION['datos_usuario']->getNombreRol() === 'agente'): ?>
				<hr class="sidebar-divider">
				<div class="sidebar-heading">
					Gestión
				</div>
			<!-- collapse: componentes. nota: usa collapse para agrupar subitems -->
			<li class="nav-item">
				<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
					aria-expanded="true" aria-controls="collapseTwo">
					<!-- icono: lista de componentes -->
					<i class="fas fa-fw fa-th-list"></i>
					<span>Componentes</span>
				</a>
				<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
					<div class="bg-white py-2 collapse-inner rounded">
						<!-- icono: polizas -->
						<a class="collapse-item" href="index.php?vista=polizasAgente"><i class="fas fa-file-alt mr-1"></i> Polizas</a>
						<!-- icono: siniestros -->
						<a class="collapse-item" href="index.php?vista=siniestrosAgente"><i class="fas fa-ambulance mr-1"></i> Siniestros</a>
					</div>
				</div>
			</li>
			<?php endif; ?>

			<?php if (isset($_SESSION['datos_usuario']) && $_SESSION['datos_usuario']->getNombreRol() === 'administrador'): ?>
				<hr class="sidebar-divider">
				<div class="sidebar-heading">
					Gestión
				</div>
			<li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-th-list"></i>
                    <span>Componentes</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="index.php?vista=polizasAdmin"><i class="fas fa-file-alt mr-1"></i> Polizas</a>
                        <a class="collapse-item" href="index.php?vista=siniestrosAdmin"><i class="fas fa-ambulance mr-1"></i> Siniestros</a>
                    </div>
                </div>
            </li>
			<?php endif; ?>

			<!-- Nav Item - Utilities Collapse Menu -->


			<!-- Divider -->
			<hr class="sidebar-divider">

			<div class="sidebar-heading">
				Adicionales
			</div>

			<?php if (isset($_SESSION['datos_usuario']) && $_SESSION['datos_usuario']->getNombreRol() === 'administrador'): ?>
			            <li class="nav-item">
			                <a class="nav-link" href="index.php?vista=estadisticasAdmin">
			                    <i class="fas fa-fw fa-chart-line"></i>
			                    <span>Estadisticas</span></a>
			            </li>                
            <li class="nav-item">
                <a class="nav-link" href="index.php?vista=reportesAdmin">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Reportes</span></a>
            </li>

			<li class="nav-item">
				<a class="nav-link" href="index.php?vista=gestionCliente">
					<!-- icono: clientes -->
					<i class="fas fa-fw fa-users"></i>
					<span>Clientes</span>
				</a>
			</li>

			<li class="nav-item">
                <a class="nav-link" href="index.php?vista=gestionAgente">
                    <i class="fas fa-fw fa-user-tie"></i>
                    <span>Agentes</span>
                </a>
            </li>
			<?php endif; ?>

			<?php if (isset($_SESSION['datos_usuario']) && $_SESSION['datos_usuario']->getNombreRol() === 'agente'): ?>
			<li class="nav-item">
				<a class="nav-link" href="index.php?vista=estadisticasAgente">
					<!-- icono: estadisticas -->
					<i class="fas fa-fw fa-chart-line"></i>
					<span>Estadisticas</span>
				</a>
			</li>

			<li class="nav-item">
				<a class="nav-link" href="index.php?vista=gestionCliente">
					<!-- icono: clientes -->
					<i class="fas fa-fw fa-users"></i>
					<span>Clientes</span>
				</a>
			</li>

			<li class="nav-item">
				<a class="nav-link" href="index.php?vista=reportesAgente">
					<!-- icono: reportes -->
					<i class="fas fa-fw fa-file-alt"></i>
					<span>Reportes</span>
				</a>
			</li>
			<?php endif; ?>

			<?php if (isset($_SESSION['datos_usuario']) && $_SESSION['datos_usuario']->getNombreRol() === 'asegurado'): ?>
			 <li class="nav-item">
                <a class="nav-link" href="index.php?vista=documentacionCliente">
                    <i class="fas fa-fw fa-folder-open"></i>
                    <span>Documentación</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?vista=solicitudCliente">
                    <i class="fas fa-fw fa-paper-plane"></i>
                    <span>Solicitudes</span></a>
            </li>
			<?php endif; ?>

			<!-- Nav Item - Tables -->

			<!-- Divider -->
			<hr class="sidebar-divider d-none d-md-block">

			<!-- Sidebar Toggler (Sidebar) -->
			<div class="text-center d-none d-md-inline">
				<button class="rounded-circle border-0" id="sidebarToggle"></button>
			</div>
		

		</ul>
		<!-- end of sidebar -->

		<!-- content wrapper -->
		<div id="content-wrapper" class="d-flex flex-column">

			<!-- Main Content -->
			<div id="content">

				<!-- Topbar -->
				<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

					<!-- Sidebar Toggle (Topbar) -->
					<button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
						<i class="fa fa-bars"></i>
					</button>


					<!-- Topbar Navbar -->
					<ul class="navbar-nav ml-auto">

						<!-- Nav Item - Search Dropdown (Visible Only XS) -->
						<li class="nav-item dropdown no-arrow d-sm-none">
							<a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
								data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-search fa-fw"></i>
							</a>
							<!-- Dropdown - Messages -->
							<div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
								aria-labelledby="searchDropdown">
								<form class="form-inline mr-auto w-100 navbar-search">
									<div class="input-group">
										<input type="text" class="form-control bg-light border-0 small"
											placeholder="Search for..." aria-label="Search"
											aria-describedby="basic-addon2">
										<div class="input-group-append">
											<button class="btn btn-primary" type="button">
												<i class="fas fa-search fa-sm"></i>
											</button>
										</div>
									</div>
								</form>
							</div>
						</li>

						<!-- Nav Item - Alerts -->
						<li class="nav-item dropdown no-arrow mx-1">
							<a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
								data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-bell fa-fw"></i>
								<!-- Counter - Alerts -->
								<span class="badge badge-danger badge-counter">3+</span>
							</a>
							<!-- Dropdown - Alerts -->
							<div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
								aria-labelledby="alertsDropdown">
								<h6 class="dropdown-header">
									Alerts Center
								</h6>
								
								<a class="dropdown-item d-flex align-items-center" href="#">
									<div class="mr-3">
										<div class="icon-circle bg-success">
											<i class="fas fa-donate text-white"></i>
										</div>
									</div>
									<div>
										<div class="small text-gray-500">Notificacion</div>
										Ya pagaron pue, que vaina mas fina
									</div>
								</a>
								<a class="dropdown-item d-flex align-items-center" href="#">
									<div class="mr-3">
										<div class="icon-circle bg-warning">
											<i class="fas fa-exclamation-triangle text-white"></i>
										</div>
									</div>
									<div>
										<div class="small text-gray-500">Octubre 2, 2025</div>
										Alo Alo probando Alo
									</div>
								</a>
								<a class="dropdown-item text-center small text-gray-500" href="#">Mostrar todas las notificaciones</a>
							</div>
						</li>

						<!-- Nav Item - Messages -->
						
						<div class="topbar-divider d-none d-sm-block"></div>

						<!-- Nav Item - User Information -->
						<li class="nav-item dropdown no-arrow">
							<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
								data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo isset($_SESSION['datos_usuario']) ? $_SESSION['datos_usuario']->getNombreCompleto() : 'Usuario'; ?></span>
								<img class="img-profile rounded-circle"
									src="img/undraw_profile.svg">
							</a>
							<!-- Dropdown - User Information -->
							<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
								aria-labelledby="userDropdown">
								<a class="dropdown-item" href="#">
									<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
									Perfil
								</a>
						
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="index.php?vista=login" data-toggle="modal" data-target="#logoutModal">
									<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
									Cerrar sesion
								</a>
							</div>
						</li>

					</ul>

				</nav>
				<!-- End of Topbar -->