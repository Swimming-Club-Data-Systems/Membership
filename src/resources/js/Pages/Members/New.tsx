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
import DateTimeInput from "@/Components/Form/DateTimeInput";
import DateNumeralInput from "@/Components/Form/DateNumeralInput";
import Checkbox from "@/Components/Form/Checkbox";
import RadioGroup from "@/Components/Form/RadioGroup";
import Radio from "@/Components/Form/Radio";

type Props = {
    codes_of_conduct: {
        value: number;
        name: ReactNode;
    }[];
};

const New = (props: Props) => {
    return (
        <>
            <Head
                title="New Member"
                breadcrumbs={[
                    { name: "Members", route: "members.index" },
                    {
                        name: "New",
                        route: "members.new",
                    },
                ]}
            />

            <Container>
                <MainHeader title="New Member" subtitle="Add a new member" />

                <FlashAlert className="mb-3" />
            </Container>

            <Container noMargin>
                <Form
                    initialValues={{
                        first_name: "",
                        last_name: "",
                        date_of_birth: null,
                        ngb_reg: "",
                        ngb_category: "",
                        club_pays_ngb_fees: false,
                        sex: "",
                        club_category: "",
                        club_pays_club_membership_fees: false,
                    }}
                    validationSchema={yup.object().shape({
                        first_name: yup
                            .string()
                            .required("A first name is required.")
                            .max(
                                255,
                                "First name can not exceed 255 characters.",
                            ),
                        last_name: yup
                            .string()
                            .required("A last name is required.")
                            .max(
                                255,
                                "Last name can not exceed 255 characters.",
                            ),
                        date_of_birth: yup
                            .date()
                            .max(
                                new Date(),
                                "Date of birth can not be in the future.",
                            )
                            .required("Date of birth is required.")
                            .min("1900-01-01", "Date can not be before 1900.")
                            .typeError("Date of birth is required."),
                        ngb_reg: yup
                            .string()
                            .max(
                                36,
                                "Governing body ID can not exceed 36 characters.",
                            ),
                        ngb_category: yup
                            .string()
                            .required("A category is required."),
                        club_pays_ngb_fees: yup.boolean(),
                        sex: yup
                            .string()
                            .required("A competition sex is required.")
                            .oneOf(["Male", "Female"]),
                    })}
                    submitTitle="Save"
                    action={route("squads.index")}
                    method="post"
                    hideDefaultButtons
                >
                    <Card footer={<SubmissionButtons />}>
                        <RenderServerErrors />
                        <FlashAlert className="mb-3" />

                        <TextInput name="first_name" label="First name" />

                        <TextInput name="last_name" label="Last name" />

                        <DateNumeralInput
                            name="date_of_birth"
                            label="Date of birth"
                        />

                        <TextInput
                            name="ngb_reg"
                            label="Swim England registration number"
                        />

                        <Select
                            name="ngb_category"
                            label="Swim England membership category"
                            items={[]}
                        />

                        <Checkbox
                            name="club_pays_ngb_fees"
                            label="Club pays Swim England fees"
                        />

                        <RadioGroup label="Competition sex" name="sex">
                            <Radio value="Male" label="Open (formerly Male)" />
                            <Radio value="Female" label="Female" />
                        </RadioGroup>

                        <Select
                            name="club_category"
                            label="Club membership category"
                            items={[]}
                        />

                        <Checkbox
                            name="club_pays_club_membership_fees"
                            label="Club pays club membership fees"
                        />
                    </Card>
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
