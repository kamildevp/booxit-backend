<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\Service\Entity\FileService;
use App\Entity\File;
use App\Entity\User;
use App\Enum\File\UploadType;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Uploadable\UploadableListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class FileServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private UploadableManager&MockObject $uploadableManager;
    private Security&MockObject $security;
    private string $storageDir;
    private FileService $fileService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->uploadableManager = $this->createMock(UploadableManager::class);
        $this->security = $this->createMock(Security::class);
        $this->storageDir = '/storage';

        $this->fileService = new FileService(
            $this->entityManager,
            $this->uploadableManager,
            $this->security,
            $this->storageDir
        );
    }

    public function testUploadRawFileEmptyContentThrowsBadRequest(): void
    {
        $this->expectException(BadRequestException::class);

        $this->fileService->uploadRawFile('', 'image/jpeg', UploadType::ORGANIZATION_BANNER);
    }

    public function testUploadRawFileUnsupportedMimeThrowsException(): void
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);

        $uploadType = UploadType::ORGANIZATION_BANNER;
        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getMimeType')->willReturn('application/pdf');

        $this->fileService->uploadFile($uploadedFileMock, $uploadType);
    }

    public function testThrowsExceptionWhenFileTooLarge(): void
    {
        $uploadType = UploadType::ORGANIZATION_BANNER;
        $maxAllowedSizeInMB = $uploadType->getMaxSizeInMB();
        $invalidSize = ($maxAllowedSizeInMB+1) * 1024 * 1024;

        $this->expectException(BadRequestException::class);

        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $uploadedFileMock->method('getMimeType')->willReturn('image/jpeg');
        $uploadedFileMock->method('getSize')->willReturn($invalidSize);

        $this->fileService->uploadFile($uploadedFileMock, $uploadType);
    }

    public function testUploadRawFileCreatesFileEntity(): void
    {
        $uploadType = UploadType::ORGANIZATION_BANNER;
        $userMock = $this->createMock(User::class);

        $this->security->method('getUser')->willReturn($userMock);

        $listenerMock = $this->createMock(UploadableListener::class);

        $this->uploadableManager->method('getUploadableListener')->willReturn($listenerMock);
        $this->uploadableManager->method('markEntityToUpload')->willReturn(null);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgV6b6xwAAAAASUVORK5CYII=';
        $content   = base64_decode($pngBase64);

        $fileEntity = $this->fileService->uploadRawFile($content, 'image/png', $uploadType);

        $this->assertInstanceOf(File::class, $fileEntity);
        $this->assertEquals($uploadType->value, $fileEntity->getType());
        $this->assertEquals($userMock, $fileEntity->getUploadedBy());
    }

    public function testUploadFileOverwritesExistingFile(): void
    {
        $uploadType = UploadType::ORGANIZATION_BANNER;
        $existingFileMock = $this->createMock(File::class);
        $userMock = $this->createMock(User::class);

        $uploadedFileMock = $this->createMock(UploadedFile::class);
        $listenerMock = $this->createMock(UploadableListener::class);

        $uploadedFileMock->method('getMimeType')->willReturn('image/jpeg');
        $existingFileMock->expects($this->once())->method('setType')->with($uploadType->value);
        $existingFileMock->expects($this->once())->method('setUploadedBy')->with($userMock);    
        $this->uploadableManager->method('getUploadableListener')->willReturn($listenerMock);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $fileEntity = $this->fileService->uploadFile($uploadedFileMock, $uploadType, $existingFileMock, $userMock);

        $this->assertSame($existingFileMock, $fileEntity);
    }
}
