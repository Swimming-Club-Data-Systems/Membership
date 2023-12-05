import React, { ReactNode } from "react";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainLayout from "@/Layouts/MainLayout";
import MainHeader from "@/Layouts/Components/MainHeader";
import { Layout } from "@/Common/Layout";
import { RenewalProps } from "@/Pages/Renewal/Index";
import { RenewalForm } from "@/Components/Renewal/RenewalForm";
import Link from "@/Components/Link";

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

            <Container>
                <MainHeader
                    title={pageName}
                    subtitle="Create a new renewal period"
                    buttons={
                        <Link
                            external
                            href="https://docs.myswimmingclub.uk/docs/onboarding/renewal/"
                        >
                            Help
                        </Link>
                    }
                ></MainHeader>
            </Container>

            <Container noMargin>
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
