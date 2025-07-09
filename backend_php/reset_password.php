<?php

// 1. Configurações de Cabeçalho (CORS)
header("Access-Control-Allow-Origin: http://localhost:3000"); // Ajuste para o domínio do seu frontend em produção
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Lida com requisições OPTIONS (preflight requests)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Configurações do Banco de Dados
$dbHost = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'my_app_db';
$dbUser = getenv('DB_USER') ?: 'user';
$dbPassword = getenv('DB_PASSWORD') ?: 'password';

// 3. Conexão com o Banco de Dados
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Erro de conexão com o banco de dados."]);
    exit();
}

// 4. Lógica de Redefinição de Senha (Método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $token = $data['token'] ?? null;
    $newPassword = $data['password'] ?? null; // Nome da chave 'password' para consistência

    // Validação básica
    if (empty($token) || empty($newPassword)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Token e nova senha são obrigatórios."]);
        exit();
    }

    // 5. Validar o Token de Redefinição
    try {
        $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = :token");
        $stmt->execute(['token' => $token]);
        $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resetRequest) {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "Token inválido ou expirado."]);
            exit();
        }

        // Verifica se o token expirou
        $expiresAt = new DateTime($resetRequest['expires_at']);
        $now = new DateTime();
        if ($now > $expiresAt) {
            // Token expirado, também o remove do DB
            $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
            $deleteStmt->execute(['token' => $token]);

            http_response_code(400); // Bad Request
            echo json_encode(["message" => "Token expirado. Por favor, solicite uma nova redefinição."]);
            exit();
        }

        $userId = $resetRequest['user_id'];

        // 6. Hash da Nova Senha
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // 7. Atualizar a Senha do Usuário
        $pdo->beginTransaction(); // Inicia uma transação para garantir atomicidade

        $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $updateStmt->execute(['password' => $hashedPassword, 'id' => $userId]);

        // 8. Invalidar/Deletar o Token Usado
        $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
        $deleteStmt->execute(['token' => $token]);

        $pdo->commit(); // Confirma a transação

        http_response_code(200); // OK
        echo json_encode(["message" => "Senha redefinida com sucesso!"]);

    } catch (PDOException $e) {
        $pdo->rollBack(); // Desfaz a transação em caso de erro
        error_log("Erro no processo de redefinição de senha: " . $e->getMessage());
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Erro interno ao redefinir a senha."]);
    }

} else {
    // 9. Lidar com Métodos Não Permitidos
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método não permitido."]);
}

?>