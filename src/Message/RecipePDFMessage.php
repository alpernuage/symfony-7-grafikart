<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class RecipePDFMessage
{
     public function __construct(
         public readonly int $id,
     ) {
     }
}
