<?php

namespace App\Service\MailingHelper;

use App\Entity\EmailConfirmation;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\User;
use App\Exceptions\MailingHelperException;
use App\Repository\EmailConfirmationRepository;
use App\Service\DataHandlingHelper\DataHandlingHelper;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailingHelper
{
    private EmailConfirmationRepository $emailConfirmationRepository;

    public function __construct(
        private EmailConfirmationHandlerInterface $emailConfirmationHandler, 
        private MailerInterface $mailer, 
        private EntityManagerInterface $entityManager
    )
    {
        $this->emailConfirmationRepository = $this->entityManager->getRepository(EmailConfirmation::class);
    }

    public function sendEmailVerification(EmailConfirmation $emailConfirmation){
        $expiryDate = new \DateTime('+1 days');
        try{
            $url = $this->emailConfirmationHandler->generateSignature($emailConfirmation);

            $email = (new TemplatedEmail())->to($emailConfirmation->getEmail())
            ->subject('Email Verification')
            ->htmlTemplate('emails/emailVerification.html.twig')
            ->context([
                'expiration_date' => $expiryDate,
                'url' => $url
            ]);
            $this->mailer->send($email);
        }
        catch(Exception){
            $this->entityManager->remove($emailConfirmation);
            throw new MailingHelperException();
        }
    }

    public function newReservationVerification(Reservation $reservation){
        $email = $reservation->getEmail();
        $reservationId = $reservation->getId();
        $expiryDate = new \DateTime('+1 hours');
        $reservation->setExpiryDate($expiryDate);

        try{
            $emailConfirmation = $this->newEmailConfirmation(null, $email, 'reservation_verify', $expiryDate, ['reservationId' => $reservationId]);
            $this->entityManager->persist($emailConfirmation);
            $this->entityManager->flush();
            
            $url = $this->generateSignature($emailConfirmation);
            $email = (new TemplatedEmail())->to($email)
            ->subject('Reservation Verification')
            ->htmlTemplate('emails/reservationVerification.html.twig')
            ->context([
                'organization' => $reservation->getSchedule()->getOrganization()->getName(),
                'dateTime' => $reservation->getDate() . $reservation->getTimeWindow()->getStartTime()->format(' H:i'),
                'serviceName' => $reservation->getService()->getName(),
                'duration' => (new DataHandlingHelper)->getPrettyDateInterval($reservation->getService()->getDuration()),
                'estimatedPrice' => $reservation->getService()->getEstimatedPrice(),
                'expiration_date' => $expiryDate,
                'url' => $url
            ]);
            $this->mailer->send($email);
        }
        catch(Exception){
            $this->entityManager->remove($emailConfirmation);
            throw new MailingHelperException();
        }
    }

    public function newReservationInformation(Reservation $reservation, string $subject, string $template, bool $generateCancellationUrl){
        $email = $reservation->getEmail();
        $reservationId = $reservation->getId();
        $startTime = $reservation->getTimeWindow()->getStartTime();
        
        if($generateCancellationUrl){
            $expiryDate = DateTime::createFromFormat(Schedule::DATE_FORMAT, $reservation->getDate())
            ->setTime((int)$startTime->format('H'), (int)$startTime->format('i'));

            $emailConfirmation = $this->newEmailConfirmation(null, $email, 'reservation_cancel', $expiryDate, ['reservationId' => $reservationId]);
            $this->entityManager->persist($emailConfirmation);
            $this->entityManager->flush();

            $url = $this->generateSignature($emailConfirmation);
            $context['url'] = $url;
        }
        
        try{
            $context['organization'] = $reservation->getSchedule()->getOrganization()->getName();
            $context['dateTime'] = $reservation->getDate() . $startTime->format(' H:i');
            $context['serviceName'] = $reservation->getService()->getName();
            $context['duration'] = (new DataHandlingHelper)->getPrettyDateInterval($reservation->getService()->getDuration());
            $context['estimatedPrice'] = $reservation->getService()->getEstimatedPrice();
            
            $email = (new TemplatedEmail())->to($email)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

            $this->mailer->send($email);
        }
        catch(Exception){
            $this->entityManager->remove($emailConfirmation);
            throw new MailingHelperException();
        }
    }

    public function newEmailConfirmation(
        ?User $user, 
        string $email, 
        \DateTime $expiryDate, 
        array $extraParams,
        string $verificationHandler,
        string $type
    ): EmailConfirmation
    {
        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setCreator($user);
        $emailConfirmation->setEmail($email);
        $emailConfirmation->setExpiryDate($expiryDate);
        $emailConfirmation->setParams($extraParams);
        $emailConfirmation->setVerificationHandler($verificationHandler);
        $emailConfirmation->setType($type);

        $this->emailConfirmationRepository->save($emailConfirmation, true);

        return $emailConfirmation;
    }
}