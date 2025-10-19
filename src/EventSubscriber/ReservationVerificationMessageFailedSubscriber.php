<?php 

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Message\ReservationVerificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use App\Repository\EmailConfirmationRepository;
use App\Repository\ReservationRepository;

class ReservationVerificationMessageFailedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailConfirmationRepository $emailConfirmationRepository,
        private ReservationRepository $reservationRepository,
    )
    {

    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if(!$message instanceof ReservationVerificationMessage || $event->willRetry()){
            return;
        }

        $emailConfirmation = $this->emailConfirmationRepository->find($message->getEmailConfirmationId());
        $emailConfirmation?->setStatus(EmailConfirmationStatus::FAILED->value);
        $emailConfirmation?->setCreator(null);
        $this->emailConfirmationRepository->flush();

        $reservation = $this->reservationRepository->find($message->getReservationId());
        if($reservation && !$reservation->isVerified()){
            $this->reservationRepository->hardDelete($reservation);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }
}