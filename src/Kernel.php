<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Configure trusted hosts to allow ngrok domains
     */
    public function getTrustedHosts(): array
    {
        // Allow ngrok.io domains and localhost
        return [
            '^.+\.ngrok\.io$',  // Match any ngrok subdomain
            '^.+\.ngrok-free\.app$',  // Match ngrok free app domains
            '^.+\.ngrok-free\.dev$',  // Match ngrok free dev domains
            '^localhost$',
            '^127\.0\.0\.1$',
        ];
    }

    /**
     * Configure trusted proxies for ngrok
     * This allows Symfony to trust X-Forwarded-* headers from ngrok
     */
    public function boot(): void
    {
        parent::boot();

        // Configure trusted proxies for ngrok
        // Trust all proxies when using ngrok (ngrok IPs are dynamic)
        // For production, you should specify exact proxy IPs for security
        Request::setTrustedProxies(
            [], // Empty array means trust all proxies - suitable for development with ngrok
            Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
        );
    }
}
