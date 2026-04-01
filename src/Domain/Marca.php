<?php

namespace App\Domain;

class Marca {
    private ?int $id;
    private string $nombre;

    public function __construct(string $nombre, ?int $id = null) {
        $this->nombre = $nombre;
        $this->id = $id;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }
}