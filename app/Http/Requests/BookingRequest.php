<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\BookingRenterDocument;
use App\Models\Vehicle;
use App\Support\Patterns;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';
        $time = 'date_format:H:i,H:i:s';
        $staffUser = ['sometimes', 'nullable', Rule::exists('users', 'user_id')];

        return [
            'code' => ['sometimes', 'string', 'max:40', Rule::unique('bookings', 'code')->ignore($this->route('booking'), 'booking_id')],
            'user_id' => [$required, Rule::exists('users', 'user_id')->where('role', 'customer')],
            'vehicle_id' => [$required, Rule::exists('vehicles', 'vehicle_id')->whereNull('deleted_at')],
            'drive_mode' => ['sometimes', Rule::in(Booking::DRIVE_MODES)],
            'driver_id' => [
                'nullable',
                'required_if:drive_mode,chauffeur',
                'prohibited_if:drive_mode,self',
                Rule::exists('drivers', 'driver_id')->where('status', 'active')->whereNull('deleted_at'),
            ],
            'created_by' => $staffUser,
            'start_date' => [$required, 'date'],
            'end_date' => [$required, 'date', 'after:start_date'],
            'pickup_time' => ['sometimes', $time],
            'return_time' => ['sometimes', $time],
            'pickup_location_id' => [$required, Rule::exists('locations', 'location_id')],
            'dropoff_location_id' => [$required, Rule::exists('locations', 'location_id')],
            'number_of_passengers' => ['sometimes', 'integer', 'between:1,30'],
            'fuel_before_rent' => ['sometimes', Rule::in(Booking::FUEL_LEVELS)],
            'fuel_upon_return' => ['sometimes', 'nullable', Rule::in(Booking::FUEL_LEVELS)],
            'subtotal' => ['sometimes', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['sometimes', Rule::in(Booking::STATUSES)],
            'payment_status' => ['sometimes', Rule::in(Booking::PAYMENT_STATUSES)],
            'payment_method' => ['sometimes', 'nullable', 'required_if:payment_status,paid', Rule::in(Booking::PAYMENT_METHODS)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:240'],
            'return_notes' => ['sometimes', 'nullable', 'string', 'max:240'],
            'started_by' => $staffUser,
            'return_requested_by' => $staffUser,
            'returned_by' => $staffUser,

            'addon_ids' => ['sometimes', 'array'],
            'addon_ids.*' => ['integer', 'distinct', Rule::exists('addons', 'addon_id')->where('is_active', true)],

            'renter_document' => ['sometimes', 'nullable', 'array'],
            'renter_document.license_name' => ['nullable', 'string', 'max:60'],
            'renter_document.license_no' => ['nullable', 'string', 'max:20', Patterns::LICENSE_NO],
            'renter_document.license_expiry' => ['nullable', 'date'],
            'renter_document.license_address' => ['nullable', 'string', 'max:120'],
            'renter_document.emergency_phone' => ['nullable', 'string', Patterns::PHONE],
            'renter_document.license_photo' => ['nullable', 'string', Patterns::LICENSE_PHOTO],
            'renter_document.id_type' => ['nullable', Rule::in(BookingRenterDocument::ID_TYPES)],
            'renter_document.id_number' => ['nullable', 'string', Patterns::ID_NUMBER],
        ];
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateTrip($validator),
            fn (Validator $validator) => $this->validateStatusChange($validator),
            fn (Validator $validator) => $this->validateRenterDocument($validator),
        ];
    }

    private function validateTrip(Validator $validator): void
    {
        $booking = $this->route('booking');
        $start = $this->input('start_date', $booking?->start_date);
        $end = $this->input('end_date', $booking?->end_date);

        if ($start && $end && strtotime((string) $start) && strtotime((string) $end)) {
            $days = Carbon::parse($start)->diffInDays(Carbon::parse($end), false);

            if ($days > 30) {
                $validator->errors()->add('end_date', 'A booking can be at most 30 days long.');
            }
        }

        $vehicle = Vehicle::find($this->input('vehicle_id', $booking?->vehicle_id));
        $passengers = (int) $this->input('number_of_passengers', $booking?->number_of_passengers ?? 1);

        if ($vehicle && $passengers > $vehicle->capacity) {
            $validator->errors()->add('number_of_passengers', "This vehicle seats at most {$vehicle->capacity} passengers.");
        }
    }

    private function validateStatusChange(Validator $validator): void
    {
        $booking = $this->route('booking');

        if ($booking instanceof Booking && $this->filled('status') && ! $booking->canTransitionTo($this->input('status'))) {
            $validator->errors()->add('status', "A {$booking->status} booking cannot be changed to {$this->input('status')}.");
        }
    }

    private function validateRenterDocument(Validator $validator): void
    {
        $document = $this->input('renter_document');

        if (! is_array($document) || $document === []) {
            return;
        }

        $hasId = filled($document['id_type'] ?? null) && filled($document['id_number'] ?? null);
        $licenseFields = ['license_name', 'license_no', 'license_expiry', 'license_address', 'emergency_phone', 'license_photo'];
        $hasLicense = collect($licenseFields)->every(fn ($field) => filled($document[$field] ?? null));

        if (! $hasId && ! $hasLicense) {
            $validator->errors()->add(
                'renter_document',
                'Provide either an ID type and number, or all driver\'s license details (name, number, expiry, address, emergency phone and photo).'
            );
        }
    }
}
