<?php

namespace CiviCoop\VragenboomBundle\Entity\Factory;

use CiviCoop\VragenboomBundle\Entity\AdviesRapport;
use CiviCoop\VragenboomBundle\Entity\AdviesRapportRegel;
use CiviCoop\VragenboomBundle\Entity\Ruimte;
use CiviCoop\VragenboomBundle\Entity\Object;
use CiviCoop\VragenboomBundle\Entity\ActieDefinitie;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Assert\Callback(methods={"isAllValid"})
 */
class AdviesRapportFactory {

	/**
	 * @var Ruimte
	 */
	private $ruimte;
	
	/**
	 * @var Object
	 */
	private $object;
	
	/**
	 * @var ActieDefinitie
	 */
	private $actie;
	
	/**
	 * @var String
	 */
	private $remark;
	
	/**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }
	
	public function setRuimte(Ruimte $ruimte) {
		$this->ruimte = $ruimte;
		return $this;
	}
	
	public function setObject(Object $object) {
		$this->object = $object;
		return $this;
	}
	
	public function setActie(ActieDefinitie $actie) {
		$this->actie = $actie;
		return $this;
	}
	
	public function setRemark($remark) {
		$this->remark = $remark;
		return $this;
	}
	
	public function getRuimte() {
		return $this->ruimte;
	}
	
	public function getObject() {
		return $this->object;
	}
	
	public function getActie() {
		return $this->actie;
	}
	
	public function getRemark() {
		return $this->remark;
	}
	
	/**
     * @param  ExecutionContext $context
     * @return bool
     */
    public function isAllValid(ExecutionContext $context)
    {
        if ($this->ruimte != $this->object->getRuimte()) {
			 $context->addViolation('Invalid object', array(), $this->object);
		}
		
		if ($this->object != $this->actie->getObject()) {
			 $context->addViolation('Invalid actie', array(), $this->object);
		}
		
		//return true;
    }
	
	/**
     * @return \CiviCoop\VragenboomBundle\Entity\AdviesRapportRegel
     */
    public function make(AdviesRapport $rapport)
    {
        $regel = new AdviesRapportRegel();
		$regel->setActie($this->actie);
		$regel->setRemark($this->remark);
		$regel->setAdviesRapport($rapport);
		
		$rapport->addRegel($regel);

        return $regel;
    }

}