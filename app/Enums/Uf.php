<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Unidades federativas, com o código IBGE (os dois primeiros dígitos do código do município).
 */
enum Uf: string
{
    case RO = 'RO';
    case AC = 'AC';
    case AM = 'AM';
    case RR = 'RR';
    case PA = 'PA';
    case AP = 'AP';
    case TO = 'TO';
    case MA = 'MA';
    case PI = 'PI';
    case CE = 'CE';
    case RN = 'RN';
    case PB = 'PB';
    case PE = 'PE';
    case AL = 'AL';
    case SE = 'SE';
    case BA = 'BA';
    case MG = 'MG';
    case ES = 'ES';
    case RJ = 'RJ';
    case SP = 'SP';
    case PR = 'PR';
    case SC = 'SC';
    case RS = 'RS';
    case MS = 'MS';
    case MT = 'MT';
    case GO = 'GO';
    case DF = 'DF';

    public function codigoIbge(): int
    {
        return match ($this) {
            self::RO => 11, self::AC => 12, self::AM => 13, self::RR => 14, self::PA => 15,
            self::AP => 16, self::TO => 17, self::MA => 21, self::PI => 22, self::CE => 23,
            self::RN => 24, self::PB => 25, self::PE => 26, self::AL => 27, self::SE => 28,
            self::BA => 29, self::MG => 31, self::ES => 32, self::RJ => 33, self::SP => 35,
            self::PR => 41, self::SC => 42, self::RS => 43, self::MS => 50, self::MT => 51,
            self::GO => 52, self::DF => 53,
        };
    }

    public function contemMunicipio(int $codigoMunicipio): bool
    {
        return intdiv($codigoMunicipio, 100000) === $this->codigoIbge();
    }
}
