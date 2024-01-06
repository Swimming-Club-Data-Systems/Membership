import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import { Layout } from "@/Common/Layout.jsx";
import Collection from "@/Components/Collection";
import { StatementIndexItemContent } from "@/Components/Payments/Statements/StatementIndexItemContent";

type Props = {
    statements: [];
};

const Index: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head title="Statements" />

            <Collection
                {...props.statements}
                itemRenderer={StatementIndexItemContent}
                route="payments.statements.show"
            />
        </>
    );
};

Index.layout = (page) => {
    return (
        <MainLayout
            title="Statements"
            subtitle="View your account statements"
            breadcrumbs={[
                { name: "Payments", route: "my_account.index" },
                { name: "Statements", route: "payments.statements.index" },
            ]}
        >
            <Container noMargin>{page}</Container>
        </MainLayout>
    );
};

export default Index;
