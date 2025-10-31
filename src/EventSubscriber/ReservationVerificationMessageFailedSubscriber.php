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

        $emailConfirmations = $this->emailConfirmationRepository->findBy([
            'id' => [$message->getVerificationEmailConfirmationId(), $message->getCancellationEmailConfirmationId()]
        ]);

        foreach($emailConfirmations as $emailConfirmation){
            $emailConfirmation?->setStatus(EmailConfirmationStatus::FAILED->value);
        }
        
        $reservation = $this->reservationRepository->find($message->getReservationId());
        if($reservation && !$reservation->isVerified()){
            $this->reservationRepository->remove($reservation);
        }
        $this->emailConfirmationRepository->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }
}