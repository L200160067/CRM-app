<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'city',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ClientService::class);
    }

    /**
     * Get the formatted WhatsApp URL for the client's phone number.
     */
    protected function whatsappUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->phone)) {
                    return null;
                }

                $phone = preg_replace('/[^0-9]/', '', $this->phone);

                if (empty($phone)) {
                    return null;
                }

                if (str_starts_with($phone, '0')) {
                    $phone = '62'.substr($phone, 1);
                } elseif (str_starts_with($phone, '8')) {
                    $phone = '62'.$phone;
                }

                return 'https://wa.me/'.$phone;
            }
        );
    }
}
