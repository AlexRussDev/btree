<?php

namespace App\Entity;

use App\Repository\BtreeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BtreeRepository::class)]

class Btree
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING)]
    private ?string $id = null;

    #[ORM\Column(type: Types::JSON)]
    private array $content = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }
}
