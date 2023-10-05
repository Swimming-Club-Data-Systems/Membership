export const RefundReasons = {
    na: "Not applicable",
    duplicate: "Duplicate",
    fraudulent: "Fraudulent",
    requested_by_customer: "Requested by customer",
};

export const RefundReasonsSelectItems: { value: string; name: string }[] = [
    {
        value: "n/a",
        name: RefundReasons.na,
    },
    {
        value: "duplicate",
        name: RefundReasons.duplicate,
    },
    {
        value: "fraudulent",
        name: RefundReasons.fraudulent,
    },
    {
        value: "requested_by_customer",
        name: RefundReasons.requested_by_customer,
    },
];
