<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class PlaceOrderRequest extends FormRequest
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
			# need revisions a lot here!!
			'items' => 'required|array|min:1',
			'items.*.product_id' => 'required|exists:products,id',
			'items.*.quantity' => 'required|integer|min:1',
			'shipping_name' => 'required|string|max:255',
			'shipping_email' => 'required|email',
			'shipping_phone' => 'required|string|max:20',
			'shipping_address' => 'required|string',
			'shipping_city' => 'required|string|max:100',
			'shipping_state' => 'required|string|max:100',
			'shipping_zip' => 'required|string|max:20',
			'shipping_country' => 'required|string|max:100',
			'notes' => 'nullable|string|max:500'
		];
	}

	# Custom validation error messages
	public function messages()
	{ {
			return [
				'items.required' => 'You must add at least one item to your order',
				'items.*.product_id.exists' => 'One or more products are invalid',
				'items.*.quantity.min' => 'Quantity must be at least 1',
			];
	}
}
