<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4"><i class="bi bi-tools"></i> Equipamentos</h1>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <a href="index.php?controler=equipamento&acao=criar" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Novo Equipamento
        </a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" class="row g-3" action="index.php">
            <input type="hidden" name="controler" value="equipamento">
            <input type="hidden" name="acao" value="listar">
            
            <div class="col-md-6">
                <input type="text" name="localizacao" class="form-control" placeholder="Localização" value="<?php echo $_GET['localizacao'] ?? ''; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($equipamentos)): ?>
    <div class="alert alert-info">Nenhum equipamento registado.</div>
<?php else: ?>
    <div class="row">
        <?php foreach ($equipamentos as $equip): ?>
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $equip['tipo_nome']; ?></h5>
                        <p class="card-text">
                            <strong>Localização:</strong> <?php echo $equip['localizacao']; ?><br>
                            <strong>Número de Registo:</strong> <?php echo $equip['numero_serie'] ?? '-'; ?><br>
                            <strong>Marca/Modelo:</strong> <?php echo $equip['marca'] ?? '-'; ?> / <?php echo $equip['modelo'] ?? '-'; ?><br>
                            <strong>Estado:</strong> 
                            <span class="badge bg-<?php echo $equip['estado'] === 'operacional' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($equip['estado']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="index.php?controler=equipamento&acao=ver&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                        <a href="index.php?controler=equipamento&acao=editar&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="index.php?controler=equipamento&acao=deletar&id=<?php echo $equip['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem a certeza?');">Deletar</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
