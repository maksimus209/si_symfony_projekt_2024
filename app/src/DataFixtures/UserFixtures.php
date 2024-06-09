<?php
/**
 * User fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserFixtures.
 */
class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private Generator $faker;
    private ObjectManager $manager;

    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create();
    }

    /**
     * Load data.
     *
     * @param ObjectManager $manager Object manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        for ($i = 0; $i < 2; ++$i) {
            $user = new User();
            $user->setEmail(sprintf('user%d@gmail.com', $i));
            $user->setRoles([UserRole::ROLE_USER->value]);
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    'user1234'
                )
            );
            $manager->persist($user);

            // Dodanie referencji do użytkownika
            $this->addReference('user_reference_'.$i, $user);
        }

        for ($i = 0; $i < 2; ++$i) {
            $admin = new User();
            $admin->setEmail(sprintf('admin%d@gmail.com', $i));
            $admin->setRoles([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
            $admin->setPassword(
                $this->passwordHasher->hashPassword(
                    $admin,
                    'admin1234'
                )
            );
            $manager->persist($admin);

            // Dodanie referencji do administratora
            $this->addReference('admin_reference_'.$i, $admin);
        }

        $manager->flush();
    }
}