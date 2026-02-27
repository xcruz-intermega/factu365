<?php

return [
    // Auth
    'login' => 'Iniciar sesión',
    'not_configured' => 'El acceso de super admin no está configurado.',
    'invalid_credentials' => 'Las credenciales no son correctas.',

    // Dashboard
    'dashboard_title' => 'Panel de Administración',
    'total_tenants' => 'Total empresas',
    'active_tenants' => 'Activas',
    'suspended_tenants' => 'Suspendidas',
    'search_placeholder' => 'Buscar por slug, nombre, NIF o email...',

    // Table columns
    'col_slug' => 'Slug',
    'col_company' => 'Empresa',
    'col_nif' => 'NIF/CIF',
    'col_owner' => 'Propietario',
    'col_users' => 'Usuarios',
    'col_invoices' => 'Facturas',
    'col_disk' => 'Espacio',
    'col_created' => 'Creado',
    'col_status' => 'Estado',
    'col_name' => 'Nombre',
    'col_role' => 'Rol',
    'col_locale' => 'Idioma',

    // Statuses
    'status_active' => 'Activo',
    'status_suspended' => 'Suspendido',

    // Actions
    'view_detail' => 'Ver detalle',
    'suspend' => 'Suspender',
    'unsuspend' => 'Reactivar',
    'reset_password' => 'Resetear contraseña',
    'delete_tenant' => 'Eliminar empresa',
    'access_tenant' => 'Acceder',
    'confirm_delete' => 'Eliminar definitivamente',

    // Password modal
    'new_password' => 'Nueva contraseña',
    'reset_password_desc' => 'Establece una nueva contraseña para el propietario de :slug.',
    'password_reset_success' => 'Contraseña del propietario actualizada correctamente.',

    // Delete modal
    'delete_tenant_warning' => 'Esta acción eliminará permanentemente la empresa, su base de datos y todos sus archivos. Esta acción NO se puede deshacer.',
    'delete_confirm_type' => 'Escribe ":slug" para confirmar la eliminación.',

    // Flash messages
    'tenant_suspended' => 'Empresa suspendida correctamente.',
    'tenant_unsuspended' => 'Empresa reactivada correctamente.',
    'tenant_deleted' => 'Empresa eliminada correctamente.',

    // Detail page
    'tenant_detail' => 'Detalle empresa',
    'section_tenant_info' => 'Información del tenant',
    'section_company' => 'Perfil de empresa',
    'section_users' => 'Usuarios',
    'section_stats' => 'Estadísticas',
    'section_storage' => 'Almacenamiento',

    // Company fields
    'legal_name' => 'Razón social',
    'trade_name' => 'Nombre comercial',
    'address' => 'Dirección',
    'phone' => 'Teléfono',
    'tax_regime' => 'Régimen fiscal',
    'enabled' => 'Activado',
    'disabled' => 'Desactivado',
    'no_company_profile' => 'Sin perfil de empresa configurado.',

    // Statistics
    'stat_invoices' => 'Facturas emitidas',
    'stat_quotes' => 'Presupuestos',
    'stat_delivery_notes' => 'Albaranes',
    'stat_rectificatives' => 'Rectificativas',
    'stat_purchase_invoices' => 'Facturas recibidas',
    'stat_clients' => 'Clientes',
    'stat_products' => 'Productos',
    'stat_expenses' => 'Gastos',
    'revenue_this_year' => 'Facturación este año',
    'last_document' => 'Último documento',

    // Storage & Backups
    'disk_usage' => 'Espacio en disco',
    'last_backup' => 'Última copia',
    'no_backup' => 'Sin copias de seguridad',
    'col_backups' => 'Copias',
    'backups_count' => 'Copias de seguridad',
    'backups_total_size' => 'Tamaño total copias',
    'backups_list' => 'Copias de seguridad',
    'backup_file' => 'Archivo',
    'backup_date' => 'Fecha',
    'backup_type' => 'Tipo',
    'backup_size' => 'Tamaño',
    'backup_type_full' => 'Completa',
    'backup_type_tenant' => 'Empresa',

    // Roles
    'role_owner' => 'Propietario',
    'role_admin' => 'Administrador',
    'role_accountant' => 'Contable',
    'role_viewer' => 'Visor',

    // Mail settings
    'mail_settings_title' => 'Configuración de Email',
    'mail_settings_smtp_section' => 'Servidor SMTP',
    'mail_settings_host' => 'Host SMTP',
    'mail_settings_port' => 'Puerto',
    'mail_settings_encryption' => 'Encriptación',
    'mail_settings_username' => 'Usuario',
    'mail_settings_password' => 'Contraseña',
    'mail_settings_password_placeholder' => 'Dejar vacío para mantener la actual',
    'mail_settings_from_address' => 'Email remitente',
    'mail_settings_from_name' => 'Nombre remitente',
    'mail_settings_is_active' => 'Servicio activo',
    'mail_settings_test_button' => 'Probar conexión',
    'mail_settings_test_success' => 'Conexión SMTP exitosa.',
    'mail_settings_test_failed' => 'Error de conexión: :error',
    'mail_settings_last_test' => 'Última prueba exitosa',
    'mail_settings_not_tested' => 'No probado',
    'mail_settings_saved' => 'Configuración de email guardada.',
    'mail_settings_guide_title' => 'Guía de configuración',
    'mail_settings_guide_provider' => 'Proveedor recomendado',
    'mail_settings_guide_provider_desc' => '300 emails/día gratis, servidores EU, compatible GDPR.',
    'mail_settings_guide_step1' => 'Crear cuenta en Brevo y verificar dominio',
    'mail_settings_guide_step2' => 'Ir a Configuración → SMTP & API y obtener la clave SMTP',
    'mail_settings_guide_step3' => 'Introducir los datos en el formulario superior',
    'mail_settings_guide_dns' => 'Registros DNS necesarios',
    'mail_settings_guide_spf' => 'Añadir registro TXT en tu DNS con el valor SPF de tu proveedor.',
    'mail_settings_guide_dkim' => 'Copiar el registro DKIM desde el panel de tu proveedor de email.',
    'mail_settings_guide_dmarc' => 'Añadir registro TXT para DMARC. Ajustar la dirección de email para recibir informes.',
    'mail_settings_guide_verify' => 'Verificar deliverabilidad',
    'mail_settings_guide_verify_desc' => 'Envía un email de prueba a mail-tester.com para verificar la configuración. Objetivo: 9+/10.',
    'mail_settings_encryption_tls' => 'TLS',
    'mail_settings_encryption_ssl' => 'SSL',
    'mail_settings_encryption_none' => 'Ninguna',
];
