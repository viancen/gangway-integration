<?php

namespace Gangway\Laravel\Enums;

enum ApiScope: string
{
    case CatalogRead = 'catalog.read';
    case CatalogWrite = 'catalog.write';
    case AvailabilityRead = 'availability.read';
    case BookingsRead = 'bookings.read';
    case BookingsWrite = 'bookings.write';
    case TicketsRead = 'tickets.read';
    case TicketsWrite = 'tickets.write';
    case CheckinsWrite = 'checkins.write';
    case EquipmentRead = 'equipment.read';
    case EquipmentWrite = 'equipment.write';
    case RentalsWrite = 'rentals.write';
    case FinanceRead = 'finance.read';
    case CustomersRead = 'customers.read';
    case WaiversRead = 'waivers.read';
    case WebhooksManage = 'webhooks.manage';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $scope): string => $scope->value, self::cases());
    }
}
