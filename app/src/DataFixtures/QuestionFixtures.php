<?php
/**
 * Question fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Question;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class QuestionFixtures.
 */
class QuestionFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class, 1: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            UserFixtures::class,
        ];
    }

    /**
     * Load data.
     */
    protected function loadData(): void
    {
        if (null === $this->faker || null === $this->manager) {
            return;
        }

        // Pobierz użytkownika z UserFixtures
        $user = $this->getReference('user_reference_0'); // Użyjemy pierwszego użytkownika

        $this->createMany(100, 'questions', function (int $i) use ($user) {
            $question = new Question();
            $question->setTitle($this->faker->sentence);
            $question->setContent($this->faker->paragraph); // Dodaj content
            $question->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $question->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $question->setCategory($category);
            $question->setAuthor($user); // Ustawienie autora pytania

            return $question;
        });

        $this->manager->flush();
    }
}
