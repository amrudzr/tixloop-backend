<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => 'required|string|exists:events,id',
            'ticket_type' => 'required|string|max:255',
            'seat_number' => 'nullable|string|max:255',
            'price' => 'required|numeric|gt:0',
            'ticket_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
