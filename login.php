<?php 
include 'config.php';

// Processa o login quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pin = $_POST['pin'];
    
    // Consulta segura no MySQL usando Prepared Statements
    $stmt = $pdo->prepare("SELECT nome, nivel FROM usuarios WHERE pin = ?");
    $stmt->execute([$pin]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['usuario'] = $user['nome'];
        $_SESSION['nivel'] = $user['nivel'];
        header('Location: index.php');
        exit;
    } else { 
        $erro = "PIN incorreto ou não cadastrado!"; 
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login - RestoPRO</title>
</head>
<body class="bg-[#0f172a] text-slate-200 flex items-center justify-center min-h-screen p-4 select-none">
    <form method="POST" class="bg-slate-800 p-8 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-sm">
        
        <div class="text-center mb-8">
            <h2 class="text-3xl font-black text-white tracking-tighter italic">RESTO<span class="text-blue-500">PRO</span></h2>
            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-[0.3em] mt-1">Acesso de Funcionários</p>
        </div>
        
        <input type="password" name="pin" id="pin_input" readonly 
               class="w-full bg-slate-900 text-center text-4xl p-4 mb-6 rounded-2xl border border-slate-700 outline-none text-blue-400 tracking-[0.5em] font-black shadow-inner" 
               placeholder="****">
        
        <div class="grid grid-cols-3 gap-3 mb-6">
            <?php for($i=1; $i<=9; $i++): ?>
                <button type="button" onclick="addNum('<?= $i ?>')" class="bg-slate-700 hover:bg-slate-600 p-4 rounded-2xl text-2xl font-black transition active:scale-95 shadow-md">
                    <?= $i ?>
                </button>
            <?php endfor; ?>
            
            <button type="button" onclick="clearPin()" class="bg-red-500/10 text-red-500 border border-red-500/20 hover:bg-red-500/20 p-4 rounded-2xl text-xl font-black transition active:scale-95 shadow-md">
                C
            </button>
            
            <button type="button" onclick="addNum('0')" class="bg-slate-700 hover:bg-slate-600 p-4 rounded-2xl text-2xl font-black transition active:scale-95 shadow-md">
                0
            </button>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white p-4 rounded-2xl text-xl font-black uppercase transition active:scale-95 shadow-lg shadow-blue-900/50">
                OK
            </button>
        </div>

        <?php if(isset($erro)): ?>
            <div class="bg-red-500/10 border border-red-500/20 p-3 rounded-xl text-center">
                <p class="text-xs text-red-500 font-bold uppercase tracking-widest"><?= $erro ?></p>
            </div>
        <?php endif; ?>
    </form>

    <script>
        const pinInput = document.getElementById('pin_input');
        
        function addNum(v) { 
            // Limita a 4 dígitos
            if(pinInput.value.length < 4) {
                pinInput.value += v; 
            }
        }
        
        function clearPin() {
            pinInput.value = '';
        }
    </script>
</body>
</html>
