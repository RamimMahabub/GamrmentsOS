<?php

return [
    ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'grid'],
    ['label' => 'Yarn Inventory', 'route' => 'yarns.index', 'active' => 'yarns.*', 'icon' => 'spool'],
    ['label' => 'Companies', 'route' => 'companies.index', 'active' => 'companies.*', 'icon' => 'building'],
    ['label' => 'Knitting', 'route' => 'knitting-jobs.index', 'active' => 'knitting-jobs.*', 'icon' => 'layers'],
    ['label' => 'Dyeing', 'route' => 'dyeing-jobs.index', 'active' => 'dyeing-jobs.*', 'icon' => 'drop'],
    ['label' => 'Cutting (Body)', 'route' => 'cutting.body.index', 'active' => 'cutting.body.*', 'icon' => 'layers'],
    ['label' => 'Cutting (Neck)', 'route' => 'cutting.neck.index', 'active' => 'cutting.neck.*', 'icon' => 'layers'],
    ['label' => 'Cutting (Rib)', 'route' => 'cutting.rib.index', 'active' => 'cutting.rib.*', 'icon' => 'layers'],
    ['label' => 'Staffs', 'route' => 'staffs.index', 'active' => 'staffs.*', 'icon' => 'building'],
    ['label' => 'Settings', 'route' => 'settings.edit', 'active' => 'settings.*', 'icon' => 'settings'],
];
