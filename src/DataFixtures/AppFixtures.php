<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Advice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = [];

        // 👤 Create 5 regular users
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email());
            $user->setCity($faker->city());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $manager->persist($user);
            $users[] = $user;
        }

        // 👨‍💼 Create 3 admins
        for ($i = 0; $i < 3; $i++) {
            $admin = new User();
            $admin->setEmail("admin$i@ecogarden.test");
            $admin->setCity($faker->city());
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));

            $manager->persist($admin);
            $users[] = $admin;
        }

        // 🌱 Create 10 pieces of advice
        for ($i = 0; $i < 10; $i++) {
            $advice = new Advice();
            $advice->setDescription($faker->paragraph());
            $advice->setMonth($faker->randomElements(range(1, 12), $faker->numberBetween(1, 3)));
            $advice->setCreatedBy($faker->randomElement($users));

            $manager->persist($advice);
        }

        $manager->flush();
    }
}
