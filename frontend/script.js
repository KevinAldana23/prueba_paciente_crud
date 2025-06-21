/**
 * SIHOS - Sistema de Gestión de Pacientes
 * Script principal del frontend
 * 
 * @author Desarrollador
 * @version 1.0
 */

// ========================================
// VARIABLES GLOBALES
// ========================================
let currentPage = 1;           // Página actual de la paginación
let perPage = 10;              // Elementos por página
let currentSearch = '';        // Término de búsqueda actual
let pacienteToDelete = null;   // ID del paciente a eliminar
let isEditing = false;         // Indica si estamos editando un paciente
let authToken = localStorage.getItem('authToken'); // Token de autenticación

// URL base de la API
const API_BASE_URL = 'http://localhost/crud_pacientes/backend/api';

// ========================================
// INICIALIZACIÓN DE LA APLICACIÓN
// ========================================

/**
 * Inicializa la aplicación cuando el DOM está listo
 */
document.addEventListener('DOMContentLoaded', function() {
    checkAuth();
    configurarEventListeners();
});

// ========================================
// AUTENTICACIÓN
// ========================================

/**
 * Verifica la autenticación del usuario
 * Si no hay token, muestra el modal de login
 * Si hay token, lo verifica y carga los datos
 */
function checkAuth() {
    if (!authToken) {
        showLoginModal();
        return;
    }

    // Verificar token con el servidor
    fetch(`${API_BASE_URL}/verify_simple_token.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ token: authToken })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Token inválido, limpiar y mostrar login
            localStorage.removeItem('authToken');
            authToken = null;
            showLoginModal();
        } else {
            // Token válido, cargar datos
            cargarDatosIniciales();
            cargarPacientes();
        }
    })
    .catch(error => {
        console.error('Error verificando token:', error);
        showLoginModal();
    });
}

/**
 * Muestra el modal de inicio de sesión
 */
function showLoginModal() {
    const loginHTML = `
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </h5>
                    </div>
                    <div class="modal-body">
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="login()">
                            <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', loginHTML);
    new bootstrap.Modal(document.getElementById('loginModal')).show();
}

/**
 * Procesa el inicio de sesión del usuario
 */
async function login() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Validar campos requeridos
    if (!username || !password) {
        mostrarAlerta('Por favor complete todos los campos (admin/1234567890)', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/simple_login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Login exitoso
            authToken = data.token;
            localStorage.setItem('authToken', authToken);
            
            // Cerrar modal y limpiar
            bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
            document.getElementById('loginModal').remove();
            
            mostrarAlerta('Inicio de sesión exitoso', 'success');
            cargarDatosIniciales();
            cargarPacientes();
        } else {
            mostrarAlerta(data.error, 'danger');
        }
    } catch (error) {
        console.error('Error en login:', error);
        mostrarAlerta('Error de conexión', 'danger');
    }
}

// ========================================
// CONFIGURACIÓN DE EVENTOS
// ========================================

/**
 * Configura todos los event listeners de la aplicación
 */
function configurarEventListeners() {
    // Búsqueda en tiempo real
    document.getElementById('searchInput').addEventListener('input', function() {
        currentSearch = this.value;
        currentPage = 1; // Resetear a la primera página
        cargarPacientes();
    });

    // Cambio en elementos por página
    document.getElementById('perPageSelect').addEventListener('change', function() {
        perPage = parseInt(this.value);
        currentPage = 1; // Resetear a la primera página
        cargarPacientes();
    });

    // Carga de municipios cuando cambia el departamento
    document.getElementById('departamento').addEventListener('change', function() {
        cargarMunicipios(this.value);
    });

    // Validación del formulario
    document.getElementById('pacienteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarPaciente();
    });
}

// ========================================
// CARGA DE DATOS INICIALES
// ========================================

/**
 * Carga todos los datos necesarios para los dropdowns
 */
async function cargarDatosIniciales() {
    try {
        await Promise.all([
            cargarTiposDocumento(),
            cargarGeneros(),
            cargarDepartamentos()
        ]);
    } catch (error) {
        console.error('Error cargando datos iniciales:', error);
        mostrarAlerta('Error cargando datos iniciales', 'danger');
    }
}

/**
 * Carga los tipos de documento en el dropdown
 */
async function cargarTiposDocumento() {
    try {
        const response = await fetch(`${API_BASE_URL}/tipos_documento.php`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('tipoDocumento');
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            data.data.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.id;
                option.textContent = tipo.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando tipos de documento:', error);
    }
}

/**
 * Carga los géneros en el dropdown
 */
async function cargarGeneros() {
    try {
        const response = await fetch(`${API_BASE_URL}/generos.php`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('genero');
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            data.data.forEach(genero => {
                const option = document.createElement('option');
                option.value = genero.id;
                option.textContent = genero.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando géneros:', error);
    }
}

/**
 * Carga los departamentos en el dropdown
 */
async function cargarDepartamentos() {
    try {
        const response = await fetch(`${API_BASE_URL}/departamentos.php`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('departamento');
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            data.data.forEach(departamento => {
                const option = document.createElement('option');
                option.value = departamento.id;
                option.textContent = departamento.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando departamentos:', error);
    }
}

/**
 * Carga los municipios según el departamento seleccionado
 * @param {number} departamentoId - ID del departamento seleccionado
 */
async function cargarMunicipios(departamentoId) {
    if (!departamentoId) {
        const select = document.getElementById('municipio');
        select.innerHTML = '<option value="">Seleccionar...</option>';
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/municipios.php?departamento_id=${departamentoId}`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('municipio');
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            data.data.forEach(municipio => {
                const option = document.createElement('option');
                option.value = municipio.id;
                option.textContent = municipio.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando municipios:', error);
    }
}

// ========================================
// GESTIÓN DE PACIENTES
// ========================================

/**
 * Carga la lista de pacientes con paginación y búsqueda
 */
async function cargarPacientes() {
    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: perPage
        });
        
        if (currentSearch) {
            params.append('search', currentSearch);
        }
        
        const response = await fetch(`${API_BASE_URL}/pacientes.php?${params}`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderizarPacientes(data.data);
            renderizarPaginacion(data.pagination);
            document.getElementById('totalPacientes').textContent = data.pagination.total;
        } else {
            mostrarAlerta('Error al cargar pacientes: ' + data.error, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al cargar pacientes', 'danger');
    }
}

/**
 * Renderiza la tabla de pacientes
 * @param {Array} pacientes - Lista de pacientes a mostrar
 */
function renderizarPacientes(pacientes) {
    const tableBody = document.getElementById('pacientesTableBody');
    
    if (pacientes.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron pacientes</p>
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = pacientes.map(paciente => `
        <tr>
            <td>
                ${paciente.foto ? 
                    `<img src="${API_BASE_URL.replace('/api', '')}/${paciente.foto}" alt="Foto" class="patient-photo">` :
                    `<div class="patient-photo bg-secondary d-flex align-items-center justify-content-center text-white">
                        <i class="fas fa-user"></i>
                    </div>`
                }
            </td>
            <td>
                <div class="fw-bold">${paciente.numero_documento}</div>
                <small class="text-muted">${paciente.tipo_documento_nombre}</small>
            </td>
            <td>${paciente.nombre1} ${paciente.nombre2 || ''}</td>
            <td>${paciente.apellido1} ${paciente.apellido2 || ''}</td>
            <td>${paciente.genero_nombre}</td>
            <td>
                <div>${paciente.departamento_nombre}</div>
                <small class="text-muted">${paciente.municipio_nombre}</small>
            </td>
            <td>${paciente.correo || '<span class="text-muted">No registrado</span>'}</td>
            <td>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-primary" onclick="editarPaciente(${paciente.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarPaciente(${paciente.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Renderiza la paginación
 * @param {Object} pagination - Información de paginación
 */
function renderizarPaginacion(pagination) {
    const paginationContainer = document.getElementById('pagination');
    
    if (pagination.total_pages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let paginationHTML = '';

    // Botón anterior
    if (pagination.current_page > 1) {
        paginationHTML += `
            <li class="page-item">
                <button class="page-link" onclick="cambiarPagina(${pagination.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </li>
        `;
    }

    // Números de página
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

    if (startPage > 1) {
        paginationHTML += `
            <li class="page-item">
                <button class="page-link" onclick="cambiarPagina(1)">1</button>
            </li>
        `;
        if (startPage > 2) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <button class="page-link" onclick="cambiarPagina(${i})">${i}</button>
            </li>
        `;
    }

    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHTML += `
            <li class="page-item">
                <button class="page-link" onclick="cambiarPagina(${pagination.total_pages})">${pagination.total_pages}</button>
            </li>
        `;
    }

    // Botón siguiente
    if (pagination.current_page < pagination.total_pages) {
        paginationHTML += `
            <li class="page-item">
                <button class="page-link" onclick="cambiarPagina(${pagination.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </li>
        `;
    }

    paginationContainer.innerHTML = paginationHTML;
}

/**
 * Cambia a una página específica
 * @param {number} page - Número de página a cargar
 */
function cambiarPagina(page) {
    currentPage = page;
    cargarPacientes();
}

// ========================================
// FORMULARIOS Y VALIDACIÓN
// ========================================

/**
 * Limpia el formulario de paciente
 */
function limpiarFormulario() {
    document.getElementById('pacienteForm').reset();
    document.getElementById('pacienteId').value = '';
    document.getElementById('municipio').innerHTML = '<option value="">Seleccionar...</option>';
    document.getElementById('pacienteModalLabel').innerHTML = '<i class="fas fa-user-plus me-2"></i>Nuevo Paciente';
    document.getElementById('fotoPreview').innerHTML = '';
    isEditing = false;
}

/**
 * Guarda o actualiza un paciente
 */
async function guardarPaciente() {
    const form = document.getElementById('pacienteForm');
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    try {
        const pacienteId = document.getElementById('pacienteId').value;
        const fileInput = document.getElementById('foto');
        let fotoPath = null;

        // Subir foto si se seleccionó un archivo
        if (fileInput.files.length > 0) {
            const formData = new FormData();
            formData.append('foto', fileInput.files[0]);

            const uploadResponse = await fetch(`${API_BASE_URL}/upload_photo.php`, {
                method: 'POST',
                body: formData
            });

            const uploadData = await uploadResponse.json();
            if (uploadData.success) {
                fotoPath = uploadData.data.path;
            } else {
                mostrarAlerta('Error al subir la foto: ' + uploadData.error, 'danger');
                return;
            }
        }

        // Preparar datos del formulario
        const formData = {
            tipo_documento_id: document.getElementById('tipoDocumento').value,
            numero_documento: document.getElementById('numeroDocumento').value,
            nombre1: document.getElementById('nombre1').value,
            nombre2: document.getElementById('nombre2').value,
            apellido1: document.getElementById('apellido1').value,
            apellido2: document.getElementById('apellido2').value,
            genero_id: document.getElementById('genero').value,
            departamento_id: document.getElementById('departamento').value,
            municipio_id: document.getElementById('municipio').value,
            correo: document.getElementById('correo').value,
            foto: fotoPath
        };

        // Determinar URL y método según si es creación o edición
        const url = pacienteId ? `${API_BASE_URL}/paciente.php/${pacienteId}` : `${API_BASE_URL}/pacientes.php`;
        const method = pacienteId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            mostrarAlerta(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('pacienteModal')).hide();
            limpiarFormulario();
            cargarPacientes();
        } else {
            mostrarAlerta(data.error, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al guardar paciente', 'danger');
    }
}

/**
 * Carga los datos de un paciente para editar
 * @param {number} id - ID del paciente a editar
 */
async function editarPaciente(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/paciente.php/${id}`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const paciente = data.data;

            // Llenar formulario con datos del paciente
            document.getElementById('pacienteId').value = paciente.id;
            document.getElementById('tipoDocumento').value = paciente.tipo_documento_id;
            document.getElementById('numeroDocumento').value = paciente.numero_documento;
            document.getElementById('nombre1').value = paciente.nombre1;
            document.getElementById('nombre2').value = paciente.nombre2 || '';
            document.getElementById('apellido1').value = paciente.apellido1;
            document.getElementById('apellido2').value = paciente.apellido2 || '';
            document.getElementById('genero').value = paciente.genero_id;
            document.getElementById('departamento').value = paciente.departamento_id;
            document.getElementById('correo').value = paciente.correo || '';

            // Cargar municipios y establecer valor
            await cargarMunicipios(paciente.departamento_id);
            document.getElementById('municipio').value = paciente.municipio_id;

            // Mostrar foto actual si existe
            const fotoPreview = document.getElementById('fotoPreview');
            if (paciente.foto) {
                fotoPreview.innerHTML = `
                    <div class="mb-2">
                        <img src="${API_BASE_URL.replace('/api', '')}/${paciente.foto}" alt="Foto actual" class="img-thumbnail" style="max-width: 100px;">
                        <small class="d-block text-muted">Foto actual</small>
                    </div>
                `;
            } else {
                fotoPreview.innerHTML = '<small class="text-muted">No hay foto</small>';
            }

            // Actualizar título del modal
            document.getElementById('pacienteModalLabel').innerHTML = '<i class="fas fa-user-edit me-2"></i>Editar Paciente';
            isEditing = true;

            // Mostrar modal
            new bootstrap.Modal(document.getElementById('pacienteModal')).show();
        } else {
            mostrarAlerta(data.error, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error al cargar datos del paciente', 'danger');
    }
}

// ========================================
// ELIMINACIÓN DE PACIENTES
// ========================================

/**
 * Prepara la eliminación de un paciente
 * @param {number} id - ID del paciente a eliminar
 */
function eliminarPaciente(id) {
    pacienteToDelete = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

/**
 * Confirma y ejecuta la eliminación del paciente
 */
async function confirmarEliminar() {
    if (!pacienteToDelete) return;

    try {
        const response = await fetch(`${API_BASE_URL}/paciente.php/${pacienteToDelete}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });

        const data = await response.json();

        if (data.success) {
            mostrarAlerta(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            cargarPacientes();
        } else {
            mostrarAlerta(data.error, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error de conexión al eliminar paciente', 'danger');
    } finally {
        pacienteToDelete = null;
    }
}

// ========================================
// UTILIDADES
// ========================================

/**
 * Exporta la lista de pacientes (funcionalidad futura)
 */
function exportarPacientes() {
    mostrarAlerta('Funcionalidad de exportación no implementada', 'info');
}

/**
 * Muestra una alerta al usuario
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} tipo - Tipo de alerta (success, danger, warning, info)
 */
function mostrarAlerta(mensaje, tipo) {
    const alertContainer = document.getElementById('alertContainer');
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show" role="alert" id="${alertId}">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('beforeend', alertHTML);
    
    // Auto eliminar después de 5 segundos
    setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            bootstrap.Alert.getOrCreateInstance(alertElement).close();
        }
    }, 5000);
}

/**
 * Cierra la sesión del usuario
 */
function logout() {
    localStorage.removeItem('authToken');
    authToken = null;
    location.reload();
}
