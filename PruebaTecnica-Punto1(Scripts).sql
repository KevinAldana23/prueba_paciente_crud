--Scripts SQL Punto 1.
    --Crea tabla e inserta registros
        CREATE TABLE inventario (
            id_fabricante VARCHAR(10),
            id_producto   VARCHAR(10),
            descripcion   VARCHAR(50),
            precio        INT,
            existencia    INT
        );
        INSERT INTO inventario (id_fabricante, id_producto, descripcion, precio, existencia) VALUES
        ('Aci', '41001', 'Aguja', 58, 227),
        ('Aci', '41002', 'Micropore', 80, 150),
        ('Aci', '41003', 'Gasa', 112, 80),
        ('Aci', '41004', 'Equipo macrogoteo', 110, 50),
        ('Bic', '41003', 'Curas', 120, 20),
        ('Inc', '41089', 'Canaleta', 500, 30),
        ('Qsa', 'Xk47', 'Compresa', 150, 200),
        ('Bic', 'Xk47', 'Compresa', 200, 200);
    --1. Listado de inventario con precio e IVA incluido (10%)
        SELECT 
            id_fabricante, 
            id_producto, 
            descripcion, 
            precio, 
            ROUND(precio * 1.10, 2) AS precio_con_iva
        FROM 
            inventario;
    --2. Cantidad total de existencias por producto
        SELECT 
            descripcion, 
            SUM(existencias) AS total_existencias
        FROM 
            inventario
        GROUP BY 
            descripcion;
    --3. Promedio de precio por fabricante
        SELECT 
            id_fabricante, 
            AVG(precio) AS promedio_precio
        FROM 
            inventario
        GROUP BY 
            id_fabricante;
    --4. Producto con mayor precio
        SELECT 
            id_fabricante, 
            id_producto, 
            descripcion, 
            precio
        FROM 
            inventario
        ORDER BY 
            precio DESC
        LIMIT 1;
    -- 5. Cargar un nuevo pedido de 500 Curas del fabricante Bic
        UPDATE
            inventario
        SET
            existencia = existencia + 500
        WHERE
            id_fabricante   = 'Bic' 
            AND id_producto = '41003';
    --6. Eliminar productos del fabricante Osa
        DELETE FROM inventario
        WHERE id_fabricante = 'Osa';



