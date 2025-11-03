<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository
    ) {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $this->getLoginUrl($request) === $request->getBaseUrl() . $request->getPathInfo();
    }

    public function authenticate(Request $request): Passport
    {
        // Accept both Symfony defaults (_username, _password) and custom names (username, password)
        $username = $request->request->get('_username', $request->request->get('username', ''));
        $password = $request->request->get('_password', $request->request->get('password', ''));

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username, fn(string $identifier) => $this->userRepository->findUserByEmailOrUsername($identifier)),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        return new RedirectResponse('/');
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->getLoginUrl($request)
        );
    }
}
