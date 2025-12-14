<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once dirname(__DIR__) . '/config/asset_paths.php';

$fotoPerfilPath = 'img/undraw_profile.svg';
if (isset($_SESSION['datos_usuario']) && method_exists($_SESSION['datos_usuario'], 'getFotoPerfil')) {
	$fotoNombre = $_SESSION['datos_usuario']->getFotoPerfil();
	if ($fotoNombre) {
		$rutaUsuario = dirname(__DIR__) . '/assets/img/usuarios/' . $fotoNombre;
		if (is_file($rutaUsuario)) {
			$fotoPerfilPath = 'assets/img/usuarios/' . $fotoNombre;
		} else {
			$fotoPerfilPath = 'img/' . $fotoNombre;
		}
	}
}

$permisosSesion = isset($_SESSION['permisos_usuario']) && is_array($_SESSION['permisos_usuario']) ? $_SESSION['permisos_usuario'] : [];
$puedeGestionarSolicitudes = in_array('solicitud_gestionar', $permisosSesion, true);

$fontAwesomeCss = resolveAssetPath(
	'vendor/fontawesome-free/css/all.min.css',
	'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'
);
$sbAdminCss = resolveAssetPath('css/sb-admin-2.min.css', 'css/sb-admin-2.min.css');
$dataTablesCss = resolveAssetPath(
	'vendor/datatables/dataTables.bootstrap4.min.css',
	'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css'
);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

	<title>Seguros la Previsora</title>
	<script>
		(function() {
			var stored = null;
			try { stored = localStorage.getItem('sb-theme'); } catch (e) {}
			var theme = stored === 'dark' ? 'dark' : 'light';
			document.documentElement.setAttribute('data-theme', theme);
		})();
	</script>

	<!-- Custom fonts for this template-->
	<link href="<?php echo htmlspecialchars($fontAwesomeCss, ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet" type="text/css">
	<link
		href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
		rel="stylesheet">

    <!-- Custom styles for this template-->
	<link href="<?php echo htmlspecialchars($sbAdminCss, ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
	<link href="<?php echo htmlspecialchars($dataTablesCss, ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">

	<style>
		:root {
			--font-family-sans-serif: 'Plus Jakarta Sans', 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
			--font-family-heading: 'Plus Jakarta Sans', 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
			--color-bg-page: #f3f5ff;
			--color-bg-card: #ffffff;
			--color-text-primary: #1f2a44;
			--color-text-muted: #62718a;
			--color-border: rgba(73, 93, 138, 0.12);
			--color-border-strong: rgba(73, 93, 138, 0.22);
			--kpi-border-primary: #4338ca;
			--kpi-border-success: #0f9f6e;
			--kpi-border-info: #0f6fbf;
			--kpi-border-warning: #d97706;
			--shadow-soft: 0 18px 35px rgba(30, 64, 118, 0.12);
			--sidebar-gradient-start: #6ba6ff;
			--sidebar-gradient-end: #2c3f91;
			--sidebar-text-color: rgba(255,255,255,0.95);
			--sidebar-icon-color: rgba(255,255,255,0.85);
			--topbar-bg: rgba(255,255,255,0.92);
			--topbar-border: rgba(100, 116, 139, 0.15);
			--topbar-text: #1f2a44;
			--chip-bg: rgba(79, 97, 125, 0.08);
			--chip-text: #2d3a5b;
			--color-accent: #6153ff;
			--color-accent-strong: #4636d3;
			--color-accent-soft: rgba(97, 83, 255, 0.16);
			--modal-header-bg: var(--color-accent);
			--modal-header-color: #ffffff;
			--modal-hover-bg: rgba(97, 83, 255, 0.08);
			--btn-main-bg: var(--color-accent);
			--btn-main-bg-hover: linear-gradient(135deg, #6153ff, #4338ca);
			--btn-main-bg-active: linear-gradient(135deg, #4c3bd6, #3124a8);
			--btn-main-icon-bg: #4338ca;
			--btn-radius-pill: 999px;
			--chart-card-border: rgba(73, 93, 138, 0.18);
			--input-bg: #ffffff;
			--input-border: rgba(73, 93, 138, 0.3);
			--dropdown-bg: rgba(255,255,255,0.98);
			--dropdown-shadow: 0 20px 45px rgba(15, 35, 55, 0.18);
			--kpi-card-border: rgba(86, 102, 196, 0.18);
			--kpi-card-overlay: linear-gradient(135deg, rgba(118, 103, 255, 0.18), rgba(118, 103, 255, 0.04));
			--kpi-card-glow: rgba(118, 103, 255, 0.22);
			--modal-backdrop-bg: rgba(22, 31, 55, 0.55);
			--submenu-bg: rgba(255,255,255,0.98);
			--submenu-text: #1f2a44;
			--submenu-icon: #4e73df;
			--submenu-hover-bg: rgba(97, 83, 255, 0.08);
		}
		html[data-theme="dark"] {
			--color-bg-page: radial-gradient(circle at top, #1b1e3c 0%, #0e1024 100%);
			--color-bg-card: rgba(28, 34, 62, 0.94);
			--color-text-primary: rgba(229, 233, 255, 0.96);
			--color-text-muted: rgba(178, 190, 255, 0.68);
			--color-border: rgba(255, 255, 255, 0.14);
			--color-border-strong: rgba(214, 209, 255, 0.32);
			--kpi-border-primary: #a78bfa;
			--kpi-border-success: #34d399;
			--kpi-border-info: #60a5fa;
			--kpi-border-warning: #fbbf24;
			--shadow-soft: 0 26px 50px rgba(10, 14, 40, 0.55);
			--sidebar-gradient-start: #161b33;
			--sidebar-gradient-end: #6a30d3;
			--sidebar-text-color: rgba(235, 239, 255, 0.92);
			--sidebar-icon-color: rgba(235, 239, 255, 0.85);
			--topbar-bg: rgba(24, 28, 52, 0.88);
			--topbar-border: rgba(114, 94, 255, 0.3);
			--topbar-text: rgba(229, 233, 255, 0.95);
			--chip-bg: rgba(114, 94, 255, 0.22);
			--chip-text: rgba(224, 229, 255, 0.94);
			--color-accent: #a78bfa;
			--color-accent-strong: #7c3aed;
			--color-accent-soft: rgba(167, 139, 250, 0.24);
			--modal-header-bg: #5b21b6;
			--modal-header-color: rgba(241, 243, 255, 0.96);
			--modal-hover-bg: rgba(167, 139, 250, 0.16);
			--btn-main-bg: linear-gradient(135deg, #7c3aed, #5b21b6);
			--btn-main-bg-hover: linear-gradient(135deg, #8b5cf6, #5b21b6);
			--btn-main-bg-active: linear-gradient(135deg, #6d28d9, #4c1d95);
			--btn-main-icon-bg: rgba(167, 139, 250, 0.9);
			--chart-card-border: rgba(114, 94, 255, 0.35);
			--input-bg: rgba(28, 32, 58, 0.85);
			--input-border: rgba(114, 94, 255, 0.35);
			--dropdown-bg: rgba(22, 26, 48, 0.96);
			--dropdown-shadow: 0 24px 55px rgba(6, 8, 24, 0.65);
			--kpi-card-border: rgba(186, 173, 255, 0.45);
			--kpi-card-overlay: linear-gradient(140deg, rgba(167, 139, 250, 0.3), rgba(56, 45, 128, 0.08));
			--kpi-card-glow: rgba(134, 112, 255, 0.42);
			--modal-backdrop-bg: rgba(8, 12, 32, 0.78);
			--submenu-bg: rgba(18, 22, 44, 0.96);
			--submenu-text: rgba(229, 233, 255, 0.9);
			--submenu-icon: rgba(167, 139, 250, 0.95);
			--submenu-hover-bg: rgba(167, 139, 250, 0.16);
		}
		html {
			background: var(--color-bg-page);
		}
		html,
		body {
			min-height: 100%;
		}
		body {
			position: relative;
			background: var(--color-bg-page);
			color: var(--color-text-primary);
			font-family: var(--font-family-sans-serif);
			line-height: 1.55;
			transition: background 0.5s ease, color 0.4s ease;
		}
		h1,
		h2,
		h3,
		h4,
		h5,
		h6 {
			font-family: var(--font-family-heading);
			font-weight: 600;
		}
		body::before {
			content: "";
			position: fixed;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			background: var(--color-bg-page);
			z-index: -1;
			pointer-events: none;
		}
		#wrapper {
			position: relative;
			background: transparent;
		}
		.modal {
			z-index: 1500 !important;
		}
		.modal-backdrop {
			z-index: 1400 !important;
			background: var(--modal-backdrop-bg);
			opacity: 0;
		}
		.modal-backdrop.show {
			opacity: 1;
		}
		.modal.show .modal-dialog {
			pointer-events: auto !important;
		}
		#content-wrapper,
		#wrapper #content-wrapper,
		#content,
		#content > .container-fluid,
		.container,
		.container-fluid {
			background: transparent !important;
		}
		#accordionSidebar {
			background: linear-gradient(180deg, var(--sidebar-gradient-start) 0%, var(--sidebar-gradient-end) 100%) !important;
		}
		.sidebar .nav-item .nav-link,
		.sidebar .sidebar-brand .sidebar-brand-text,
		.sidebar .sidebar-brand .sidebar-brand-icon {
			color: var(--sidebar-text-color) !important;
		}
		.sidebar .fas,
		.sidebar .fa {
			color: var(--sidebar-icon-color) !important;
		}
		.sidebar .collapse-inner {
			background: var(--submenu-bg) !important;
			border: 1px solid var(--color-border);
			box-shadow: var(--dropdown-shadow);
		}
		html[data-theme="dark"] .sidebar .collapse-inner {
			border-color: var(--color-border-strong);
		}
		.sidebar .collapse-inner .collapse-item {
			color: var(--submenu-text) !important;
			font-weight: 500;
			border-radius: 0.5rem;
			transition: color 0.2s ease, background 0.2s ease;
		}
		.sidebar .collapse-inner .collapse-item i {
			color: var(--submenu-icon) !important;
			transition: color 0.2s ease, transform 0.2s ease;
		}
		.sidebar .collapse-inner .collapse-item:hover,
		.sidebar .collapse-inner .collapse-item:focus,
		.sidebar .collapse-inner .collapse-item.active {
			background: var(--submenu-hover-bg) !important;
			color: var(--color-accent-strong) !important;
		}
		.sidebar .collapse-inner .collapse-item:hover i,
		.sidebar .collapse-inner .collapse-item:focus i {
			color: var(--color-accent-strong) !important;
			transform: translateX(2px);
		}
		.sidebar .collapse-inner .collapse-item.active i {
			color: var(--color-accent-strong) !important;
		}
		.navbar.navbar-light.topbar {
			background: var(--topbar-bg);
			border-bottom: 1px solid var(--topbar-border);
			backdrop-filter: blur(12px);
			transition: background 0.4s ease, border-color 0.4s ease;
		}
		.navbar.navbar-light.topbar .nav-link,
		.navbar.navbar-light.topbar .navbar-nav .nav-item .nav-link,
		.navbar.navbar-light.topbar .navbar-nav .nav-item .nav-link i {
			color: var(--topbar-text) !important;
		}
		.navbar.navbar-light.topbar .badge-counter {
			background: var(--color-accent-strong);
			color: #ffffff;
		}
		.sticky-footer {
			position: relative !important;
			width: 100% !important;
			z-index: 2;
			clear: both;
			background: var(--color-bg-card);
			border-top: 1px solid var(--color-border);
			color: var(--color-text-muted);
		}
		#content-wrapper {
			min-height: 80vh;
			background: transparent !important;
		}
		#content .container-fluid {
			padding-bottom: 4rem;
			background: transparent;
		}
		.kpi-card {
			position: relative;
			display: block;
			border-radius: 1rem;
			border: 1px solid var(--kpi-card-border) !important;
			background: var(--color-bg-card) !important;
			box-shadow: 0 22px 45px rgba(18, 25, 55, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.12);
			overflow: hidden;
			transition: transform 0.22s ease, box-shadow 0.28s ease;
		}
		.kpi-card::after {
			content: "";
			position: absolute;
			inset: 0;
			border-radius: inherit;
			padding: 1.2px;
			background: var(--kpi-card-overlay);
			pointer-events: none;
			box-sizing: border-box;
			-webkit-mask:
				linear-gradient(#000 0 0) content-box,
				linear-gradient(#000 0 0);
			-webkit-mask-composite: xor;
			mask-composite: exclude;
			opacity: 0.9;
		}
		.kpi-card::before {
			content: "";
			position: absolute;
			inset: 1px;
			border-radius: inherit;
			border: 1px solid rgba(255, 255, 255, 0.18);
			pointer-events: none;
			z-index: 0;
		}
		.kpi-card > * {
			position: relative;
			z-index: 1;
		}
		.kpi-card:hover {
			transform: translateY(-2px);
			box-shadow: 0 26px 55px rgba(18, 25, 55, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.16);
		}
		.kpi-card .small {
			color: var(--color-text-muted);
			font-weight: 600;
			letter-spacing: 0.02em;
		}
		.kpi-card h4,
		.kpi-card .kpi-value {
			color: var(--color-text-primary);
			margin-bottom: 0;
		}
		.kpi-card__spark {
			margin-top: 0.75rem;
			height: 4px;
			width: 42px;
			border-radius: 999px;
			background: linear-gradient(90deg, rgba(97, 83, 255, 0.65) 0%, rgba(167, 139, 250, 0.2) 100%);
		}
		html[data-theme="dark"] .kpi-card {
			background: rgba(32, 38, 68, 0.94) !important;
			border-color: var(--kpi-card-border) !important;
			box-shadow:
				0 0 0 1px rgba(167, 139, 250, 0.26),
				0 28px 60px rgba(6, 8, 24, 0.78),
				inset 0 0 0 1px rgba(9, 12, 28, 0.62);
		}
		html[data-theme="dark"] .kpi-card::before {
			border-color: rgba(255, 255, 255, 0.25);
			box-shadow: 0 0 0 1px rgba(140, 120, 255, 0.25);
		}
		html[data-theme="dark"] .kpi-card::after {
			background: linear-gradient(145deg, rgba(198, 175, 255, 0.65), rgba(52, 38, 118, 0.18));
			opacity: 1;
		}
		html[data-theme="dark"] .kpi-card__spark {
			background: linear-gradient(90deg, rgba(236, 233, 255, 0.9) 0%, rgba(167, 139, 250, 0.45) 100%);
		}
		.card,
		.bg-white {
			background: var(--color-bg-card) !important;
			border: 1px solid var(--color-border);
			box-shadow: var(--shadow-soft);
			color: var(--color-text-primary);
			transition: background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
		}
		.card > .card-header {
			background: var(--color-bg-card);
			border-bottom: 1px solid var(--color-border);
			color: var(--color-text-primary);
		}
		.card > .card-header small,
		.card > .card-header .text-muted {
			color: var(--color-text-muted) !important;
		}
		.card.border-left-primary,
		.card.border-left-success,
		.card.border-left-info,
		.card.border-left-warning,
		.card.border-left-danger {
			position: relative;
			border-radius: 1rem;
			border: 1px solid var(--color-border-strong);
			border-left: none !important;
			overflow: hidden;
			padding-left: 1.35rem;
		}
		.card.border-left-primary {
			background: linear-gradient(120deg, rgba(97, 83, 255, 0.08) 0%, var(--color-bg-card) 45%) !important;
		}
		.card.border-left-success {
			background: linear-gradient(120deg, rgba(28, 181, 137, 0.08) 0%, var(--color-bg-card) 45%) !important;
		}
		.card.border-left-info {
			background: linear-gradient(120deg, rgba(54, 185, 204, 0.08) 0%, var(--color-bg-card) 45%) !important;
		}
		.card.border-left-warning {
			background: linear-gradient(120deg, rgba(246, 173, 85, 0.1) 0%, var(--color-bg-card) 45%) !important;
		}
		.card.border-left-danger {
			background: linear-gradient(120deg, rgba(231, 74, 59, 0.1) 0%, var(--color-bg-card) 45%) !important;
		}
		.card.border-left-primary .card-body,
		.card.border-left-success .card-body,
		.card.border-left-info .card-body,
		.card.border-left-warning .card-body,
		.card.border-left-danger .card-body {
			position: relative;
			z-index: 1;
		}
		.card.border-left-primary::before,
		.card.border-left-success::before,
		.card.border-left-info::before,
		.card.border-left-warning::before,
		.card.border-left-danger::before {
			content: "";
			position: absolute;
			top: 12px;
			bottom: 12px;
			left: 0;
			width: 8px;
			border-radius: 999px;
			box-shadow: 0 0 22px rgba(17, 24, 68, 0.22);
		}
		.card.border-left-primary::after,
		.card.border-left-success::after,
		.card.border-left-info::after,
		.card.border-left-warning::after,
		.card.border-left-danger::after {
			content: "";
			position: absolute;
			inset: 0;
			border-radius: inherit;
			border: 1px solid transparent;
			mix-blend-mode: screen;
			pointer-events: none;
		}
		.card.border-left-primary::before {
			background: linear-gradient(180deg, var(--kpi-border-primary) 0%, rgba(67, 56, 202, 0.35) 100%);
		}
		.card.border-left-success::before {
			background: linear-gradient(180deg, var(--kpi-border-success) 0%, rgba(15, 159, 110, 0.32) 100%);
		}
		.card.border-left-info::before {
			background: linear-gradient(180deg, var(--kpi-border-info) 0%, rgba(15, 111, 191, 0.32) 100%);
		}
		.card.border-left-warning::before {
			background: linear-gradient(180deg, var(--kpi-border-warning) 0%, rgba(217, 119, 6, 0.32) 100%);
		}
		.card.border-left-danger::before {
			background: linear-gradient(180deg, rgba(231, 76, 60, 0.95) 0%, rgba(179, 44, 35, 0.4) 100%);
		}
	html[data-theme="dark"] .card > .card-header {
		background: linear-gradient(145deg, rgba(28, 34, 62, 0.95), rgba(16, 20, 44, 0.92));
		border-bottom-color: rgba(167, 139, 250, 0.26);
		color: rgba(233, 236, 255, 0.94);
	}
	html[data-theme="dark"] .card > .card-header small,
	html[data-theme="dark"] .card > .card-header .text-muted {
		color: rgba(188, 195, 245, 0.85) !important;
	}
		html[data-theme="dark"] .card.border-left-primary,
		html[data-theme="dark"] .card.border-left-success,
		html[data-theme="dark"] .card.border-left-info,
		html[data-theme="dark"] .card.border-left-warning,
		html[data-theme="dark"] .card.border-left-danger {
			border-color: rgba(235, 235, 255, 0.22);
			box-shadow:
				0 0 0 2px rgba(255, 255, 255, 0.18),
				0 0 0 1px rgba(255, 255, 255, 0.34),
				0 16px 40px rgba(6, 8, 24, 0.72),
				inset 0 0 0 1px rgba(8, 12, 32, 0.35);
		}
		html[data-theme="dark"] .card.border-left-primary::after,
		html[data-theme="dark"] .card.border-left-success::after,
		html[data-theme="dark"] .card.border-left-info::after,
		html[data-theme="dark"] .card.border-left-warning::after,
		html[data-theme="dark"] .card.border-left-danger::after {
			border-width: 1.5px;
			border-color: rgba(255, 255, 255, 0.42);
			opacity: 1;
		}
		html[data-theme="dark"] .card.border-left-primary {
			background: linear-gradient(120deg, rgba(167, 139, 250, 0.32) 0%, rgba(29, 34, 60, 0.96) 38%, rgba(23, 27, 52, 0.92) 100%) !important;
		}
		html[data-theme="dark"] .card.border-left-success {
			background: linear-gradient(120deg, rgba(52, 211, 153, 0.28) 0%, rgba(29, 34, 60, 0.96) 38%, rgba(23, 27, 52, 0.92) 100%) !important;
		}
		html[data-theme="dark"] .card.border-left-info {
			background: linear-gradient(120deg, rgba(96, 165, 250, 0.3) 0%, rgba(29, 34, 60, 0.96) 38%, rgba(23, 27, 52, 0.92) 100%) !important;
		}
		html[data-theme="dark"] .card.border-left-warning {
			background: linear-gradient(120deg, rgba(251, 191, 36, 0.3) 0%, rgba(29, 34, 60, 0.96) 38%, rgba(23, 27, 52, 0.92) 100%) !important;
		}
		html[data-theme="dark"] .card.border-left-danger {
			background: linear-gradient(120deg, rgba(240, 82, 67, 0.4) 0%, rgba(29, 34, 60, 0.96) 38%, rgba(23, 27, 52, 0.92) 100%) !important;
		}
		html[data-theme="dark"] .card.border-left-primary::before {
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(190, 160, 255, 0.6) 46%, rgba(105, 67, 187, 0.7) 100%);
		}
		html[data-theme="dark"] .card.border-left-success::before {
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(74, 222, 172, 0.55) 46%, rgba(19, 127, 93, 0.7) 100%);
		}
		html[data-theme="dark"] .card.border-left-info::before {
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(125, 187, 255, 0.56) 46%, rgba(22, 105, 168, 0.7) 100%);
		}
		html[data-theme="dark"] .card.border-left-warning::before {
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 206, 92, 0.55) 46%, rgba(181, 92, 16, 0.7) 100%);
		}
		html[data-theme="dark"] .card.border-left-danger::before {
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.88) 0%, rgba(255, 168, 158, 0.58) 46%, rgba(176, 48, 41, 0.7) 100%);
		}
		.dashboard-card {
			padding: 1.25rem;
			margin-bottom: 1rem;
			border-radius: 1rem;
			border: 1px solid var(--chart-card-border);
		}
		.chart-note--inline {
			display: block;
			margin: 0;
			color: var(--color-text-muted);
		}
		.text-gray-800,
		h1, h2, h3, h4, h5, h6,
		label {
			color: var(--color-text-primary) !important;
		}
		.text-muted,
		.small.text-muted {
			color: var(--color-text-muted) !important;
		}
		.theme-toggle {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 40px;
			height: 40px;
			border-radius: 999px;
			border: none;
			background: var(--color-accent-soft);
			color: var(--color-accent-strong);
			transition: transform 0.2s ease, background 0.3s ease;
			cursor: pointer;
		}
		.theme-toggle:hover {
			transform: translateY(-1px);
			background: var(--color-accent);
			color: #ffffff;
		}
		.theme-toggle:focus {
			outline: none;
			box-shadow: 0 0 0 3px rgba(97, 83, 255, 0.35);
		}
		html[data-theme="dark"] .theme-toggle {
			background: rgba(167, 139, 250, 0.18);
			color: rgba(241, 243, 255, 0.96);
		}
		html[data-theme="dark"] .theme-toggle:hover {
			background: rgba(167, 139, 250, 0.35);
		}
		.table.table thead th {
			background: var(--color-accent-soft);
			border-bottom: 1px solid var(--color-border-strong);
			color: var(--color-text-primary);
		}
		.table.table tbody td {
			color: var(--color-text-primary);
			border-color: var(--color-border);
		}
		html[data-theme="dark"] .table.table thead th {
			background: rgba(124, 58, 237, 0.18);
		}
		html[data-theme="dark"] .table.table tbody td {
			color: rgba(223, 229, 255, 0.92);
		}
		.table-action-buttons {
			display: flex;
			align-items: center;
			gap: 0.4rem;
		}
		.table-action-buttons .action-icon {
			width: 34px;
			height: 34px;
			border-radius: 50%;
			border: none;
			background: var(--color-accent-soft);
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 0;
			cursor: pointer;
			transition: background-color 0.2s ease, transform 0.2s ease, color 0.2s ease;
			color: var(--color-accent-strong);
		}
		.action-icon--edit {
			color: #1cc88a;
		}
		.action-icon--delete {
			color: var(--color-accent-strong);
		}
		.action-icon--perm {
			color: #4e73df;
		}
		html[data-theme="dark"] .table-action-buttons .action-icon {
			background: rgba(167, 139, 250, 0.2);
			color: rgba(223, 229, 255, 0.9);
		}
		html[data-theme="dark"] .action-icon--edit {
			color: #34d399;
		}
		html[data-theme="dark"] .action-icon--delete {
			color: var(--color-accent);
		}
		html[data-theme="dark"] .action-icon--perm {
			color: #a5b4fc;
		}
		.table-action-buttons .action-icon i {
			font-size: 0.85rem;
			line-height: 1;
		}
		.table-action-buttons .action-icon:hover {
			background: var(--color-accent);
			color: #ffffff;
			transform: translateY(-1px);
		}
		.table-action-buttons .action-icon:active {
			transform: translateY(0);
		}
		.card-neo {
			border: 1px solid var(--color-border);
			border-radius: 1rem;
			box-shadow: 0 16px 38px rgba(15, 35, 55, 0.08);
			background: var(--color-bg-card);
			overflow: hidden;
			transition: border-color 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
		}
		.card-neo > .card-header {
			background: var(--color-bg-card);
			border-bottom: 1px solid var(--color-border);
			color: var(--color-text-primary);
			padding: 1rem 1.5rem;
		}
		.card-neo > .card-body {
			padding: 1.5rem;
		}
		html[data-theme="dark"] .card-neo {
			background: linear-gradient(152deg, rgba(32, 38, 72, 0.96) 0%, rgba(16, 21, 44, 0.94) 55%, rgba(8, 11, 28, 0.9) 100%);
			border: 1px solid rgba(140, 120, 255, 0.28);
			box-shadow: 0 36px 72px rgba(4, 6, 18, 0.68), 0 0 0 1px rgba(114, 94, 255, 0.18);
		}
		html[data-theme="dark"] .card-neo > .card-header {
			border-bottom-color: rgba(129, 108, 255, 0.35);
		}
		.badge-soft {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 0.35rem 0.9rem;
			border-radius: 999px;
			font-weight: 600;
			font-size: 0.75rem;
			letter-spacing: 0.02em;
			line-height: 1;
			text-transform: none;
			border: 1px solid transparent;
			transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
		}
		.badge-soft[data-variant="aprobado"],
		.badge-soft[data-variant="success"] {
			background: rgba(16, 185, 129, 0.16);
			color: #047857;
		}
		.badge-soft[data-variant="rechazado"],
		.badge-soft[data-variant="danger"] {
			background: rgba(248, 113, 113, 0.18);
			color: #7f1d1d;
		}
		.badge-soft[data-variant="pendiente"],
		.badge-soft[data-variant="warning"] {
			background: rgba(250, 204, 21, 0.18);
			color: #92400e;
		}
		.badge-soft[data-variant="revision"],
		.badge-soft[data-variant="info"] {
			background: rgba(129, 140, 248, 0.18);
			color: #3730a3;
		}
		.badge-soft[data-variant="neutral"],
		.badge-soft[data-variant="secondary"] {
			background: rgba(148, 163, 184, 0.18);
			color: #334155;
		}
		html[data-theme="dark"] .badge-soft {
			border-color: rgba(255, 255, 255, 0.08);
		}
		html[data-theme="dark"] .badge-soft[data-variant="aprobado"],
		html[data-theme="dark"] .badge-soft[data-variant="success"] {
			background: rgba(16, 185, 129, 0.32);
			color: #bbf7d0;
		}
		html[data-theme="dark"] .badge-soft[data-variant="rechazado"],
		html[data-theme="dark"] .badge-soft[data-variant="danger"] {
			background: rgba(248, 113, 113, 0.32);
			color: #fecaca;
		}
		html[data-theme="dark"] .badge-soft[data-variant="pendiente"],
		html[data-theme="dark"] .badge-soft[data-variant="warning"] {
			background: rgba(250, 204, 21, 0.3);
			color: #fce588;
		}
		html[data-theme="dark"] .badge-soft[data-variant="revision"],
		html[data-theme="dark"] .badge-soft[data-variant="info"] {
			background: rgba(129, 140, 248, 0.32);
			color: #c7d2fe;
		}
		html[data-theme="dark"] .badge-soft[data-variant="neutral"],
		html[data-theme="dark"] .badge-soft[data-variant="secondary"] {
			background: rgba(148, 163, 184, 0.28);
			color: #e2e8f0;
		}
		.dataTables_wrapper .dataTables_filter label,
		.dataTables_wrapper .dataTables_length label {
			font-weight: 500;
			color: var(--color-text-primary);
		}
		.dataTables_wrapper .dataTables_filter input,
		.dataTables_wrapper .dataTables_length select {
			background: var(--input-bg);
			border: 1px solid var(--input-border);
			border-radius: 999px;
			padding: 0.35rem 0.85rem;
			color: var(--color-text-primary);
			transition: border-color 0.2s ease, background 0.2s ease;
		}
		.dataTables_wrapper .dataTables_filter input:focus,
		.dataTables_wrapper .dataTables_length select:focus {
			outline: none;
			border-color: var(--color-accent);
			box-shadow: 0 0 0 0.15rem rgba(97, 83, 255, 0.2);
		}
		.dataTables_wrapper .dataTables_paginate {
			padding-top: 0.65rem;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button {
			border-radius: 999px !important;
			border: 1px solid transparent;
			background: transparent;
			color: var(--color-text-primary) !important;
			transition: background 0.2s ease, color 0.2s ease;
			padding: 0.3rem 0.65rem;
			margin: 0 0.1rem;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
			background: var(--color-accent-soft) !important;
			color: var(--color-accent-strong) !important;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button.current,
		.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
			background: var(--color-accent) !important;
			color: #ffffff !important;
			border-color: transparent;
		}
		html[data-theme="dark"] .dataTables_wrapper .dataTables_filter input,
		html[data-theme="dark"] .dataTables_wrapper .dataTables_length select {
			background: rgba(38, 44, 80, 0.9);
			border-color: rgba(147, 129, 255, 0.45);
			color: rgba(234, 237, 255, 0.92);
		}
		html[data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
			color: rgba(223, 229, 255, 0.85) !important;
		}
		html[data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
			background: rgba(129, 108, 255, 0.25) !important;
			color: #ffffff !important;
		}
		html[data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current {
			background: rgba(129, 108, 255, 0.9) !important;
		}
		.btn-neo {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 0.55rem 1.6rem;
			border-radius: var(--btn-radius-pill);
			font-weight: 600;
			transition: all 0.18s ease;
			border: 1px solid transparent;
			box-shadow: inset 3px 3px 6px rgba(0, 0, 0, 0.04), inset -3px -3px 6px rgba(255, 255, 255, 0.35), 4px 4px 12px rgba(24, 39, 58, 0.12);
		}
		.btn-neo > i:first-child,
		.btn-neo > svg:first-child {
			margin-right: 0.5rem;
		}
		.btn-neo--light {
			background: rgba(240, 244, 255, 0.85);
			color: #303a44;
			border-color: rgba(210, 220, 235, 0.9);
		}
		.btn-neo--light:hover {
			background: rgba(226, 234, 255, 0.9);
			color: #1f2a33;
		}
		html[data-theme="dark"] .btn-neo--light {
			background: rgba(41, 45, 74, 0.85);
			color: rgba(229, 233, 255, 0.9);
			border-color: rgba(114, 94, 255, 0.35);
		}
		.btn-neo--primary {
			background: var(--btn-main-bg);
			color: #ffffff;
			border: none;
			box-shadow: 0 14px 24px rgba(97, 83, 255, 0.28);
		}
		.btn-neo--primary:hover {
			background: var(--btn-main-bg-hover);
		}
		.btn-neo--primary:active {
			background: var(--btn-main-bg-active);
		}
		.btn-main-action {
			position: relative;
			min-width: 220px;
			height: 44px;
			display: inline-flex;
			align-items: center;
			justify-content: flex-start;
			padding: 0 56px 0 16px;
			border-radius: 12px;
			border: none;
			color: #ffffff;
			font-weight: 600;
			background: var(--btn-main-bg);
			box-shadow: 0 18px 30px rgba(97, 83, 255, 0.28);
			transition: background 0.18s ease, transform 0.18s ease;
		}
		.btn-main-action__label {
			color: inherit;
			margin: 0;
			font-size: 0.95rem;
			white-space: nowrap;
		}
		.btn-main-action:hover {
			background: var(--btn-main-bg-hover);
			transform: translateY(-1px);
			color: #ffffff;
		}
		.btn-main-action:active {
			background: var(--btn-main-bg-active);
			transform: translateY(0);
		}
		.btn-main-action__icon {
			position: absolute;
			right: 0;
			top: 0;
			width: 52px;
			height: 100%;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: var(--btn-main-icon-bg);
			border-top-right-radius: 12px;
			border-bottom-right-radius: 12px;
		}
		.btn-main-action__icon svg {
			width: 22px;
			height: 22px;
			stroke: #ffffff;
		}
		@media (max-width: 576px) {
			.btn-main-action {
				width: 100%;
				justify-content: center;
				padding-right: 16px;
			}
			.btn-main-action__icon {
				display: none;
			}
		}
		.modal-consistent .modal-content {
			border-radius: 1rem;
			overflow: hidden;
			border: 0;
			box-shadow: 0 24px 60px rgba(15, 24, 45, 0.2);
			background: var(--color-bg-card);
		}
		.modal-consistent .modal-header {
			background: var(--modal-header-bg);
			color: var(--modal-header-color);
			border-bottom: none;
		}
		.modal-consistent .modal-header .close {
			color: var(--modal-header-color);
			opacity: 0.85;
		}
		html[data-theme="dark"] .modal-consistent .modal-header .close {
			color: rgba(245, 247, 255, 0.85);
		}
		html[data-theme="dark"] .modal-consistent .modal-header .close:hover {
			opacity: 1;
		}
		.modal-consistent .modal-body {
			padding: 1.5rem;
			background: var(--color-bg-card);
			color: var(--color-text-primary);
		}
		.modal-consistent .modal-footer {
			border-top: none;
			background: rgba(97, 83, 255, 0.06);
			padding: 1rem 1.5rem;
			gap: 0.75rem;
		}
		#logoutModal .modal-content {
			border-radius: 1rem;
			border: 1px solid var(--color-border);
			background: var(--color-bg-card);
			color: var(--color-text-primary);
			box-shadow: 0 26px 60px rgba(15, 24, 45, 0.25);
		}
		#logoutModal .modal-header,
		#logoutModal .modal-footer {
			border-color: var(--color-border);
			background: var(--color-bg-card);
			color: var(--color-text-primary);
		}
		#logoutModal .modal-body {
			color: var(--color-text-muted);
		}
		html[data-theme="dark"] #logoutModal .modal-content {
			background: linear-gradient(155deg, rgba(28, 34, 62, 0.96), rgba(14, 18, 38, 0.92));
			border: 1px solid rgba(147, 129, 255, 0.4);
			color: rgba(231, 235, 255, 0.94);
			box-shadow: 0 40px 85px rgba(4, 6, 18, 0.78);
		}
		html[data-theme="dark"] #logoutModal .modal-header,
		html[data-theme="dark"] #logoutModal .modal-footer {
			background: rgba(22, 27, 52, 0.94);
			border-color: rgba(147, 129, 255, 0.35);
			color: rgba(231, 235, 255, 0.92);
		}
		html[data-theme="dark"] #logoutModal .modal-body {
			color: rgba(196, 204, 248, 0.86);
		}
		html[data-theme="dark"] .modal-consistent .modal-content {
			background: linear-gradient(152deg, rgba(32, 38, 72, 0.96) 0%, rgba(16, 21, 44, 0.94) 55%, rgba(8, 11, 28, 0.9) 100%);
			border: 1px solid rgba(140, 120, 255, 0.45);
			box-shadow: 0 42px 80px rgba(4, 6, 18, 0.72), 0 0 0 1px rgba(114, 94, 255, 0.2);
			color: rgba(233, 236, 255, 0.92);
		}
		html[data-theme="dark"] .modal-consistent .modal-header {
			background: linear-gradient(135deg, rgba(147, 124, 255, 0.95), rgba(96, 76, 219, 0.88));
			color: rgba(245, 247, 255, 0.96);
		}
		html[data-theme="dark"] .modal-consistent .modal-body,
		html[data-theme="dark"] .modal-consistent .modal-footer {
			background: linear-gradient(165deg, rgba(23, 28, 55, 0.94), rgba(14, 18, 38, 0.9));
			color: rgba(229, 233, 255, 0.9);
		}
		html[data-theme="dark"] .modal-consistent .modal-footer {
			border-top: 1px solid rgba(121, 103, 240, 0.35);
			box-shadow: inset 0 1px 0 rgba(245, 247, 255, 0.06);
		}
		html[data-theme="dark"] .modal-consistent .form-control,
		html[data-theme="dark"] .modal-consistent .custom-select,
		html[data-theme="dark"] .modal-consistent .input-group-text {
			background: rgba(38, 44, 80, 0.9);
			border-color: rgba(147, 129, 255, 0.45);
			color: rgba(234, 237, 255, 0.92);
		}
		html[data-theme="dark"] .modal-consistent .form-control::placeholder {
			color: rgba(176, 186, 232, 0.6);
		}
		.form-control,
		.custom-select,
		.input-group-text {
			background: var(--input-bg);
			border-color: var(--input-border);
			color: var(--color-text-primary);
			transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease;
		}
		.form-control:focus,
		.custom-select:focus {
			border-color: var(--color-accent);
			box-shadow: 0 0 0 0.2rem rgba(97, 83, 255, 0.25);
		}
		.form-control::placeholder {
			color: var(--color-text-muted);
			opacity: 0.7;
		}
		.dropdown-menu {
			background: var(--dropdown-bg);
			border: 1px solid var(--color-border);
			box-shadow: var(--dropdown-shadow);
			color: var(--color-text-primary);
		}
		.dropdown-item {
			color: var(--color-text-primary);
		}
		.dropdown-item:hover,
		.dropdown-item:focus {
			background: var(--color-accent-soft);
			color: var(--color-accent-strong);
		}
		footer.sticky-footer .copyright {
			color: inherit;
		}
	</style>

	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var root = document.documentElement;
			var toggleBtn = document.getElementById('themeToggleBtn');
			if (!toggleBtn) { return; }
			var icon = toggleBtn.querySelector('i');
			var label = toggleBtn.querySelector('.theme-toggle__label');

			function applyTheme(theme) {
				root.setAttribute('data-theme', theme);
				try { localStorage.setItem('sb-theme', theme); } catch (e) {}
				if (icon) {
					icon.classList.remove('fa-moon', 'fa-sun');
					icon.classList.add(theme === 'dark' ? 'fa-sun' : 'fa-moon');
				}
				if (label) {
					label.textContent = theme === 'dark' ? 'Tema claro' : 'Tema oscuro';
				}
				toggleBtn.setAttribute('aria-label', theme === 'dark' ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro');
			}

			var initialTheme = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
			applyTheme(initialTheme);

			toggleBtn.addEventListener('click', function () {
				var current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
				var next = current === 'dark' ? 'light' : 'dark';
				applyTheme(next);
			});
		});
	</script>

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
						<a class="collapse-item" href="index.php?vista=pagosCuotasGestion"><i class="fas fa-money-check-alt mr-1"></i> Pagos de cuotas</a>
						<?php if ($puedeGestionarSolicitudes): ?>
						<a class="collapse-item" href="index.php?vista=solicitudesGestion"><i class="fas fa-inbox mr-1"></i> Solicitudes</a>
						<?php endif; ?>
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
						<a class="collapse-item" href="index.php?vista=pagosCuotasGestion"><i class="fas fa-money-check-alt mr-1"></i> Pagos de cuotas</a>
						<a class="collapse-item" href="index.php?vista=solicitudesGestion"><i class="fas fa-inbox mr-1"></i> Solicitudes</a>
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

			<li class="nav-item">
                <a class="nav-link" href="index.php?vista=gestionTipoPoliza">
                    <i class="fas fa-fw fa-list-alt"></i>
                    <span>Tipos de Póliza</span>
                </a>
            </li>

			<li class="nav-item">
				<a class="nav-link" href="index.php?vista=gestionAsegurado">
					<i class="fas fa-fw fa-user-plus"></i>
					<span>Asegurados</span>
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

			<li class="nav-item">
				<a class="nav-link" href="index.php?vista=gestionAsegurado">
					<i class="fas fa-fw fa-user-plus"></i>
					<span>Asegurados</span>
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

				<li class="nav-item">
					<a class="nav-link" href="index.php?vista=pagosCuotasCliente">
						<i class="fas fa-fw fa-money-bill-wave"></i>
						<span>Pagos de cuotas</span></a>
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

						<li class="nav-item d-flex align-items-center mr-2">
							<button type="button" class="theme-toggle" id="themeToggleBtn" aria-label="Cambiar tema">
								<i class="fas fa-moon"></i>
								<span class="sr-only theme-toggle__label">Tema oscuro</span>
							</button>
						</li>

						<!-- Nav Item - Alerts -->
						<li class="nav-item">
							<a class="nav-link" href="index.php?vista=notificaciones">
								<i class="fas fa-fw fa-bell"></i>
								<span></span>
								<?php
								// Mostrar contador de notificaciones no leídas
								require_once dirname(__DIR__) . '/modelo/modeloNotificacion.php';
								$modeloNotif = new ModeloNotificacion();
								$totalNoLeidas = $modeloNotif->contarNoLeidas($usuario->getCedula());
								if ($totalNoLeidas > 0): ?>
									<span class="badge badge-danger badge-counter"><?php echo $totalNoLeidas; ?></span>
								<?php endif; ?>
							</a>
						</li>
						<!-- Nav Item - Messages -->
						
						<div class="topbar-divider d-none d-sm-block"></div>

						<!-- Nav Item - User Information -->
						<li class="nav-item dropdown no-arrow">
							<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
								data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo isset($_SESSION['datos_usuario']) ? $_SESSION['datos_usuario']->getNombreCompleto() : 'Usuario'; ?></span>
								<img class="img-profile rounded-circle"
									src="<?php echo htmlspecialchars($fotoPerfilPath, ENT_QUOTES, 'UTF-8'); ?>">
							</a>
							<!-- Dropdown - User Information -->
							<div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
								aria-labelledby="userDropdown">
								<a class="dropdown-item" href="index.php?vista=perfil">
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