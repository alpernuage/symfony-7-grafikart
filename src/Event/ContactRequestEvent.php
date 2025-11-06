<?php

namespace App\Event;

use App\Dto\ContactDto;

class ContactRequestEvent
{
    public function __construct(public readonly ContactDto $data)
    {
    }
}
