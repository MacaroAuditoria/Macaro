<?php

// config/app.php
// Interruptor único entre "estoy en mi XAMPP" y "esto está en producción".
//
// EN XAMPP (desarrollo): dejalo en true. Vas a ver los errores de PHP en
// pantalla, lo cual ayuda muchísimo a debuggear.
//
// EN EL HOSTING REAL (producción): cambialo a false ANTES de subir el
// sistema. Así, si algo falla, el visitante ve un mensaje genérico en vez
// de rutas de tu servidor o fragmentos de una consulta SQL.

return [
    'debug' => true,
];
