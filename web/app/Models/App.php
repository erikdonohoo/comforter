<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App model
 *
 * @property int $id
 * @property string $name
 * @property int $gitlab_project_id
 * @property string $primary_branch_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class App extends Model
{
    use SoftDeletes;

    protected $timestamps = true;

    public function commits (): HasMany
    {
        return $this->hasMany(Commit::class);
    }

    public function getCurrentCoverage (): float
    {
        /** @var Commit $latestCommit */
        $latestCommit = $this->commits()
            ->whereBranchName($this->primary_branch_name)
            ->orderByDesc('created_at')
            ->first();

        return $latestCommit ? $latestCommit->coverage : 0;
    }
}
