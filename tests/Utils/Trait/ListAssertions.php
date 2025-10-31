<?php

declare(strict_types=1);

namespace App\Tests\Utils\Trait;

trait ListAssertions
{
    protected function assertPaginatorResponse(array $responseData, int $page, int $perPage, int $total, array $expectedItems): void
    {
        $pagesCount = ceil($total/$perPage);
        $expectedItemsCount = $page == $pagesCount ? $total % $perPage : $perPage;

        $this->assertEquals($expectedItems, $responseData['items']);
        $this->assertCount($expectedItemsCount, $responseData['items']);
        $this->assertEquals($page, $responseData['page']);
        $this->assertEquals($perPage, $responseData['per_page']);
        $this->assertEquals($pagesCount, $responseData['pages_count']);
        $this->assertEquals($total, $responseData['total']);
    }
}