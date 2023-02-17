import React from "react";
import MainLayout from "@/Layouts/MainLayout.jsx";
import Head from "@/Components/Head";
import Container from "@/Components/Container.jsx";
import { Layout } from "@/Common/Layout.jsx";
import Combobox from "@/Components/Form/Combobox";
import Form from "@/Components/Form/Form";
import * as yup from "yup";

type Props = {
    statements: [];
};

const ComponentTesting: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head title="Component Testing" />

            <Form
                initialValues={{
                    user_select: null,
                }}
                validationSchema={yup.object().shape({
                    user_select: yup
                        .mixed()
                        .required("A user must be selected"),
                })}
                onSubmit={(values) => {
                    console.log(values);
                }}
            >
                <Combobox
                    endpoint="/component-testing-user-search"
                    name="user_select"
                    label="User"
                    help="Start typing to find a user"
                />
            </Form>
        </>
    );
};

ComponentTesting.layout = (page) => {
    return (
        <MainLayout
            title="Component Testing"
            subtitle="Testing new components"
            breadcrumbs={[]}
        >
            <Container noMargin>{page}</Container>
        </MainLayout>
    );
};

export default ComponentTesting;
