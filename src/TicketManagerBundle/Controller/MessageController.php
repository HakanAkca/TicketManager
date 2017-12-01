<?php

namespace TicketManagerBundle\Controller;

use TicketManagerBundle\Entity\Message;
use TicketManagerBundle\Entity\Ticket;
use TicketManagerBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * Message controller.
 *
 * @Route("ticket/message")
 */

class MessageController extends Controller
{
    /**
     * @Route("/{ticketId}/new", name="message_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, $ticketId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user) {
            throw new \Exception('Vous n\'etes pas connecter');
        }

        $message = new Message();

        $form = $this->createForm('TicketManagerBundle\Form\MessageType', $message);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $ticket = $this->getDoctrine()->getRepository('TicketManagerBundle:Ticket')->find($ticketId);

            $message->setTicket($ticket);

            $message->setUser($user);

            $em->persist($message);

            $em->flush();

            return $this->redirectToRoute('ticket_show', ['id' => $ticket->getId()]);
        }
    }
}