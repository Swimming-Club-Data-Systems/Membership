<?php

// Get the user's access levels
$rawPermissions = app()->user->getPermissions();
$permissions = [];

foreach ($rawPermissions as $permission) {
    $disabled = $permission == $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

    $permissions[] = [
        'name' => $permission,
        'current' => $permission == $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'],
    ];
}

header('content-type: application/json');
echo json_encode($permissions);