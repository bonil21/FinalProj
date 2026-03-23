<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailVerificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private string $mailerFromAddress
    ) {}

    /**
     * Generate a unique verification token
     */
    public function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Send verification email to user
     */
    public function sendVerificationEmail(User $user, string $verificationUrl): void
    {
        $toEmail = $user->getEmail();
        if ($toEmail === null || trim($toEmail) === '') {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromAddress, 'GreenBites'))
            ->to(new Address($toEmail))
            ->subject('GreenBites | Verify your email')
            ->htmlTemplate('email_verification/index.html.twig')
            ->context([
                'user' => $user,
                'verificationUrl' => $verificationUrl,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Verify a token and mark user as verified
     */
    public function verifyToken(string $token): ?User
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            return null;
        }

        // Mark user as verified
        $user->setIsVerified(true);
        $user->setVerificationToken(null); // Clear the token

        $this->entityManager->flush();

        return $user;
    }

    /**
     * Check if a user needs verification
     */
    public function needsVerification(User $user): bool
    {
        return !$user->isVerified();
    }
}