<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GorohDaroei extends Model
{
    protected $table = 'goroh_daroei';
    protected $primaryKey = 'cod';
    public $incrementing = true;
    protected $keyType = 'integer';
    
    public function drugInfos()
    {
        return $this->hasMany(DrugInfo::class, 'goroh_daroei_cod', 'cod');
    }
}
