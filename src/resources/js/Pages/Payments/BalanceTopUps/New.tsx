import React from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import DateInput from "@/Components/Form/DateInput";

export type Props = {
    user: {
        id: number;
        name: string;
    };
    initiator: {
        id: number;
        name: string;
    };
};

const New: Layout<Props> = (props: Props) => {
    return (
        <>
            <Head
                title="Transactions"
                breadcrumbs={[
                    { name: "Billing", route: "payments.index" },
                    {
                        name: "Balance Top Ups",
                        route: "users.top_up.index",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                    {
                        name: "New",
                        route: "users.top_up.new",
                        routeParams: {
                            user: props.user.id,
                        },
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={"Create Balance Top Up"}
                    subtitle={"Initiate"}
                ></MainHeader>

                <Form validationSchema={yup.object().shape({})}>
                    <DateInput name="scheduled_for" label="Schedule for" />
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
