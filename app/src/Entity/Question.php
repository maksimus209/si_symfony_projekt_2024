<?php
/**
 * Question entity.
 */

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Question.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'questions')]
class Question
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Created at.
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotNull(message: 'validators.question.created_at.not_null')]
    #[Assert\Type(\DateTimeImmutable::class, message: 'validators.question.created_at.type')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotNull(message: 'validators.question.updated_at.not_null')]
    #[Assert\Type(\DateTimeImmutable::class, message: 'validators.question.updated_at.type')]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Title.
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'validators.question.title.not_blank')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'validators.question.title.length_min',
        maxMessage: 'validators.question.title.length_max'
    )]
    private ?string $title = null;

    /**
     * Slug.
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Gedmo\Slug(fields: ['title'])]
    private ?string $slug = null;

    /**
     * Content.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'validators.question.content.not_blank')]
    #[Assert\Length(
        min: 10,
        minMessage: 'validators.question.content.length_min'
    )]
    private ?string $content = null;

    /**
     * Category.
     */
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'questions', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'validators.question.category.not_null')]
    private ?Category $category = null;

    /**
     * Answers.
     *
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class)]
    private Collection $answers;

    /**
     * Tags.
     *
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'questions')]
    #[ORM\JoinTable(name: 'question_tag')]
    private Collection $tags;

    /**
     * Author of the question.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'validators.question.author.not_null')]
    private ?User $author = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for created at.
     *
     * @return \DateTimeImmutable|null Created at
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for created at.
     *
     * @param \DateTimeImmutable|null $createdAt Created at
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for updated at.
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updated at.
     *
     * @param \DateTimeImmutable|null $updatedAt Updated at
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string|null $title Title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for content.
     *
     * @return string|null Content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Setter for content.
     *
     * @param string|null $content Content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * Getter for category.
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setter for category.
     *
     * @param Category|null $category Category
     *
     * @return $this
     */
    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Getter for answers.
     *
     * @return Collection<int, Answer> Answers collection
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * Add an answer.
     *
     * @param Answer $answer Answer entity
     *
     * @return $this
     */
    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    /**
     * Remove an answer.
     *
     * @param Answer $answer Answer entity
     *
     * @return $this
     */
    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag> Tags collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add a tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return $this
     */
    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addQuestion($this);
        }

        return $this;
    }

    /**
     * Remove a tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return $this
     */
    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeQuestion($this);
        }

        return $this;
    }

    /**
     * Getter for author.
     *
     * @return User|null Author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for author.
     *
     * @param User $author Author
     *
     * @return $this
     */
    public function setAuthor(User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
