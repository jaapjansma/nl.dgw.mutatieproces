<?php

namespace CiviCoop\VragenboomBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CiviCoop\VragenboomBundle\Entity\Factory\AdviesRapportFactory;
use CiviCoop\VragenboomBundle\Entity\AdviesRapportRegel;
use CiviCoop\VragenboomBundle\Entity\AdviesRapport;
use CiviCoop\VragenboomBundle\Form\AdviesRapportFactoryType;

/**
 * AdviesRapportRegel controller.
 *
 * @Route("/adviesrapport/{rapport}")
 */
class AdviesRapportRegelController extends Controller
{

    /**
     * Creates a new AdviesRapportRegel entity.
     *
     * @Route("/create", name="adviesrapportregel_create")
     * @Method("POST")
     * @Template("CiviCoopVragenboomBundle:AdviesRapportRegel:new.html.twig")
     */
    public function createAction(Request $request, $rapport)
    {
		$em = $this->getDoctrine()->getManager();
        $rapport = $em->getRepository('CiviCoopVragenboomBundle:AdviesRapport')->find($rapport);
        $entity = new AdviesRapportFactory($em);
        $form   = $this->createForm(new AdviesRapportFactoryType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
			$regel = $entity->make($rapport);
			$em->persist($regel);
            $em->flush();
            return $this->redirect($this->generateUrl('adviesrapport_show', array('id' => $rapport->getId())));
        }
		
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
			'rapport' => $rapport,
        );
    }

    /**
     * Displays a form to create a new AdviesRapportRegel entity.
     *
     * @Route("/new", name="adviesrapportregel_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($rapport)
    {
		$em = $this->getDoctrine()->getManager();
        $rapport = $em->getRepository('CiviCoopVragenboomBundle:AdviesRapport')->find($rapport);
		
        $entity = new AdviesRapportFactory($em);
        $form   = $this->createForm(new AdviesRapportFactoryType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
			'rapport' => $rapport,
        );
    }
	
	/**
	 * @Route("/objects", name="adviesrapportregel_objects")
	 */
	public function listObjects()
	{
		$this->em = $this->get('doctrine')->getEntityManager();
		$this->repository = $this->em->getRepository('CiviCoopVragenboomBundle:Object');
 
		$id = $this->get('request')->query->get('data');
 
		$object = $this->repository->findByRuimte($id);
 
		if (empty($object)) {
			return new Response('<option>Geen objecten</option>');
		}
 
		$html = '<option selected="selected" disabled="disabled">Kies een object</option>';
		foreach($object as $o)
		{
			$html = $html . sprintf("<option value=\"%d\">%s</option>",$o->getId(), $o->getNaam());
		}
 
		return new Response($html);
	}
	
	/**
	 * @Route("/acties", name="adviesrapportregel_acties")
	 */
	public function listActies()
	{
		$this->em = $this->get('doctrine')->getEntityManager();
		$this->repository = $this->em->getRepository('CiviCoopVragenboomBundle:ActieDefinitie');
 
		$id = $this->get('request')->query->get('data');
 
		$acties = $this->repository->findByObject($id);
 
		if (empty($acties)) {
			return new Response('<option>Geen acties</option>');
		}
 
		$html = '<option selected="selected" disabled="disabled">Kies een actie</option>';
		foreach($acties as $o)
		{
			$html = $html . sprintf("<option data-description=\"%s\" value=\"%d\">%s</option>",$o->getDescription(), $o->getId(), $o->getActie());
		}
 
		return new Response($html);
	}
	
	/**
     * Deletes a Regel entity.
     *
     * @Route("/delete/{id}", name="adviesrapportregel_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $rapport, $id)
    {
		$em = $this->getDoctrine()->getManager();
		$rapport = $em->getRepository('CiviCoopVragenboomBundle:AdviesRapport')->find($rapport);
        $entity = $em->getRepository('CiviCoopVragenboomBundle:AdviesRapportRegel')->findOneById($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find AdviesRapportRegel entity.');
		}

		$em->remove($entity);
		$em->flush();

        return $this->redirect($this->generateUrl('adviesrapport_show', array('id' => $rapport->getid())));
    }
	
}