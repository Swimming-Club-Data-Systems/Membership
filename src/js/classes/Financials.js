/**
 * Financial Helper Functions
 */
import BigNumber from "bignumber.js";

export const intToDec = (number) => {
  return new BigNumber(number).shiftedBy(-2).toString();
};

export const decToInt = (number) => {
  return new BigNumber(number).shiftedBy(2).toString();
};

export const formatCurrency = (amount, currency) => {
  return new Intl.NumberFormat(undefined, {
    currency: currency,
    style: "currency",
  }).format(amount);
};