<?php

namespace App\Exceptions;

use Exception;

class InvalidTransferException extends Exception
{
    public function __construct($message = 'Transferência inválida', $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
        ], 422);
    }
}