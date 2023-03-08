import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
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
                    props.start
                )} - ${formatDate(props.end)})`}
                breadcrumbs={[
                    { name: "Users", route: "users.index" },
                    {
                        name: props.user.name,
                        route: "users.show",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                    {
                        name: "Statements",
                        route: "users.statements.index",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                    {
                        name: `#${props.id}`,
                        route: "users.statements.show",
                        routeParams: {
                            user: props.user.id,
                            statement: props.id,
                        },
                    },
                ]}
            />

            <MainHeader
                title={`${formatDate(props.start)} - ${formatDate(props.end)}`}
                subtitle={`Statement information for ${props.user.name}`}
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
