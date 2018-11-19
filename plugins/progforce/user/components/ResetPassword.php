<?php namespace Progforce\User\Components;

use Auth;
use Mail;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\ComponentBase;
use Progforce\User\Models\User as UserModel;

/**
 * Password reset workflow
 *
 * When a user has forgotten their password, they are able to reset it using
 * a unique token that, sent to their email address upon request.
 */
class ResetPassword extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'progforce.user::lang.reset_password.reset_password',
            'description' => 'progforce.user::lang.reset_password.reset_password_desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'paramCode' => [
                'title'       => 'progforce.user::lang.reset_password.code_param',
                'description' => 'progforce.user::lang.reset_password.code_param_desc',
                'type'        => 'string',
                'default'     => 'code'
            ]
        ];
    }

    /**
     * Trigger the password reset email
     */
    public function onRestorePassword()
    {
        $rules = [
            'email' => 'required|email|between:6,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        if (!$user = UserModel::findByEmail(post('email'))) {
            throw new ApplicationException(trans('progforce.user::lang.account.invalid_user'));
        }

        $code = implode('!', [$user->id, $user->getResetPasswordCode()]);
        $link = $this->controller->currentPageUrl([
            $this->property('paramCode') => $code
        ]);

        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('progforce.user::mail.restore', $data, function($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });
    }

    /**
     * Perform the password reset
     */
    public function onResetPassword()
    {
        $rules = [
            'code'     => 'required',
            'password' => 'required|between:4,255'
        ];

        $validation = Validator::make(post(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        /*
         * Break up the code parts
         */
        $parts = explode('!', post('code'));
        if (count($parts) != 2) {
            throw new ValidationException(['code' => trans('progforce.user::lang.account.invalid_activation_code')]);
        }

        list($userId, $code) = $parts;

        if (!$code || !strlen(trim($code))) {
            throw new ApplicationException(trans('progforce.user::lang.account.invalid_activation_code'));
        }

        if (!strlen(trim($userId)) || !($user = Auth::findUserById($userId))) {
            throw new ApplicationException(trans('progforce.user::lang.account.invalid_user'));
        }

        if (!$user->attemptResetPassword($code, post('password'))) {
            throw new ValidationException(['code' => trans('progforce.user::lang.account.invalid_activation_code')]);
        }
    }

    /**
     * Returns the reset password code from the URL
     * @return string
     */
    public function code()
    {
        $routeParameter = $this->property('paramCode');

        return $this->param($routeParameter);
    }
}