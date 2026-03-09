<?php 
include 'config.php'; 
verificarAcesso('garcom'); 

// Consulta as mesas na base de dados MySQL usando PDO
$stmt = $pdo->query("SELECT * FROM mesas ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="pt-pt" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>RestoPRO - Salão</title>
</head>
<body class="bg-[#0f172a] text-slate-200 min-h-screen select-none">
    
    <nav class="bg-[#1e293b] p-4 flex justify-between items-center border-b border-slate-700 sticky top-0 z-50 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-black shadow-lg shadow-blue-900/50">R</div>
            <h1 class="font-bold tracking-tight hidden sm:block text-white">RESTO<span class="text-blue-500">PRO</span></h1>
        </div>
        <div class="flex gap-3">
            <?php if(($_SESSION['nivel'] ?? '') == 'admin'): ?>
                <a href="admin.php" class="bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest transition shadow-md">
                    Painel ADM
                </a>
            <?php endif; ?>
            
            <a href="logout.php" class="bg-red-500/10 text-red-500 border border-red-500/20 hover:bg-red-500 hover:text-white px-4 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest transition shadow-md">
                Sair
            </a>
        </div>
    </nav>

    <main class="p-4 grid grid-cols-2 xs:grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-8 gap-4 mt-2">
        <?php 
        // Loop para desenhar cada mesa
        while($m = $stmt->fetch()): 
            $isOcupada = ($m['status'] != 'Livre');
            
            // Define as cores consoante o estado da mesa
            $corCard = $isOcupada ? 'border-orange-500/50 bg-orange-500/5' : 'border-emerald-500/50 bg-emerald-500/5';
            $corTexto = $isOcupada ? 'text-orange-400' : 'text-emerald-400';
            $corBadge = $isOcupada ? 'bg-orange-500' : 'bg-emerald-500';
        ?>
            <a href="pedido.php?mesa_id=<?= $m['id'] ?>" class="border <?= $corCard ?> p-5 rounded-3xl flex flex-col items-center justify-center gap-2 transition active:scale-95 touch-manipulation shadow-xl">
                <span class="text-3xl font-black <?= $corTexto ?> drop-shadow-md">
                    <?= str_replace('Mesa ', '', $m['numero']) ?>
                </span>
                <span class="<?= $corBadge ?> text-[9px] text-white px-3 py-1 rounded-full font-bold uppercase tracking-widest shadow-md">
                    <?= $m['status'] ?>
                </span>
            </a>
        <?php endwhile; ?>
    </main>

</body>
</html>
