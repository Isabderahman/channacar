<?php

namespace Tests\Unit;

use App\Support\FrenchAmountInWords;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FrenchAmountInWordsTest extends TestCase
{
    /**
     * @return array<string, array{0: float, 1: string}>
     */
    public static function amounts(): array
    {
        return [
            'sample devis' => [24840, 'Vingt-quatre mille huit cent quarante dirhams'],
            'zero' => [0, 'Zéro dirham'],
            'one' => [1, 'Un dirham'],
            'et un' => [21, 'Vingt et un dirhams'],
            'soixante et onze' => [71, 'Soixante et onze dirhams'],
            'soixante-douze' => [72, 'Soixante-douze dirhams'],
            'quatre-vingts' => [80, 'Quatre-vingts dirhams'],
            'quatre-vingt-un (no et)' => [81, 'Quatre-vingt-un dirhams'],
            'quatre-vingt-onze' => [91, 'Quatre-vingt-onze dirhams'],
            'cent' => [100, 'Cent dirhams'],
            'deux cents' => [200, 'Deux cents dirhams'],
            'deux cent un' => [201, 'Deux cent un dirhams'],
            'mille' => [1000, 'Mille dirhams'],
            'quatre-vingt mille (no s before mille)' => [80000, 'Quatre-vingt mille dirhams'],
            'deux cent mille (no s before mille)' => [200000, 'Deux cent mille dirhams'],
            'six cents at the end' => [48600, 'Quarante-huit mille six cents dirhams'],
            'un million de' => [1000000, 'Un million de dirhams'],
            'deux millions' => [2500000, 'Deux millions cinq cent mille dirhams'],
            'quatre-vingts millions' => [80000000, 'Quatre-vingts millions de dirhams'],
            'centimes' => [1350.5, 'Mille trois cent cinquante dirhams et cinquante centimes'],
            'one centime' => [10.01, 'Dix dirhams et un centime'],
        ];
    }

    #[DataProvider('amounts')]
    public function test_amount_is_spelled_in_french(float $amount, string $expected): void
    {
        $this->assertSame($expected, FrenchAmountInWords::dirhams($amount));
    }
}
