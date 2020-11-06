<?php
namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class BaseModel
 * @package App\Models
 */
class BaseModel extends \Lyndon\Model\BaseModel
{
    use SoftDeletes;

    protected $dateFormat = 'Y-m-d H:i:s';

    public $timestamps = false;

    protected $guarded = ['id'];
}
