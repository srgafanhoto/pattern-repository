<?php

namespace srgafanhoto\PatternRepository\Traits;

use Illuminate\Http\Request;

/**
 * Class RedirectHelperTrait
 *
 * Helper trait for defining the primary slug of a model
 * and providing useful scopes and query methods.
 *
 * @package Cviebrock\EloquentSluggable
 */
trait RedirectHelperTrait
{

    /**
     * Redireciona o usuário com uma mensagem toastr de suscesso
     *
     * @param string $routeIndex Rota de listagem
     * @param string $routeCreate Rota de cadastro
     * @param Request $request
     * @param string $msg
     * @param array $dataRoute
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectStoredSuccess($routeIndex, $routeCreate, Request $request, $msg = '', $dataRoute = [])
    {

        $saveType = $request->salvar ?: 'voltar_listagem';
        $saveContinue = $saveType == 'continuar';
        $route = $saveContinue ? $routeCreate : $routeIndex;
        $dataRoute = $saveContinue ? $dataRoute : [];

        $msg = ( !empty($msg) ? $msg : trans('str.registroCadastradoSucesso') );

        return redirect(route($route, $dataRoute))->with([
            'status' => $msg,
            'toastr' => [
                'type' => 'success',
                'msg'  => $msg
            ],

        ]);

    }


    /**
     * Redireciona o usuário com uma mensagem toastr de suscesso
     *
     * @param string $routeIndex Rota de listagem
     * @param string $routeCreate Rota de cadastro
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectUpdatedSuccess($routeIndex, $routeCreate, Request $request, $msg = null, $dataRoute = [])
    {

        $saveType = $request->salvar ?: 'voltar_listagem';
        $saveContinue = $saveType == 'continuar';
        $route = $saveContinue ? $routeCreate : $routeIndex;
        $dataRoute = $saveContinue ? $dataRoute : [];

        $msg = ( !empty($msg) ? $msg : trans('str.registroEditadoSucesso') );

        return redirect(route($route, $dataRoute))->with([
            'status' => $msg,
            'toastr' => [
                'type' => 'success',
                'msg'  => $msg
            ]
        ]);

    }


    /**
     * Redireciona o usuário com uma mensagem toastr de suscesso
     *
     * @param string $route Rota de listagem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectDeletedSuccess($route, $dataRoute = [])
    {

        return redirect(route($route, $dataRoute))->with([
            'toastr' => [
                'type' => 'success',
                'msg'  => trans('str.registroExcluidoSucesso')
            ]
        ]);

    }


    /**
     * Redireciona o usuário com uma mensagem toastr de sucesso
     *
     * @param string $route Rota
     * @param string $msg Mensagem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectSuccess($route, $msg = null, $dataRoute = [])
    {

        $msg = ( !empty($msg) ? $msg : trans('str.successOperacao') );

        return redirect(route($route, $dataRoute))->with([
            'toastr' => [
                'type' => 'success',
                'msg'  => $msg
            ],
            'status' => $msg
        ]);

    }


    /**
     * Redireciona o usuário com uma mensagem toastr de erro
     *
     * @param string $route Rota
     * @param string $msg Mensagem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectError($route, $msg = ' ', $dataRoute = [])
    {

        $msg = ( !empty($msg) ? $msg : trans('str.erroOperacao') );

        return redirect(route($route, $dataRoute))->with([
            'toastr' => [
                'type' => 'error',
                'msg'  => $msg
            ],
            'statusError' => $msg
        ]);

    }


    /**
     * Redireciona o usuário com uma mensagem toastr de sucesso
     *
     * @param string $route Rota
     * @param array $dataSession Data que será passado por sessão
     * @param array $dataRoute Data que será passado por rota como, será acessada como variável
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectData($route, $dataSession = [], $dataRoute = [])
    {

        return redirect(route($route, $dataRoute))->with($dataSession);

    }


    /**
     * Redireciona o usuário com uma mensagem toastr de erro
     *
     * @param string $route Rota
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectNotFound($route)
    {

        return redirect(route($route))->with([
            'statusError' => trans('str.registroNaoExiste'),
            'toastr' => [
                'type' => 'error',
                'msg'  => trans('str.registroNaoExiste')
            ]
        ]);

    }


    /**
     * Redireciona o usuário com uma mensagem toastr de erro
     *
     * @param string $route Rota
     * @param array $toastr Toastr
     * @param array $routeData Dados passados no route
     * @example function route('name-route', $reouteData)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectWithToastr($route, $toastr, $routeData = [])
    {

        return redirect(route($route, $routeData))->with([
            'toastr' => $toastr
        ]);

    }


    /**
     * Redireciona o usuário para a página anterior com uma mensagem toastr de erro
     *
     * @param array $toastr Toastr
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectBackWithToastr($toastr)
    {

        return redirect()->back()->with([
            'toastr' => $toastr
        ]);

    }


    /**
     * Redireciona o usuário para a página anterior com uma mensagem de sucesso
     *
     * @param string $msg
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectBackSuccess($msg)
    {

        return redirect()->back()->with([
            'status' => $msg
        ]);

    }


    /**
     * Redireciona o usuário para a página anterior com uma mensagem de erro
     *
     * @param string $msg
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectBackError($msg)
    {

        return redirect()->back()->with([
            'statusError' => $msg
        ]);

    }

}
