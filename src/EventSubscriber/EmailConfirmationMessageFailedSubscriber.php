<?php 

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Message\EmailConfirmationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use App\Repository\EmailConfirmationRepository;

class EmailConfirmationMessageFailedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailConfirmationRepository $emailConfirmationRepository,
    )
    {

    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if(!$message instanceof EmailConfirmationMessage || $event->willRetry()){
            return;
        }

        $emailConfirmation = $this->emailConfirmationRepository->find($message->getEmailConfirmationId());
        $emailConfirmation?->setStatus(EmailConfirmationStatus::FAILED->value);
        
        $this->emailConfirmationRepository->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }
}