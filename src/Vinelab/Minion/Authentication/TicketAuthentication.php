<?php

namespace Vinelab\Minion\Authentication;

use Thruway\Authentication\ClientAuthenticationInterface;
use Thruway\Message\AuthenticateMessage;
use Thruway\Message\ChallengeMessage;

/**
 * Client Ticket Authentication
 *
 * Class TicketAuthentication
 * @package Vinelab\Minion\Authentication
 */
class TicketAuthentication implements ClientAuthenticationInterface
{
    const AUTHENTICATION_METHOD = 'ticket';

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $secret;

    /**
     * ClientAuthentication constructor.
     *
     * @param string $id
     * @param string $secret
     */
    public function __construct($id, $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    /**
     * @return string|null
     */
    public function getAuthId()
    {
        return $this->id;
    }

    /**
     * @param string $authid
     */
    public function setAuthId($authid)
    {
        $this->id = $authid;
    }

    /**
     * @return array
     */
    public function getAuthMethods()
    {
        return [self::AUTHENTICATION_METHOD];
    }

    /**
     * @param ChallengeMessage $msg
     * @return bool|AuthenticateMessage
     */
    public function getAuthenticateFromChallenge(ChallengeMessage $msg)
    {
        return new AuthenticateMessage($this->secret);
    }
}
