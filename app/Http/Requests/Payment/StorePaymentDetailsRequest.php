<?php

namespace App\Http\Requests\Payment;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePaymentDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'product_type' => ['required', 'string', Rule::in(['service', 'course', 'module', 'lesson'])],
            'product_id' => ['required', 'integer', 'min:1'],
            'account_name_number' => ['required', 'string', 'max:255'],
            'transaction_amount' => ['required', 'numeric', 'min:0'],
            'transaction_id_reference' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $type = $this->input('product_type');
            $id = (int) $this->input('product_id');

            $exists = match ($type) {
                'service' => Service::whereKey($id)->exists(),
                'course' => Course::whereKey($id)->exists(),
                'module' => Module::whereKey($id)->exists(),
                'lesson' => Lesson::whereKey($id)->exists(),
                default => false,
            };

            if (! $exists) {
                $validator->errors()->add('product_id', 'The selected product does not exist.');
            }
        });
    }
}
