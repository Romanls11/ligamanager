<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'logo',
        'color_uniforme',
        'delegado_user_id',
        'telefono_contacto',
    ];

    public function delegado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegado_user_id');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }
}
