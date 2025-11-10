<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:generated,draft',
            'date' => 'required|date',
            'city' => 'required|string|max:255',
            'seller_id' => 'required|exists:people,id',
            'buyer_id' => 'required|exists:people,id',
            'price' => 'required|numeric|min:0',
            'car_id' => 'required|exists:cars,id',
        ];
    }
}
