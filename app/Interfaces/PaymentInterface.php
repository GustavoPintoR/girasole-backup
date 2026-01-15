<?php

namespace App\Interfaces;

interface PaymentInterface
{
    public function getPaymentLink(): array;
}
