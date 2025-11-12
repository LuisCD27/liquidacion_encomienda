
# Sistema HUNOS (PHP + MySQL)

## Instalación rápida
1. Crear BD `hunos_db` en MySQL.
2. Importar `database/schema.sql` y luego `database/seed.sql`.
3. Configurar credenciales en `config/env.php`.
4. Montar el proyecto en `http://localhost/hunos_php_system/` (XAMPP o similar).
5. Abrir `views/formulario.php` para registrar una L.E.

### PDF
- El sistema genera páginas **imprimibles** (Ctrl+P → Guardar como PDF).
- Si instalas **dompdf/dompdf**, se generará PDF automáticamente.
  - `composer require dompdf/dompdf`

### Numeración por oficina
- La tabla `numeraciones` lleva el correlativo independiente por `oficina_id`.
- Cada vez que guardas, incrementa `ultimo_numero` para esa oficina.

### Exportaciones
- Excel: descarga CSV (compatible con Excel).
- PDF: consolidado imprimible o con Dompdf si está instalado.
