<?php

namespace App\Infrastructure;

use PDO;
use PDOException;

class Database {
    private ?PDO $connection = null;

    public function getConnection(): PDO {
        // Si ya estamos conectados, no volvemos a conectarnos (ahorra recursos)
        if ($this->connection === null) {
            $config = require __DIR__ . '/../../config/database.php';
            
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            
            try {
                // PDO nos protege automáticamente de muchos ataques
                $this->connection = new PDO($dsn, $config['user'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Si XAMPP está apagado o el nombre de la BD está mal, el sistema avisa elegante
                die("Error de conexión a la base de datos MACARO: " . $e->getMessage());
            }
        }

        return $this->connection;
    }
}