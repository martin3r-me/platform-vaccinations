<?php

namespace Platform\Vaccinations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;

/**
 * Vaccination — ein Eintrag im Impfungen-Katalog (frei pflegbar). Referenziert von erbrachten
 * Leistungen (encounter Service) via morphMap-Alias 'vaccination'. Team-scoped.
 *
 * @ai.description Impfung/Impfleistung als Katalog-Referenz.
 */
class Vaccination extends Model
{
    use SoftDeletes;

    protected $table = 'vaccinations';

    protected $fillable = [
        'uuid', 'catalog_code', 'team_id', 'code', 'name', 'description',
        'status', 'position', 'created_by_user_id',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) UuidV7::generate();
            }
            if (empty($model->team_id) && auth()->check()) {
                $model->team_id = auth()->user()->currentTeam?->id;
            }
            if (empty($model->created_by_user_id) && auth()->check()) {
                $model->created_by_user_id = auth()->id();
            }
        });
    }

    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where($this->getTable() . '.team_id', $teamId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Anzeigename: "MMR · Masern-Mumps-Röteln" oder nur Name. */
    public function label(): string
    {
        return $this->code ? trim($this->code . ' · ' . $this->name) : (string) $this->name;
    }
}
