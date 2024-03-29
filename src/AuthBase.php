<?php

namespace JMD\Auth;

use JMD\Auth\Exceptions\ExpiredException;
use JMD\Auth\Exceptions\SignatureTokenException;
use JMD\Auth\Models\Ticket;
use JMD\Auth\Util\AuthUtil;

class AuthBase
{
    public static function getUser()
    {
        if (!$token = AuthUtil::requestToken()) {
            throw new SignatureTokenException('无效 TOKEN');
        }
        return self::getUserByToken($token);
    }

    public static function getUserByToken($token)
    {
        $ticketModel = new Ticket();
        $ticket = $ticketModel->findOneByToken($token);
        if (empty($ticket)) {
            throw new SignatureTokenException('无效 TOKEN');
        }
        if (self::tokenIsExpired($ticket)) {
            throw new ExpiredException('TOKEN 已失效');
        }
        $userModel = AuthUtil::getUserModel();
        $user = (new $userModel)->getUserByIdToJAuth($ticket->uid);
        return $user;
    }

    public static function tokenIsExpired(Ticket $ticket)
    {
        return $ticket->expiration < AuthUtil::currentTime();
    }

    public static function guardNameIsValid($guard)
    {
        $arr = AuthUtil::getGuardConfig();
        return array_key_exists($guard, $arr);
    }
}
