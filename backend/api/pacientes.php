<?php
/**
 * SIHOS - Sistema de Gestión de Pacientes
 * API para gestión de pacientes (CRUD)
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
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========================================
// INCLUSIÓN DE DEPENDENCIAS
// ========================================
require_once __DIR__ . '/../config/db.php';

// ========================================
// VARIABLES GLOBALES
// ========================================
$requestMethod = $_SERVER['REQUEST_METHOD'];

// ========================================
// MANEJO DE PETICIONES POR MÉTODO HTTP
// ========================================
switch ($requestMethod) {
    case 'GET':
        obtenerPacientes();
        break;
        
    case 'POST':
        crearPaciente();
        break;
        
    default:
        enviarRespuestaError('Método no permitido', 405);
        break;
}

// ========================================
// FUNCIONES PRINCIPALES
// ========================================

/**
 * Obtiene la lista de pacientes con paginación y búsqueda
 * 
 * @return void
 */
function obtenerPacientes() {
    global $conn;
    
    // Obtener parámetros de la petición
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['per_page'] ?? 10);
    $search = trim($_GET['search'] ?? '');
    
    // Validar parámetros
    if ($page < 1) $page = 1;
    if ($perPage < 1 || $perPage > 100) $perPage = 10;
    
    try {
        // Construir cláusula WHERE para búsqueda
        $whereClause = '';
        $searchParams = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE p.nombre1 LIKE ? OR p.nombre2 LIKE ? OR p.apellido1 LIKE ? 
                           OR p.apellido2 LIKE ? OR p.numero_documento LIKE ? OR p.correo LIKE ?";
            $searchParam = "%{$search}%";
            $searchParams = array_fill(0, 6, $searchParam);
        }
        
        // Contar total de registros para paginación
        $total = contarPacientes($conn, $whereClause, $searchParams);
        
        // Obtener registros paginados
        $pacientes = obtenerPacientesPaginados($conn, $whereClause, $searchParams, $page, $perPage);
        
        // Calcular información de paginación
        $totalPages = ceil($total / $perPage);
        
        // Enviar respuesta exitosa
        enviarRespuestaExito([
            'data' => $pacientes,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ]);
        
    } catch (Exception $e) {
        enviarRespuestaError('Error al obtener pacientes: ' . $e->getMessage(), 500);
    }
}

/**
 * Crea un nuevo paciente en la base de datos
 * 
 * @return void
 */
function crearPaciente() {
    global $conn;
    
    // Obtener y validar datos de entrada
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        enviarRespuestaError('Datos JSON inválidos', 400);
        return;
    }
    
    // Validar campos requeridos
    $camposRequeridos = ['tipo_documento_id', 'numero_documento', 'nombre1', 'apellido1', 'genero_id', 'departamento_id', 'municipio_id'];
    
    foreach ($camposRequeridos as $campo) {
        if (empty($input[$campo])) {
            enviarRespuestaError("El campo '{$campo}' es requerido", 400);
            return;
        }
    }
    
    try {
        // Verificar si el documento ya existe
        if (documentoExiste($conn, $input['numero_documento'])) {
            enviarRespuestaError('Ya existe un paciente con este número de documento', 409);
            return;
        }
        
        // Insertar nuevo paciente
        $pacienteId = insertarPaciente($conn, $input);
        
        enviarRespuestaExito([
            'message' => 'Paciente creado correctamente',
            'data' => ['id' => $pacienteId]
        ]);
        
    } catch (Exception $e) {
        enviarRespuestaError('Error al crear paciente: ' . $e->getMessage(), 500);
    }
}

// ========================================
// FUNCIONES AUXILIARES DE BASE DE DATOS
// ========================================

/**
 * Cuenta el total de pacientes según los filtros
 * 
 * @param PDO $conn Conexión a la base de datos
 * @param string $whereClause Cláusula WHERE para filtros
 * @param array $params Parámetros para la consulta
 * @return int Total de registros
 */
function contarPacientes($conn, $whereClause, $params) {
    $query = "SELECT COUNT(*) as total FROM paciente p {$whereClause}";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

/**
 * Obtiene pacientes paginados con información relacionada
 * 
 * @param PDO $conn Conexión a la base de datos
 * @param string $whereClause Cláusula WHERE para filtros
 * @param array $params Parámetros para la consulta
 * @param int $page Página actual
 * @param int $perPage Elementos por página
 * @return array Lista de pacientes
 */
function obtenerPacientesPaginados($conn, $whereClause, $params, $page, $perPage) {
    $offset = ($page - 1) * $perPage;
    
    $query = "
        SELECT p.*, 
               td.nombre as tipo_documento_nombre,
               g.nombre as genero_nombre,
               d.nombre as departamento_nombre,
               m.nombre as municipio_nombre
        FROM paciente p
        LEFT JOIN tipos_documento td ON p.tipo_documento_id = td.id
        LEFT JOIN genero g ON p.genero_id = g.id
        LEFT JOIN departamentos d ON p.departamento_id = d.id
        LEFT JOIN municipios m ON p.municipio_id = m.id
        {$whereClause}
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($query);
    
    // Agregar parámetros de paginación
    $params[] = $perPage;
    $params[] = $offset;
    
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Verifica si ya existe un paciente con el número de documento
 * 
 * @param PDO $conn Conexión a la base de datos
 * @param string $numeroDocumento Número de documento a verificar
 * @return bool True si existe, false en caso contrario
 */
function documentoExiste($conn, $numeroDocumento) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM paciente WHERE numero_documento = ?");
    $stmt->execute([$numeroDocumento]);
    return (int)$stmt->fetchColumn() > 0;
}

/**
 * Inserta un nuevo paciente en la base de datos
 * 
 * @param PDO $conn Conexión a la base de datos
 * @param array $datos Datos del paciente
 * @return int ID del paciente creado
 */
function insertarPaciente($conn, $datos) {
    $query = "
        INSERT INTO paciente (
            tipo_documento_id, numero_documento, nombre1, nombre2, 
            apellido1, apellido2, genero_id, departamento_id, 
            municipio_id, correo, foto, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
        )
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        $datos['tipo_documento_id'],
        $datos['numero_documento'],
        $datos['nombre1'],
        $datos['nombre2'] ?? null,
        $datos['apellido1'],
        $datos['apellido2'] ?? null,
        $datos['genero_id'],
        $datos['departamento_id'],
        $datos['municipio_id'],
        $datos['correo'] ?? null,
        $datos['foto'] ?? null
    ]);
    
    return $conn->lastInsertId();
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