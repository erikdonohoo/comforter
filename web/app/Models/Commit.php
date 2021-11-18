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
 * @property string $comparison_sha
 * @property float $coverage
 * @property int $total_lines
 * @property mixed $total_lines_covered
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property App $app
 */
class Commit extends Model
{
    public $timestamps = true;
    protected $guarded = [];
    protected $appends = [
        'coverage_path'
    ];

    public function app (): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function getCoveragePathAttribute (): string
    {
        return "coverage/{$this->app->name}/{$this->branch_name}";
    }

    public function getBaseCommit (): ?Commit
    {
        return static::whereSha($this->comparison_sha)->whereAppId($this->app_id)->first();
    }
}
