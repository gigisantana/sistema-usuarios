<?php

// 1. Configurações de Cabeçalho (CORS) - IGUAIS ao register.php
header("Access-Control-Allow-Origin: http://localhost:3000"); // Ajuste para o domínio do seu frontend
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Configurações e Conexão ao Banco de Dados (IGUAIS ao register.php)
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

// 3. Lógica de Login (Método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null; // A chave da senha deve ser 'password' (do React)

    if (empty($email) || empty($password)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "E-mail e senha são obrigatórios."]);
        exit();
    }

    // 4. Buscar o Usuário no Banco de Dados
    try {
        $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 5. Verificar a Senha (usando password_verify)
        // Se o usuário existe E a senha fornecida corresponde ao hash armazenado
        if ($user && password_verify($password, $user['password'])) {
            // Sucesso no login!
            http_response_code(200); // OK

            // Gerar um token simples (PARA FINS DE APRENDIZADO)
            // Em produção, use JWT (JSON Web Tokens) com uma biblioteca apropriada.
            $token = bin2hex(random_bytes(16)); // Token aleatório simples

            echo json_encode([
                "message" => "Login bem-sucedido!",
                "token" => $token,
                "user" => ["id" => $user['id'], "email" => $user['email']] // Retorna dados básicos do usuário
            ]);

        } else {
            // Falha na autenticação (usuário não encontrado ou senha incorreta)
            http_response_code(401); // Unauthorized
            echo json_encode(["message" => "E-mail ou senha incorretos."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erro ao buscar usuário: " . $e->getMessage()]);
    }

} else {
    // 6. Lidar com Métodos Não Permitidos
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método não permitido."]);
}

?>