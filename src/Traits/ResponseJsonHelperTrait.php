<?php

namespace srgafanhoto\PatternRepository\Traits;

use Response;

/**
 * Class SluggableScopeHelpers
 *
 * Helper trait for defining the primary slug of a model
 * and providing useful scopes and query methods.
 *
 * @package Cviebrock\EloquentSluggable
 */
trait ResponseJsonHelperTrait
{

    /**
     * @param mixed   $result
     * @param string|null  $message
     * @param integer  $code
     *
     * @return array
     */
    public function sendResponse($result, $message = null, $code = 200)
    {
        return Response::json($this->makeResponse($message, $result), $code);
    }

    /**
     * @param string $message
     * @param mixed  $data
     *
     * @return array
     */
    public static function makeResponse($message, $data)
    {
        return [
            'success' => true,
            'data'    => $data,
            'message' => $message ?: trans('str.dadosRetornadosSucesso'),
        ];
    }

    /**
     * @param string $message
     * @param array  $data
     * @param array  $errors
     *
     * @return array
     */
    public static function makeError($message, array $data = [], array $errors = [])
    {
        $res = [
            'success' => false,
            'message' => $message ?: trans('str.erroAoRealizarOperacao'),
            'errors' => $errors
        ];

        if (!empty($data)) {
            $res['data'] = $data;
        }

        return $res;
    }

    /**
     * @param $code
     * @param $message
     * @param array $data
     * @param array  $errors
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($code, $message, array $data = [], array $errors = [])
    {

        return Response::json($this->makeError($message, $data, $errors), $code);

    }

}
