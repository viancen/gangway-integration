<?php

namespace Gangway\Laravel\Enums;

enum WebhookEvent: string
{
    case BookingCreated = 'booking.created';
    case BookingUpdated = 'booking.updated';
    case BookingCancelled = 'booking.cancelled';
    case BookingRescheduled = 'booking.rescheduled';

    case TicketCheckedIn = 'ticket.checked_in';
    case TicketCheckedOut = 'ticket.checked_out';
    case TicketCancelled = 'ticket.cancelled';
    case TicketNoShow = 'ticket.no_show';

    case RentalHandedOut = 'rental.handed_out';
    case RentalReturned = 'rental.returned';

    case EquipmentStatusChanged = 'equipment.status_changed';

    case ProductCreated = 'product.created';
    case ProductUpdated = 'product.updated';
    case EventCreated = 'event.created';
    case EventUpdated = 'event.updated';

    case ProgramRunCreated = 'program_run.created';
    case ProgramRunUpdated = 'program_run.updated';
    case ProgramRunConfirmed = 'program_run.confirmed';
    case ProgramRunCancelled = 'program_run.cancelled';

    case InvoiceIssued = 'invoice.issued';
    case CreditNoteIssued = 'credit_note.issued';

    case WebhookTest = 'webhook.test';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $event): string => $event->value, self::cases());
    }
}
