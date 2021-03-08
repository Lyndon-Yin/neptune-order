<?php
namespace App\Repositories\Orders;


use App\Models\Orders\OrderMailingModel;

/**
 * Class OrderMailingRepository
 * @package App\Repositories\Orders
 */
class OrderMailingRepository extends BaseOrderRepository
{
    public function model()
    {
        return OrderMailingModel::class;
    }

    /**
     * 重新获取多行数据方法
     *
     * @param array $primaryKeys
     * @param array $extraWhere
     * @param string $trashed
     * @return array
     */
    public function getRepoListByPrimaryKeys($primaryKeys, $extraWhere = [], $trashed = '')
    {
        $extraWhere = array_merge($this->scopeQuery, $extraWhere);

        // 获取表所有字段列表
        $select = $this->model->tableColumn;
        // 去除位置字段
        unset($select['point']);
        // 增加经纬度
        $select = array_keys($select);
        array_push($select, 'x(point) point_lng', 'y(point) point_lat');

        return $this->model
            ->selectRaw(implode(',', $select))
            ->whereIn('order_id', $primaryKeys)
            ->when(! empty($extraWhere), function ($query) use ($extraWhere) {
                return $query->where($extraWhere);
            })
            ->when(! empty($trashed), function ($query) use ($trashed) {
                return $query->$trashed();
            })
            ->get()->toArray();
    }

    /**
     * 重写根据主键获取数据行
     *
     * @param mixed $primaryKey
     * @param array $extraWhere
     * @param string $trashed
     * @return array
     */
    public function getRepoRowByPrimaryKey($primaryKey, array $extraWhere = [], $trashed = '')
    {
        $extraWhere = array_merge(['order_id' => $primaryKey], $this->scopeQuery, $extraWhere);

        // 获取表所有字段列表
        $select = $this->model->tableColumn;
        // 去除位置字段
        unset($select['point']);
        // 增加经纬度
        $select = array_keys($select);
        array_push($select, 'x(point) point_lng', 'y(point) point_lat');

        $result = $this->model
            ->selectRaw(implode(',', $select))
            ->where($extraWhere)
            ->when(! empty($trashed), function ($query) use ($trashed) {
                return $query->$trashed();
            })
            ->first();

        return empty($result) ? [] : $result->toArray();
    }
}
