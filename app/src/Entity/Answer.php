<?php
/**
 * Answer entity.
 */

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AnswerRepository::class)
 */
#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Content of the answer.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'validators.answer.content.not_blank')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'validators.answer.content.length_min',
        maxMessage: 'validators.answer.content.length_max'
    )]
    private ?string $content = null;

    /**
     * Creation date of the answer.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'validators.answer.created_at.not_null')]
    #[Assert\Type(\DateTimeInterface::class, message: 'validators.answer.created_at.type')]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Question associated with the answer.
     */
    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'validators.answer.question.not_null')]
    private ?Question $question = null;

    /**
     * Author of the answer.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $author = null;

    /**
     * Best answer flag.
     */
    #[ORM\Column(type: 'boolean')]
    #[Assert\Type('bool', message: 'validators.answer.is_best.type')]
    private bool $isBest = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get the ID of the answer.
     *
     * @return int|null The ID of the answer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the content of the answer.
     *
     * @return string|null The content of the answer
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the content of the answer.
     *
     * @param string $content The content of the answer
     *
     * @return $this
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the creation date of the answer.
     *
     * @return \DateTimeInterface|null The creation date
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Set the creation date of the answer.
     *
     * @param \DateTimeInterface $createdAt The creation date
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the question associated with the answer.
     *
     * @return Question|null The question
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * Set the question associated with the answer.
     *
     * @param Question|null $question The question
     *
     * @return $this
     */
    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get the author of the answer.
     *
     * @return User|null The author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Set the author of the answer.
     *
     * @param User|null $author The author
     *
     * @return $this
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the best answer flag.
     *
     * @return bool The best answer flag
     */
    public function getIsBest(): bool
    {
        return $this->isBest;
    }

    /**
     * Set the best answer flag.
     *
     * @param bool $isBest Best answer flag
     *
     * @return $this
     */
    public function setIsBest(bool $isBest): static
    {
        $this->isBest = $isBest;

        return $this;
    }
}
