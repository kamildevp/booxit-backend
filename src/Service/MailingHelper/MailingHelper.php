<?php

namespace App\Service\MailingHelper;

use App\Exceptions\MailingHelperException;
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