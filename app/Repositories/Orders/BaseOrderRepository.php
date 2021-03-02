<?php
namespace App\Repositories\Orders;


use App\Repositories\BaseRepository;

/**
 * Class BaseOrderRepository
 * @package App\Repositories\Orders
 */
abstract class BaseOrderRepository extends BaseRepository
{
    /**
     * 根据用户传参查询ID列表
     *
     * @param int $maxLimit
     * @param string $trashed
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Lyndon\Exceptions\RepositoryException
     */
    public function getAllIdsByCriteria($maxLimit = 1000, $trashed = '')
    {
        // repository应用范围查询和标准查询
        $this->applyScopeQuery()->applyCriteria();

        // 数据查询
        $result = $this->model
            ->when($maxLimit > 0, function ($query) use ($maxLimit) {
                return $query->limit($maxLimit);
            })
            ->when(! empty($trashed), function ($query) use ($trashed) {
                return $query->$trashed();
            })
            ->pluck('id')->toArray();

        // 重置模型
        $this->resetModel();

        return $result;
    }
}
