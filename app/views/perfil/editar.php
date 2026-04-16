<section class="page-shell page-shell--narrow">
    <div class="page-hero compact">
        <div>
            <span class="page-hero__eyebrow">Conta</span>
            <h1><i class="bi bi-person-gear"></i> Meu perfil</h1>
            <p>Atualize os seus dados pessoais e, se necessário, altere a senha de acesso.</p>
        </div>
    </div>

    <form method="POST" action="index.php?controler=perfil&acao=atualizar" class="panel-surface modern-form">
        <div class="panel-surface__header compact">
            <div>
                <span class="panel-surface__eyebrow">Dados pessoais</span>
                <h2>Informação de conta</h2>
            </div>
        </div>

        <div class="form-grid-two">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($utilizador['nome'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($utilizador['email'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="form-grid-two">
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($utilizador['telefone'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Função</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($utilizador['funcao'] ?? 'tecnico'); ?>" disabled>
            </div>
        </div>

        <div class="panel-surface__header compact top-padding">
            <div>
                <span class="panel-surface__eyebrow">Segurança</span>
                <h2>Alterar senha</h2>
            </div>
        </div>

        <div class="form-grid-two">
            <div class="mb-3">
                <label for="senha_nova" class="form-label">Nova senha</label>
                <input type="password" class="form-control" id="senha_nova" name="senha_nova" minlength="8" placeholder="Deixe vazio para manter a atual">
            </div>

            <div class="mb-3">
                <label for="confirmar_senha" class="form-label">Confirmar nova senha</label>
                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" minlength="8" placeholder="Repita a nova senha">
            </div>
        </div>

        <div class="form-actions-bar">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar alterações</button>
            <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        </div>
    </form>
</section>