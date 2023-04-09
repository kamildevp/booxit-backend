<?php

namespace App\Service\MailingHelper;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class MailingHelper{

    public function __construct(private VerifyEmailHelperInterface $verifyEmailHelper, private MailerInterface $mailer, private EntityManagerInterface $entityManager)
    {
        
    }

    public function newEmailVerification(User $user, string $email){
        $expiryDate = new \DateTime('+1 days');
        $url = $this->newEmailConfirmation($user, $email, 'user_verify', $expiryDate, []);

        $email = (new TemplatedEmail())->to($email)
        ->subject('Email Verification')
        ->htmlTemplate('emails/emailVerification.html.twig')
        ->context([
            'expiration_date' => $expiryDate,
            'url' => $url
        ]);
        $this->mailer->send($email);
    }

    public function newEmailConfirmation(?User $user, string $email, string $verificationRoute, \DateTime $expiryDate, array $extraParams)
    {
        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setCreator($user);
        $emailConfirmation->setEmail($email);
        $emailConfirmation->setVerificationRoute($verificationRoute);
        $emailConfirmation->setExpiryDate($expiryDate);
        $emailConfirmation->setParams($extraParams);

        $this->entityManager->persist($emailConfirmation);
        $this->entityManager->flush();

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $emailConfirmation->getVerificationRoute(),
            $emailConfirmation->getId(),
            $emailConfirmation->getEmail(),
            ['id' => $emailConfirmation->getId()] 
        );
        return $signatureComponents->getSignedUrl();
    }
}