<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enum\EmailType;
use App\Message\EmailMessage;
use App\Service\MailingHelper\MailingHelper;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class EmailMessageHandler
{
    public function __construct(
        private MailingHelper $mailingHelper,
        private TranslatorInterface $translator,
        private LocaleSwitcher $localeSwitcher,
    )
    {

    }

    public function __invoke(EmailMessage $message)
    {
        $emailType = EmailType::from($message->getEmailType());
        $this->localeSwitcher->setLocale($message->getLocale());
        $subject = $this->translator->trans('email.'.$emailType->getSubject());

        $this->mailingHelper->sendTemplatedEmail(
            [$message->getEmail()],
            $subject,
            $emailType->getTemplatePath(),
            $message->getTemplateParams()
        );

        $this->localeSwitcher->reset();
    }
}