import React, { ReactNode } from "react";
import Table from "@/Components/Table";
import Card from "@/Components/Card";

type PaymentLineItemsSummaryProps = {
    data: Record<string, ReactNode>[];
};

export const PaymentLineItemsSummary = ({
    data,
}: PaymentLineItemsSummaryProps) => {
    return (
        <Card>
            <Table
                data={data}
                columns={[
                    {
                        headerName: "Description",
                        field: "description",
                    },
                    {
                        headerName: "Quantity",
                        field: "quantity",
                    },
                    {
                        headerName: "Total",
                        field: "formatted_amount",
                    },
                ]}
            />
        </Card>
    );
};
