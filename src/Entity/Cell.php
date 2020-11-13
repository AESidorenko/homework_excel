<?php

namespace App\Entity;

use App\Repository\CellRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CellRepository::class)
 * @ORM\Table(name="cell", uniqueConstraints={
 *      @ORM\UniqueConstraint(
 *          name="cell_coordinates",
 *          columns={"sheet_id", "row", "col"}
 *      )
 * })
 */
class Cell
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Sheet::class, inversedBy="cells")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sheet;

    /**
     * @ORM\Column(type="integer")
     */
    private $row;

    /**
     * @ORM\Column(type="integer")
     */
    private $col;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSheet(): ?Sheet
    {
        return $this->sheet;
    }

    public function setSheet(?Sheet $sheet): self
    {
        $this->sheet = $sheet;

        return $this;
    }

    public function getRow(): ?int
    {
        return $this->row;
    }

    public function setRow(int $row): self
    {
        $this->row = $row;

        return $this;
    }

    public function getCol(): ?int
    {
        return $this->col;
    }

    public function setCol(int $col): self
    {
        $this->col = $col;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }
}
