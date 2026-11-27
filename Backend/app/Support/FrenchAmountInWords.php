<?php

namespace App\Support;

/**
 * Spells an amount in French for the "Arrêté le présent devis à la somme de"
 * line: 24840 → "Vingt-quatre mille huit cent quarante dirhams".
 *
 * Traditional spelling rules: "et" in 21/31/…/71 but not 81/91, "vingts" and
 * "cents" take an s only when they end the number or precede million/milliard
 * (nouns), never before "mille" (invariable), and "un million de dirhams".
 */
final class FrenchAmountInWords
{
    private const UNITS = [
        'zéro', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf',
    ];

    private const TENS = [2 => 'vingt', 3 => 'trente', 4 => 'quarante', 5 => 'cinquante', 6 => 'soixante'];

    public static function dirhams(float $amount): string
    {
        $cents = (int) round(abs($amount) * 100);
        $whole = intdiv($cents, 100);
        $centimes = $cents % 100;

        $text = self::number($whole).' '.self::currency($whole);

        if ($centimes > 0) {
            $text .= ' et '.self::number($centimes).' '.($centimes > 1 ? 'centimes' : 'centime');
        }

        return mb_strtoupper(mb_substr($text, 0, 1)).mb_substr($text, 1);
    }

    public static function number(int $number): string
    {
        if ($number === 0) {
            return self::UNITS[0];
        }

        $billions = intdiv($number, 1_000_000_000);
        $millions = intdiv($number % 1_000_000_000, 1_000_000);
        $thousands = intdiv($number % 1_000_000, 1000);
        $rest = $number % 1000;

        $parts = [];

        if ($billions > 0) {
            $parts[] = self::belowThousand($billions, true).' milliard'.($billions > 1 ? 's' : '');
        }

        if ($millions > 0) {
            $parts[] = self::belowThousand($millions, true).' million'.($millions > 1 ? 's' : '');
        }

        if ($thousands > 0) {
            $parts[] = $thousands === 1 ? 'mille' : self::belowThousand($thousands, false).' mille';
        }

        if ($rest > 0) {
            $parts[] = self::belowThousand($rest, true);
        }

        return implode(' ', $parts);
    }

    /** "dirham(s)", with "de" after a round million ("un million de dirhams"). */
    private static function currency(int $whole): string
    {
        if ($whole >= 1_000_000 && $whole % 1_000_000 === 0) {
            return 'de dirhams';
        }

        return $whole > 1 ? 'dirhams' : 'dirham';
    }

    /** @param bool $pluralizable whether a trailing "cent"/"vingt" may take its plural s */
    private static function belowThousand(int $number, bool $pluralizable): string
    {
        $hundreds = intdiv($number, 100);
        $rest = $number % 100;
        $words = [];

        if ($hundreds === 1) {
            $words[] = 'cent';
        } elseif ($hundreds > 1) {
            $words[] = self::UNITS[$hundreds].' '.($rest === 0 && $pluralizable ? 'cents' : 'cent');
        }

        if ($rest > 0) {
            $words[] = self::belowHundred($rest, $pluralizable);
        }

        return implode(' ', $words);
    }

    private static function belowHundred(int $number, bool $pluralizable): string
    {
        if ($number < 20) {
            return self::UNITS[$number];
        }

        $tens = intdiv($number, 10);
        $unit = $number % 10;

        // 70–79 and 90–99 count on from the teens: soixante-douze, quatre-vingt-dix-neuf.
        if ($tens === 7) {
            return $unit === 1 ? 'soixante et onze' : 'soixante-'.self::UNITS[10 + $unit];
        }

        if ($tens === 9) {
            return 'quatre-vingt-'.self::UNITS[10 + $unit];
        }

        if ($tens === 8) {
            return $unit === 0
                ? ($pluralizable ? 'quatre-vingts' : 'quatre-vingt')
                : 'quatre-vingt-'.self::UNITS[$unit];
        }

        if ($unit === 0) {
            return self::TENS[$tens];
        }

        return self::TENS[$tens].($unit === 1 ? ' et un' : '-'.self::UNITS[$unit]);
    }
}
