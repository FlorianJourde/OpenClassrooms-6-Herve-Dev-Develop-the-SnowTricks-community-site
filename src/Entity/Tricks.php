<?php

namespace App\Entity;

use App\Repository\TricksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TricksRepository::class)]
class Tricks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $tricks_name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $tricks_description = null;

    #[ORM\Column(type:'datetime_immutable', options:['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $tricks_created_at = null;

    #[ORM\ManyToOne(inversedBy: 'Tricks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'tricks', targetEntity: MediaTricks::class, orphanRemoval: true, cascade:['persist'])]
    private Collection $MediaTricks;

    #[ORM\ManyToOne(inversedBy: 'Tricks')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'tricks', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $Comment;

    public function __construct()
    {
        $this->MediaTricks = new ArrayCollection();
        $this->Comment = new ArrayCollection();
        $this->tricks_created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTricksName(): ?string
    {
        return $this->tricks_name;
    }

    public function setTricksName(string $tricks_name): self
    {
        $this->tricks_name = $tricks_name;

        return $this;
    }

    public function getTricksDescription(): ?string
    {
        return $this->tricks_description;
    }

    public function setTricksDescription(string $tricks_description): self
    {
        $this->tricks_description = $tricks_description;

        return $this;
    }

    public function getTricksCreatedAt(): ?\DateTimeImmutable
    {
        return $this->tricks_created_at;
    }

    public function setTricksCreatedAt(\DateTimeImmutable $tricks_created_at): self
    {
        $this->tricks_created_at = $tricks_created_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, MediaTricks>
     */
    public function getMediaTricks(): Collection
    {
        return $this->MediaTricks;
    }

    public function addMediaTrick(MediaTricks $mediaTrick): self
    {
        if (!$this->MediaTricks->contains($mediaTrick)) {
            $this->MediaTricks->add($mediaTrick);
            $mediaTrick->setTricks($this);
        }

        return $this;
    }

    public function removeMediaTrick(MediaTricks $mediaTrick): self
    {
        if ($this->MediaTricks->removeElement($mediaTrick)) {
            // set the owning side to null (unless already changed)
            if ($mediaTrick->getTricks() === $this) {
                $mediaTrick->setTricks(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComment(): Collection
    {
        return $this->Comment;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->Comment->contains($comment)) {
            $this->Comment->add($comment);
            $comment->setTricks($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->Comment->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTricks() === $this) {
                $comment->setTricks(null);
            }
        }

        return $this;
    }
}
