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

require_once __DIR__.'/../config/db.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

// Obtener el ID del paciente de la URL
$pacienteId = null;
foreach ($uri as $segment) {
    if (is_numeric($segment)) {
        $pacienteId = $segment;
        break;
    }
}

if (!$pacienteId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID de paciente requerido'
    ]);
    exit();
}

switch ($requestMethod) {
    case 'GET':
        // Obtener paciente específico
        try {
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
                WHERE p.id = :id
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $pacienteId]);
            $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($paciente) {
                echo json_encode([
                    'success' => true,
                    'data' => $paciente
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Paciente no encontrado'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener paciente: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'PUT':
        // Actualizar paciente
        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            $stmt = $conn->prepare("
                UPDATE paciente SET 
                    tipo_documento_id = :tipo_documento_id,
                    numero_documento = :numero_documento,
                    nombre1 = :nombre1,
                    nombre2 = :nombre2,
                    apellido1 = :apellido1,
                    apellido2 = :apellido2,
                    genero_id = :genero_id,
                    departamento_id = :departamento_id,
                    municipio_id = :municipio_id,
                    correo = :correo,
                    foto = :foto,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                ':id' => $pacienteId,
                ':tipo_documento_id' => $input['tipo_documento_id'],
                ':numero_documento' => $input['numero_documento'],
                ':nombre1' => $input['nombre1'],
                ':nombre2' => $input['nombre2'] ?? null,
                ':apellido1' => $input['apellido1'],
                ':apellido2' => $input['apellido2'] ?? null,
                ':genero_id' => $input['genero_id'],
                ':departamento_id' => $input['departamento_id'],
                ':municipio_id' => $input['municipio_id'],
                ':correo' => $input['correo'] ?? null,
                ':foto' => $input['foto'] ?? null
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Paciente actualizado correctamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Paciente no encontrado'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al actualizar paciente: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'DELETE':
        // Eliminar paciente
        try {
            $stmt = $conn->prepare("DELETE FROM paciente WHERE id = :id");
            $result = $stmt->execute([':id' => $pacienteId]);
            
            if ($result && $stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Paciente eliminado correctamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Paciente no encontrado'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al eliminar paciente: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido'
        ]);
        break;
}
?> 