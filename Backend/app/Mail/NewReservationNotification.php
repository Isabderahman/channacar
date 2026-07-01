<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewReservationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('echanaacar@gmail.com', config('app.name')),
            to: ['abde.khafi36@gmail.com'],
            subject: sprintf('Nouvelle réservation %s', $this->reservation->reservation_number),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-created',
            with: ['reservation' => $this->reservation],
        );
    }
}
