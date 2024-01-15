import React, { ReactNode } from "react";
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
import Select from "@/Components/Form/Select";
import TextArea from "@/Components/Form/TextArea";
import Checkbox from "@/Components/Form/Checkbox";

const New = () => {
    return (
        <>
            <Head
                title="New Product"
                breadcrumbs={[
                    { name: "Products", route: "products.index" },
                    {
                        name: "New",
                        route: "products.new",
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
                        // Product
                        name: "",
                        description: "",
                        shippable: false,
                        unit_label: "",
                        // Default price
                        unit_amount: 0,
                        nickname: "",
                    }}
                    validationSchema={yup.object().shape({
                        name: yup
                            .string()
                            .required("A name is required.")
                            .max(255, "Name can not exceed 255 characters."),
                        description: yup
                            .string()
                            .required("A description is required.")
                            .max(
                                1024,
                                "Description can not exceed 1024 characters.",
                            ),
                        shippable: yup.boolean(),
                        unit_label: yup
                            .string()
                            .max(
                                255,
                                "Unit label can not exceed 255 characters.",
                            ),
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
                    action={route("products.create")}
                    method="post"
                    hideDefaultButtons
                >
                    <Card footer={<SubmissionButtons />}>
                        <RenderServerErrors />
                        <FlashAlert className="mb-3" />

                        <TextInput name="name" label="Name" />
                        <TextArea
                            name="description"
                            label="Description"
                            maxLength={1024}
                        />
                        <Checkbox
                            name="shippable"
                            label="Shippable"
                            help="Is this item shippable?"
                        />
                        <TextInput name="unit_label" label="Unit label" />
                        <DecimalInput
                            name="unit_amount"
                            label="Default price unit amount (£)"
                            precision={2}
                        />
                        <TextInput
                            name="nickname"
                            label="Default price nickname"
                        />
                    </Card>
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
