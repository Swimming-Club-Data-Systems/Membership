<?php

namespace App\Models\Tenant;

use App\Enums\ManualPaymentEntryLineType;
use App\Models\Accounting\Journal;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property integer $id
 * @property ManualPaymentEntry $manualPaymentEntry
 * @property string $description
 * @property integer $credit
 * @property integer $debit
 * @property string $credit_string
 * @property string $debit_string
 * @property ManualPaymentEntryLineType $line_type
 * @property ManualPaymentEntryLineType $line_opposite_type
 * @property Journal $accountingJournal
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ManualPaymentEntryLine extends Model
{
    use HasFactory, BelongsToPrimaryModel;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'type' => ManualPaymentEntryLineType::class,
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'credit' => 0,
        'debit' => 0,
    ];

    public function manualPaymentEntry(): BelongsTo
    {
        return $this->belongsTo(ManualPaymentEntry::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'manualPaymentEntry';
    }

    public function accountingJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'accounting_journal_id');
    }

    /**
     * Get the transaction type.
     *
     * @return Attribute
     */
    protected function lineType(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['credit'] > 0 ? ManualPaymentEntryLineType::CREDIT : ManualPaymentEntryLineType::DEBIT,
        );
    }

    /**
     * Get the opposite transaction type (for the other journal in a double entry transaction).
     *
     * @return Attribute
     */
    protected function lineOppositeType(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['debit'] > 0 ? ManualPaymentEntryLineType::CREDIT : ManualPaymentEntryLineType::DEBIT,
        );
    }

    /**
     * Get or set the credit amount as a string.
     *
     * @return Attribute
     */
    protected function creditString(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => (string)BigDecimal::of((string)$attributes['credit'])->withPointMovedLeft(2),
            set: fn($value, $attributes) => [
                'credit' => BigDecimal::of($value)->withPointMovedRight(2)->toInt()
            ],
        );
    }

    /**
     * Get or set the debit amount as a string.
     *
     * @return Attribute
     */
    protected function debitString(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => (string)BigDecimal::of((string)$attributes['debit'])->withPointMovedLeft(2),
            set: fn($value, $attributes) => [
                'debit' => BigDecimal::of($value)->withPointMovedRight(2)->toInt()
            ],
        );
    }
}
