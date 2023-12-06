<?php

namespace App\Models\Tenant;

use App\Models\Central\Tenant;
use App\Traits\UuidIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property int $user
 * @property \DateTime $created
 * @property int $creator
 * @property Date $start
 * @property int $charge_outstanding
 * @property int $charge_pro_rata
 * @property string $welcome_text
 * @property string $token
 * @property string $token_on
 * @property string $status
 * @property Date $due_date
 * @property \DateTime $completed_at
 */
class OnboardingSession extends Model
{
    use BelongsToPrimaryModel, UuidIdentifier;

    protected $table = 'onboardingSessions';

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'UserID');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator', 'UserID');
    }

    public static function getDefaultStages(): array
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        return [
            'account_details' => [
                'required' => true,
                'completed' => false,
                'required_locked' => true,
                'metadata' => [],
                'revisitable' => true,
            ],
            'address_details' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
                'revisitable' => true,
            ],
            'communications_options' => [
                'required' => true,
                'completed' => false,
                'required_locked' => true,
                'metadata' => [],
                'revisitable' => true,
            ],
            'emergency_contacts' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
                'revisitable' => true,
            ],
            'member_forms' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
                'revisitable' => true,
            ],
            'parent_conduct' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
                'revisitable' => true,
            ],
            'data_privacy_agreement' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
                'revisitable' => true,
            ],
            'terms_agreement' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
                'revisitable' => true,
            ],
            'direct_debit_mandate' => [
                'required' => boolval($tenant->getOption('USE_DIRECT_DEBIT')),
                'completed' => false,
                'required_locked' => ! ($tenant->getOption('USE_DIRECT_DEBIT')),
                'metadata' => [],
                'revisitable' => true,
            ],
            'fees' => [
                'required' => true,
                'completed' => false,
                'required_locked' => false,
                'metadata' => [],
                'revisitable' => true,
            ],
        ];
    }

    public static function getDefaultRenewalStages(): array
    {
        $stages = self::getDefaultStages();
        $stages['account_details']['required'] = false;
        $stages['account_details']['required_locked'] = true;

        return $stages;
    }

    public static function stages(): array
    {
        return [
            'account_details' => 'Set your account password',
            'address_details' => 'Tell us your address',
            'communications_options' => 'Tell us your communications options',
            'emergency_contacts' => 'Tell us your emergency contact details',
            'member_forms' => 'Complete member information',
            'parent_conduct' => 'Agree to the parent/guardian Code of Conduct',
            'data_privacy_agreement' => 'Data Privacy Agreement',
            'terms_agreement' => 'Agree to the terms and conditions of club membership',
            'direct_debit_mandate' => 'Set up a Direct Debit Instruction',
            'fees' => 'Pay your registration fees',
        ];
    }

    public static function stagesOrder(): array
    {
        return [
            'account_details',
            'address_details',
            'communications_options',
            'emergency_contacts',
            'member_forms',
            'parent_conduct',
            'data_privacy_agreement',
            'terms_agreement',
            'direct_debit_mandate',
            'fees',
        ];
    }
}
