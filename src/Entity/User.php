<?php

/**
 * User entity.
 */

namespace App\Entity;

use App\Entity\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'email_idx', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Primary key.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Email.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email;

    /**
     * Roles.
     *
     * @var array<int, string>
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * Password.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    private ?string $password;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    /**
     * Username.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\NotBlank]
    private ?string $username = null;

    /**
     * @var Collection<int, Album>
     */
    #[ORM\ManyToMany(targetEntity: Album::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'favorites')]
    private Collection $favorites;


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->comments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    /**
     * Getter for id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for email.
     *
     * @return string|null Email
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Setter for email.
     *
     * @param string $email Email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @return string User identifier
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     *
     * @return string Username
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * Getter for roles.
     *
     * @return array<int, string> Roles
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = UserRole::ROLE_USER->value;

        return array_unique($roles);
    }

    /**
     * Setter for roles.
     *
     * @param array<int, string> $roles Roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Getter for password.
     *
     * @return string|null Password
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Setter for password.
     *
     * @param string $password User password
     *
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @return string|null
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Removes sensitive information from the token.
     *
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Add a comment to the user's collection.
     *
     * @param Comment $comment Comment object to add
     *
     * @return $this
     */
    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    /**
     * Remove a comment from the user's collection.
     *
     * @param Comment $comment Comment object to remove
     *
     * @return $this
     */
    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Set the username for the user.
     *
     * @param string $username Username to set
     *
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, Album>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    /**
     * Add an album to the user's favorites.
     *
     * @param Album $favorite Album object to add
     *
     * @return $this
     */
    public function addFavorite(Album $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->addUser($this);
        }

        return $this;
    }

    /**
     * Remove an album from the user's favorites.
     *
     * @param Album $favorite Album object to remove
     *
     * @return $this
     */
    #[Route('/album/{id}/remove-favorite', name: 'remove_favorite')]
    public function removeFavorite(Album $favorite): self
    {
        if ($this->favorites->removeElement($favorite)) {
            $favorite->removeUser($this);
        }

        return $this;
    }
}
