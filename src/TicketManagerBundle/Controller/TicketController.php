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
 * Ticket controller.
 *
 * @Route("ticket")
 */
class TicketController extends Controller
{
    /**
     *
     * @Route("/create", name="create")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user) {
            throw new \Exception('Vous devez être connecter');
        }
        $ticket = new Ticket();

        $form = $this->createForm('TicketManagerBundle\Form\TicketType', $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $ticket->setOwner($user);
            $em->persist($ticket);
            $em->flush();
            return $this->redirectToRoute('ticket_show', ['id' => $ticket->getId()]);
        }

        return $this->render('TicketManagerBundle:Post:create.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a ticket entity.
     *
     * @Route("/{id}", name="ticket_show")
     * @Method("GET")
     */
    public function showAction(Ticket $ticket, Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$user->getId()) {
            throw new \Exception('Vous n\'avez pas la permission');
        }
        if (!$user->hasRole('ROLE_ADMIN')) {
            if ($user != $ticket->getOwner() && !in_array($user, $ticket->getUsers()->getValues())) {
                throw new \Exception('Vous n\'avez pas la permission');
            }
        }
        $deleteForm = $this->createDeleteForm($ticket);

        $message = new Message();

        $form = $this->createForm('TicketManagerBundle\Form\MessageType', $message);

        $messages = $this->getDoctrine()->getRepository('TicketManagerBundle:Message')->findBy(['ticket' => $ticket], array('id' => 'ASC'));

        return $this->render('TicketManagerBundle:Post:ticket-content.html.twig', [
            'messages' => $messages,
            'ticket' => $ticket,
            'delete_form' => $deleteForm->createView(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing ticket entity.
     *
     * @Route("/{id}/edit", name="ticket_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Ticket $ticket)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user->hasRole('ROLE_ADMIN')) {
            throw new \Exception('Vous n\'avez pas la permission');
        }

        $deleteForm = $this->createDeleteForm($ticket);

        $editForm = $this->createForm('TicketManagerBundle\Form\TicketType', $ticket);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ticket_show', ['id' => $ticket->getId()]);
        }
        return $this->render('TicketManagerBundle:Post:ticket-edition.html.twig', [
            'ticket' => $ticket,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a ticket entity.
     *
     * @Route("/{id}", name="ticket_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Ticket $ticket)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user->hasRole('ROLE_ADMIN')) {
            throw new \Exception('You have no permission');
        }

        $form = $this->createDeleteForm($ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $messages = $em
                ->getRepository('TicketManagerBundle:Message')
                ->findBy(['ticket' => $ticket], ['id' => 'DESC']);

            foreach ($messages as $message) {
                $em->remove($message);
                $em->flush();
            }

            $em->remove($ticket);
            $em->flush();
        }
        return $this->redirectToRoute('ticket_index');
    }

    /**
     * Creates a form to delete a ticket entity.
     *
     * @param Ticket $ticket The ticket entity
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createDeleteForm(Ticket $ticket)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ticket_delete', array('id' => $ticket->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}