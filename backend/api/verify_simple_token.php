<?php
/**
 * SIHOS - Sistema de Gestión de Pacientes
 * API para verificación de tokens de autenticación
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
    enviarRespuestaError('Método no permitido', 405);
    exit();
}

// ========================================
// PROCESAMIENTO DE LA VERIFICACIÓN
// ========================================
procesarVerificacionToken();

// ========================================
// FUNCIONES PRINCIPALES
// ========================================

/**
 * Procesa la verificación del token de autenticación
 * 
 * @return void
 */
function procesarVerificacionToken() {
    // Obtener y validar datos de entrada
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        enviarRespuestaError('Datos JSON inválidos', 400);
        return;
    }
    
    // Validar que el token esté presente
    if (empty($input['token'])) {
        enviarRespuestaError('Token es requerido', 400);
        return;
    }
    
    $token = trim($input['token']);
    
    try {
        // Verificar el token
        $datosToken = verificarToken($token);
        
        if ($datosToken) {
            enviarRespuestaExito([
                'data' => $datosToken
            ]);
        } else {
            enviarRespuestaError('Token inválido o expirado', 401);
        }
        
    } catch (Exception $e) {
        enviarRespuestaError('Error verificando token: ' . $e->getMessage(), 500);
    }
}

// ========================================
// FUNCIONES AUXILIARES
// ========================================

/**
 * Verifica la validez del token de autenticación
 * 
 * @param string $token Token a verificar
 * @return array|false Datos del token si es válido, false en caso contrario
 */
function verificarToken($token) {
    // Decodificar el token simple (base64)
    $decoded = base64_decode($token, true);
    
    if ($decoded === false) {
        return false;
    }
    
    // Separar las partes del token
    $parts = explode('|', $decoded);
    
    // Verificar que tenga al menos 3 partes (id, username, timestamp)
    if (count($parts) < 3) {
        return false;
    }
    
    $userId = $parts[0];
    $username = $parts[1];
    $timestamp = (int)$parts[2];
    
    // Validar que el ID de usuario sea numérico
    if (!is_numeric($userId) || $userId <= 0) {
        return false;
    }
    
    // Validar que el username no esté vacío
    if (empty($username)) {
        return false;
    }
    
    // Verificar que el token no haya expirado (24 horas)
    $tiempoActual = time();
    $tiempoExpiracion = 24 * 60 * 60; // 24 horas en segundos
    
    if (($tiempoActual - $timestamp) > $tiempoExpiracion) {
        return false;
    }
    
    // Token válido, retornar datos
    return [
        'userId' => (int)$userId,
        'username' => $username,
        'timestamp' => $timestamp,
        'expira_en' => $timestamp + $tiempoExpiracion - $tiempoActual
    ];
}

// ========================================
// FUNCIONES DE RESPUESTA
// ========================================

/**
 * Envía una respuesta exitosa en formato JSON
 * 
 * @param array $data Datos a incluir en la respuesta
 * @param int $statusCode Código de estado HTTP (por defecto 200)
 * @return void
 */
function enviarRespuestaExito($data, $statusCode = 200) {
    http_response_code($statusCode);
    $respuesta = array_merge([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ], $data);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
}

/**
 * Envía una respuesta de error en formato JSON
 * 
 * @param string $mensaje Mensaje de error
 * @param int $statusCode Código de estado HTTP (por defecto 400)
 * @return void
 */
function enviarRespuestaError($mensaje, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => $mensaje,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?> 