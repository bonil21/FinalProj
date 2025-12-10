<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserManagementType;
use App\Repository\UserRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ActivityLogger $activityLogger,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    #[Route('', name: 'admin_users_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $search = $request->query->get('search', '');
        $roleFilter = $request->query->get('role', '');
        $statusFilter = $request->query->get('status', '');

        $users = $this->userRepository->findWithFilters($search, $roleFilter, $statusFilter);

        $totalUsers = $this->userRepository->count([]);
        $allUsers = $this->userRepository->findAll();
        $totalStaff = count(array_filter($allUsers, fn($u) => in_array('ROLE_STAFF', $u->getRoles())));
        $totalAdmins = count(array_filter($allUsers, fn($u) => in_array('ROLE_ADMIN', $u->getRoles())));

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'totalStaff' => $totalStaff,
            'totalAdmins' => $totalAdmins,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
        ]);
    }

    #[Route('/new', name: 'admin_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserManagementType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->activityLogger->log(
                $this->getUser(),
                'CREATE',
                'User',
                (string)$user->getId(),
                [
                    'email' => $user->getEmail(),
                    'role' => implode(', ', $user->getRoles()),
                    'name' => $user->getName(),
                ]
            );

            $this->addFlash('success', 'User created successfully.');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_users_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserManagementType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->flush();

            $this->activityLogger->log(
                $this->getUser(),
                'UPDATE',
                'User',
                (string)$user->getId(),
                [
                    'email' => $user->getEmail(),
                    'role' => implode(', ', $user->getRoles()),
                    'name' => $user->getName(),
                ]
            );

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/reset-password', name: 'admin_users_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('reset_password'.$user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Generate random temporary password (16 characters for better security)
        $tempPassword = bin2hex(random_bytes(8));
        $hashedPassword = $this->passwordHasher->hashPassword($user, $tempPassword);
        $user->setPassword($hashedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->refresh($user);

        $this->activityLogger->log(
            $this->getUser(),
            'UPDATE',
            'User',
            (string)$user->getId(),
            [
                'action' => 'password_reset',
                'email' => $user->getEmail(),
                'reset_by' => $this->getUser()->getEmail(),
            ]
        );

        // Check if admin is resetting their own password
        $currentUser = $this->getUser();
        $isResettingOwnPassword = $currentUser->getId() === $user->getId();

        if ($isResettingOwnPassword) {
            // If admin resets their own password, log them out and redirect to login
            $this->tokenStorage->setToken(null);
            $request->getSession()->invalidate();
            
            // Store the temporary password in session before invalidating (won't work)
            // Instead, redirect with the password in query parameter (less secure but functional)
            // Better: show it in a modal or copy to clipboard
            return $this->redirectToRoute('app_login', [
                'password_reset' => '1',
                'temp_password' => $tempPassword,
                'email' => $user->getEmail()
            ]);
        } else {
            // If resetting someone else's password, show the temporary password
            $this->addFlash('success', sprintf(
                'Password reset successfully for %s. Temporary password: <strong style="font-family: monospace; background: #f0f0f0; padding: 0.25rem 0.5rem; border-radius: 4px;">%s</strong> - Please share this with the user securely.',
                $user->getEmail(),
                $tempPassword
            ));
            return $this->redirectToRoute('admin_users_index');
        }
    }

    #[Route('/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('admin_users_index');
        }

        $userEmail = $user->getEmail();
        $userId = $user->getId();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->activityLogger->log(
            $this->getUser(),
            'DELETE',
            'User',
            (string)$userId,
            [
                'email' => $userEmail,
            ]
        );

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/toggle-status', name: 'admin_users_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('toggle_status'.$user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('admin_users_index');
        }

        $newStatus = $user->getStatus() === 'active' ? 'disabled' : 'active';
        $user->setStatus($newStatus);
        $this->entityManager->flush();

        $this->activityLogger->log(
            $this->getUser(),
            'UPDATE',
            'User',
            (string)$user->getId(),
            [
                'action' => 'status_change',
                'email' => $user->getEmail(),
                'status' => $newStatus,
            ]
        );

        $this->addFlash('success', sprintf('User status changed to %s.', $newStatus));
        return $this->redirectToRoute('admin_users_index');
    }
}

