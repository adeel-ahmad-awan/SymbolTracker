<?php

namespace App\Entity;

use App\Repository\CompanySymbolRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 */
#[ORM\Entity(repositoryClass: CompanySymbolRepository::class)]
class CompanySymbol
{
    /**
     * @var null|int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var null|string
     */
    #[ORM\Column(length: 6)]
    private ?string $symbol = null;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     *
     * @return $this
     */
    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;
        return $this;
    }
}
