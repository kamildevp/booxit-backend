<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enum\EmailType;
use App\Message\EmailMessage;
use App\Service\MailingHelper\MailingHelper;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailMessageHandler
{
    public function __construct(
        private MailingHelper $mailingHelper,
    )
    {

    }

    public function __invoke(EmailMessage $message)
    {
        $emailType = EmailType::from($message->getEmailType());

        $this->mailingHelper->sendTemplatedEmail(
            [$message->getEmail()],
            $emailType->getSubject(),
            $emailType->getTemplatePath(),
            $message->getTemplateParams()
        );
    }
}