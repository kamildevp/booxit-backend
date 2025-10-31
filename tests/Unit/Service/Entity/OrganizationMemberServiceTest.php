<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\DTO\OrganizationMember\OrganizationMemberPatchDTO;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Exceptions\ConflictException;
use App\Repository\OrganizationMemberRepository;
use App\Repository\UserRepository;
use App\Service\Entity\OrganizationMemberService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrganizationMemberServiceTest extends TestCase
{
    private MockObject&EntitySerializerInterface $serializerMock;

    private MockObject&UserRepository $userRepositoryMock;
    private MockObject&OrganizationMemberRepository $organizationMemberRepositoryMock;

    private OrganizationMemberService $organizationMemberService;

    protected function setUp(): void
    {
        $this->serializerMock = $this->createMock(EntitySerializerInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->organizationMemberRepositoryMock = $this->createMock(OrganizationMemberRepository::class);

        $this->organizationMemberService = new OrganizationMemberService(
            $this->serializerMock,
            $this->organizationMemberRepositoryMock,
            $this->userRepositoryMock,
        );
    }

    public function testCreateOrganizationMemberCreatesNewWhenNotExisting(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $userMock = $this->createMock(User::class);
        $role = OrganizationRole::MEMBER;

        $this->userRepositoryMock
            ->expects($this->never())
            ->method('findOrFail');

        $this->organizationMemberRepositoryMock
            ->method('findOneBy')
            ->willReturn(null);

        $this->organizationMemberRepositoryMock
            ->expects($this->once())
            ->method('save');

        $member = $this->organizationMemberService->createOrganizationMember(
            $organizationMock,
            $userMock,
            $role
        );

        $this->assertInstanceOf(OrganizationMember::class, $member);
        $this->assertSame($organizationMock, $member->getOrganization());
        $this->assertSame($userMock, $member->getAppUser());
        $this->assertSame($role->value, $member->getRole());
    }

    public function testCreateOrganizationMemberReturnsExisting(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $userMock = $this->createMock(User::class);
        $existingMemberMock = $this->createMock(OrganizationMember::class);

        $this->organizationMemberRepositoryMock
            ->method('findOneBy')
            ->willReturn($existingMemberMock);

        $this->organizationMemberRepositoryMock
            ->expects($this->never())
            ->method('save');

        $result = $this->organizationMemberService->createOrganizationMember(
            $organizationMock,
            $userMock,
            OrganizationRole::ADMIN
        );

        $this->assertSame($existingMemberMock, $result);
    }

    public function testPatchOrganizationMemberThrowsConflictIfRemovingLastAdmin(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $organizationId = 42;
        $organizationMock->method('getId')->willReturn($organizationId);

        $memberMock = $this->createMock(OrganizationMember::class);
        $memberMock->method('getRole')->willReturn(OrganizationRole::ADMIN->value);
        $memberMock->method('getOrganization')->willReturn($organizationMock);

        $dto = new OrganizationMemberPatchDTO(OrganizationRole::MEMBER->value);

        $this->organizationMemberRepositoryMock
            ->method('getOrganizationMembersCount')
            ->with($organizationId, OrganizationRole::ADMIN)
            ->willReturn(1);

        $this->expectException(ConflictException::class);

        $this->organizationMemberService->patchOrganizationMember($memberMock, $dto);
    }

    public function testPatchOrganizationMemberUpdatesRole(): void
    {
        $organizationMock = $this->createMock(Organization::class);

        $memberMock = $this->createMock(OrganizationMember::class);
        $memberMock->method('getRole')->willReturn(OrganizationRole::MEMBER->value);
        $memberMock->method('getOrganization')->willReturn($organizationMock);

        $dto = new OrganizationMemberPatchDTO(OrganizationRole::ADMIN->value);

        $updatedMemberMock = $this->createMock(OrganizationMember::class);

        $this->serializerMock
            ->expects($this->once())
            ->method('parseToEntity')
            ->with($dto, $memberMock)
            ->willReturn($updatedMemberMock);

        $this->organizationMemberRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($updatedMemberMock, true);

        $result = $this->organizationMemberService->patchOrganizationMember($memberMock, $dto);

        $this->assertSame($updatedMemberMock, $result);
    }

    public function testRemoveOrganizationMemberThrowsConflictIfLastAdmin(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $organizationId = 42;
        $organizationMock->method('getId')->willReturn($organizationId);

        $memberMock = $this->createMock(OrganizationMember::class);
        $memberMock->method('getRole')->willReturn(OrganizationRole::ADMIN->value);
        $memberMock->method('getOrganization')->willReturn($organizationMock);

        $this->organizationMemberRepositoryMock
            ->method('getOrganizationMembersCount')
            ->with($organizationId, OrganizationRole::ADMIN)
            ->willReturn(1);

        $this->expectException(ConflictException::class);

        $this->organizationMemberService->removeOrganizationMember($memberMock);
    }

    public function testRemoveOrganizationMemberRemovesWhenNotLastAdmin(): void
    {
        $organizationMock = $this->createMock(Organization::class);
        $organizationId = 42;
        $organizationMock->method('getId')->willReturn($organizationId);

        $memberMock = $this->createMock(OrganizationMember::class);
        $memberMock->method('getRole')->willReturn(OrganizationRole::ADMIN->value);
        $memberMock->method('getOrganization')->willReturn($organizationMock);

        $this->organizationMemberRepositoryMock
            ->method('getOrganizationMembersCount')
            ->with($organizationId, OrganizationRole::ADMIN)
            ->willReturn(2);

        $this->organizationMemberRepositoryMock
            ->expects($this->once())
            ->method('remove')
            ->with($memberMock, true);

        $this->organizationMemberService->removeOrganizationMember($memberMock);
    }
}
