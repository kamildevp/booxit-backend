<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\EntitySerializer;

use App\Repository\Pagination\Model\PaginationResult;
use App\Service\EntitySerializer\EntitySerializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntitySerializerTest extends TestCase
{
    private MockObject&NormalizerInterface $normalizerMock;
    private MockObject&DenormalizerInterface $denormalizerMock;
    private EntitySerializer $entitySerializer;

    protected function setUp(): void
    {
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);
        $this->denormalizerMock = $this->createMock(DenormalizerInterface::class);

        $this->entitySerializer = new EntitySerializer(
            $this->normalizerMock,
            $this->denormalizerMock
        );
    }

    public function testParseToEntityWithClassString(): void
    {
        $params = ['name' => 'John'];
        $class = 'dummy';
        $expected = $this->createMock(stdClass::class);

        $this->denormalizerMock->expects($this->once())
            ->method('denormalize')
            ->with($params, $class, null, [])
            ->willReturn($expected);

        $result = $this->entitySerializer->parseToEntity($params, $class);

        $this->assertSame($expected, $result);
    }

    public function testParseToEntityWithObjectInstance(): void
    {
        $params = ['name' => 'John'];
        $object = $this->createMock(stdClass::class);
        $class = get_class($object);

        $this->denormalizerMock->expects($this->once())
            ->method('denormalize')
            ->with(
                $params,
                $class,
                null,
                [AbstractNormalizer::OBJECT_TO_POPULATE => $object]
            )
            ->willReturn($object);

        $result = $this->entitySerializer->parseToEntity($params, $object);

        $this->assertSame($object, $result);
    }

    public function testNormalize(): void
    {
        $object = $this->createMock(stdClass::class);
        $groups = ['group1'];
        $normalizedData = ['name' => 'Serialized Name'];

        $this->normalizerMock->expects($this->once())
            ->method('normalize')
            ->with($object, null, ['groups' => $groups])
            ->willReturn($normalizedData);

        $result = $this->entitySerializer->normalize($object, $groups);

        $this->assertSame($normalizedData, $result);
    }

    public function testNormalizePaginationResult(): void
    {
        $items = [new \stdClass(), new \stdClass()];
        $groups = ['group1'];
        $normalizedItems = [
            ['id' => 1],
            ['id' => 2],
        ];

        $paginationResultMock = $this->createMock(PaginationResult::class);
        $paginationResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);        

        $this->normalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->with($items, null, ['groups' => $groups])
            ->willReturn($normalizedItems);

        $paginationResultMock->expects($this->once())
            ->method('setItems')
            ->with($normalizedItems);

        $result = $this->entitySerializer->normalizePaginationResult($paginationResultMock, $groups);

        $this->assertSame($paginationResultMock, $result);
    }
}
