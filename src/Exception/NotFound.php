<?php
namespace App\Exception;

final class NotFound extends DabbaException
{
    public function __construct($message = "", $code = 404, \Throwable $previous = null) {
        parent::__construct($message,$code,$previous);
    }
}