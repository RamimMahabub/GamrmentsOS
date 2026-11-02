<?php

return [
    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'grid'],
    ['label' => 'Yarn Inventory', 'route' => 'yarns.index', 'active' => 'yarns.*', 'icon' => 'spool'],
    ['label' => 'Companies', 'route' => 'companies.index', 'active' => 'companies.*', 'icon' => 'building'],
    ['label' => 'Knitting', 'route' => 'knitting-jobs.index', 'active' => 'knitting-jobs.*', 'icon' => 'layers'],
    ['label' => 'Dyeing', 'route' => 'dyeing-jobs.index', 'active' => 'dyeing-jobs.*', 'icon' => 'drop'],
    ['label' => 'Cutting', 'route' => 'cutting.body.index', 'active' => 'cutting.*', 'icon' => 'scissors'],
    ['label' => 'Sewing', 'route' => 'sewing-jobs.index', 'active' => 'sewing-jobs.*', 'icon' => 'factory'],
    ['label' => 'Staffs', 'route' => 'staffs.index', 'active' => 'staffs.*', 'icon' => 'users'],
    ['label' => 'Settings', 'route' => 'settings.edit', 'active' => 'settings.*', 'icon' => 'settings'],
];
