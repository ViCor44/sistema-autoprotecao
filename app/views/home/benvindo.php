<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg text-center">
            <div class="card-body p-5">
                <i class="bi bi-shield-check" style="font-size: 3rem; color: #0d6efd;"></i>
                <h3 class="mt-3"><?php echo APP_NAME; ?></h3>
                <hr>
                <i class="bi bi-person-check-fill text-success" style="font-size: 2.5rem;"></i>
                <h4 class="mt-3">Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['utilizador_nome'] ?? 'Utilizador'); ?></strong>!</h4>
                <p class="text-muted">Login efetuado com sucesso. A redirecionar...</p>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<meta http-equiv="refresh" content="2;url=index.php">
