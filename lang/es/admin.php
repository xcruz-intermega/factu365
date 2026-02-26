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
];
