export const CancellationReasons = {
    too_slow: "Too slow",
    too_fast: "Too fast",
    invalid_at_entry: "Invalid at entry into software",
    rejected: "Generic rejection",
    medical: "Medical",
    member_declined_selection: "Member declined selection",
    generic_refund: "Generic refund",
    generic_no_refund: "Generic, without refund",
};

export const CancellationReasonsSelectItems: { value: string; name: string }[] =
    [
        {
            value: "too_slow",
            name: CancellationReasons.too_slow,
        },
        {
            value: "too_fast",
            name: CancellationReasons.too_fast,
        },
        {
            value: "invalid_at_entry",
            name: CancellationReasons.invalid_at_entry,
        },
        {
            value: "rejected",
            name: CancellationReasons.rejected,
        },
        {
            value: "medical",
            name: CancellationReasons.medical,
        },
        {
            value: "member_declined_selection",
            name: CancellationReasons.member_declined_selection,
        },
        {
            value: "generic_refund",
            name: CancellationReasons.generic_refund,
        },
    ];
