<?php

namespace TicketManagerBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Ticket", mappedBy="users")
     */
    private $ticket;

    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->roles = array('ROLE_USER');

        $this->ticket = new \Doctrine\Common\Collections\ArrayCollection();
    }
    /**
     * Add ticket
     *
     * @param \TicketManagerBundle\Entity\Ticket $ticket
     *
     * @return User
     */
    public function addTicket(\TicketManagerBundle\Entity\Ticket $ticket)
    {
        $this->ticket[] = $ticket;
        return $this;
    }
    /**
     * Remove ticket
     *
     * @param \TicketManagerBundle\Entity\Ticket $ticket
     */
    public function removeTicket(\TicketManagerBundle\Entity\Ticket $ticket)
    {
        $this->ticket->removeElement($ticket);
    }
    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTickets()
    {
        return $this->ticket;
    }

    /**
     * Get ticket
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}
