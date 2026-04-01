<?php

// config/database.php
// Aquí guardamos los datos de acceso a XAMPP. 
// Si un día subís el sistema a un hosting real, solo cambiás las contraseñas acá.

return [
    'host' => '127.0.0.1',
    'dbname' => 'macaro',
    'user' => 'root',
    'password' => '', // XAMPP por defecto no tiene contraseña
    'charset' => 'utf8mb4' // Esto asegura que la letra 'ñ' y los acentos se guarden bien
];