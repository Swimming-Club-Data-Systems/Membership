<?php

namespace App\Interfaces;

interface PaidObject
{
    public function handlePaid(): void;

    public function handleChargedBack(): void;

    public function getPaymentLineDescriptor(): string;

    public function handleRefund(int $refundAmount, int $totalAmountRefunded): void;

    public function handleFailed(): void;

    public function handleCanceled(): void;
}
