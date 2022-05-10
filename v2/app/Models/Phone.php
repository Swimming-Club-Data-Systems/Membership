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

    /**
     * Modify the phone number when set.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function linkFormat(): Attribute
    {
        return new Attribute(
            get: fn () => $this->toUrl(),
        );
    }

    /**
     * Modify the phone number when set.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function localOrNational(): Attribute
    {
        return new Attribute(
            get: fn () => $this->toString(),
        );
    }

    /**
     * Return the number as local if UK, international otherwise
     * 
     * @return string
     */
    public function toString(): string
    {
        $number = PhoneNumber::parse($this->number);
        if ($number->getRegionCode() == "GB") {
            return $number->format(PhoneNumberFormat::NATIONAL);
        }
        return $number->format(PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * Return the RFC3966 tel: format of this number
     * 
     * @return string
     */
    public function toUrl(): string
    {
        $number = PhoneNumber::parse($this->number);
        return $number->format(PhoneNumberFormat::RFC3966);
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['link_format', 'local_or_national'];
}
