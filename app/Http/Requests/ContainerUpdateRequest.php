<?php

namespace App\Http\Requests;

use App\Container;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ContainerUpdateRequest
 * @property-read Container $cargoContainer
 */
class ContainerUpdateRequest extends FormRequest
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
    public function rules()
    {
        $containerCapacity = config('container.capacity');

        return [
            'title' => 'required|string|max:255|unique:containers,title,'.$this->cargoContainer->id,
            'products' => "nullable|array|max:$containerCapacity",
            'products.*' => 'exists:products,id',
        ];
    }
}
