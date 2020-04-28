<?php

namespace srgafanhoto\PatternRepository\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use srgafanhoto\PatternRepository\Traits\ResponseJsonHelperTrait;

class ResponseRequest extends FormRequest
{

    use ResponseJsonHelperTrait;

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        if ($this->expectsJson()) {
            return $this->sendError(422, trans('str.dadosInvalidos'), [], $errors);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }

}
