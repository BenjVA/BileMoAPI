<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($prdct = 0; $prdct < 25; $prdct++) {
            $product = new Product();
            $product->setName($faker->word)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setDescription($faker->sentences(5, true))
                ->setPrice($faker->randomFloat(1, 75, 1500));
            $manager->persist($product);
        }
        $manager->flush();
    }
}
