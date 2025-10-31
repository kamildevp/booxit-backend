<?php

declare(strict_types=1);

namespace App\Repository\Pagination\Model;

use JsonSerializable;

class PaginationResult implements JsonSerializable
{
    private array $items;
    private int $page;
    private int $perPage;
    private int $pagesCount;
    private int $total;

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    public function getPagesCount(): int
    {
        return $this->pagesCount;
    }

    public function setPagesCount(int $pagesCount): void
    {
        $this->pagesCount = $pagesCount;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function jsonSerialize(): mixed {
        return [
            'items' => $this->items,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'pages_count' => $this->pagesCount,
            'total' => $this->total,
        ];
    }
}
