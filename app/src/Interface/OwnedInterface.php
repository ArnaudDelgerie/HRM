<?php

namespace App\Interface;

use App\Entity\User;

interface OwnedInterface
{
    public function getOwner(): User;
}
