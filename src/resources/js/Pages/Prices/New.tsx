import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import * as yup from "yup";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import DecimalInput from "@/Components/Form/DecimalInput";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";

type Props = {
    product: {
        id: string;
        name: string;
    };
};

export type Price = {
    id: string;
    nickname: string;
    formatted_unit_amount: string;
    active: boolean;
};

const New = (props: Props) => {
    return (
        <>
            <Head
                title="New Product"
                breadcrumbs={[
                    { name: "Products", route: "products.index" },
                    {
                        name: props.product.name,
                        route: "products.show",
                        routeParams: {
                            product: props.product.id,
                        },
                    },
                    {
                        name: "New",
                        route: "products.prices.new",
                        routeParams: {
                            product: props.product.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title="New Product"
                    subtitle="Create a new product and price"
                />

                <FlashAlert className="mb-3" />
            </Container>

            <Container noMargin>
                <Form
                    initialValues={{
                        unit_amount: 0,
                        nickname: "",
                    }}
                    validationSchema={yup.object().shape({
                        unit_amount: yup
                            .number()
                            .min(0, "Unit amount must not be negative."),
                        nickname: yup
                            .string()
                            .required(
                                "A nickname for the default price is required.",
                            )
                            .max(
                                255,
                                "Nickname can not exceed 255 characters.",
                            ),
                    })}
                    submitTitle="Save"
                    action={route("products.prices.create", {
                        product: props.product.id,
                    })}
                    method="post"
                    hideDefaultButtons
                >
                    <Card footer={<SubmissionButtons />}>
                        <RenderServerErrors />
                        <FlashAlert className="mb-3" />

                        <DecimalInput
                            name="unit_amount"
                            label="Price unit amount (£)"
                            precision={2}
                        />
                        <TextInput
                            name="nickname"
                            label="Price nickname"
                            help="This is not displayed to users, it is for internal organisation."
                        />
                    </Card>
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
