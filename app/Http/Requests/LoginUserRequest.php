<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Http\Exceptions\HttpResponseException;
class LoginUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',

            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function messages()
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'password.required' => 'Password is required',
            'email.exists' => 'Email does not exist',
            'password.exists' => 'Password is incorrect',

            //
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function attributes()
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
            //
        ];
    }

    /**
     * Get the validation custom attributes that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function customAttributes()
    {
        return [
            'email' => [
                'required' => 'Email is required',
                'email' => 'Email is not valid',
                'exists' => 'Email does not exist',

            ],
            'password' => [
                'required' => 'Password is required',
                'exists' => 'Password is incorrect',
            ],
            //
        ];
    }

    /**
     * Get the validation custom messages that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function customMessages()
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'password.required' => 'Password is required',
            'email.exists' => 'Email does not exist',
            'password.exists' => 'Password is incorrect',

            //
        ];
    }
    public function failedValidation(ValidationValidator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error occurred',
            'errors' => $validator->errors()->toArray(),
        ]));
    }

}
