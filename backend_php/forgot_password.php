<?php
// backend_php/forgot_password.php

// Inclui o autoloader do Composer para carregar PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configurações de Cabeçalho (CORS) - Similar aos outros arquivos PHP
header("Access-Control-Allow-Origin: http://localhost:3000"); // Ajuste para o domínio do seu frontend
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurações do Banco de Dados e Conexão (igual aos outros arquivos)
$dbHost = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'my_app_db';
$dbUser = getenv('DB_USER') ?: 'user';
$dbPassword = getenv('DB_PASSWORD') ?: 'password';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Erro de conexão com o banco de dados: " . $e->getMessage()]);
    exit();
}

// --- Lógica do Esqueceu a Senha ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? null;

    if (empty($email)) {
        http_response_code(400);
        echo json_encode(["message" => "E-mail é obrigatório para redefinição de senha."]);
        exit();
    }

    // 1. Verificar se o e-mail existe no SEU banco de dados
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Em produção, para evitar enumerar e-mails, você pode retornar sempre
        // uma mensagem de sucesso, mesmo que o e-mail não exista.
        http_response_code(200); // OK
        echo json_encode(["message" => "Se o e-mail estiver cadastrado, um link de redefinição foi enviado."]);
        exit();
    }

    $userId = $user['id'];
    $token = bin2hex(random_bytes(32)); // Gera um token seguro para redefinição (64 caracteres hex)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expira em 1 hora

    try {
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        $stmt->execute(['user_id' => $userId, 'token' => $token, 'expires_at' => $expiresAt]);
    } catch (PDOException $e) {
        error_log("Erro ao salvar token de redefinição: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["message" => "Erro interno ao processar a solicitação."]);
        exit();
    }

    // 3. Enviar E-mail com o Link de Redefinição (Usando Mailtrap)
    $mail = new PHPMailer(true); // Passe true para habilitar exceções

    try {
        // Configurações SMTP para Mailtrap (obtidas da sua conta Mailtrap)
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io'; // Ou o host que o Mailtrap te fornecer
        $mail->SMTPAuth = true;
        $mail->Port = 2525; // Ou a porta que o Mailtrap te fornecer (geralmente 25, 465, 587, 2525)
        $mail->Username = 'aae0180afd76d8'; // <--- SEU USERNAME DO MAILTRAP
        $mail->Password = 'a46d39a5aff16b';   // <--- SUA SENHA DO MAILTRAP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use ENCRYPTION_SMTPS para porta 465

        // Remetente e Destinatário
        $mail->setFrom('no-reply@sistemausuarios.com', 'Sistema de Usuários');
        $mail->addAddress($email);

        // Conteúdo do E-mail
        $mail->isHTML(true);
        $mail->Subject = 'Redefinir Senha do Seu Sistema';
        $resetLink = "http://localhost:3000/reset-password?token=" . $token; // <--- URL da sua página de redefinição no frontend
        $mail->Body = "Olá,<br><br>Para redefinir sua senha, clique no link abaixo:<br><br><a href='{$resetLink}'>{$resetLink}</a><br><br>Este link expira em 1 hora.<br><br>Atenciosamente,<br>Sua Equipe";
        $mail->AltBody = "Para redefinir sua senha, copie e cole o link no seu navegador: {$resetLink}";

        $mail->send();
        http_response_code(200);
        echo json_encode(["message" => "Se o e-mail estiver cadastrado, um link de redefinição foi enviado."]);

    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de redefinição: {$mail->ErrorInfo}");
        http_response_code(500);
        echo json_encode(["message" => "Erro ao enviar e-mail de redefinição. Tente novamente mais tarde."]);
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método não permitido."]);
}

?>