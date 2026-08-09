<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a lookup by id finds nothing — including when the row exists but is
 * soft deleted, which is indistinguishable from "gone" to every caller.
 *
 * The code is the HTTP status on purpose: every controller in this app ends with
 * `response()->error($th->getMessage(), $th->getCode() ?: 500)`, so carrying 404
 * here is what turns a missing record into a 404 instead of a 500, without
 * touching a single controller. Eloquent's own ModelNotFoundException carries
 * code 0, which is why findOrFail surfaces as a 500 app-wide.
 */
class RecordNotFoundException extends Exception
{
    public function __construct(string $message = 'Record not found')
    {
        parent::__construct($message, 404);
    }
}
