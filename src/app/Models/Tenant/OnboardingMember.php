<?php

namespace App\Models\Tenant;

use App\Traits\UuidIdentifier;
use Illuminate\Database\Eloquent\Model;

class OnboardingMember extends Model
{
    use UuidIdentifier;

    protected $table = 'onboardingSessions';

    public function getRelationshipToPrimaryModel(): string
    {
        return 'member';
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class, 'member', 'MemberID');
    }

    public static function getDefaultStages(): array
    {
        return [
            'medical_form' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
            ],
            'photography_consent' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
            ],
            'code_of_conduct' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
            ],
        ];
    }

    public static function stages(): array
    {
        return [
            'medical_form' => 'Medical form',
            'photography_consent' => 'Photography consent',
            'code_of_conduct' => 'Code of conduct',
        ];
    }

    public static function stagesOrder(): array
    {
        return [
            'medical_form',
            'photography_consent',
            'code_of_conduct',
        ];
    }
}
