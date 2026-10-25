<?php

namespace Tests\Unit;

use App\Modules\Dyeing\FabricAvailability;
use App\Modules\Yarn\UnitConverter as U;
use App\Support\Decimal as D;
use App\Support\WasteCalculator as W;
use PHPUnit\Framework\TestCase;

class CalculationsTest extends TestCase
{
    public function test_reference_conversions_and_stored_precision_round_trips(): void
    {
        $fixtures = json_decode(file_get_contents(__DIR__.'/../Fixtures/calculations.json'), true);
        foreach ($fixtures['conversions'] as $case) {
            $this->assertSame($case['kg'], U::toKg($case['value'], $case['unit']));
            $this->assertSame($case['kg'], U::toKg(U::fromKg($case['kg'], $case['unit']), $case['unit']));
        }
    }

    public function test_prices_convert_without_using_display_rounding(): void
    {
        $canonical = U::priceToKg('2.00', 'lb');
        $this->assertSame('4.409245', $canonical);
        $this->assertSame('2.00', D::display(U::priceFromKg($canonical, 'lb'), 2));
        $this->assertSame($canonical, U::priceToKg(U::priceFromKg($canonical, 'lb'), 'lb'));
    }

    public function test_bag_factors_preserve_the_reference_examples(): void
    {
        $this->assertSame('25.00', U::bags(U::toKg('2500', 'lb'), '45.35923700'));
        $this->assertSame('226.796', U::bagsToKg('5', '45.35923700'));
        $this->assertSame('500.000', U::bagsToKg('5', '100.00000000'));
        $this->assertNull(U::bags('1', null));
    }

    public function test_knitting_and_dyeing_waste_at_display_precision(): void
    {
        $fixtures = json_decode(file_get_contents(__DIR__.'/../Fixtures/calculations.json'), true);
        foreach ($fixtures['waste'] as $case) {
            $this->assertSame($case['output'], W::output($case['input'], $case['percent']));
        }
        $this->assertSame('7.01', W::percent('37.799', '35.150'));
        $this->assertNull(W::percent('0', '0'));
        $this->assertNull(W::percent('100', null));
        $this->assertSame('-10.00', W::percent('100', '110'));
    }

    public function test_color_splits_balance_without_rounding_over_allocation(): void
    {
        $fabric = new FabricAvailability;
        $six = $fabric->split('226.796', 6);
        $this->assertSame('37.799', $six[0]);
        $this->assertSame('37.801', $six[5]);
        $this->assertSame('226.796', D::scale(D::sum($six)));
        $this->assertSame(['0.000', '0.000', '0.002'], $fabric->split('0.002', 3));
    }

    public function test_pasted_thousands_are_normalized(): void
    {
        $this->assertSame('2500.125', D::normalize(' 2,500.125 '));
        $this->expectException(\InvalidArgumentException::class);
        D::normalize('2,50.1');
    }

    public function test_negative_numbers_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        D::normalize('-1');
    }

    public function test_float_inputs_are_rejected_at_the_boundary(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        D::normalize(json_decode('1.1'));
    }

    public function test_waste_above_one_hundred_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        W::output('100', '100.01');
    }
}
