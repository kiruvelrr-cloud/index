<?php
// ================================================
//  UniSkill Registration API (Single File)
//  Save this as: api.php
// ================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';           // Change if needed
$db   = 'uniskill_db';         // ← Change to your database name
$user = 'root';                // ← Your MySQL username
$pass = '';                    // ← Your MySQL password (empty for XAMPP)

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// ==================== CREATE TABLE (runs automatically) ====================
$pdo->exec("CREATE TABLE IF NOT EXISTS uniskill_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    department VARCHAR(150) NOT NULL,
    instagram VARCHAR(100) UNIQUE NOT NULL,
    learn_skills JSON NOT NULL,
    teach_skills JSON NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// ==================== HANDLE REGISTRATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = json_decode(file_get_contents('php://input'), true);

    // Basic validation
    if (empty($input['full_name']) || empty($input['department']) || empty($input['instagram'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Full name, department and Instagram are required!'
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO uniskill_registrations 
            (full_name, department, instagram, learn_skills, teach_skills) 
            VALUES (?, ?, ?, ?, ?)");

        $stmt->execute([
            trim($input['full_name']),
            trim($input['department']),
            trim($input['instagram']),
            json_encode($input['learn_skills'] ?? []),
            json_encode($input['teach_skills'] ?? [])
        ]);

        echo json_encode([
            'status'  => 'success',
            'message' => 'Registration successful! 🎉',
            'id'      => $pdo->lastInsertId(),
            'instagram' => $input['instagram']
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Registration failed. Instagram username may already exist.'
        ]);
    }

    exit;
}

// ==================== IF SOMEONE OPENS THE FILE IN BROWSER ====================
echo json_encode([
    'status'  => 'ready',
    'message' => 'UniSkill PHP API is running ✅',
    'table'   => 'uniskill_registrations created'
]);