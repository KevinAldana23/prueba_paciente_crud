<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__.'/../config/db.php';
    
    $departamentoId = $_GET['departamento_id'] ?? null;
    
    if (!$departamentoId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de departamento requerido'
        ]);
        exit();
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM municipios WHERE departamento_id = :departamento_id ORDER BY nombre");
        $stmt->execute([':departamento_id' => $departamentoId]);
        $municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $municipios
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener municipios: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
}
?> 