<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class UsersFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordEncoder, private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Users();
        $admin->setEmail('will@remi.fr');
        $admin->setLastname('Willebrouck');
        $admin->setFirstname('Rémi');
        $admin->setAddress('6 rue de La Tour');
        $admin->setZipcode('62300');
        $admin->setCity('Lens');
        $admin->setPassword(
            $this->passwordEncoder->hashPassword($admin, 'azerty')
        );
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        $faker = Faker\Factory::create('fr_FR');

        for ($usr = 1; $usr <= 5; $usr++) {
            $user = new Users();
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastName);
            $user->setFirstname($faker->firstName);
            $user->setAddress($faker->streetAddress);
            $user->setZipcode(str_replace(' ', '', $faker->postcode));
            $user->setCity($faker->city);
            $user->setPassword(
                $this->passwordEncoder->hashPassword($user, 'secret')
            );

            $manager->persist($user);
        }

        $manager->flush();
    }
}
