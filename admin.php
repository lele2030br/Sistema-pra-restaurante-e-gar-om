<?php 
include 'config.php'; 
verificarAcesso('admin');

// --- LÓGICA DE GERENCIAMENTO (MySQL / PDO) ---

// 1. Gerenciar Mesas
if (isset($_POST['add_mesa'])) { 
    $n = $_POST['numero_mesa']; 
    $stmt = $pdo->prepare("INSERT IGNORE INTO mesas (numero) VALUES (?)");
    $stmt->execute(["Mesa $n"]); 
}
if (isset($_GET['del_mesa'])) { 
    $stmt = $pdo->prepare("DELETE FROM mesas WHERE id = ?");
    $stmt->execute([(int)$_GET['del_mesa']]); 
}

// 2. Gerenciar Produtos
if (isset($_POST['add_prod'])) {
    $n = $_POST['nome']; 
    $p = (float)$_POST['preco']; 
    $c = $_POST['cat'] ?? 'Geral';
    $stmt = $pdo->prepare("INSERT INTO produtos (nome, preco, categoria) VALUES (?, ?, ?)");
    $stmt->execute([$n, $p, $c]);
}
if (isset($_GET['del_prod'])) { 
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([(int)$_GET['del_prod']]); 
}

// 3. Gerenciar Equipe
if (isset($_POST['add_user'])) {
    $n = $_POST['nome']; 
    $p = $_POST['pin'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO usuarios (nome, pin, nivel) VALUES (?, ?, 'garcom')");
    $stmt->execute([$n, $p]);
}
if (isset($_GET['del_user'])) { 
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND nivel != 'admin'");
    $stmt->execute([(int)$_GET['del_user']]); 
}

// 4. Fluxo de Caixa (Hoje)
$hoje = date('Y-m-d');
$stmt = $pdo->prepare("SELECT SUM(total) FROM pedidos WHERE status = 'Finalizado' AND DATE(data) = ?");
$stmt->execute([$hoje]);
$totalVendas = $stmt->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="pt-br" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>ADM - Gestão Completa</title>
</head>
<body class="bg-[#0f172a] text-slate-200 p-4 min-h-screen select-none">
    <div class="max-w-7xl mx-auto">
        
        <header class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-3xl font-black text-white italic">ADMIN<span class="text-blue-500">PRO</span></h1>
            <a href="index.php" class="bg-slate-800 border border-slate-700 px-6 py-2 rounded-xl font-bold text-xs uppercase hover:bg-slate-700 transition shadow-lg">Voltar ao Salão</a>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-blue-600 p-5 rounded-3xl shadow-lg shadow-blue-900/30 flex flex-col justify-center">
                <span class="text-[10px] font-bold uppercase opacity-80 tracking-widest">Caixa Hoje</span>
                <h2 class="text-3xl font-black mt-1">R$ <?= number_format($totalVendas, 2, ',', '.') ?></h2>
            </div>
            
            <div class="bg-slate-800 p-5 rounded-3xl border border-slate-700 lg:col-span-3 shadow-xl">
                <h3 class="text-[10px] font-bold text-slate-400 mb-3 uppercase tracking-widest text-center md:text-left">🏆 Melhores Vendedores</h3>
                <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                    <?php 
                    $ranking = $pdo->query("SELECT garcom_nome, SUM(subtotal) as total FROM itens_pedido WHERE garcom_nome IS NOT NULL GROUP BY garcom_nome ORDER BY total DESC LIMIT 4");
                    while($r = $ranking->fetch()): ?>
                        <div class="bg-slate-900 px-5 py-3 rounded-2xl border border-slate-700 flex flex-col min-w-[130px] shadow-inner">
                            <span class="text-[9px] text-blue-400 font-bold uppercase tracking-wider truncate max-w-[100px]"><?= $r['garcom_nome'] ?></span>
                            <span class="text-sm font-black text-white mt-1">R$ <?= number_format($r['total'], 2, ',', '.') ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <section class="bg-slate-800 p-5 rounded-3xl border border-slate-700 shadow-xl">
                <h2 class="text-sm font-bold mb-4 text-orange-400 uppercase tracking-widest flex items-center gap-2">🪑 Mesas</h2>
                <form method="POST" class="flex gap-2 mb-4">
                    <input type="number" name="numero_mesa" placeholder="Nº" class="w-16 bg-slate-900 p-3 rounded-xl border border-slate-700 text-sm font-bold outline-none focus:border-orange-500" required>
                    <button name="add_mesa" class="flex-1 bg-orange-600 hover:bg-orange-500 rounded-xl font-black text-[10px] uppercase transition">Adicionar</button>
                </form>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    <?php 
                    $ms = $pdo->query("SELECT * FROM mesas ORDER BY CAST(REPLACE(numero, 'Mesa ', '') AS UNSIGNED) ASC"); 
                    while($m = $ms->fetch()): ?>
                        <div class="flex justify-between items-center bg-slate-900 p-3 rounded-xl border border-slate-800 group">
                            <span class="font-bold text-sm text-slate-300"><?= $m['numero'] ?></span>
                            <a href="?del_mesa=<?= $m['id'] ?>" onclick="return confirm('Excluir esta mesa?')" class="text-red-500 text-[10px] opacity-100 lg:opacity-0 group-hover:opacity-100 transition font-bold uppercase">Remover</a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <section class="bg-slate-800 p-5 rounded-3xl border border-slate-700 shadow-xl">
                <h2 class="text-sm font-bold mb-4 text-emerald-400 uppercase tracking-widest">🍕 Menu</h2>
                <form method="POST" class="space-y-2 mb-4">
                    <input type="text" name="nome" placeholder="Nome do Produto" class="w-full bg-slate-900 p-3 rounded-xl border border-slate-700 text-sm outline-none focus:border-emerald-500" required>
                    <div class="flex gap-2">
                        <input type="number" step="0.01" name="preco" placeholder="Preço (R$)" class="w-full bg-slate-900 p-3 rounded-xl border border-slate-700 text-sm font-mono outline-none focus:border-emerald-500" required>
                        <button name="add_prod" class="bg-emerald-600 hover:bg-emerald-500 px-4 rounded-xl font-black text-sm transition">+</button>
                    </div>
                </form>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    <?php 
                    $ps = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC"); 
                    while($p = $ps->fetch()): ?>
                        <div class="flex justify-between items-center bg-slate-900 p-3 rounded-xl text-xs border border-slate-800">
                            <span class="truncate pr-2 text-slate-300"><?= $p['nome'] ?></span>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-emerald-400">R$<?= number_format($p['preco'], 2, ',', '') ?></span>
                                <a href="?del_prod=<?= $p['id'] ?>" onclick="return confirm('Excluir produto?')" class="text-red-500 font-bold text-lg hover:scale-110 transition">&times;</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <section class="bg-slate-800 p-5 rounded-3xl border border-slate-700 shadow-xl">
                <h2 class="text-sm font-bold mb-4 text-purple-400 uppercase tracking-widest">👤 Equipe</h2>
                <form method="POST" class="space-y-2 mb-4">
                    <input type="text" name="nome" placeholder="Nome do Funcionário" class="w-full bg-slate-900 p-3 rounded-xl border border-slate-700 text-sm outline-none focus:border-purple-500" required>
                    <input type="password" name="pin" maxlength="4" placeholder="PIN Numérico (4)" class="w-full bg-slate-900 p-3 rounded-xl border border-slate-700 text-sm font-mono outline-none focus:border-purple-500" required>
                    <button name="add_user" class="w-full bg-purple-600 hover:bg-purple-500 p-3 rounded-xl font-black text-[10px] uppercase transition">Cadastrar</button>
                </form>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    <?php 
                    $us = $pdo->query("SELECT * FROM usuarios WHERE nivel != 'admin'"); 
                    while($u = $us->fetch()): ?>
                        <div class="bg-slate-900 p-3 rounded-xl flex justify-between items-center text-xs border border-slate-800">
                            <span class="font-bold text-slate-300"><?= $u['nome'] ?></span>
                            <a href="?del_user=<?= $u['id'] ?>" onclick="return confirm('Excluir garçom?')" class="text-red-500 uppercase font-bold text-[9px] hover:underline">Sair</a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <section class="bg-slate-800 p-5 rounded-3xl border border-slate-700 shadow-xl">
                <h2 class="text-sm font-bold mb-4 text-slate-300 uppercase tracking-widest">📜 Fluxo Recente</h2>
                <div class="space-y-3 max-h-[380px] overflow-y-auto text-xs pr-1">
                    <?php 
                    $fluxo = $pdo->query("SELECT p.*, m.numero FROM pedidos p JOIN mesas m ON p.mesa_id = m.id WHERE p.status = 'Finalizado' ORDER BY p.data DESC LIMIT 20");
                    while($h = $fluxo->fetch()): ?>
                        <div class="bg-slate-900/80 p-3 rounded-xl border border-slate-700/50">
                            <div class="flex justify-between mb-2 uppercase font-black text-[9px]">
                                <span class="text-blue-400 tracking-wider"><?= $h['numero'] ?></span>
                                <span class="text-slate-500"><?= date('H:i', strtotime($h['data'])) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Total Pago</span>
                                <span class="text-emerald-400 font-black text-sm">R$ <?= number_format($h['total'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

        </div>
    </div>
</body>
</html>
