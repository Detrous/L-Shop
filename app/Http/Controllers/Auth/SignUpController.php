<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\User\UsernameAlreadyExistsException;
use App\Exceptions\User\EmailAlreadyExistsException;
use App\Exceptions\User\UnableToCreateUser;
use App\Http\Requests\SignUpRequest;
use App\Http\Controllers\Controller;
use App\Services\Registrar;
use App\Traits\Validator;
use Illuminate\Container\Container;
use Illuminate\Http\Request;

/**
 * Class SignUpController
 *
 * @author D3lph1 <d3lph1.contact@gmail.com>
 *
 * @package App\Http\Controllers\Auth
 */
class SignUpController extends Controller
{
    /**
     * Render the sign up page.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render(Request $request)
    {
        if ((bool)s_get('shop.enable_signup')) {
            return view('auth.signup');
        }
        $this->msg->warning(__('messages.auth.signup.disabled'));

        return response()->redirectToRoute('servers');
    }

    /**
     * Register new user.
     *
     * @param SignUpRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signup(SignUpRequest $request)
    {
        if (!s_get('shop.enable_signup')) {
            return response()->redirectToRoute('signin');
        }

        $username = $request->get('username');
        $email = $request->get('email');
        $password = $request->get('password');
        $forceActivate = !(bool)s_get('auth.email_activation');

        // Get registrar service from container.
        /** @var Registrar $registrar */
        $registrar = $this->app->make('registrar');

        try {;
            // Call registrar service method.
            $registrar->register($username, $email, $password, 0, $forceActivate, false);
        } catch (UsernameAlreadyExistsException $e) {

            $this->msg->danger(__('messages.auth.signup.username_already_exists', ['username' => $username]));
            return back();

        } catch (EmailAlreadyExistsException $e) {

            $this->msg->danger(__('messages.auth.signup.email_already_exists', ['email' => $email]));
            return back();

        } catch (UnableToCreateUser $e) {

            $this->msg->danger(__('messages.auth.signup.fail'));
            return back();
        }

        return $this->redirect(!$forceActivate);
    }

    /**
     * @param bool $activate
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirect($activate)
    {
        if (s_get('auth.signup.redirect')) {
            return response()->redirectTo(s_get('auth.signup.redirect_url'));
        }

        if ($activate) {
            return response()->redirectToRoute('activation.wait');
        }else {
            $this->msg->success(__('messages.auth.signup.success'));

            return response()->redirectToRoute('signin');
        }
    }
}
