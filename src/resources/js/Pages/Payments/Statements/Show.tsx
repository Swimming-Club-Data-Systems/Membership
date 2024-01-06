import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import { Layout } from "@/Common/Layout.jsx";
import MainHeader from "@/Layouts/Components/MainHeader";
import { formatDate } from "@/Utils/date-utils";
import {
    StatementContent,
    StatementContentProps,
} from "@/Components/Payments/Statements/StatementContent";

const Show: Layout<StatementContentProps> = (props: StatementContentProps) => {
    return (
        <>
            <Head
                title={`Statement #${props.id} (${formatDate(
                    props.start,
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

            <StatementContent {...props} />
        </>
    );
};

Show.layout = (page) => (
    <MainLayout hideHeader>
        <Container noMargin>{page}</Container>
    </MainLayout>
);

export default Show;
