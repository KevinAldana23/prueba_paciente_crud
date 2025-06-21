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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar si se subió un archivo
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se subió ningún archivo o hubo un error en la subida');
        }

        $file = $_FILES['foto'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileType = $file['type'];

        // Verificar el tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Solo se permiten archivos JPG, PNG y GIF');
        }

        // Verificar el tamaño (máximo 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($fileSize > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Máximo 2MB');
        }

        // Crear directorio de uploads si no existe
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único para el archivo
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueName = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $uniqueName;

        // Mover el archivo
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            echo json_encode([
                'success' => true,
                'message' => 'Foto subida correctamente',
                'data' => [
                    'filename' => $uniqueName,
                    'path' => 'uploads/' . $uniqueName,
                    'size' => $fileSize,
                    'type' => $fileType
                ]
            ]);
        } else {
            throw new Exception('Error al mover el archivo subido');
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
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