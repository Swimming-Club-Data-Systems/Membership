import Stats from "@/Components/Stats";
import Stat from "@/Components/Stat";
import Card from "@/Components/Card";
import Table from "@/Components/Table";
import React from "react";

export type StatementContentProps = {
    start: string;
    end: string;
    credits: number;
    debits: number;
    opening_balance: number;
    closing_balance: number;
    credits_formatted: string;
    debits_formatted: string;
    opening_balance_formatted: string;
    closing_balance_formatted: string;
    id: number;
    transactions: [];
    user: {
        id: number;
        name: string;
    };
};

export const StatementContent: React.FC<StatementContentProps> = (props) => {
    return (
        <div className="grid gap-4">
            <Stats title="Overview">
                <Stat name="New Credits" stat={props.credits_formatted} />
                <Stat name="New Debits" stat={props.debits_formatted} />
                <Stat
                    name="Closing Balance"
                    stat={props.closing_balance_formatted}
                />
            </Stats>

            <Card title="Transactions">
                <Table
                    data={props.transactions}
                    columns={[
                        {
                            headerName: "Description",
                            field: "memo",
                        },
                        {
                            headerName: "Debit",
                            field: "debit_formatted",
                        },
                        {
                            headerName: "Credit",
                            field: "credit_formatted",
                        },
                    ]}
                />
            </Card>
        </div>
    );
};
