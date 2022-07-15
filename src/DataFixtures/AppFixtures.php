<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Customer;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName('Téléphone ' . $i);
            $product->setDescription('Description téléphone ' . $i);
            $product->setPrice(($i * 10) + 10);
            $manager->persist($product);
        }

        $user = new User();
        $user->setEmail('spyoo@spyoo.fr');
        $user->setPassword('$2y$10$jM5laK.5N5.0zQ7Zcdi58u93IwV.jBlvJjDHdnnNlI2MLCvctncE6');
        $user->setName('spyoo');
        $manager->persist($user);
        $manager->flush();

        for ($i = 0; $i < 20; $i++) {
            $customer = new Customer();
            $customer->setUser($user);
            $customer->setEmail('email' . $i . '@gmail.com');
            $customer->setFirtName('Client ' . $i . ' Prénom');
            $customer->setLastName('Client ' . $i . ' Nom');
            $manager->persist($customer);
        }
    }
}
