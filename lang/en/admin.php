<?php

return [
    // Auth
    'login' => 'Log in',
    'not_configured' => 'Super admin access is not configured.',
    'invalid_credentials' => 'The provided credentials are incorrect.',

    // Dashboard
    'dashboard_title' => 'Admin Panel',
    'total_tenants' => 'Total companies',
    'active_tenants' => 'Active',
    'suspended_tenants' => 'Suspended',
    'search_placeholder' => 'Search by slug, name, NIF or email...',

    // Table columns
    'col_slug' => 'Slug',
    'col_company' => 'Company',
    'col_nif' => 'NIF/CIF',
    'col_owner' => 'Owner',
    'col_users' => 'Users',
    'col_invoices' => 'Invoices',
    'col_disk' => 'Disk',
    'col_created' => 'Created',
    'col_status' => 'Status',
    'col_name' => 'Name',
    'col_role' => 'Role',
    'col_locale' => 'Language',

    // Statuses
    'status_active' => 'Active',
    'status_suspended' => 'Suspended',

    // Actions
    'view_detail' => 'View detail',
    'suspend' => 'Suspend',
    'unsuspend' => 'Reactivate',
    'reset_password' => 'Reset password',
    'delete_tenant' => 'Delete company',
    'access_tenant' => 'Access',
    'confirm_delete' => 'Delete permanently',

    // Password modal
    'new_password' => 'New password',
    'reset_password_desc' => 'Set a new password for the owner of :slug.',
    'password_reset_success' => 'Owner password updated successfully.',

    // Delete modal
    'delete_tenant_warning' => 'This action will permanently delete the company, its database and all its files. This action CANNOT be undone.',
    'delete_confirm_type' => 'Type ":slug" to confirm deletion.',

    // Flash messages
    'tenant_suspended' => 'Company suspended successfully.',
    'tenant_unsuspended' => 'Company reactivated successfully.',
    'tenant_deleted' => 'Company deleted successfully.',

    // Detail page
    'tenant_detail' => 'Company detail',
    'section_tenant_info' => 'Tenant information',
    'section_company' => 'Company profile',
    'section_users' => 'Users',
    'section_stats' => 'Statistics',
    'section_storage' => 'Storage',

    // Company fields
    'legal_name' => 'Legal name',
    'trade_name' => 'Trade name',
    'address' => 'Address',
    'phone' => 'Phone',
    'tax_regime' => 'Tax regime',
    'enabled' => 'Enabled',
    'disabled' => 'Disabled',
    'no_company_profile' => 'No company profile configured.',

    // Statistics
    'stat_invoices' => 'Issued invoices',
    'stat_quotes' => 'Quotes',
    'stat_delivery_notes' => 'Delivery notes',
    'stat_rectificatives' => 'Rectificatives',
    'stat_purchase_invoices' => 'Received invoices',
    'stat_clients' => 'Clients',
    'stat_products' => 'Products',
    'stat_expenses' => 'Expenses',
    'revenue_this_year' => 'Revenue this year',
    'last_document' => 'Last document',

    // Storage & Backups
    'disk_usage' => 'Disk usage',
    'last_backup' => 'Last backup',
    'no_backup' => 'No backups',
    'col_backups' => 'Backups',
    'backups_count' => 'Backups',
    'backups_total_size' => 'Total backup size',
    'backups_list' => 'Backups',
    'backup_file' => 'File',
    'backup_date' => 'Date',
    'backup_type' => 'Type',
    'backup_size' => 'Size',
    'backup_type_full' => 'Full',
    'backup_type_tenant' => 'Tenant',

    // Roles
    'role_owner' => 'Owner',
    'role_admin' => 'Administrator',
    'role_accountant' => 'Accountant',
    'role_viewer' => 'Viewer',
];
