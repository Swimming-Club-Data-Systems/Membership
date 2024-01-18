import { useEffect, useState } from "react";
import { loadStripe } from "@stripe/stripe-js";

/**
 * Load and return the stripe sdk
 *
 * @param stripe_account
 * @param stripe_publishable_key
 */
export const useLoadStripe = (
    stripe_account: string,
    stripe_publishable_key: string,
) => {
    const [stripe, setStripe] = useState(null);

    useEffect(() => {
        loadStripe(stripe_publishable_key, {
            stripeAccount: stripe_account,
        }).then((value) => {
            if (value) {
                setStripe(value);
            }
        });
    }, [stripe_account, stripe_publishable_key]);

    return stripe;
};
