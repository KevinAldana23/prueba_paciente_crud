# SOLUCIÓN PUNTO 2 - PREGUNTAS TÉCNICAS

## 📚 Respuestas a Preguntas de Programación

### **A. ¿Qué es una variable?**
Una variable es un espacio de memoria reservado que se utiliza para almacenar datos que pueden cambiar durante la ejecución de un programa. Tiene un nombre identificador y un valor.

---

### **B. ¿Qué es un ciclo? ¿Mencione dos tipos?**
Un ciclo (o bucle) permite repetir un bloque de instrucciones mientras se cumpla una condición.

**Dos tipos comunes de ciclos son:**
- **`for`** – usado cuando sabemos cuántas veces queremos repetir algo.
- **`while`** – usado cuando queremos repetir algo mientras se cumpla una condición.

---

### **C. ¿Qué es un condicional?**
Un condicional permite que el programa tome decisiones. Ejecuta un bloque de código solo si se cumple una condición específica.

---

### **D. Cuando mencionamos nombre, edad, sexo, dirección y teléfono de un estudiante estamos mencionando:**
**a. Los atributos de una clase.**

---

### **E. ¿Qué es la Programación Orientada a Objetos (POO)?**
La POO es un paradigma de programación basado en el uso de objetos, que son instancias de clases. Cada objeto tiene:
- **Atributos** (datos o propiedades)
- **Métodos** (acciones o comportamientos)

Permite modelar programas de forma más cercana a la realidad.

---

### **F. ¿Qué son los patrones de diseño y para qué se usan?**
Los patrones de diseño son soluciones reutilizables y probadas a problemas comunes en el desarrollo de software. Se utilizan para:
- Mejorar la organización del código.
- Promover buenas prácticas.
- Resolver problemas de diseño frecuentes.

---

### **G. ¿Cuál operador condicional en PHP evalúa que los valores son iguales y del mismo tipo? (5%)**
**Rta: `===`**

`===` compara valor y tipo de dato.
**Ejemplo:** `5 === '5'` → `false` porque uno es entero y otro string.

---

### **H. En PHP, ¿qué se considera desde un punto de vista booleano un 0 o string vacío? (5%)**
**`False`**

---

### **I. Resultado final de la variable $c:**
**Resultado final: `10`**

---

### **J. Resultado final de $c:**
**Resultado final: `10`**

---

### **K. Resultado final de $print:**
**Resultado final: `Feliz Año`**

------------------------------------------------------------------------------------------------------------

# SIHOS - Sistema de Gestión de Pacientes

## 📋 Descripción

**SIHOS** (Sistema de Información Hospitalaria) es una aplicación web completa para la gestión de pacientes, desarrollada como prueba técnica que demuestra habilidades en desarrollo full-stack con PHP, MySQL y JavaScript.

## ✨ Características Principales

### 🔐 Autenticación y Seguridad
- Sistema de login seguro con tokens
- Validación de sesiones
- Protección contra ataques comunes

### 👥 Gestión de Pacientes
- **CRUD completo**: Crear, Leer, Actualizar y Eliminar pacientes
- **Búsqueda avanzada**: Por nombre, apellido, documento o correo
- **Paginación**: Navegación eficiente con múltiples elementos por página
- **Subida de fotos**: Soporte para imágenes de pacientes con validación

### 📊 Información Completa
- Datos personales completos (nombres, apellidos, documento)
- Información de contacto (correo electrónico)
- Ubicación geográfica (departamentos y municipios)
- Categorización por género y tipo de documento

### 🎨 Interfaz de Usuario
- Diseño responsivo y moderno
- Interfaz intuitiva con Bootstrap 5
- Iconografía clara con Font Awesome
- Modales para formularios y confirmaciones

## 🛠️ Tecnologías Utilizadas

### Frontend
- **HTML5**: Estructura semántica y accesible
- **CSS3**: Estilos modernos con gradientes y animaciones
- **JavaScript (ES6+)**: Funcionalidad dinámica y asíncrona
- **Bootstrap 5**: Framework CSS para diseño responsivo
- **Font Awesome**: Iconografía profesional

### Backend
- **PHP 8.0+**: Lenguaje de programación del servidor
- **MySQL**: Base de datos relacional
- **PDO**: Acceso seguro a base de datos
- **JSON**: Intercambio de datos con el frontend

### Herramientas de Desarrollo
- **XAMPP**: Servidor local Apache + MySQL + PHP
- **Composer**: Gestión de dependencias PHP
- **Git**: Control de versiones

## 📁 Estructura del Proyecto

```
crud_pacientes/
├── backend/
│   ├── api/                    # Endpoints de la API
│   │   ├── pacientes.php       # CRUD de pacientes
│   │   ├── simple_login.php    # Autenticación
│   │   ├── verify_simple_token.php # Verificación de tokens
│   │   ├── tipos_documento.php # Tipos de documento
│   │   ├── generos.php         # Géneros
│   │   ├── departamentos.php   # Departamentos
│   │   ├── municipios.php      # Municipios
│   │   └── upload_photo.php    # Subida de fotos
│   ├── config/
│   │   ├── db.php             # Configuración de base de datos
│   │   └── env.php            # Variables de entorno
│   ├── uploads/               # Almacenamiento de fotos
│   └── vendor/                # Dependencias de Composer
├── frontend/
│   ├── index.html             # Página principal
│   ├── script.js              # Lógica del frontend
│   └── styles.css             # Estilos personalizados
└── README.md                  # Documentación
```

## 🚀 Instalación y Configuración

### Prerrequisitos
- XAMPP (Apache + MySQL + PHP)
- PHP 8.0 o superior
- MySQL 5.7 o superior
- Navegador web moderno

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   cd crud_pacientes
   ```

2. **Configurar XAMPP**
   - Iniciar Apache y MySQL desde el panel de control de XAMPP
   - Verificar que ambos servicios estén ejecutándose

3. **Configurar la base de datos**
   - Abrir phpMyAdmin (http://localhost/phpmyadmin)
   - Crear una nueva base de datos llamada `sihos_db`
   - Importar el archivo `backend/database/setup.sql`

4. **Configurar variables de entorno**
   - Editar `backend/config/env.php`
   - Ajustar las credenciales de la base de datos según tu configuración

5. **Instalar dependencias PHP**
   ```bash
   cd backend
   composer install
   ```

6. **Configurar permisos**
   - Asegurar que la carpeta `backend/uploads/` tenga permisos de escritura

7. **Acceder a la aplicación**
   - Abrir http://localhost/crud_pacientes/frontend/
   - Usar las credenciales por defecto:
     - **Usuario**: `admin`
     - **Contraseña**: `1234567890`

## 📖 Uso de la Aplicación

### Inicio de Sesión
1. Acceder a la URL de la aplicación
2. Ingresar credenciales de usuario
3. El sistema validará la autenticación automáticamente

### Gestión de Pacientes

#### Crear Nuevo Paciente
1. Hacer clic en "Nuevo Paciente"
2. Completar el formulario con los datos requeridos
3. Opcionalmente subir una foto del paciente
4. Guardar los datos

#### Editar Paciente
1. Hacer clic en el icono de editar (lápiz) en la fila del paciente
2. Modificar los datos necesarios
3. Guardar los cambios

#### Eliminar Paciente
1. Hacer clic en el icono de eliminar (basura) en la fila del paciente
2. Confirmar la eliminación en el modal
3. El paciente será eliminado permanentemente

#### Buscar Pacientes
1. Utilizar el campo de búsqueda en la parte superior
2. La búsqueda funciona por nombre, apellido, documento o correo
3. Los resultados se actualizan en tiempo real

### Navegación
- **Paginación**: Usar los controles de paginación para navegar entre páginas
- **Elementos por página**: Seleccionar cuántos pacientes mostrar por página
- **Exportar**: Funcionalidad preparada para exportar datos (futura implementación)

## 🔧 Configuración Avanzada

### Variables de Entorno
Editar `backend/config/env.php`:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sihos_db');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
?>
```

### Configuración de Base de Datos
El archivo `backend/database/setup.sql` contiene:
- Estructura de todas las tablas necesarias
- Datos iniciales (tipos de documento, géneros, departamentos, municipios)
- Usuario administrador por defecto

### Personalización de Estilos
Los estilos se pueden personalizar editando:
- `frontend/index.html` (estilos inline)
- Crear un archivo CSS separado para mayor organización

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
- Verificar que MySQL esté ejecutándose en XAMPP
- Comprobar las credenciales en `backend/config/env.php`
- Asegurar que la base de datos `sihos_db` existe

### Error 404 en API
- Verificar que Apache esté ejecutándose
- Comprobar que los archivos estén en la ubicación correcta
- Revisar la configuración de mod_rewrite si se usa

### Problemas con Subida de Fotos
- Verificar permisos de escritura en `backend/uploads/`
- Comprobar el tamaño máximo de archivo en PHP
- Validar que el formato de imagen sea compatible

### Error de Autenticación
- Verificar que el usuario existe en la base de datos
- Comprobar que la contraseña sea correcta
- Revisar la configuración de tokens

## 🔒 Seguridad

### Medidas Implementadas
- **Prepared Statements**: Previene inyección SQL
- **Validación de Entrada**: Sanitización de datos de usuario
- **Tokens de Autenticación**: Sesiones seguras
- **CORS Configurado**: Control de acceso entre dominios
- **Validación de Archivos**: Verificación de tipos y tamaños de imagen


## 📝 API Documentation

### Endpoints Principales

#### Autenticación
- `POST /api/simple_login.php` - Iniciar sesión
- `POST /api/verify_simple_token.php` - Verificar token

#### Pacientes
- `GET /api/pacientes.php` - Listar pacientes (con paginación y búsqueda)
- `POST /api/pacientes.php` - Crear paciente
- `GET /api/paciente.php/{id}` - Obtener paciente específico
- `PUT /api/paciente.php/{id}` - Actualizar paciente
- `DELETE /api/paciente.php/{id}` - Eliminar paciente

#### Datos de Referencia
- `GET /api/tipos_documento.php` - Tipos de documento
- `GET /api/generos.php` - Géneros
- `GET /api/departamentos.php` - Departamentos
- `GET /api/municipios.php?departamento_id={id}` - Municipios por departamento

#### Archivos
- `POST /api/upload_photo.php` - Subir foto de paciente


## 👨‍💻 Autor

**Desarrollador** - KevinAldana
**Versión**: 1.0 