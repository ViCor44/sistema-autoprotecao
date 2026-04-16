<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus" style="font-size: 3rem; color: #0d6efd;"></i>
                    <h3 class="mt-3">Pedido de registo</h3>
                    <p class="text-muted">Submeta os seus dados para aprovação por um administrador.</p>
                </div>

                <form method="POST" action="index.php?controler=home&acao=registar">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control form-control-lg" id="nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control form-control-lg" id="telefone" name="telefone">
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control form-control-lg" id="senha" name="senha" minlength="8" required>
                    </div>

                    <div class="mb-4">
                        <label for="confirmar_senha" class="form-label">Confirmar senha</label>
                        <input type="password" class="form-control form-control-lg" id="confirmar_senha" name="confirmar_senha" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-send"></i> Submeter pedido
                    </button>
                </form>

                <div class="d-grid mt-3">
                    <a href="index.php?controler=home&acao=login" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-left"></i> Voltar ao login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>