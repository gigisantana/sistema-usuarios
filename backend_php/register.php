<?php

// 1. Configurações de Cabeçalho (CORS)
// Permite que seu frontend React (em http://localhost:3000) faça requisições
header("Access-Control-Allow-Origin: http://localhost:3000"); // Permite apenas seu frontend. Para qualquer origem durante o desenvolvimento, use "*", mas ajuste para segurança em produção!
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Permite que o frontend envie esses cabeçalhos
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Permite métodos POST e OPTIONS
header("Content-Type: application/json"); // Define que a resposta será JSON

// Lida com requisições OPTIONS (preflight requests) que os navegadores fazem para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Configurações do Banco de Dados
// As variáveis de ambiente vêm do seu docker-compose.yml
$dbHost = getenv('DB_HOST') ?: 'localhost'; // 'db' é o nome do serviço MySQL no Docker Compose
$dbName = getenv('DB_NAME') ?: 'my_app_db';
$dbUser = getenv('DB_USER') ?: 'user';
$dbPassword = getenv('DB_PASSWORD') ?: 'password';

// 3. Conexão com o Banco de Dados
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Define o modo de erro para lançar exceções
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Erro de conexão com o banco de dados: " . $e->getMessage()]);
    exit();
}

// 4. Lógica de Registro (Método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodifica o JSON enviado pelo frontend
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null; // Aqui recebemos a 'senha'

    // Validação básica dos dados
    if (empty($email) || empty($password)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "E-mail e senha são obrigatórios."]);
        exit();
    }

    // 5. Verificar se o E-mail já Existe
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Já existe uma conta com esse E-mail."]);
            exit();
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erro ao verificar e-mail existente: " . $e->getMessage()]);
        exit();
    }

    // 6. Hash da password (CRUCIAL para Segurança!)
    // JAMAIS armazene senhas em texto puro. Use password_hash().
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    // PASSWORD_BCRYPT é um algoritmo seguro e recomendado.

    // 7. Inserir Novo Usuário no Banco de Dados
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
        if ($stmt->execute(['email' => $email, 'password' => $hashedPassword])) {
            http_response_code(201); // 201 Created (sucesso na criação de um recurso)
            echo json_encode(["message" => "Usuário registrado com sucesso!"]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Erro ao registrar usuário."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Erro ao inserir usuário no banco de dados: " . $e->getMessage()]);
    }

} else {
    // 8. Lidar com Métodos Não Permitidos
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método não permitido."]);
}

?>