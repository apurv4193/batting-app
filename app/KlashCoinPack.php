<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DB;
use Config;

class KlashCoinPack extends Model
{
  use Notifiable;
  use SoftDeletes;

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'klash_coin_pack';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = [];

  /**
   * The attributes that are dates
   *
   * @var array
   */
  protected $dates = ['deleted_at'];

  /**
   * Insert and Update KlashCoinPack
   */
  public function insertUpdate($data)
  {
    if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
    {
      $getData = KlashCoinPack::find($data['id']);
      $getData->update($data);
      return KlashCoinPack::find($data['id']);
    }
    else
    {
      return KlashCoinPack::create($data);
    }
  }

  public function getAll($filters = array(), $paginate = false)
  {
    $getData = KlashCoinPack::whereNull('deleted_at')->orderBy('name', 'DESC');

    if(isset($filters) && !empty($filters))
    {
        if(isset($filters['status']) && !empty($filters['status']))
        {
            $getData->where('status', $filters['status']);
        }
    }
    if(isset($paginate) && $paginate == true)
    {
      return $response = $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
    }
    else
    {
      return $response = $getData->get();
    }
  }

  public function companyDocuments()
  {
    return $this->hasMany('App\CompanyDocuments', 'company_id');
  }

}
