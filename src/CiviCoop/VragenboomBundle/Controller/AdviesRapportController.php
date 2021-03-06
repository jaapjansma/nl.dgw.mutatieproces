<?php

namespace CiviCoop\VragenboomBundle\Controller;

use CiviCoop\VragenboomBundle\Entity\ToekomstAdres;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CiviCoop\VragenboomBundle\Model\Client as ClientModel;
use CiviCoop\VragenboomBundle\Form\ClientType;
use CiviCoop\VragenboomBundle\Form\AfdVerhuurType;
use CiviCoop\VragenboomBundle\Form\EindopnameType;

/**
 * AdviesRapport controller.
 *
 * @Route("/")
 */
class AdviesRapportController extends AbstractController {

  /**
   * Lists all AdviesRapport entities.
   *
   * @Route("/", name="adviesrapport")
   * @Method("GET")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:index.html.twig")
   */
  public function indexAction() {
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    $entities = $factory->findAllCombinedActive();
    
    /*($adviesgesprek = $em->getRepository('CiviCoopVragenboomBundle:AdviesRapport')->findAllActive();
    $eindgesprek = $em->getRepository('CiviCoopVragenboomBundle:EindRapport')->findAllActive();*/

    return array(
      'entities' => $entities,
      'factory' => $factory,
    );
  }
  
  /**
   * Syncs
   *
   * @Route("/sync", name="adviesrapport_sync")
   * @Method("GET")
   */
  public function sync() {
    $civicontact = $this->get('civicoop.dgw.mutatieproces.civicontact');
		$civicontact->sync();
        
    $civicase = $this->get('civicoop.dgw.mutatieproces.civicase');
		$civicase->sync();
    
    return $this->redirect($this->generateUrl('adviesrapport'));
  }

  /**
   * Finds and displays a AdviesRapport entity.
   *
   * @Route("/{shortname}/{id}", name="adviesrapport_show")
   * @Method("GET")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:show.html.twig")
   */
  public function showAction($shortname, $id) {
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    
    $entity = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Rapport entity.');
    }
    
    $showStatus = false;
    if ($factory->getEntity($entity) == 'CiviCoopVragenboomBundle:EindRapport') {
      //do loading from other reports
     $this->loadEindGesprekRapport($entity);
     $showStatus = true;
    }

    $editForm = $this->createForm($factory->getNewForm($factory->getEntity($entity)), $entity);

    return array(
      'entity' => $entity,
      'factory' => $factory,
      'edit_form' => $editForm->createView(),
      'showStatus' => $showStatus,
    );
  }
  
  protected function loadEindGesprekRapport($rapport) {
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    $reports = $em->getRepository($factory->getEntityFromShortname('vooropname'))->findByCaseId($rapport->getCaseId());
    foreach($reports as $rep) {
      foreach($rep->getRegels() as $regel) {
        $contains = false;
        foreach($rapport->getRegels() as $rregel) {
          if ($rregel->getAdviesRapportRegel() && $rregel->getAdviesRapportRegel() == $regel) {
            $contains = true;
            break;
          }
        }
        
        if (!$contains) {
          $eind_regel = new \CiviCoop\VragenboomBundle\Entity\EindRapportRegel();
          $eind_regel->setAdviesRapportRegel($regel);
          $eind_regel->setRapport($rapport);
          $rapport->addRegel($eind_regel);
          $em->persist($eind_regel);
        }
      }
    }
    
    $em->persist($rapport);
    $em->flush();
  }

  /**
   * Edits an existing AdviesRapport entity.
   *
   * @Route("/{shortname}/{id}/update", name="adviesrapport_update")
   * @Method("PUT")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:show.html.twig")
   */
  public function updateAction(Request $request, $shortname, $id) {
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');


      if ($request->request->get('back_to_overview')) {
          return $this->redirect($this->generateUrl('adviesrapport'));
      }
    
    $entity = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find AdviesRapport entity.');
    }

    $editForm = $this->createForm($factory->getNewForm($factory->getEntity($entity)), $entity);
    $editForm->bind($request);

    if ($editForm->isValid()) {
      if ($factory->getEntity($entity) == 'CiviCoopVragenboomBundle:EindRapport') {
        $status = $request->get('status');
        foreach($status as $rid => $s ) {
          foreach($entity->getRegels() as $regel) {
            if ($regel->getId() == $rid) {
              $regel->setStatus($s);
              $em->persist($regel);
              break;
            }
          }
        }
      }
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('adviesrapport_show', array('id' => $id, 'shortname' => $factory->getShortName($entity))));
    }

    return array(
      'entity' => $entity,
      'regel_form' => $regel_form->createView(),
    );
  }
  
  /**
   * Close an AdviesRapport entity.
   *
   * @Route("/{shortname}/{id}/close", name="adviesrapport_close")
   * @Method("GET")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:close.html.twig")
   */
  public function closeAction(Request $request, $shortname, $id) {
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    
    $entity = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find AdviesRapport entity.');
    }
    
    $entityType = $factory->getEntity($entity);
    $rapport = $factory->getRapportGenerator($entityType)->createReport($entity);
    $this->get('civicoop.dgw.mutatieproces.civicase')->closeActivity($entity->getActivityId(), $rapport);
    $entity->setClosed(true);
    
    $em->persist($entity);
    $em->flush();

    return $this->redirect($this->generateUrl('adviesrapport'));
  }
  
  /**
   * Display an edit form for planning the eindopname
   *
   * @Route("/{shortname}/{id}/eindopname/", name="adviesrapport_show_eindopname")
   * @Method("GET")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:show_eindopname.html.twig")
   */
  public function showEindopnameAction($shortname, $id) {
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    
    $rapport = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$rapport) {
      throw $this->createNotFoundException('Unable to find Rapport entity.');
    }
    
    $eindopname = $rapport->getEindopname();
    if (empty($eindopname)) {
      $eindopname = new \DateTime();
      $eindopname->setTime(10,0,0);
      $rapport->setEindopname($eindopname);
    }

    $editForm = $this->createForm(new EindopnameType(), $rapport);

    return array(
      'rapport' => $rapport,
      'factory' => $factory,
      'edit_form' => $editForm->createView(),
    );
  }
  
  /**
   * Edits an existing client entity.
   *
   * @Route("/{shortname}/{id}/eindopname/update", name="adviesrapport_update_eindopname")
   * @Method("PUT")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:show_eindopname.html.twig")
   */
  public function updateEindopnameAction(Request $request, $shortname, $id) {
    
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    
    $rapport = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$rapport) {
      throw $this->createNotFoundException('Unable to find Rapport entity.');
    }

    $editForm = $this->createForm(new EindopnameType(), $rapport);
    $editForm->bind($request);
    
   if ($editForm->isValid()) {
      $em->persist($rapport);
      $em->flush();
      $civi_user_id = $this->get('security.context')->getToken()->getAttribute('civicrm_contact_id');
      $civicase = $this->get('civicoop.dgw.mutatieproces.civicase');
      $civicase->createEindopname($rapport, $civi_user_id);
      
      return $this->redirect($this->generateUrl('adviesrapport'));
   }
    return array(
      'rapport' => $rapport,
      'factory' => $factory,
      'edit_form' => $editForm->createView(),
    );
  }
  
  /**
   * Display a client edit form.
   *
   * @Route("/{shortname}/{id}/client/{client_id}", name="adviesrapport_show_client")
   * @Method("GET")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:show_client.html.twig")
   */
  public function showClientAction($shortname, $id, $client_id) {
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    
    $rapport = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$rapport) {
      throw $this->createNotFoundException('Unable to find Rapport entity.');
    }
    
    $client = $em->getRepository('CiviCoopVragenboomBundle:Client')->findOneById($client_id);
    if (!$client) {
      throw $this->createNotFoundException('Unable to find client entity.');
    }

    $clientModel = new ClientModel($client, $rapport);

    $editForm = $this->createForm(new ClientType(), $clientModel);

    return array(
      'rapport' => $rapport,
      'client' => $clientModel,
      'factory' => $factory,
      'edit_form' => $editForm->createView(),
    );
  }
  
  /**
   * Edits an existing client entity.
   *
   * @Route("/{shortname}/{id}/client/{client_id}/update", name="adviesrapport_update_client")
   * @Method("PUT")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:show_client.html.twig")
   */
  public function updateClientAction(Request $request, $shortname, $id, $client_id) {
    
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    
    $rapport = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$rapport) {
      throw $this->createNotFoundException('Unable to find Rapport entity.');
    }
    
    $client = $em->getRepository('CiviCoopVragenboomBundle:Client')->findOneById($client_id);
    if (!$client) {
      throw $this->createNotFoundException('Unable to find client entity.');
    }

    $clientModel = new ClientModel($client, $rapport);

    $editForm = $this->createForm(new ClientType(), $clientModel);
    $editForm->bind($request);
    
   if ($editForm->isValid()) {
      $em->persist($clientModel->getRapport());
      $em->persist($clientModel->getClient());
      $em->flush();
      
      $civicontact = $this->get('civicoop.dgw.mutatieproces.civicontact');
      $civicontact->updateContact($clientModel->getClient());

      $civicase = $this->get('civicoop.dgw.mutatieproces.civicase');
      $civicase->updateInfoAfdVerhuur($clientModel->getRapport());

      return $this->redirect($this->generateUrl('adviesrapport_show', array('id' => $id, 'shortname' => $factory->getShortName($rapport))));
    }

    return array(
      'rapport' => $rapport,
      'client' => $client,
      'factory' => $factory,
      'edit_form' => $editForm->createView(),
    );
  }
  
  
  /**
   * Display an edit form for info for afdeling verhuur
   *
   * @Route("/{shortname}/{id}/verhuur", name="adviesrapport_show_afdverhuur")
   * @Method("GET")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:show_afdverhuur.html.twig")
   */
  public function showAfdVerhuurAction($shortname, $id) {
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    
    $rapport = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$rapport) {
      throw $this->createNotFoundException('Unable to find Rapport entity.');
    }

    $editForm = $this->createForm(new AfdVerhuurType(), $rapport);

    return array(
      'rapport' => $rapport,
      'factory' => $factory,
      'edit_form' => $editForm->createView(),
    );
  }
  
  /**
   * Edits info for afdeling verhuur
   *
   * @Route("/{shortname}/{id}/verhuur/update", name="adviesrapport_update_afdverhuur")
   * @Method("PUT")
   * @Template("CiviCoopVragenboomBundle:AdviesRapport:show_afdverhuur.html.twig")
   */
  public function updateAfdVerhuurAction(Request $request, $shortname, $id) {
    
    $em = $this->getDoctrine()->getManager();
    $factory = $this->get('civicoop.vragenboom.rapportfactory');
    
    $rapport = $em->getRepository($factory->getEntityFromShortname($shortname))->findOneById($id);

    if (!$rapport) {
      throw $this->createNotFoundException('Unable to find Rapport entity.');
    }

    $editForm = $this->createForm(new AfdVerhuurType(), $rapport);
    $editForm->bind($request);
    
   if ($editForm->isValid()) {
      $em->persist($rapport);
      $em->flush();
      
      $civicase = $this->get('civicoop.dgw.mutatieproces.civicase');
      $civicase->updateInfoAfdVerhuur($rapport);
      
      return $this->redirect($this->generateUrl('adviesrapport_show', array('id' => $id, 'shortname' => $factory->getShortName($rapport))));
    }

    return array(
      'rapport' => $rapport,
      'factory' => $factory,
      'edit_form' => $editForm->createView(),
    );
  }

}
