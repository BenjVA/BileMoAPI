<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($cstmr = 0; $cstmr < 10; $cstmr++) {
            $customer = new Customer();
            $customer->setName($faker->company)
                ->setEmail($faker->companyEmail)
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->passwordHasher->hashPassword($customer, 'password'));
            $manager->persist($customer);
        }
        $manager->flush();
    }
}
