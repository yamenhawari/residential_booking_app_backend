<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'owner_id' => 'nullable|exists:users,id',

        'governorate_id' => 'required|exists:governorates,id',
        'city_id'        => 'required|exists:cities,id',

        'title'          => 'required|string|max:255',
        'description'    => 'required|string',
        'address'        => 'required|string|max:255',

        'room_count'          => 'required|integer|min:1',
        'price_per_month'=> 'required|numeric|min:0',

        'available_from' => 'nullable|date',
        'available_to'   => 'nullable|date|after_or_equal:available_from',

        'status'         => 'nullable|in:available,rented,unavailable',

        // علاقات
        'categories'   => 'nullable|array',
        'categories.*' => 'exists:categories,id',

        'features'     => 'nullable|array',
        'features.*'   => 'exists:features,id',

        // الصور
        'images'       => 'nullable|array',
        'images.*'     => 'image|mimes:jpg,jpeg,png,webp|max:4096',
    ];
}



    public function messages()
    {
        return [
            'title.required' => 'العنوان مطلوب.',
            'description.required' => 'الوصف مطلوب.',
            'governorate_id.required' => 'المحافظة مطلوبة.',
            'city_id.required' => 'المدينة مطلوبة.',
            'owner_id.required' => 'رقم المالك مطلوب.',
            'price_per_month.required' => 'السعر مطلوب.',
            'rooms.required' => 'عدد الغرف مطلوب.',
        ];
    }
}
