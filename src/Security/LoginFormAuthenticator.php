<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Service\ActivityLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ActivityLogger $activityLogger,
        private UserRepository $userRepository
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->getString('_username');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function (string $userIdentifier) {
                $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);
                
                if (!$user) {
                    throw new \Symfony\Component\Security\Core\Exception\AuthenticationException('Invalid credentials.');
                }
                
                // Check if user account is disabled
                if ($user->getStatus() === 'disabled') {
                    throw new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException('Your account is disabled.');
                }

                // Only block logins that are clearly pending email verification.
                if (\method_exists($user, 'isVerified') && \method_exists($user, 'getVerificationToken')) {
                    $isVerified = $user->isVerified();
                    $verificationToken = $user->getVerificationToken();

                    if ($isVerified === false && !empty($verificationToken)) {
                        throw new CustomUserMessageAuthenticationException('Please verify your email address before logging in.');
                    }
                }
                
                return $user;
            }),
            new PasswordCredentials($request->request->getString('_password')),
            [
                new CsrfTokenBadge('authenticate', $request->request->getString('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        
        // Log successful login
        $this->activityLogger->log(
            $user,
            'LOGIN',
            'User',
            (string)$user->getId(),
            ['email' => $user->getUserIdentifier()]
        );

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $roles = \method_exists($user, 'getRoles') ? $user->getRoles() : [];
        if (\in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        if (\in_array('ROLE_STAFF', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('staff_dashboard'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_landing_page'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}

