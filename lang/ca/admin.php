<?php

return [
    // Auth
    'login' => 'Iniciar sessió',
    'not_configured' => "L'accés de super admin no està configurat.",
    'invalid_credentials' => 'Les credencials no són correctes.',

    // Dashboard
    'dashboard_title' => "Panell d'Administració",
    'total_tenants' => 'Total empreses',
    'active_tenants' => 'Actives',
    'suspended_tenants' => 'Suspeses',
    'search_placeholder' => 'Cercar per slug, nom, NIF o email...',

    // Table columns
    'col_slug' => 'Slug',
    'col_company' => 'Empresa',
    'col_nif' => 'NIF/CIF',
    'col_owner' => 'Propietari',
    'col_users' => 'Usuaris',
    'col_invoices' => 'Factures',
    'col_disk' => 'Espai',
    'col_created' => 'Creat',
    'col_status' => 'Estat',
    'col_name' => 'Nom',
    'col_role' => 'Rol',
    'col_locale' => 'Idioma',

    // Statuses
    'status_active' => 'Actiu',
    'status_suspended' => 'Suspès',

    // Actions
    'view_detail' => 'Veure detall',
    'suspend' => 'Suspendre',
    'unsuspend' => 'Reactivar',
    'reset_password' => 'Restablir contrasenya',
    'delete_tenant' => 'Eliminar empresa',
    'access_tenant' => 'Accedir',
    'confirm_delete' => 'Eliminar definitivament',

    // Password modal
    'new_password' => 'Nova contrasenya',
    'reset_password_desc' => 'Estableix una nova contrasenya per al propietari de :slug.',
    'password_reset_success' => 'Contrasenya del propietari actualitzada correctament.',

    // Delete modal
    'delete_tenant_warning' => "Aquesta acció eliminarà permanentment l'empresa, la seva base de dades i tots els seus arxius. Aquesta acció NO es pot desfer.",
    'delete_confirm_type' => 'Escriu ":slug" per confirmar l\'eliminació.',

    // Flash messages
    'tenant_suspended' => 'Empresa suspesa correctament.',
    'tenant_unsuspended' => 'Empresa reactivada correctament.',
    'tenant_deleted' => 'Empresa eliminada correctament.',

    // Detail page
    'tenant_detail' => 'Detall empresa',
    'section_tenant_info' => 'Informació del tenant',
    'section_company' => "Perfil d'empresa",
    'section_users' => 'Usuaris',
    'section_stats' => 'Estadístiques',
    'section_storage' => 'Emmagatzematge',

    // Company fields
    'legal_name' => 'Raó social',
    'trade_name' => 'Nom comercial',
    'address' => 'Adreça',
    'phone' => 'Telèfon',
    'tax_regime' => 'Règim fiscal',
    'enabled' => 'Activat',
    'disabled' => 'Desactivat',
    'no_company_profile' => "Sense perfil d'empresa configurat.",

    // Statistics
    'stat_invoices' => 'Factures emeses',
    'stat_quotes' => 'Pressupostos',
    'stat_delivery_notes' => 'Albarans',
    'stat_rectificatives' => 'Rectificatives',
    'stat_purchase_invoices' => 'Factures rebudes',
    'stat_clients' => 'Clients',
    'stat_products' => 'Productes',
    'stat_expenses' => 'Despeses',
    'revenue_this_year' => "Facturació d'enguany",
    'last_document' => 'Últim document',

    // Storage & Backups
    'disk_usage' => 'Espai en disc',
    'last_backup' => 'Última còpia',
    'no_backup' => 'Sense còpies de seguretat',
    'col_backups' => 'Còpies',
    'backups_count' => 'Còpies de seguretat',
    'backups_total_size' => 'Mida total còpies',
    'backups_list' => 'Còpies de seguretat',
    'backup_file' => 'Arxiu',
    'backup_date' => 'Data',
    'backup_type' => 'Tipus',
    'backup_size' => 'Mida',
    'backup_type_full' => 'Completa',
    'backup_type_tenant' => 'Empresa',

    // Roles
    'role_owner' => 'Propietari',
    'role_admin' => 'Administrador',
    'role_accountant' => 'Comptable',
    'role_viewer' => 'Visor',
];
