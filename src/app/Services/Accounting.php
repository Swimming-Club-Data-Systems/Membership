<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Accounting\DebitsAndCreditsDoNotEqual;
use App\Exceptions\Accounting\InvalidJournalEntryValue;
use App\Exceptions\Accounting\InvalidJournalMethod;
use App\Exceptions\Accounting\TransactionCouldNotBeProcessed;
use App\Models\Accounting\Journal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Money\Currency;
use Money\Money;

class Accounting
{
    /**
     * @var array
     */
    protected $transactions_pending = [];

    public static function newDoubleEntryTransactionGroup(): Accounting
    {
        return new self;
    }

    /**
     * @param  null  $referenced_object
     *
     * @throws InvalidJournalEntryValue
     * @throws InvalidJournalMethod
     */
    public function addDollarTransaction(
        Journal $journal,
        string $method,
        $value,
        string $memo = null,
        $referenced_object = null,
        Carbon $postdate = null
    ): void {
        $value = (int) ($value * 100);
        $money = new Money($value, new Currency('USD'));
        $this->addTransaction($journal, $method, $money, $memo, $referenced_object, $postdate);
    }

    /**
     * @param  null  $referenced_object
     *
     * @throws InvalidJournalEntryValue
     * @throws InvalidJournalMethod
     *
     * @internal param int $value
     */
    public function addTransaction(
        Journal $journal,
        string $method,
        Money $money,
        string $memo = null,
        $referenced_object = null,
        Carbon $postdate = null
    ): void {

        if (! in_array($method, ['credit', 'debit'])) {
            throw new InvalidJournalMethod;
        }

        if ($money->getAmount() <= 0) {
            throw new InvalidJournalEntryValue();
        }

        $this->transactions_pending[] = [
            'journal' => $journal,
            'method' => $method,
            'money' => $money,
            'memo' => $memo,
            'referenced_object' => $referenced_object,
            'postdate' => $postdate,
        ];
    }

    public function getTransactionsPending(): array
    {
        return $this->transactions_pending;
    }

    /**
     * @throws DebitsAndCreditsDoNotEqual
     * @throws TransactionCouldNotBeProcessed
     */
    public function commit($handleDatabaseTransactions = true): string
    {
        $this->verifyTransactionCreditsEqualDebits();
        try {
            $transactionGroupUUID = \Ramsey\Uuid\Uuid::uuid4()->toString();

            if ($handleDatabaseTransactions) {
                DB::beginTransaction();
            }

            foreach ($this->transactions_pending as $transaction_pending) {
                $transaction = $transaction_pending['journal']->{$transaction_pending['method']}($transaction_pending['money'],
                    $transaction_pending['memo'], $transaction_pending['postdate'], $transactionGroupUUID);
                if ($object = $transaction_pending['referenced_object']) {
                    $transaction->referencesObject($object);
                }
            }

            if ($handleDatabaseTransactions) {
                DB::commit();
            }

            return $transactionGroupUUID;

        } catch (\Exception $e) {
            if ($handleDatabaseTransactions) {
                DB::rollBack();
                throw new TransactionCouldNotBeProcessed('Rolling Back Database. Message: '.$e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * @throws DebitsAndCreditsDoNotEqual
     */
    private function verifyTransactionCreditsEqualDebits(): void
    {
        $credits = 0;
        $debits = 0;

        foreach ($this->transactions_pending as $transaction_pending) {
            if ($transaction_pending['method'] == 'credit') {
                $credits += $transaction_pending['money']->getAmount();
            } else {
                $debits += $transaction_pending['money']->getAmount();
            }
        }

        if ($credits !== $debits) {
            throw new DebitsAndCreditsDoNotEqual('In this transaction, credits == '.$credits.' and debits == '.$debits);
        }
    }
}
