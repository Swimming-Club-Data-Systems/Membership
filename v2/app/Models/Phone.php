<?php

namespace App\Models;

use App\Exceptions\GenericValidationFailure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberParseException;

class Phone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'number',
    ];

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Modify the phone number when set.
     *
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function number(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                try {
                    $number = PhoneNumber::parse($value, "GB");
                    return $number->format(PhoneNumberFormat::E164);
                } catch (PhoneNumberParseException $e) {
                    throw new GenericValidationFailure($e->getMessage());
                }
            },
        );
    }
}
