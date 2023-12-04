import React, { ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import { formatDate, formatDateTime } from "@/Utils/date-utils";
import { RenewalProps } from "@/Pages/Renewal/Index";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Checkbox from "@/Components/Form/Checkbox";
import Alert from "@/Components/Alert";
import Card from "@/Components/Card";
import { useFormikContext } from "formik";
import { RenewalForm } from "@/Components/Renewal/RenewalForm";

type StageField = {
    id: string;
    name: string;
    locked: boolean;
};

interface Props extends RenewalProps {
    user_fields: StageField[];
    member_fields: StageField[];
    membership_years?: {
        value: string;
        name: ReactNode;
    }[];
}

const New: Layout<Props> = (props: Props) => {
    const pageName = "Create Renewal Period";
    const date = new Date();
    date.setHours(0, 0, 0, 0);

    return (
        <>
            <Head
                title={pageName}
                breadcrumbs={[
                    { name: "Renewal Periods", route: "renewals.index" },
                    {
                        name: "New",
                        route: "renewals.new",
                    },
                ]}
            />

            <Container noMargin>
                <MainHeader
                    title={pageName}
                    subtitle="Create a new renewal period"
                ></MainHeader>

                <div className="grid gap-6">
                    <RenewalForm
                        mode="create"
                        started={props.started}
                        action={route("renewals.create")}
                        method="post"
                        user_fields={props.user_fields}
                        member_fields={props.member_fields}
                        membership_years={props.membership_years}
                    />
                </div>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
