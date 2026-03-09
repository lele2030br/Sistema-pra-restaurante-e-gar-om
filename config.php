<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// --- CONFIGURAÇÃO DO BANCO (Mude conforme seus dados) ---
$host = 'localhost';
$dbname = 'nome';
$user = 'nome';
$pass = 'nome';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro Crítico de Conexão: " . $e->getMessage());
}

// --- CRIAÇÃO DAS TABELAS (Executado apenas se não existirem) ---
$pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100), pin VARCHAR(4) UNIQUE, nivel ENUM('garcom', 'admin') DEFAULT 'garcom') ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS mesas (id INT AUTO_INCREMENT PRIMARY KEY, numero VARCHAR(50) UNIQUE, status ENUM('Livre', 'Ocupada') DEFAULT 'Livre') ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS produtos (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100), preco DECIMAL(10,2), categoria VARCHAR(50)) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS pedidos (id INT AUTO_INCREMENT PRIMARY KEY, mesa_id INT, total DECIMAL(10,2) DEFAULT 0, status ENUM('Aberto', 'Finalizado') DEFAULT 'Aberto', data TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS itens_pedido (id INT AUTO_INCREMENT PRIMARY KEY, pedido_id INT, produto_id INT, qtd INT, subtotal DECIMAL(10,2), garcom_nome VARCHAR(100)) ENGINE=InnoDB");

// Garantir Admin padrão
$stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE pin = '9999'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO usuarios (nome, pin, nivel) VALUES ('Gerente', '9999', 'admin')");
}

function verificarAcesso($nivelRequerido = 'garcom') {
    if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit; }
    if ($nivelRequerido == 'admin' && $_SESSION['nivel'] != 'admin') {
        echo "<script>alert('Acesso negado!'); window.location.href='index.php';</script>";
        exit;
    }
}
?>
