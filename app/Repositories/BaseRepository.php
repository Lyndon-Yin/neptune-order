<?php
namespace App\Repositories;


use Lyndon\Repository\Criteria\RequestCriteria;

/**
 * Class BaseRepository
 * @package App\Repositories
 */
abstract class BaseRepository extends \Lyndon\Repository\Eloquent\BaseRepository
{
    /**
     * BaseRepository constructor.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Lyndon\Exceptions\RepositoryException
     */
    public function __construct()
    {
        parent::__construct(app());
    }

    /**
     * 启用标准查询RequestCriteria
     *
     * @throws \Lyndon\Exceptions\RepositoryException
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
