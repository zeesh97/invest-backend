<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DependentCrud extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'parent_id', 'company_id', 'data'];

    protected $casts = [
        'data' => 'array',
    ];
    public function setDataAttribute($value)
    {
        $data = [];

        foreach ($value as $array_item) {
            if (!is_null($array_item['value'])) {
                $data[] = $array_item;
            }
        }

        $this->attributes['data'] = json_encode($data);
    }
    public function parent()
    {
        return $this->belongsTo(DependentCrud::class, 'parent_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function children()
    {
        return $this->hasMany(DependentCrud::class, 'parent_id');
    }
}
