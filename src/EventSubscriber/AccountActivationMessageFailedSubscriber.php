<?php 

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Message\AccountActivationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use App\Repository\EmailConfirmationRepository;
use App\Repository\UserRepository;

class AccountActivationMessageFailedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailConfirmationRepository $emailConfirmationRepository,
        private UserRepository $userRepository,
    )
    {

    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if(!$message instanceof AccountActivationMessage || $event->willRetry()){
            return;
        }

        $emailConfirmation = $this->emailConfirmationRepository->find($message->getEmailConfirmationId());
        $emailConfirmation?->setStatus(EmailConfirmationStatus::FAILED->value);
        $emailConfirmation?->setCreator(null);
        $this->emailConfirmationRepository->flush();

        $user = $this->userRepository->find($message->getUserId());
        if($user && !$user->isVerified()){
            $this->userRepository->hardDelete($user);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }
}