<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $section_title = null;

    #[ORM\Column(length: 105)]
    private ?string $section_slug = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $section_detail = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'sections')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getSectionTitle(): ?string
    {
        return $this->section_title;
    }

    public function setSectionTitle(string $section_title): static
    {
        $this->section_title = $section_title;

        return $this;
    }

    public function getSectionSlug(): ?string
    {
        return $this->section_slug;
    }

    public function setSectionSlug(string $section_slug): static
    {
        $this->section_slug = $section_slug;

        return $this;
    }

    public function getSectionDetail(): ?string
    {
        return $this->section_detail;
    }

    public function setSectionDetail(?string $section_detail): static
    {
        $this->section_detail = $section_detail;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->addSection($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            $article->removeSection($this);
        }

        return $this;
    }
}
