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

type Props = {
    codes_of_conduct: {
        value: number;
        name: ReactNode;
    }[];
};

const Edit = (props: Props) => {
    return (
        <>
            <Head
                title="New Squad"
                breadcrumbs={[
                    { name: "Squads", route: "squads.index" },
                    {
                        name: "New",
                        route: "squads.new",
                    },
                ]}
            />

            <Container>
                <MainHeader title="New Squad" subtitle="Create a new squad" />

                <FlashAlert className="mb-3" />
            </Container>

            <Container noMargin>
                <Form
                    initialValues={{
                        name: "",
                        monthly_fee: 0,
                        timetable: "",
                        code_of_conduct: null,
                    }}
                    validationSchema={yup.object().shape({
                        name: yup
                            .string()
                            .required("A squad name is required.")
                            .max(
                                255,
                                "Squad name can not exceed 100 characters."
                            ),
                        monthly_fee: yup
                            .number()
                            .typeError("Monthly fee must be a number.")
                            .required("Monthly fee is required.")
                            .min(0, "Monthly fee can not be less than zero."),
                        timetable: yup
                            .string()
                            .max(
                                100,
                                "Timetable url must not exceed 100 characters."
                            )
                            .url("Timetable must be a valid URL."),
                        code_of_conduct: yup.number().nullable(),
                    })}
                    submitTitle="Save"
                    action={route("squads.index")}
                    method="post"
                    hideDefaultButtons
                >
                    <Card footer={<SubmissionButtons />}>
                        <RenderServerErrors />
                        <FlashAlert className="mb-3" />

                        <TextInput name="name" label="Name" />
                        <DecimalInput
                            name="monthly_fee"
                            label="Monthly fee (£)"
                            precision={2}
                        />
                        <TextInput
                            name="timetable"
                            label="Timetable URL"
                            help="You can link to a timetable on your website. If you don't provide a link, we'll show a link to a timetable generated from the sessions in your registers."
                        />
                        <Select
                            nullable
                            name="code_of_conduct"
                            label="Code of conduct"
                            items={props.codes_of_conduct}
                        />
                    </Card>
                </Form>
            </Container>
        </>
    );
};

Edit.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Edit;
