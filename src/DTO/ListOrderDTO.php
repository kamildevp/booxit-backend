<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validator\Constraints as CustomAssert;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

abstract class ListOrderDTO extends AbstractDTO implements OrderDTOInterface
{
    protected array $orderMap;
    protected string $separator = ',';

    public function __construct(  
        #[CustomAssert\StringifiedCollectionSubset(baseCollectionCallbackMethod: 'getOrderableColumns', message: 'Specified order columns are invalid')]
        public ?string $order = null,
        #[CustomAssert\StringifiedCollectionSubset(baseCollection: ['asc', 'desc'], message: 'Specified order directions are invalid')]
        public ?string $order_dir = null,
    )
    {
        $orderColumns = !empty($order) ? explode($this->separator, $order) : [];
        $orderDirs = !empty($order_dir) ? explode($this->separator, $order_dir) : [];
        $this->orderMap = count($orderColumns) == count($orderDirs) ? array_combine($orderColumns, $orderDirs) : [];
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        $orderColumns = !empty($this->order) ? explode($this->separator, $this->order) : [];
        $orderDirs = !empty($this->order_dir) ? explode($this->separator, $this->order_dir) : [];
        if (count($orderColumns) != count($orderDirs)) {
            $context->buildViolation('Specified order directions does not match order columns')
            ->atPath('order_dir')
                ->addViolation();
        }
    }

    #[Ignore]
    public function getOrderMap(): array
    {
        return $this->orderMap;
    }

    public function getOrderDir(string $parameterName): ?string
    {
        return $this->hasOrder($parameterName) ? $this->orderMap[$parameterName] : null;
    }

    public function hasOrder(string $parameterName): bool
    {
        return array_key_exists($parameterName, $this->orderMap);
    }

    #[Ignore]
    abstract public function getOrderableColumns(): array;
}