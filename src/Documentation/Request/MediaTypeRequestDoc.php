<?php

declare(strict_types=1);

namespace App\Documentation\Request;

use App\Enum\File\UploadType;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
class MediaTypeRequestDoc extends OA\RequestBody
{
    public function __construct(
        UploadType|array $uploadType,
        ?string $description = 'File to upload', 
    )
    {
        $mediaTypes = $uploadType instanceof UploadType ? $uploadType->getFileType()->getMimeTypes() : $uploadType;
        $content = [];
        foreach($mediaTypes as $mediaType){
            $content[] = new OA\MediaType(
                mediaType: $mediaType,
                schema: new OA\Schema(type: "string", format: "binary"),
            );
        }

        parent::__construct(
            content: $content,
            description: $description,
        );
    }
}