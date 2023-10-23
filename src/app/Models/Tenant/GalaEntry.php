<?php

namespace App\Models\Tenant;

use App\Enums\BalanceTopUpStatus;
use App\Interfaces\PaidObject;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property Gala $gala
 * @property Member $member
 * @property int $EntryID
 * @property int $GalaID
 * @property int $MemberID
 * @property bool $EntryProcessed
 * @property bool $TimesRequired
 * @property bool $TimesProvided
 * @property float $FeeToPay
 * @property int $amount
 * @property bool $Charged
 * @property bool $Refunded
 * @property bool $Locked
 * @property bool $Vetoable
 * @property int $AmountRefunded
 * @property int $StripePayment
 * @property bool $Approved
 * @property int $PaymentID
 * @property int $ProcessingFee
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class GalaEntry extends Model implements PaidObject
{
    use BelongsToPrimaryModel, HasFactory;

    protected $primaryKey = 'EntryID';

    protected $table = 'galaEntries';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'EntryProcessed' => false,
        'TimesRequired' => false,
        'TimesProvided' => false,
        'FeeToPay' => 0,
        'Charged' => false,
        'Refunded' => false,
        'Locked' => false,
        'AmountRefunded' => 0,
        'Approved' => false,
        'ProcessingFee' => 0,
        '25Free' => false,
        '50Free' => false,
        '100Free' => false,
        '200Free' => false,
        '400Free' => false,
        '800Free' => false,
        '1500Free' => false,
        '25Back' => false,
        '50Back' => false,
        '100Back' => false,
        '200Back' => false,
        '25Breast' => false,
        '50Breast' => false,
        '100Breast' => false,
        '200Breast' => false,
        '25Fly' => false,
        '50Fly' => false,
        '100Fly' => false,
        '200Fly' => false,
        '100IM' => false,
        '150IM' => false,
        '200IM' => false,
        '400IM' => false,
        '25FreeTime' => null,
        '50FreeTime' => null,
        '100FreeTime' => null,
        '200FreeTime' => null,
        '400FreeTime' => null,
        '800FreeTime' => null,
        '1500FreeTime' => null,
        '25BackTime' => null,
        '50BackTime' => null,
        '100BackTime' => null,
        '200BackTime' => null,
        '25BreastTime' => null,
        '50BreastTime' => null,
        '100BreastTime' => null,
        '200BreastTime' => null,
        '25FlyTime' => null,
        '50FlyTime' => null,
        '100FlyTime' => null,
        '200FlyTime' => null,
        '100IMTime' => null,
        '150IMTime' => null,
        '200IMTime' => null,
        '400IMTime' => null,
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'MemberID', 'MemberID');
    }

    public function gala(): BelongsTo
    {
        return $this->belongsTo(Gala::class, 'GalaID', 'GalaID');
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'gala';
    }

    public function handlePaid($line): void
    {
        $this->Charged = true;
        $this->save();
    }

    public function handleChargedBack(): void
    {
        // TODO: Implement handleChargedBack() method.
    }

    public function handleRefund(int $refundAmount, int $totalAmountRefunded): void
    {
        $this->Refunded = true;
        $this->AmountRefunded = $this->AmountRefunded + $refundAmount;
        $this->save();
    }

    public function getPaymentLineDescriptor(): string
    {
        return "Entry to {$this->gala->GalaName} for {$this->member->name}";
    }

    public function handleFailed(): void
    {
        //        $this->status = BalanceTopUpStatus::FAILED;
        //        $this->save();
    }

    public function handleCanceled(): void
    {
        //        $this->status = BalanceTopUpStatus::FAILED;
        //        $this->save();
    }

    /**
     * Get or set the fee as an integer.
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => BigDecimal::of((string) $attributes['FeeToPay'])->withPointMovedRight(2)->toInt(),
            set: fn ($value) => [
                'FeeToPay' => BigDecimal::of($value)->withPointMovedLeft(2),
            ],
        );
    }
}
