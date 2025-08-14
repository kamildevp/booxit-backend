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
    public function __construct(private MailerInterface $mailer)
    {
        
    }

    public function sendTemplatedEmail(array $to, string $subject, string $templatePath, array $context){
        try{
            $email = (new TemplatedEmail())->to(...$to)
            ->subject($subject)
            ->htmlTemplate($templatePath)
            ->context($context);

            $this->mailer->send($email);
        }
        catch(Exception){
            throw new MailingHelperException();
        }
    }
}