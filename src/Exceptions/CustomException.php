<?php

namespace srgafanhoto\PatternRepository\Exceptions;

class CustomException extends \Exception
{
    /**
     * @var array
     */
    private $data;

    /**
     * CustomException constructor.
     * @param $message
     * @param array $data
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message, array $data = [], $code = 0, \Exception $previous = null)
    {
        $this->data = $data;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
