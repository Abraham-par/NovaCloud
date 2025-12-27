<?php
// One-off admin creation script for local development only.
// Run from browser or CLI: visit http://localhost/NovaCloudV2/create_admin.php

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/database.php';

$functions = new NovaCloudFunctions();
$db = Database::getInstance();

// Fixed admin accounts to ensure (idempotent)
$admins = [
    [
        'email' => 'aparadox@email.com',
        'username' => 'aparadox',
        'password' => 'AdminPas1',
        'full_name' => 'A Paradox'
    ],
    [
        'email' => 'Aabraham@email.com',
        'username' => 'aabraham',
        'password' => 'AdminPas2',
        'full_name' => 'Aabraham'
    ]
];

$results = [];

foreach ($admins as $adm) {
    $existing = $db->getRow('users', '*', 'email = ?', [$adm['email']]);

    if ($existing) {
        // Update existing account: ensure username, password, and admin flags
        $hashed = password_hash($adm['password'], PASSWORD_DEFAULT);
        $updateData = [
            'username' => $adm['username'],
            'password' => $hashed,
            'user_type' => 'admin',
            'account_status' => 'active'
        ];
        $ok = $db->update('users', $updateData, 'id = ?', [$existing['id']]);
        $results[] = [ 'email' => $adm['email'], 'action' => $ok ? 'updated' : 'update_failed' ];
        continue;
    }

    // If no user with that email, check for username conflict and remove/rename if necessary
    $conflict = $db->getRow('users', '*', 'username = ?', [$adm['username']]);
    if ($conflict) {
        // If username exists for another account, append timestamp to that account's username to free it
        $newName = $conflict['username'] . '_old_' . time();
        $db->update('users', ['username' => $newName], 'id = ?', [$conflict['id']]);
    }

    // Register new admin user
    $data = [
        'username' => $adm['username'],
        'email' => $adm['email'],
        'password' => $adm['password'],
        'full_name' => $adm['full_name'],
        'security_question' => 'setup script',
        'security_answer' => 'setup'
    ];

    $created = $functions->registerUser($data);
    if ($created) {
        // Promote to admin and activate
        $row = $db->getRow('users', '*', 'email = ?', [$adm['email']]);
        if ($row && isset($row['id'])) {
            $db->update('users', ['user_type' => 'admin', 'account_status' => 'active'], 'id = ?', [$row['id']]);
            $results[] = [ 'email' => $adm['email'], 'action' => 'created' ];
        } else {
            $results[] = [ 'email' => $adm['email'], 'action' => 'created_but_not_found' ];
        }
    } else {
        $results[] = [ 'email' => $adm['email'], 'action' => 'create_failed' ];
    }
}

// Output results
foreach ($results as $r) {
    echo htmlspecialchars($r['email']) . ': ' . htmlspecialchars($r['action']) . "\n";
}

echo "\nIMPORTANT: This script is for local/dev use only. Remove or protect create_admin.php in production.\n";

if (php_sapi_name() !== 'cli') {
    echo "<pre>\n";
    foreach ($results as $r) {
        echo htmlspecialchars($r['email']) . ': ' . htmlspecialchars($r['action']) . "\n";
    }
    echo "</pre>";
}

exit;
