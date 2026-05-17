<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-check" style="font-size: 3rem; color: #0d6efd;"></i>
                    <h3 class="mt-3"><?php echo APP_NAME; ?></h3>
                    <p class="text-muted">Sistema de Autoproteção</p>
                    <p class="text-primary fw-semibold">Bem-vindo! Inicie sessão para continuar.</p>
                </div>

                <?php if (!empty($benvindo)): ?>
                <div class="text-center py-3">
                    <i class="bi bi-person-check-fill text-success" style="font-size: 2.5rem;"></i>
                    <h5 class="mt-2">Bem-vindo, <strong><?php echo htmlspecialchars($benvindo); ?></strong>!</h5>
                    <p class="text-muted small">A redirecionar para o painel...</p>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:100%;"></div>
                    </div>
                </div>
                <script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>
                <?php else: ?>

                <form method="POST" action="index.php?controler=home&acao=autenticar">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control form-control-lg" id="senha" name="senha" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </button>
                </form>

                <div class="d-grid mt-3">
                    <a href="index.php?controler=home&acao=registo" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Criar conta
                    </a>
                </div>

                <hr>

                <div class="alert alert-info" role="alert">
                    <small>
                        <strong>Dados de teste:</strong><br>
                        Email: <code>admin@autoprotecao.com</code><br>
                        Senha: Altere após o primeiro acesso
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
