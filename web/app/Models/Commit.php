<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Commit class
 *
 * @property int $id
 * @property int $app_id
 * @property string $branch_name
 * @property string $sha
 * @property float $coverage
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Commit extends Model
{
    public $timestamps = true;
    protected $guarded = [];

    public function app (): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
