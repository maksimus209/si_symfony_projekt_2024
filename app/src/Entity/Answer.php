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
    #[Assert\NotBlank]
    private ?string $content = null;

    /**
     * Creation date of the answer.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Question associated with the answer.
     */
    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    /**
     * Author of the answer.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $author = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get the ID of the answer.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the content of the answer.
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
     * Best answer flag.
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isBest = false;

    /**
     * Getter for isBest.
     *
     * @return bool
     */
    public function getIsBest(): bool
    {
        return $this->isBest;
    }

    /**
     * Setter for isBest.
     *
     * @param bool $isBest
     */
    public function setIsBest(bool $isBest): static
    {
        $this->isBest = $isBest;

        return $this;
    }
}
