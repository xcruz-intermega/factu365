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

    // Mail settings
    'mail_settings_title' => 'Email Configuration',
    'mail_settings_smtp_section' => 'SMTP Server',
    'mail_settings_host' => 'SMTP Host',
    'mail_settings_port' => 'Port',
    'mail_settings_encryption' => 'Encryption',
    'mail_settings_username' => 'Username',
    'mail_settings_password' => 'Password',
    'mail_settings_password_placeholder' => 'Leave empty to keep current',
    'mail_settings_from_address' => 'From email',
    'mail_settings_from_name' => 'From name',
    'mail_settings_is_active' => 'Service active',
    'mail_settings_test_button' => 'Test connection',
    'mail_settings_test_success' => 'SMTP connection successful.',
    'mail_settings_test_failed' => 'Connection error: :error',
    'mail_settings_last_test' => 'Last successful test',
    'mail_settings_not_tested' => 'Not tested',
    'mail_settings_saved' => 'Email configuration saved.',
    'mail_settings_guide_title' => 'Configuration guide',
    'mail_settings_guide_provider' => 'Recommended provider',
    'mail_settings_guide_provider_desc' => '300 free emails/day, EU servers, GDPR compliant.',
    'mail_settings_guide_step1' => 'Create a Brevo account and verify your domain',
    'mail_settings_guide_step2' => 'Go to Settings → SMTP & API and get the SMTP key',
    'mail_settings_guide_step3' => 'Enter the details in the form above',
    'mail_settings_guide_dns' => 'Required DNS records',
    'mail_settings_guide_spf' => 'Add a TXT record in your DNS with your provider\'s SPF value.',
    'mail_settings_guide_dkim' => 'Copy the DKIM record from your email provider\'s dashboard.',
    'mail_settings_guide_dmarc' => 'Add a TXT record for DMARC. Adjust the email address to receive reports.',
    'mail_settings_guide_verify' => 'Verify deliverability',
    'mail_settings_guide_verify_desc' => 'Send a test email to mail-tester.com to verify the configuration. Target: 9+/10.',
    'mail_settings_encryption_tls' => 'TLS',
    'mail_settings_encryption_ssl' => 'SSL',
    'mail_settings_encryption_none' => 'None',
];
