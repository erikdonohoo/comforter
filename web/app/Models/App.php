<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App model
 *
 * @property int $id
 * @property string $name
 * @property int $gitlab_project_id
 * @property string $primary_branch_name
 * @property float $coverage
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class App extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    protected $guarded = [];
    protected $appends = ['coverage'];

    public function commits (): HasMany
    {
        return $this->hasMany(Commit::class);
    }

    public function getLatestCommit (): Commit
    {
        return $this->commits()
            ->whereBranchName($this->primary_branch_name)
            ->orderByDesc('created_at')
            ->first();
    }

    public function getCoverageAttribute (): string
    {
        $commit = $this->getLatestCommit();
        return $commit ? $commit->coverage : '0.0';
    }
}
