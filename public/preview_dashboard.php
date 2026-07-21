<?php
session_start();
$_SESSION['usuario_id']=1;
$_SESSION['nombre_completo']='Marcos Administrador';
$_SESSION['rol_id']=1;
$_SESSION['cliente_nombre']='Supermercado Don José';
require __DIR__ . '/../src/Application/Views/dashboard.php';
