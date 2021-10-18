<?php
namespace App\Exception;

final class Expired extends DabbaException
{
    public function __construct($message = "", $code = 422, \Throwable $previous = null) {
        parent::__construct($message,$code,$previous);
    }
}