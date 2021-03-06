<?php

namespace VolunteerManagementSystem\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use VolunteerManagementSystem\EventBundle\Form\EventType;
use VolunteerManagementSystem\EventBundle\Entity\Event;
use Symfony\Component\HttpFoundation\Request;

class CreateEventController extends Controller {

    public function createEventAction(Request $request) {
        $id = $request->get('id');
        $pid = $request->get('projectId');

        $em = $this->getDoctrine()->getEntityManager();

        $repository = $em->getRepository('VolunteerManagementSystemRegistrationBundle:User');
        $projects = $em->getRepository('VolunteerManagementSystemProjectBundle:Project');

        $project = $projects->findOneBy(array('id' => $pid));
        $pname = $project->getName();
        $pm = $repository->findOneBy(array('id' => $project->getProjectmanager()));
        $user = $repository->findOneBy(array('id' => $id));
        if ($user->getAccessLevel() == 'Admin' || $user == $pm) {

            $event = new Event();
          
            $form = $this->createForm(new EventType(), $event, array(
                'action' => $this->generateUrl('save_event', array('id' => $id, 'pid' => $pid, 'pname' => $pname)),
            ));

            return $this->render('VolunteerManagementSystemEventBundle:Default:createevent.html.twig', array('form' => $form->createView(),'pid'=>$pid, 'id' => $id, 'pname' => $pname));
        }

        return $this->render('VolunteerManagementSystemPagesBundle:Error:adminerror.html.twig', array('id' => $id));
    }

    public function eventSaveAction(Request $request) {
        
        $em = $this->getDoctrine()->getEntityManager();
        $em2 = $this->getDoctrine()->getEntityManager();
        $id = $request->get('id');
        $pid = $request->get('pid');
        $repositoryE =$em2->getRepository('VolunteerManagementSystemEventBundle:Event');
        
        $event = new Event();
        $event->setProject($pid);
        $array = array();
        $event->setVolunteerslist($array);
        $event->setSubscribers($array);
        $form = $this->createForm(new EventType(), $event);

        $form->handleRequest($request);

        if ($form->isValid()) {

            //   $event = $form->getData();
            $enddate =null;
            $endtime = $event->getDeadlinetime();
            $event->setEnddate($enddate);
            $event->setEndtime($endtime);
            $ename=$event->getName();
            $Teamleader = $event->getTeamleader();
            $tn=$Teamleader->getUsername();
            $teamleaderid = $Teamleader->getId();
            
            $event->setTeamleader($teamleaderid);

            $em2->persist($event);
            $em2->flush();
            $eve = $repositoryE->findOneBy(array('name' => $ename));
            $eid=$eve->getId();
            $projects = $em->getRepository('VolunteerManagementSystemProjectBundle:Project');
            $project = $projects->findOneBy(array('id' => $pid));
            $array = $project->getEvents();
            $array[] = $eid;
            $project->setEvents($array);
            $em->persist($project);
            $em->flush();
        }

        

        return $this->redirect($this->generateUrl('confirm_event', array('tl' =>$tn,'tlid'=>$teamleaderid,'event'=>$ename,  ' pid'=>$pid,'id' => $id,'eid'=>$event->getId())));
     }

        


}


