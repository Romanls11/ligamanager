<?php

namespace App\Models;

use App\Enums\EstadoTorneo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Torneo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'estado' => EstadoTorneo::class,
        ];
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }
}
