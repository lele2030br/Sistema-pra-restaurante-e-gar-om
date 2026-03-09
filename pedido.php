<?php 
include 'config.php'; 
verificarAcesso('garcom'); 

// Obter ID da mesa de forma segura
$mesa_id = isset($_GET['mesa_id']) ? (int)$_GET['mesa_id'] : null;
if (!$mesa_id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM mesas WHERE id = ?");
$stmt->execute([$mesa_id]);
$mesa = $stmt->fetch();

if (!$mesa) { header('Location: index.php'); exit; }

$nome_logado = $_SESSION['usuario']; 

// --- LÓGICA DE FECHAMENTO (Apenas ADM) ---
if (isset($_GET['fechar']) && $_GET['fechar'] == '1') {
    if (($_SESSION['nivel'] ?? '') == 'admin') {
        $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE mesa_id = ? AND status = 'Aberto'");
        $stmt->execute([$mesa_id]);
        $pedido = $stmt->fetch();
        
        if ($pedido) {
            $pid = $pedido['id'];
            
            // Calcular o total exato
            $stmt_sum = $pdo->prepare("SELECT SUM(subtotal) FROM itens_pedido WHERE pedido_id = ?");
            $stmt_sum->execute([$pid]);
            $total_calc = $stmt_sum->fetchColumn() ?: 0;
            
            // Atualizar pedido e mesa
            $pdo->prepare("UPDATE pedidos SET status = 'Finalizado', total = ?, data = CURRENT_TIMESTAMP WHERE id = ?")->execute([$total_calc, $pid]);
            $pdo->prepare("UPDATE mesas SET status = 'Livre' WHERE id = ?")->execute([$mesa_id]);
        }
        header("Location: index.php");
        exit;
    }
}

// --- LÓGICA DE REMOVER ITEM (Apenas ADM) ---
if (isset($_GET['del_item'])) {
    if (($_SESSION['nivel'] ?? '') == 'admin') {
        $pdo->prepare("DELETE FROM itens_pedido WHERE id = ?")->execute([(int)$_GET['del_item']]);
        header("Location: pedido.php?mesa_id=$mesa_id");
        exit;
    }
}

// --- LÓGICA DE ADIÇÃO DE ITENS ---
if (isset($_POST['add_item'])) {
    $prod_id = (int)$_POST['prod_id'];
    $qtd = (int)$_POST['qtd'];
    
    // Obter preço do produto
    $stmt_p = $pdo->prepare("SELECT preco FROM produtos WHERE id = ?");
    $stmt_p->execute([$prod_id]);
    $preco = $stmt_p->fetchColumn();
    $sub = $preco * $qtd;

    // Verificar se já existe um pedido aberto
    $stmt_ped = $pdo->prepare("SELECT id FROM pedidos WHERE mesa_id = ? AND status = 'Aberto'");
    $stmt_ped->execute([$mesa_id]);
    $pid = $stmt_ped->fetchColumn();

    // Se não existir, criar um novo pedido
    if (!$pid) {
        $pdo->prepare("INSERT INTO pedidos (mesa_id, status) VALUES (?, 'Aberto')")->execute([$mesa_id]);
        $pid = $pdo->lastInsertId();
        $pdo->prepare("UPDATE mesas SET status = 'Ocupada' WHERE id = ?")->execute([$mesa_id]);
    }
    
    // Inserir item no pedido
    $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, qtd, subtotal, garcom_nome) VALUES (?, ?, ?, ?, ?)")->execute([$pid, $prod_id, $qtd, $sub, $nome_logado]);
    header("Location: pedido.php?mesa_id=$mesa_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-pt" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
    <title><?= $mesa['numero'] ?></title>
</head>
<body class="bg-[#0f172a] text-slate-200 min-h-screen p-4 select-none">
    <div class="max-w-lg mx-auto">
        <header class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-black text-white"><?= $mesa['numero'] ?></h2>
                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Painel de Atendimento</p>
            </div>
            <a href="index.php" class="bg-slate-800 border border-slate-700 px-4 py-2 rounded-xl text-xs font-bold uppercase hover:bg-slate-700 transition">Voltar</a>
        </header>

        <form method="POST" class="bg-slate-800 p-5 rounded-3xl mb-6 shadow-xl border border-slate-700">
            <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-widest">Novo Item</label>
            <select name="prod_id" class="w-full bg-slate-900 p-4 rounded-xl mb-4 outline-none border border-slate-700 text-sm focus:border-blue-500 transition">
                <?php 
                $ps = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC"); 
                while($p = $ps->fetch()): ?>
                    <option value='<?= $p['id'] ?>'><?= $p['nome'] ?> - R$ <?= number_format($p['preco'], 2, ',', '.') ?></option>
                <?php endwhile; ?>
            </select>
            <div class="flex gap-3">
                <input type="number" name="qtd" value="1" min="1" class="w-20 bg-slate-900 p-4 rounded-xl text-center font-bold border border-slate-700 outline-none focus:border-blue-500">
                <button name="add_item" class="flex-1 bg-blue-600 hover:bg-blue-500 rounded-xl font-black uppercase text-xs transition shadow-lg shadow-blue-900/20">Lançar Item</button>
            </div>
        </form>

        <div class="bg-slate-800 p-5 rounded-3xl border border-slate-700 shadow-xl">
            <h3 class="font-bold text-slate-500 text-[10px] uppercase mb-4 text-center tracking-[0.2em]">Consumo da Mesa</h3>
            
            <?php 
            // Verifica se há pedido aberto
            $stmt_pid = $pdo->prepare("SELECT id FROM pedidos WHERE mesa_id = ? AND status = 'Aberto'");
            $stmt_pid->execute([$mesa_id]);
            $pid = $stmt_pid->fetchColumn();
            
            if($pid):
                $total = 0;
                $stmt_its = $pdo->prepare("SELECT i.*, p.nome FROM itens_pedido i JOIN produtos p ON i.produto_id = p.id WHERE i.pedido_id = ?");
                $stmt_its->execute([$pid]);
                
                while($i = $stmt_its->fetch()): 
                    $total += $i['subtotal']; ?>
                    <div class="flex justify-between items-center py-3 border-b border-slate-700/40">
                        <div class="text-sm">
                            <span class="font-bold text-blue-400"><?= $i['qtd'] ?>x</span> 
                            <span class="text-slate-200"><?= $i['nome'] ?></span>
                            <p class="text-[9px] text-slate-500 font-bold uppercase mt-1 italic">👤 <?= $i['garcom_nome'] ?></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-mono font-bold">R$ <?= number_format($i['subtotal'], 2, ',', '.') ?></span>
                            <?php if(($_SESSION['nivel'] ?? '') == 'admin'): ?>
                                <a href="?mesa_id=<?= $mesa_id ?>&del_item=<?= $i['id'] ?>" onclick="return confirm('Remover este item?')" class="text-red-500 text-lg hover:scale-110 transition">&times;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <div class="mt-6">
                    <div class="flex justify-between items-center mb-4 px-2">
                        <span class="text-slate-400 text-xs font-bold uppercase">Total Acumulado</span>
                        <span class="text-2xl font-black text-emerald-400">R$ <?= number_format($total, 2, ',', '.') ?></span>
                    </div>

                    <?php if(($_SESSION['nivel'] ?? '') == 'admin'): ?>
                        <a href="pedido.php?mesa_id=<?= $mesa_id ?>&fechar=1" 
                           onclick="return confirm('Confirmar pagamento e liberar mesa?')" 
                           class="block w-full bg-emerald-600 hover:bg-emerald-500 text-center py-4 rounded-2xl font-black uppercase text-sm shadow-lg transition">
                           Encerrar Conta
                        </a>
                    <?php else: ?>
                        <div class="text-center p-3 bg-slate-900 rounded-xl border border-slate-700">
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest italic">Aguardando Pagamento no Caixa</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <p class='text-slate-600 text-center py-6 text-xs italic uppercase tracking-widest'>Mesa Disponível</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
