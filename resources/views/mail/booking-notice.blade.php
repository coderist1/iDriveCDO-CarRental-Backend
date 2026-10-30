<x-mail::message>
# {{ $kind === 'confirmed' ? 'Booking confirmed' : 'Reservation received' }}

Hello {{ $booking->user?->name ?: 'renter' }},

@if ($kind === 'confirmed')
Staff confirmed your booking.
@else
We received your reservation. It stays **Pending** until staff accepts it.
@endif

- **Reference:** {{ $booking->ref }}
- **Renter:** {{ $booking->user?->name }}
- **Car:** {{ $booking->vehicle?->name ?: 'Vehicle '.$booking->vehicle_id }}
- **Pick-up:** {{ $booking->pickupLocation?->name }} · {{ $booking->start_date?->format('M j, Y') }} {{ $booking->pickup_time }}
- **Drop-off / return:** {{ $booking->dropoffLocation?->name }} · {{ $booking->end_date?->format('M j, Y') }} {{ $booking->return_time }}
- **Price:** ₱{{ number_format((float) $booking->total, 2) }}
- **Status:** {{ ucfirst(str_replace('_', ' ', $booking->status)) }}

Thank you,<br>
{{ config('app.name') }}
</x-mail::message>
