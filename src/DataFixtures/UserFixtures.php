<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@greenbites.com');
        $admin->setName('Administrator');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setStatus('active');

        // Hash the password securely
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'
        );
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        // Create a staff user
        $staff = new User();
        $staff->setEmail('staff@greenbites.com');
        $staff->setName('Staff Member');
        $staff->setRoles(['ROLE_STAFF']);
        $staff->setStatus('active');

        $hashedPasswordStaff = $this->passwordHasher->hashPassword(
            $staff,
            'staff123'
        );
        $staff->setPassword($hashedPasswordStaff);

        $manager->persist($staff);
        $manager->flush();
    }
}
