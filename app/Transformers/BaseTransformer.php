<?php
namespace App\Transformers;

/**
 * Class BaseTransformer
 * @package App\Transformers
 */
abstract class BaseTransformer
{
    protected $transformResults = [];

    /**
     * BaseTransformer constructor.
     * @param array $param
     * @param int $dimensional
     */
    public function __construct(array $param, $dimensional = 1)
    {
        if (empty($param)) {
            return;
        }

        switch ($dimensional) {
            case 1:
                $this->transformResults = $this->transform($param);
                break;
            case 2:
                foreach ($param as $item) {
                    $this->transformResults[] = $this->transform($item);
                }
                break;
            default:
                $this->transformResults = $param;
                break;
        }
    }

    /**
     * 结果数组形式返回
     *
     * @return array
     */
    public function toArray()
    {
        return $this->transformResults;
    }

    abstract protected function transform($param);
}
