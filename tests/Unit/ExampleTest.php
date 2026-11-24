<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Unit;

use App\Modules\Core\Services\Money;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_money_is_converted_without_floating_point_arithmetic(): void
    {
        $this->assertSame(123456, Money::toMinor('1,234.56'));
        $this->assertSame(-500, Money::toMinor('-5.00'));
        $this->assertSame('1234.56', Money::input(123456));
        $this->assertSame(1450, Money::percentToBasisPoints('14.5'));
    }
}
