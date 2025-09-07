<?php

declare(strict_types=1);

namespace App\Documentation\Response;

use App\Enum\File\UploadType;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class BinaryFileResponseDoc extends OA\Response
{
    public function __construct(
        UploadType|array $uploadType,
        ?string $description = 'Requested file',  
        array $headers = []
    )
    { 

        $mediaTypes = $uploadType instanceof UploadType ? $uploadType->getFileType()->getMimeTypes() : $uploadType;
        $content = [];
        foreach($mediaTypes as $mediaType){
            $content[] = new OA\MediaType(
                mediaType: $mediaType,
                schema: new OA\Schema(type: "string", format: "binary")
            );
        }

        parent::__construct(
            response: BinaryFileResponse::HTTP_OK,
            description: $description,
            content: $content,
            headers: $headers
        );
    }
}