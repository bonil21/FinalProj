<?php

namespace App\Controller;

use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/staff/profile')]
#[IsGranted('ROLE_STAFF')]
class StaffProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ActivityLogger $activityLogger,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    #[Route('', name: 'staff_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        
        return $this->render('staff/profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/change-password', name: 'staff_profile_change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request): Response
    {
        $user = $this->getUser();
        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            // Validate current password
            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                $error = 'Current password is incorrect.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters long.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New password and confirmation do not match.';
            } elseif ($this->passwordHasher->isPasswordValid($user, $newPassword)) {
                $error = 'Please use a new password. The new password must be different from your current password.';
            } else {
                // Update password - get fresh user from database to ensure we're working with the latest entity
                $userFromDb = $this->entityManager->getRepository(\App\Entity\User::class)->find($user->getId());
                if (!$userFromDb) {
                    $error = 'User not found. Please try again.';
                } else {
                    $hashedPassword = $this->passwordHasher->hashPassword($userFromDb, $newPassword);
                    $userFromDb->setPassword($hashedPassword);
                    $this->entityManager->persist($userFromDb);
                    $this->entityManager->flush();
                    $this->entityManager->refresh($userFromDb);

                    $this->activityLogger->log(
                        $userFromDb,
                        'UPDATE',
                        'User',
                        (string)$userFromDb->getId(),
                        [
                            'action' => 'password_change',
                            'email' => $userFromDb->getEmail(),
                        ]
                    );

                    // Log out the user automatically after password change
                    $this->tokenStorage->setToken(null);
                    $request->getSession()->invalidate();

                    // Redirect to login page with success message as query parameter
                    return $this->redirectToRoute('app_login', ['password_changed' => '1']);
                }
            }
        }

        return $this->render('staff/profile/change_password.html.twig', [
            'user' => $user,
            'error' => $error,
            'success' => $success,
        ]);
    }
}

