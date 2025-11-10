<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
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
            'date' => 'sometimes|date',
            'city' => 'sometimes|string|max:255',
            'seller_id' => 'sometimes|exists:people,id',
            'buyer_id' => 'sometimes|exists:people,id',
            'price' => 'sometimes|numeric|min:0',
            'car_id' => 'sometimes|exists:cars,id',
        ];
    }
}
