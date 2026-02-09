<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrugInfo extends Model
{
     protected $table = 'drug_info';
    
    protected $primaryKey = 'cod';
    
    public $incrementing = false;
    
    protected $keyType = 'integer';
    
    protected $fillable = [
        'cod',
        'goroh_darmani_detail_cod',
        'goroh_daroei_cod',
        'goroh_farmakologic_cod',
        'goroh_darmani_cod',
        'nam_fa',
        'nam_en',
        'mavaredmasraf',
        'meghdarmasraf',
        'masrafdarhamelegi',
        'masrafdarshirdehi',
        'manemasraf',
        'avarez',
        'tadakhol',
        'mekanismtasir',
        'nokte',
        'hoshdar',
        'sharayetnegahdari',
        'ashkal_daroei',
    ];
    
    // روابط با جدول‌های دیگر
    public function gorohDaroei()
    {
        return $this->belongsTo(GorohDaroei::class, 'goroh_daroei_cod', 'cod');
    }
    
    public function gorohDarmaniDetail()
    {
        return $this->belongsTo(GorohDarmaniDetail::class, 'goroh_darmani_detail_cod', 'cod');
    }
    
    public function gorohDarmani()
    {
        return $this->belongsTo(GorohDarmani::class, 'goroh_darmani_cod', 'cod');
    }
}
