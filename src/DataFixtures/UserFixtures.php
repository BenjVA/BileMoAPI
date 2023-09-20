<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\CustomerRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function __construct(private CustomerRepository $customerRepository)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $customers = $this->customerRepository->findAll();

        foreach ($customers as $customer) {
            for ($usr = 0; $usr < mt_rand(3, 10); $usr++) {
                $user = new User();
                $user->setFirstName($faker->firstName)
                    ->setLastName($faker->lastName)
                    ->setPhoneNumber($faker->phoneNumber)
                    ->setEmail($faker->email)
                    ->setPassword($faker->password)
                    ->setCustomer($customer);
                $manager->persist($user);
            }
        }
        $manager->flush();
    }
}
