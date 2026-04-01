<?php

namespace App\Domain;

class Usuario {
    private ?int $id;
    private string $nombreCompleto;
    private string $usuario;
    private string $password;
    private int $rolId;
    private bool $estado;

    public function __construct(
        string $nombreCompleto, 
        string $usuario, 
        string $password, 
        int $rolId, 
        bool $estado = true, 
        ?int $id = null
    ) {
        $this->nombreCompleto = $nombreCompleto;
        $this->usuario = $usuario;
        $this->password = $password;
        $this->rolId = $rolId;
        $this->estado = $estado;
        $this->id = $id;
    }

    // Métodos para leer los datos de forma segura
    public function getId(): ?int {
        return $this->id;
    }

    public function getNombreCompleto(): string {
        return $this->nombreCompleto;
    }

    public function getUsuario(): string {
        return $this->usuario;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getRolId(): int {
        return $this->rolId;
    }

    public function isActivo(): bool {
        return $this->estado;
    }
}