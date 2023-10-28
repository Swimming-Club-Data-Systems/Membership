<?php

namespace App\Models\Tenant;

use App\Enums\PostType;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID,
 * @property int $Author,
 * @property string $Title,
 * @property string $Excerpt,
 * @property string $Path,
 * @property string $Type,
 * @property string $MIME,
 */
class Post extends Model
{
    use BelongsToTenant;

    protected $primaryKey = 'ID';

    protected $casts = [
        'type' => PostType::class,
    ];
}
