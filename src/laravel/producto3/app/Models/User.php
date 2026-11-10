<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Laravel buscará automáticamente 'laravel_usuarios' gracias al prefijo global
    protected $table = 'usuarios'; 

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol', // 'admin', 'tecnico', 'particular','gestora'
        'telefono',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Métodos auxiliares para verificar roles rápidamente
    public function isAdmin()
    {
        return $this->rol === 'admin';
    }

    public function isTecnico()
    {
        return $this->rol === 'tecnico';
    }

    public function isParticular()
    {
        return $this->rol === 'particular';
    }

    public function isGestora()
    {
        return $this->rol === 'gestora';
    }
}
