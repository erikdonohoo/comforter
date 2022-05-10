<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App model
 *
 * @property int $id
 * @property string $name
 * @property string $namespace
 * @property string $repo_path
 * @property int $gitlab_project_id
 * @property string $primary_branch_name
 * @property float $coverage
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property string $coverage
 *
 * @property Collection $commits
 */
class App extends Model
{
    use SoftDeletes, HasFactory;

    public $timestamps = true;
    protected $guarded = [];
    protected $appends = [
        'coverage',
        'app_domain'
    ];

    public function commits (): HasMany
    {
        return $this->hasMany(Commit::class);
    }

    public function getAppDomainAttribute ()
    {
        return config('app.gitlab.domain');
    }

    public function getLatestCommit (): ?Commit
    {
        return $this->commits()
            ->whereBranchName($this->primary_branch_name)
            ->orderByDesc('updated_at')
            ->first();
    }

    public function getCoverageAttribute (): string
    {
        $commit = $this->getLatestCommit();
        return $commit ? $commit->coverage : '0.0';
    }
}
