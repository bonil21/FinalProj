<?php

namespace App\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TemplatePresenceTest extends KernelTestCase
{
    #[DataProvider('requiredTemplateProvider')]
    public function testRequiredTemplatesExist(string $relativePath): void
    {
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $fullPath = $projectDir . DIRECTORY_SEPARATOR . $relativePath;

        self::assertFileExists($fullPath, sprintf('Missing required template: %s', $relativePath));
    }

    public static function requiredTemplateProvider(): iterable
    {
        yield ['templates/admin/index.html.twig'];
        yield ['templates/staff/dashboard.html.twig'];
        yield ['templates/admin/users/index.html.twig'];
        yield ['templates/admin/logs/index.html.twig'];
        yield ['templates/admin/profile/index.html.twig'];
    }
}

