<x-mail::message>
@if ($kind === 'return_requested')
# Vehicle return requested

Hello desk,

**{{ $booking->user?->name ?: 'A renter' }}** asked to return booking **{{ $booking->ref }}**. Accept the return in Inbox or Bookings when the car is back.

@elseif ($kind === 'confirmed')
# Booking confirmed

Hello {{ $booking->user?->name ?: 'renter' }},

Staff confirmed your booking.
@else
# Reservation received

Hello {{ $booking->user?->name ?: 'renter' }},

We received your reservation. It stays **Pending** until staff accepts it.
@endif

- **Reference:** {{ $booking->ref }}
- **Renter:** {{ $booking->user?->name }}
- **Car:** {{ $booking->vehicle?->name ?: 'Vehicle '.$booking->vehicle_id }}
- **Pick-up:** {{ $booking->pickupLocation?->name }} · {{ $booking->start_date?->format('M j, Y') }} {{ $booking->pickup_time }}
- **Drop-off / return:** {{ $booking->dropoffLocation?->name }} · {{ $booking->end_date?->format('M j, Y') }} {{ $booking->return_time }}
- **Price:** ₱{{ number_format((float) $booking->total, 2) }}
- **Status:** {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
@if ($kind === 'return_requested' && filled($booking->return_notes))
- **Return notes:** {{ $booking->return_notes }}
@endif

Thank you,<br>
{{ config('app.name') }}
</x-mail::message>
