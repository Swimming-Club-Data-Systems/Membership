import BigNumber from "bignumber.js";

type Currency = "GBP" | "gbp";

export const formatCurrency = (
    number: BigNumber,
    currency: Currency = "gbp",
): string => {
    const numFormatter = new Intl.NumberFormat("en-GB", {
        style: "currency",
        currency: currency,
    });

    return numFormatter.format(new BigNumber(number).shiftedBy(-2).toNumber());
};
