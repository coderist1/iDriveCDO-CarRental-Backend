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
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'return_notes' => ['sometimes', 'nullable', 'string', 'max:240'],
            'started_by' => $staffUser,
            'return_requested_by' => $staffUser,
            'returned_by' => $staffUser,

            'addon_ids' => ['sometimes', 'array'],
            'addon_ids.*' => ['integer', 'distinct', Rule::exists('addons', 'addon_id')->where('is_active', true)],

            'renter_document' => ['sometimes', 'nullable', 'array'],
            'renter_document.license_name' => ['nullable', 'string', 'max:60'],
            'renter_document.dl_codes' => ['nullable', 'string', 'max:40'],
            'renter_document.license_no' => ['nullable', 'string', 'max:20', Patterns::LICENSE_NO],
            'renter_document.license_expiry' => ['nullable', 'date', 'after_or_equal:today'],
            'renter_document.license_address' => ['nullable', 'string', 'max:120'],
            'renter_document.emergency_phone' => ['nullable', 'string', Patterns::PHONE],
            'renter_document.license_photo' => ['nullable', 'string', Patterns::LICENSE_PHOTO],
            'renter_document.id_photo' => ['nullable', 'string', Patterns::LICENSE_PHOTO],
            'renter_document.id_type' => ['nullable', Rule::in(BookingRenterDocument::ID_TYPES)],
            'renter_document.id_number' => ['nullable', 'string', Patterns::ID_NUMBER],
            'renter_document.id_expiry' => ['nullable', 'date'],
            'renter_document.birthdate' => ['nullable', 'date'],
            'renter_document.parent_name' => ['nullable', 'string', 'max:80'],
            'renter_document.parent_phone' => ['nullable', 'string', Patterns::PHONE],
            'renter_document.parent_id_photo' => ['nullable', 'string', Patterns::LICENSE_PHOTO],
            'renter_document.parent_consent_at' => ['nullable', 'date'],
            'renter_document.privacy_accepted_at' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateTrip($validator),
            fn (Validator $validator) => $this->validateNoOverlap($validator),
            fn (Validator $validator) => $this->validateOneCarAtATime($validator),
            fn (Validator $validator) => $this->validateStatusChange($validator),
            fn (Validator $validator) => $this->validateRenterDocument($validator),
            fn (Validator $validator) => $this->validateIdsWhenCarInUse($validator),
        ];
    }

    private function validateOneCarAtATime(Validator $validator): void
    {
        $booking = $this->route('booking');
        $userId = $this->input('user_id', $booking?->user_id);

        if (! $userId) {
            return;
        }

        $exceptId = $booking instanceof Booking ? $booking->booking_id : null;

        if ($this->isMethod('POST')) {
            $this->addErrorIfUserHasOtherActiveBooking($validator, (int) $userId, null);

            return;
        }

        if ($booking instanceof Booking && $this->filled('status')) {
            $next = $this->input('status');
            if (in_array($next, ['confirmed', 'ongoing'], true)) {
                $this->addErrorIfUserHasOtherActiveBooking($validator, (int) $userId, $exceptId);
            }
        }
    }

    private function addErrorIfUserHasOtherActiveBooking(Validator $validator, int $userId, ?int $exceptBookingId): void
    {
        $query = Booking::query()
            ->where('user_id', $userId)
            ->whereIn('status', Booking::ACTIVE_STATUSES);

        if ($exceptBookingId !== null) {
            $query->where('booking_id', '!=', $exceptBookingId);
        }

        if (! $query->exists()) {
            return;
        }

        $validator->errors()->add(
            'user_id',
            'This renter already has an active booking. One car at a time — finish, pay, or cancel it before starting another.'
        );
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

        if ($booking instanceof Booking && $this->input('status') === 'cancelled') {
            $validator->errors()->add('status', 'Cancel a booking with a reason. Changing the status alone is not enough.');

            return;
        }

        if ($booking instanceof Booking && $this->filled('status') && ! $booking->canTransitionTo($this->input('status'))) {
            $validator->errors()->add('status', "A {$booking->status} booking cannot be changed to {$this->input('status')}.");
        }
    }

    private function validateNoOverlap(Validator $validator): void
    {
        $booking = $this->route('booking');
        $vehicleId = $this->input('vehicle_id', $booking?->vehicle_id);
        $start = $this->input('start_date', $booking?->start_date);
        $end = $this->input('end_date', $booking?->end_date);
        if (! $vehicleId || ! $start || ! $end) {
            return;
        }

        $query = Booking::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start);
        if ($booking instanceof Booking) {
            $query->where('booking_id', '!=', $booking->booking_id);
        }
        if ($query->exists()) {
            $validator->errors()->add(
                'vehicle_id',
                'This car is already reserved for some of those dates. Choose other dates or another car.'
            );
        }
    }

    private function validateIdsWhenCarInUse(Validator $validator): void
    {
        $booking = $this->route('booking');
        $confirming = $this->input('status') === 'confirmed' || $this->isMethod('POST');
        if (! $confirming) {
            return;
        }

        $vehicleId = $this->input('vehicle_id', $booking?->vehicle_id);
        if (! $vehicleId) {
            return;
        }

        $current = Booking::query()
            ->with('renterDocument')
            ->where('vehicle_id', $vehicleId)
            ->where('status', 'ongoing')
            ->when($booking instanceof Booking, fn ($q) => $q->where('booking_id', '!=', $booking->booking_id))
            ->first();
        if (! $current) {
            return;
        }

        if (! $this->hasValidId($current->renterDocument)) {
            $validator->errors()->add(
                'vehicle_id',
                'This car is in use and the current renter has no valid ID on file. Staff must collect that ID before another booking is confirmed.'
            );
        }

        $incoming = $this->input('renter_document');
        $existing = $booking instanceof Booking ? $booking->renterDocument : null;
        $newDoc = is_array($incoming) ? $incoming : $existing;
        if (! $this->hasValidId($newDoc)) {
            $validator->errors()->add(
                'renter_document',
                'This car is currently in use. Upload a valid ID for the new renter before the booking is confirmed.'
            );
        }
    }

    private function hasValidId(mixed $document): bool
    {
        if (! $document) {
            return false;
        }
        if (is_array($document)) {
            $photo = filled($document['id_photo'] ?? null) || filled($document['license_photo'] ?? null);
            $number = filled($document['id_number'] ?? null) || filled($document['license_no'] ?? null);

            return $photo && $number;
        }

        $photo = filled($document->getAttribute('id_photo')) || filled($document->getAttribute('license_photo'));
        $number = filled($document->id_number) || filled($document->license_no);

        return $photo && $number;
    }

    private function validateRenterDocument(Validator $validator): void
    {
        $document = $this->input('renter_document');

        if (! is_array($document) || $document === []) {
            if ($this->isMethod('POST')) {
                $validator->errors()->add('renter_document', 'Upload the required ID for this booking.');
            }

            return;
        }

        if (! filled($document['privacy_accepted_at'] ?? null)) {
            $validator->errors()->add(
                'renter_document.privacy_accepted_at',
                'Accept the Data Privacy notice before we store your ID.'
            );
        }

        $mode = $this->input('drive_mode', $this->route('booking')?->drive_mode ?: 'self');
        $hasId = filled($document['id_type'] ?? null) && filled($document['id_number'] ?? null) && filled($document['id_photo'] ?? null);
        $licenseFields = ['dl_codes', 'license_no', 'license_expiry'];
        $hasLicense = collect($licenseFields)->every(fn ($field) => filled($document[$field] ?? null));
        $anyLicense = collect($licenseFields)->contains(fn ($field) => filled($document[$field] ?? null));

        if ($mode === 'self') {
            if (! $hasLicense || ! filled($document['license_photo'] ?? null)) {
                $validator->errors()->add(
                    'renter_document',
                    'Self-drive requires a driver’s license photo plus the DL codes, license number, and expiration date.'
                );
            }
            if (! $hasId) {
                $validator->errors()->add(
                    'renter_document.id_type',
                    'Self-drive also requires one other valid ID (type, number, and photo).'
                );
            }
        } else {
            if ($anyLicense && ! $hasLicense) {
                $validator->errors()->add(
                    'renter_document',
                    'DL codes, license number, and expiration date are all required.'
                );
            }
            if (! $hasId) {
                $validator->errors()->add(
                    'renter_document',
                    'With a chauffeur, upload a valid ID type, number, and photo. A driver’s license is not required.'
                );
            }
        }

        $expiry = $document['license_expiry'] ?? null;
        $end = $this->input('end_date');
        if ($hasLicense && $expiry && $end && strtotime((string) $expiry) && strtotime((string) $end) && $expiry < $end) {
            $validator->errors()->add(
                'renter_document.license_expiry',
                'This license expires before the vehicle is returned.'
            );
        }

        $birth = $document['birthdate'] ?? null;
        if ($birth && strtotime((string) $birth)) {
            $age = Carbon::parse($birth)->age;
            if ($age <= 17) {
                $parentOk = filled($document['parent_name'] ?? null)
                    && filled($document['parent_phone'] ?? null)
                    && filled($document['parent_id_photo'] ?? null)
                    && filled($document['parent_consent_at'] ?? null);
                if (! $parentOk) {
                    $validator->errors()->add(
                        'renter_document.parent_name',
                        'Renters aged 17 or below need parent or guardian name, mobile number, a valid ID photo, and consent.'
                    );
                }
            }
        }
    }
}
