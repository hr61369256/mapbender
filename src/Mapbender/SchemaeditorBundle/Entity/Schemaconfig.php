<?php

namespace Mapbender\SchemaeditorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Mapbender\CoreBundle\Entity\Element;
use Mapbender\CoreBundle\Component\Element As ComponentElement;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Schemaconfig entity
 * 
 * @ORM\Entity
 * @UniqueEntity("title")
 * @UniqueEntity("slug")
 * @ORM\Table(name="mb_schema_config")
 */
class Schemaconfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    protected $description;


    /**
     * @ORM\Column(type="text")
     */
    protected $yml;


    public function getId() {
        return $this->id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getYml() {
        return $this->yml;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setYml($yml) {
        $this->yml = $yml;
        return $this;
    }
}

