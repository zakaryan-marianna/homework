<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommissionCalculationTest extends TestCase
{
    /**
     * Test currency calculation.
     *
     * @return void
     */
    public function testCurrencyCalculation(): void
    {
        Http::fake([
            config('payments.exchange_rates_source') => Http::response([
                'rates' => ['USD' => 1.1497, 'JPY' => 129.53]
            ]),
        ]);

        $inputFileContent = implode("\n", [
            '2014-12-31,4,private,withdraw,1200.00,EUR',
            '2015-01-01,4,private,withdraw,1000.00,EUR',
            '2016-01-05,4,private,withdraw,1000.00,EUR',
            '2016-01-05,1,private,deposit,200.00,EUR',
            '2016-01-06,2,business,withdraw,300.00,EUR',
            '2016-01-06,1,private,withdraw,30000,JPY',
            '2016-01-07,1,private,withdraw,1000.00,EUR',
            '2016-01-07,1,private,withdraw,100.00,USD',
            '2016-01-10,1,private,withdraw,100.00,EUR',
            '2016-01-10,2,business,deposit,10000.00,EUR',
            '2016-01-10,3,private,withdraw,1000.00,EUR',
            '2016-02-15,1,private,withdraw,300.00,EUR',
            '2016-02-19,5,private,withdraw,3000000,JPY',
        ]);

        Storage::fake()->put('test.csv', $inputFileContent);

        $command = $this->artisan('commissions:calculate', ['filename' => 'test.csv']);

        $command->expectsOutput('0.60');
        $command->expectsOutput('3.00');
        $command->expectsOutput('0.00');
        $command->expectsOutput('0.06');
        $command->expectsOutput('1.50');
        $command->expectsOutput('0');
        $command->expectsOutput('0.70');
        $command->expectsOutput('0.30');
        $command->expectsOutput('0.30');
        $command->expectsOutput('3.00');
        $command->expectsOutput('0.00');
        $command->expectsOutput('0.00');
        $command->expectsOutput('8612');
        $command->assertOk();
    }
}
