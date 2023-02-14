import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import MainHeader from "@/Layouts/Components/MainHeader";
import { formatDate } from "@/Utils/date-utils";
import Table from "@/Components/Table";
import Card from "@/Components/Card";
import Stats from "@/Components/Stats";
import Stat from "@/Components/Stat";

type Props = {
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
};

const Show: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title={`Statement #${props.id} (${formatDate(
                    props.start
                )} - ${formatDate(props.end)})`}
                breadcrumbs={[
                    { name: "Payments", route: "my_account.index" },
                    { name: "Statements", route: "payments.statements.index" },
                    {
                        name: `#${props.id}`,
                        route: "payments.statements.show",
                        routeParams: props.id,
                    },
                ]}
            />

            <MainHeader
                title={`${formatDate(props.start)} - ${formatDate(props.end)}`}
                subtitle="Statement information"
            ></MainHeader>

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
        </>
    );
};

Show.layout = (page) => (
    <MainLayout hideHeader>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Show;
