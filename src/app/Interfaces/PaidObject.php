<?php

namespace App\Interfaces;

use App\Models\Tenant\PaymentLine;

interface PaidObject
{
    public function handlePaid(PaymentLine $line): void;

    public function handleChargedBack(): void;

    public function getPaymentLineDescriptor(): string;

    public function handleRefund(int $refundAmount, int $totalAmountRefunded): void;

    public function handleFailed(): void;

    public function handleCanceled(): void;
}
