<?php

/**
 * Comment entity.
 */

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Comment.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    /**
     * Primary key.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    /**
     * Content of the comment.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    /**
     * Creation date of the comment.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Author of the comment.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * Album to which the comment belongs.
     *
     * @var Album|null
     */
    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    /**
     * Getter for Id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * Getter for content.
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Setter for content.
     *
     * @param string|null $content
     *
     * @return static
     */
    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Getter for createdAt.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for createdAt.
     *
     * @param \DateTimeImmutable|null $createdAt
     *
     * @return static
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Getter for author.
     *
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for author.
     *
     * @param User|null $author
     *
     * @return static
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Getter for album.
     *
     * @return Album|null
     */
    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    /**
     * Setter for album.
     *
     * @param Album|null $album
     *
     * @return static
     */
    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }
}
