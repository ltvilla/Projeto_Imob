<?php

namespace TemplateInicial;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $table = 'facitilies';

    public function properties() 
    {
        return $this->belongsToMany(Property::class, 'facitilies_properties', 'facilityId', 'propertyId');
    }
}