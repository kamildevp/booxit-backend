<?php

declare(strict_types=1);

namespace App\Scheduler\MessageHandler;

use App\Repository\RefreshTokenRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Scheduler\Message\DbCleanupMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DbCleanupMessageHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private RefreshTokenRepository $refreshTokenRepository,
        private ReservationRepository $reservationRepository,
    )
    {
        
    }

    public function __invoke(DbCleanupMessage $message)
    {
        $this->userRepository->removeExpiredUserAccounts();
        $this->refreshTokenRepository->removeExpiredRefreshTokens();
        $this->reservationRepository->removeExpiredReservations();
    }
}