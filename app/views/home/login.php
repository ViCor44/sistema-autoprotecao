<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-check" style="font-size: 3rem; color: #0d6efd;"></i>
                    <h3 class="mt-3"><?php echo APP_NAME; ?></h3>
                    <p class="text-muted">Sistema de Autoproteção</p>
                </div>

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
            </div>
        </div>
    </div>
</div>
