<?php

declare(strict_types=1);

namespace App\DTO\Trait;

use App\Enum\OrderDir;
use App\Validator\Constraints as CustomAssert;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use OpenApi\Attributes as OA;

trait OrderFieldsDTO
{
    protected string $separator = ',';
    
    #[OA\Property(description: 'Comma-separated list of fields to sort by. Prefix with "-" for descending order. Example: "-name,created_at,updated_at".
    </br>**Orderable columns: {{ '. self::class . '::getOrderableColumns }}**')]
    #[CustomAssert\StringifiedCollectionSubset(baseCollectionCallbackMethod: 'getAllowedColumns', message: 'Specified order columns are invalid')]
    public readonly ?string $order;

    #[Ignore]
    public function getOrderMap(): array
    {
        $columnsOrders = !empty($this->order) ? explode($this->separator, $this->order) : [];
        $orderMap = [];
        foreach($columnsOrders as $columnOrder){
            $column = str_replace('-', '', $columnOrder);
            $orderMap[$column] = str_starts_with($columnOrder, '-') ? OrderDir::DESC->value : OrderDir::ASC->value;
        }

        return $orderMap;
    }

    #[Ignore]
    public function getAllowedColumns(): array
    {
        $orderableColumns = static::getOrderableColumns();
        $descOrderColumns = array_map(fn($column) => "-$column" ,$orderableColumns);
        return array_merge($orderableColumns, $descOrderColumns);
    }

    #[Ignore]
    abstract public static function getOrderableColumns(): array;
}