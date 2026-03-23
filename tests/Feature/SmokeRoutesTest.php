<?php

namespace App\Tests\Feature;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeRoutesTest extends WebTestCase
{
    public function testPublicPagesRespondWithoutServerError(): void
    {
        $client = static::createClient();

        foreach (['/login'] as $path) {
            $client->request('GET', $path);
            $status = $client->getResponse()->getStatusCode();
            self::assertLessThan(500, $status, sprintf('Expected non-500 response for "%s", got %d.', $path, $status));
        }
    }

    public function testProtectedPagesEitherLoadOrRedirect(): void
    {
        $client = static::createClient();

        foreach (['/cart', '/checkout', '/admin/', '/admin/users', '/admin/logs', '/admin/profile', '/staff/dashboard', '/admin/subscriptions/'] as $path) {
            $client->request('GET', $path);
            $status = $client->getResponse()->getStatusCode();

            self::assertContains(
                $status,
                [200, 301, 302, 303, 307, 308, 403],
                sprintf('Expected auth-related status for "%s", got %d.', $path, $status)
            );
        }
    }
}
