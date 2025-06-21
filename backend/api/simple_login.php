<?php
/**
 * SIHOS - Sistema de Gestión de Pacientes
 * API para autenticación de usuarios
 * 
 * @author Desarrollador
 * @version 1.0
 * @package SIHOS\API
 */

// ========================================
// CONFIGURACIÓN DE CABECERAS HTTP
// ========================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========================================
// VALIDACIÓN DEL MÉTODO HTTP
// ========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit();
}

// ========================================
// PROCESAMIENTO DE LA AUTENTICACIÓN
// ========================================
try {
    // Incluir configuración de base de datos
    require_once __DIR__ . '/../config/db.php';
    
    // Obtener y validar datos de entrada
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Datos JSON inválidos'
        ]);
        exit();
    }
    
    // Validar campos requeridos
    if (empty($input['username']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Username y password son requeridos'
        ]);
        exit();
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    
    // Verificar credenciales del usuario
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        // Generar token de autenticación
        $datosToken = $usuario['id'] . '|' . $usuario['username'] . '|' . time();
        $token = base64_encode($datosToken);
        
        echo json_encode([
            'success' => true,
            'message' => 'Autenticación exitosa',
            'token' => $token,
            'user' => [
                'id' => $usuario['id'],
                'username' => $usuario['username'],
                'role' => $usuario['role'] ?? 'admin'
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Credenciales inválidas'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en la autenticación: ' . $e->getMessage()
    ]);
}
?> 