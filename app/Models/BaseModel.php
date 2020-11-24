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

    protected $guarded = ['id'];

    /**
     * 重写，使时间格式正确展示
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->format($this->getDateFormat());
    }
}
