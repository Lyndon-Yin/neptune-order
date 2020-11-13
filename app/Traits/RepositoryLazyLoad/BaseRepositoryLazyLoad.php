<?php
namespace App\Traits\RepositoryLazyLoad;

/**
 * Trait BaseRepositoryLazyLoad
 * @package App\Traits\RepositoryLazyLoad
 */
trait BaseRepositoryLazyLoad
{
    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (! isset($this->repository[$name])) {
            throw new \Exception($name . ' Undefined');
        }

        if (empty($this->$name)) {
            $className = $this->repository[$name];
            $this->$name = new $className();
        }

        return $this->$name;
    }
}
