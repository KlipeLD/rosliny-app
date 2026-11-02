<?php

declare(strict_types=1);

namespace App\Services\Sensors;

use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

final class SensorClientException extends RuntimeException
{
    public static function missingConfiguration(): self
    {
        return new self('Brak konfiguracji adresu API czujnika.');
    }

    public static function connectionFailed(ConnectionException $previous): self
    {
        return new self('Nie udało się połączyć z czujnikiem.', previous: $previous);
    }

    public static function httpError(int $status): self
    {
        return new self('Błąd pobierania danych z czujnika (HTTP '.$status.')');
    }

    public static function invalidResponse(): self
    {
        return new self('Czujnik zwrócił niepoprawne dane.');
    }
}
