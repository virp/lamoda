<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContainerStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $containerCapacity = config('container.capacity');

        return [
            'title' => 'required|string|max:255|unique:containers,title',
            'products' => "nullable|array|max:$containerCapacity",
            'products.*' => 'exists:products,id',
        ];
    }
}
