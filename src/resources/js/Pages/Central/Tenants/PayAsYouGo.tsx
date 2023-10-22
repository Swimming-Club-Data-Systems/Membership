import React, { ReactNode } from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/react";
import Layout from "@/Pages/Central/Tenants/Layout";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import ButtonLink from "@/Components/ButtonLink";

type TenantAdminstrator = {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    gravatar_url: string;
};

type Props = {
    id: number;
    name: string;
    auth: {
        user: {
            id: number;
        };
    };
    users: TenantAdminstrator[];
    formatted_balance: string;
};

interface LayoutType<P> extends React.FC<P> {
    layout: (ReactNode) => ReactNode;
}

const PayAsYouGo: LayoutType<Props> = (props: Props) => {
    return (
        <>
            <Head title={`Pay As You Go Services - ${props.name}`} />

            <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <Card
                    title="Pay As You Go Services"
                    subtitle="SCDS offers additional pay as you go services to clubs such as SMS messaging."
                    footer={
                        <ButtonLink
                            href={route("central.tenants.top_up", props.id)}
                        >
                            Top Up
                        </ButtonLink>
                    }
                >
                    <FlashAlert className="mb-4" />

                    <div>
                        <p className="text-sm">
                            Your account balance is {props.formatted_balance}.
                        </p>
                    </div>
                </Card>
            </div>
        </>
    );
};

PayAsYouGo.layout = (page) => (
    <CentralMainLayout
        title={page.props.name}
        subtitle={`Manage details for ${page.props.name}`}
    >
        <Layout>{page}</Layout>
    </CentralMainLayout>
);

export default PayAsYouGo;
