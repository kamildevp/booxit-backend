<?php

declare(strict_types=1);

namespace App\Message;

class EmailMessage
{
    public function __construct(
        protected string $emailType,
        protected string $email,
        protected array $templateParams
    ) 
    {

    }

    public function getEmailType(): string
    {
        return $this->emailType;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTemplateParams(): array
    {
        return $this->templateParams;
    }
}