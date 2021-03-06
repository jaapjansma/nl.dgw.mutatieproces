<?php

namespace CiviCoop\VragenboomBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ActieDefinitie
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CiviCoop\VragenboomBundle\Entity\ActieDefinitieRepository")
 */
class ActieDefinitie
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
	 * @Assert\NotBlank()
     * @ORM\Column(name="actie", type="string", length=255)
     */
    private $actie;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="omschrijving", type="text", nullable=true)
	 */
	 private $description;
	 
	 /**
     * @ORM\ManyToOne(targetEntity="Object", inversedBy="acties")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     */
    protected $object;
	
	/**
     * @ORM\ManyToOne(targetEntity="Type")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="verantwoordelijke", type="string", length=255)
     */
    protected $verantwoordelijke;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set actie
     *
     * @param string $actie
     * @return ActieDefinitie
     */
    public function setActie($actie)
    {
        $this->actie = $actie;
    
        return $this;
    }

    /**
     * Get actie
     *
     * @return string 
     */
    public function getActie()
    {
        return $this->actie;
    }

    /**
     * Set verantwoordelijke
     *
     * @param string $verantwoordelijke
     * @return ActieDefinitie
     */
    public function setVerantwoordelijke($verantwoordelijke)
    {
        $this->verantwoordelijke = $verantwoordelijke;
    
        return $this;
    }

    /**
     * Get verantwoordelijke
     *
     * @return string 
     */
    public function getVerantwoordelijke()
    {
        return $this->verantwoordelijke;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ActieDefinitie
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Returns label in the format of Object: actie
     */
    public function getObjectActieLabel() {
      return $this->object->getNaam().': '.$this->actie;
    }

    /**
     * Set object
     *
     * @param \CiviCoop\VragenboomBundle\Entity\Object $object
     * @return ActieDefinitie
     */
    public function setObject(\CiviCoop\VragenboomBundle\Entity\Object $object = null)
    {
        $this->object = $object;
    
        return $this;
    }

    /**
     * Get object
     *
     * @return \CiviCoop\VragenboomBundle\Entity\Object 
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set type
     *
     * @param \CiviCoop\VragenboomBundle\Entity\Type $type
     * @return ActieDefinitie
     */
    public function setType(\CiviCoop\VragenboomBundle\Entity\Type $type = null)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return \CiviCoop\VragenboomBundle\Entity\Type 
     */
    public function getType()
    {
        return $this->type;
    }
}
