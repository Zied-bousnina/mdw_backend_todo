<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only authenticated users can create tasks
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'description' => 'required',
            'due_date' => 'required',
            'remind_at' => 'required',
            'status' => 'in:en attente,open,in progress,Accepted,solved,on hold', // Add this line for the status field
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, mixed>
     */
    public function messages()
    {
        return [
            'title.required' => 'A title is required',
            'description.required' => 'A description is required',
            'due_date.required' => 'A due date is required',
            'remind_at.required' => 'A reminder date is required',
            'status.in' => 'Invalid status value. Supported values are: en attente, open, in progress, Accepted, solved, on hold',
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
