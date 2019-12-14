<?php

declare(strict_types=1);

namespace Brick\Money;

use Brick\Money\Exception\MoneyMismatchException;

use Brick\Math\BigNumber;
use Brick\Math\RoundingMode;
use Brick\Math\Exception\MathException;
use Brick\Math\Exception\RoundingNecessaryException;

/**
 * Base class for Money and RationalMoney.
 */
abstract class AbstractMoney implements MoneyContainer
{
    /**
     * @return BigNumber
     */
    abstract public function getAmount();

    /**
     * @return Currency
     */
    abstract public function getCurrency() : Currency;

    /**
     * Converts this money to a Money in the given Context.
     *
     * @param Context $context      The context.
     * @param int     $roundingMode The rounding mode, if necessary.
     *
     * @return Money
     *
     * @throws RoundingNecessaryException If RoundingMode::UNNECESSARY is used but rounding is necessary.
     */
    final public function to(Context $context, int $roundingMode = RoundingMode::UNNECESSARY) : Money
    {
        return Money::create($this->getAmount(), $this->getCurrency(), $context, $roundingMode);
    }

    /**
     * Required by interface MoneyContainer.
     *
     * @return BigNumber[]
     */
    final public function getAmounts() : array
    {
        return [
            $this->getCurrency()->getCurrencyCode() => $this->getAmount()
        ];
    }

    /**
     * Returns the sign of this money.
     *
     * @return int -1 if the number is negative, 0 if zero, 1 if positive.
     */
    final public function getSign() : int
    {
        return $this->getAmount()->getSign();
    }

    /**
     * Returns whether this money has zero value.
     *
     * @return bool
     */
    final public function isZero() : bool
    {
        return $this->getAmount()->isZero();
    }

    /**
     * Returns whether this money has a negative value.
     *
     * @return bool
     */
    final public function isNegative() : bool
    {
        return $this->getAmount()->isNegative();
    }

    /**
     * Returns whether this money has a negative or zero value.
     *
     * @return bool
     */
    final public function isNegativeOrZero() : bool
    {
        return $this->getAmount()->isNegativeOrZero();
    }

    /**
     * Returns whether this money has a positive value.
     *
     * @return bool
     */
    final public function isPositive() : bool
    {
        return $this->getAmount()->isPositive();
    }

    /**
     * Returns whether this money has a positive or zero value.
     *
     * @return bool
     */
    final public function isPositiveOrZero() : bool
    {
        return $this->getAmount()->isPositiveOrZero();
    }

    /**
     * Compares this money to the given amount.
     *
     * @param AbstractMoney|BigNumber|number|string $that
     *
     * @return int [-1, 0, 1] if `$this` is less than, equal to, or greater than `$that`.
     *
     * @throws MathException          If the argument is an invalid number.
     * @throws MoneyMismatchException If the argument is a money in a different currency.
     */
    final public function compareTo($that) : int
    {
        return $this->getAmount()->compareTo($this->getAmountOf($that));
    }

    /**
     * Returns whether this money is equal to the given amount.
     *
     * @param AbstractMoney|BigNumber|number|string $that
     *
     * @return bool
     *
     * @throws MathException          If the argument is an invalid number.
     * @throws MoneyMismatchException If the argument is a money in a different currency.
     */
    final public function isEqualTo($that) : bool
    {
        return $this->getAmount()->isEqualTo($this->getAmountOf($that));
    }

    /**
     * Returns whether this money is less than the given amount.
     *
     * @param AbstractMoney|BigNumber|number|string $that
     *
     * @return bool
     *
     * @throws MathException          If the argument is an invalid number.
     * @throws MoneyMismatchException If the argument is a money in a different currency.
     */
    final public function isLessThan($that) : bool
    {
        return $this->getAmount()->isLessThan($this->getAmountOf($that));
    }

    /**
     * Returns whether this money is less than or equal to the given amount.
     *
     * @param AbstractMoney|BigNumber|number|string $that
     *
     * @return bool
     *
     * @throws MathException          If the argument is an invalid number.
     * @throws MoneyMismatchException If the argument is a money in a different currency.
     */
    final public function isLessThanOrEqualTo($that) : bool
    {
        return $this->getAmount()->isLessThanOrEqualTo($this->getAmountOf($that));
    }

    /**
     * Returns whether this money is greater than the given amount.
     *
     * @param AbstractMoney|BigNumber|number|string $that
     *
     * @return bool
     *
     * @throws MathException          If the argument is an invalid number.
     * @throws MoneyMismatchException If the argument is a money in a different currency.
     */
    final public function isGreaterThan($that) : bool
    {
        return $this->getAmount()->isGreaterThan($this->getAmountOf($that));
    }

    /**
     * Returns whether this money is greater than or equal to the given amount.
     *
     * @param AbstractMoney|BigNumber|number|string $that
     *
     * @return bool
     *
     * @throws MathException          If the argument is an invalid number.
     * @throws MoneyMismatchException If the argument is a money in a different currency.
     */
    final public function isGreaterThanOrEqualTo($that) : bool
    {
        return $this->getAmount()->isGreaterThanOrEqualTo($this->getAmountOf($that));
    }

    /**
     * Returns the amount of the given parameter.
     *
     * If the parameter is a money, its currency is checked against this money's currency.
     *
     * @param AbstractMoney|BigNumber|number|string $that A money or amount.
     *
     * @return BigNumber|number|string
     *
     * @throws MoneyMismatchException If currencies don't match.
     */
    final protected function getAmountOf($that)
    {
        if ($that instanceof AbstractMoney) {
            if (! $that->getCurrency()->is($this->getCurrency())) {
                throw MoneyMismatchException::currencyMismatch($this->getCurrency(), $that->getCurrency());
            }

            return $that->getAmount();
        }

        return $that;
    }
}
