import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import Collection from "@/Components/Collection";
import { StatementIndexItemContent } from "@/Components/Payments/Statements/StatementIndexItemContent";

type Props = {
    statements: [];
    user: {
        id: number;
        name: string;
    };
};

const UserIndex: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Statements"
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
                ]}
                subtitle={`View account statements for ${props.user.name}`}
            />

            <Collection
                {...props.statements}
                itemRenderer={StatementIndexItemContent}
                route="payments.statements.show"
                // route="users.statements.show"
                // routeParams={["user.id", "id"]}
            />
        </>
    );
};

UserIndex.layout = (page) => {
    return (
        <MainLayout title="Statements" subtitle="View account statements">
            <Container noMargin>{page}</Container>
        </MainLayout>
    );
};

export default UserIndex;
