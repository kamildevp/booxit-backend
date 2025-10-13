<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Utils;

use App\Model\TimeWindow;
use App\Service\Utils\DateTimeUtils;
use App\Tests\Unit\Service\Utils\DataProvider\DateTimeUtilsDataProvider;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class DateTimeUtilsTest extends TestCase
{
    private DateTimeUtils $utils;

    protected function setUp(): void
    {
        $this->utils = new DateTimeUtils();
    }

    #[DataProviderExternal(DateTimeUtilsDataProvider::class, 'compareDateIntervalsDataCases')]
    public function testCompareDateIntervals(DateInterval $dt1, DateInterval $dt2, int $expectedResult): void
    {
        $result = $this->utils->compareDateIntervals($dt1, $dt2);
        $this->assertSame($expectedResult, $result);
    }

    #[DataProviderExternal(DateTimeUtilsDataProvider::class, 'resolveDateTimeImmutableWithDefaultDataCases')]
    public function testResolveDateTimeImmutableWithDefault(DateTimeInterface|string|null $date, DateTimeInterface $default, string $expectedResult): void
    {
        $result = $this->utils->resolveDateTimeImmutableWithDefault($date, $default);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertEquals($expectedResult, $result->format('Y-m-d'));
    }

    #[DataProviderExternal(DateTimeUtilsDataProvider::class, 'subtractTimeWindowDataCases')]
    public function testSubtractTimeWindow(TimeWindow $t1, TimeWindow $t2, array $expectedResult): void
    {
        $result = $this->utils->subtractTimeWindow($t1, $t2);
        $this->assertEquals($expectedResult, $result);
    }

    #[DataProviderExternal(DateTimeUtilsDataProvider::class, 'timeWindowCollectionDiffDataCases')]
    public function testTimeWindowCollectionDiff(array $collection1, array $collection2, array $expectedResult): void
    {
        $result = $this->utils->timeWindowCollectionDiff($collection1, $collection2);
        $this->assertEquals($expectedResult, $result);
    }
}
