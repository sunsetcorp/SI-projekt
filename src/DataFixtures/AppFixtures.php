<?php

/**
 * App fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AppFixtures
 *
 * Fixture class for loading initial data into the database.
 */
class AppFixtures extends Fixture
{
    /**
     * Load method to load data fixtures into the database.
     *
     * @param ObjectManager $manager Doctrine ObjectManager instance
     */
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
