<!DOCTYPE html>
<html lang="pt-PT">
<?php
$styleVersion = file_exists(PUBLIC_PATH . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'style.css')
    ? filemtime(PUBLIC_PATH . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'style.css')
    : time();
$scriptVersion = file_exists(PUBLIC_PATH . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'script.js')
    ? filemtime(PUBLIC_PATH . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'script.js')
    : time();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/style.css?v=<?php echo $styleVersion; ?>">
</head>
<body>
    <?php if (isset($_SESSION['utilizador_id'])): ?>
        <?php
        $controllerAtual = $_GET['controler'] ?? $_GET['controller'] ?? 'home';
        $nomeUtilizador = $_SESSION['utilizador_nome'] ?? 'Utilizador';
        $inicialUtilizador = strtoupper(substr($nomeUtilizador, 0, 1));
        ?>
        <nav class="navbar navbar-expand-xl app-navbar">
            <div class="container-fluid app-navbar__inner">
                <a class="navbar-brand app-navbar__brand" href="index.php">
                    <span class="app-navbar__brand-icon"><i class="bi bi-shield-check"></i></span>
                    <span>
                        <strong><?php echo APP_NAME; ?></strong>
                        <small>Centro operacional</small>
                    </span>
                </a>
                <button class="navbar-toggler app-navbar__toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Alternar menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto app-navbar__links">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $controllerAtual === 'home' ? 'active' : ''; ?>" href="index.php"><i class="bi bi-house"></i> Início</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $controllerAtual === 'equipamento' ? 'active' : ''; ?>" href="index.php?controler=equipamento&acao=listar"><i class="bi bi-tools"></i> Equipamentos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $controllerAtual === 'tipo_equipamento' ? 'active' : ''; ?>" href="index.php?controler=tipo_equipamento&acao=listar"><i class="bi bi-sliders"></i> Tipos de Equipamentos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $controllerAtual === 'relatorio' ? 'active' : ''; ?>" href="index.php?controler=relatorio&acao=listar"><i class="bi bi-clipboard-data"></i> Relatórios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $controllerAtual === 'calendario' ? 'active' : ''; ?>" href="index.php?controler=calendario&acao=calendario"><i class="bi bi-calendar3"></i> Calendário</a>
                        </li>
                        <?php if (($_SESSION['utilizador_funcao'] ?? '') === 'administrador'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $controllerAtual === 'utilizador' ? 'active' : ''; ?>" href="index.php?controler=utilizador&acao=listar"><i class="bi bi-people"></i> Utilizadores</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown app-navbar__user">
                            <a class="nav-link dropdown-toggle app-navbar__user-trigger" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="app-navbar__avatar"><?php echo htmlspecialchars($inicialUtilizador); ?></span>
                                <span><?php echo htmlspecialchars($nomeUtilizador); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end app-navbar__dropdown" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="index.php?controler=perfil&acao=editar"><i class="bi bi-person-gear"></i> Meu perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?controler=home&acao=logout"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <main class="container mt-4">
        <?php if (isset($_SESSION['mensagem'])): ?>
            <?php
            $alertTypes = [
                'sucesso' => 'success',
                'erro' => 'danger',
                'aviso' => 'warning',
                'info' => 'info',
            ];
            $alertClass = $alertTypes[$_SESSION['tipo_mensagem'] ?? 'info'] ?? 'info';
            ?>
            <div class="alert alert-<?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['mensagem']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
        <?php endif; ?>

        <?php if (isset($content)): ?>
            <?php echo $content; ?>
        <?php elseif (isset($view_path) && file_exists($view_path)): ?>
            <?php include $view_path; ?>
        <?php endif; ?>
    </main>

    <footer class="app-footer mt-5">
        <div class="container app-footer__inner">
            <div class="app-footer__brand">
                <div class="app-footer__logo"><i class="bi bi-shield-lock"></i></div>
                <div>
                    <strong><?php echo APP_NAME; ?></strong>
                    <p>Gestão técnica de medidas de autoproteção.</p>
                </div>
            </div>

            <div class="app-footer__links">
                <a href="index.php">Início</a>
                <a href="index.php?controler=calendario&acao=calendario">Calendário</a>
                <a href="index.php?controler=relatorio&acao=listar">Relatórios</a>
                <?php if (isset($_SESSION['utilizador_id'])): ?>
                    <a href="index.php?controler=perfil&acao=editar">Meu perfil</a>
                <?php else: ?>
                    <a href="index.php?controler=home&acao=login">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="app-footer__bottom">
            <div class="container">
                <span>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Todos os direitos reservados.</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/script.js?v=<?php echo $scriptVersion; ?>"></script>
</body>
</html>
