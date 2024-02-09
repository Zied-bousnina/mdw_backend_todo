<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidationValidator;
class RegisterUser extends FormRequest
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
            'name' => 'required',
            'first_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'password' => 'required|min:6'

        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'A name is required',
            'first_name.required' => 'A first name is required',
            'email.required' => 'An email is required',
            'email.email' => 'Email is not valid',
            'email.unique' => 'Email already exists',
            'phone.required' => 'A phone number is required',
            'password.required' => 'A password is required',
            'password.min' => 'Password must be at least 6 characters'
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



