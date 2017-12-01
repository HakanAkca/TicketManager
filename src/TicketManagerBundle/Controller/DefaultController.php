<?php

namespace TicketManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="ticket_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $userConnected = $this->get('security.token_storage')->getToken()->getUser();

        if ($userConnected !== 'anon.') {
            $allTickets = $em->getRepository('TicketManagerBundle:Ticket')->findBy([], ['created' => 'DESC']);

            $userTickets = $em->getRepository('TicketManagerBundle:Ticket')->findBy(['owner' => $userConnected], ['created' => 'DESC']);

            $ticketsGranted = $userConnected->getTickets()->toArray();

            if ($userConnected->hasRole('ROLE_ADMIN')) {
                return $this->render('TicketManagerBundle:Default:index.html.twig', [
                    'tickets' => $allTickets,
                ]);
            }
            return $this->render('TicketManagerBundle:Default:index.html.twig', [
                'tickets_created' => $userTickets,
                'tickets_granted' => $ticketsGranted
            ]);
        } else {
            return $this->render('TicketManagerBundle:Default:index.html.twig');
        }
    }
}
