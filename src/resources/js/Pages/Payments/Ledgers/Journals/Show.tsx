import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import MainHeader from "@/Layouts/Components/MainHeader";

type Props = {
    name: string;
    id: number;
    ledger_id: number;
};

const Show: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title={props.name}
                breadcrumbs={[
                    { name: "Payments", route: "my_account.index" },
                    { name: "Ledgers", route: "payments.ledgers.index" },
                    {
                        name: props.ledger_name,
                        route: "payments.ledgers.show",
                        routeParams: props.ledger_id,
                    },
                    {
                        name: props.name,
                        route: "payments.ledgers.journals.show",
                        routeParams: [props.ledger_id, props.id],
                    },
                ]}
            />

            <MainHeader
                title={props.name}
                subtitle="Manage journal"
            ></MainHeader>
        </>
    );
};

Show.layout = (page) => (
    <MainLayout hideHeader>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Show;
