import React, { ReactNode, useState } from "react";
import CentralMainLayout from "@/Layouts/CentralMainLayout";
import { Head } from "@inertiajs/inertia-react";
import Layout from "@/Pages/Central/Tenants/Layout";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import Alert from "@/Components/Alert";
import BasicList from "@/Components/BasicList";
import Button from "@/Components/Button";
import { formatUnixTime } from "@/Utils/date-utils";
import Modal from "@/Components/Modal";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
} from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import { Inertia } from "@inertiajs/inertia";

type ApplePayDomain = {
    id: string;
    domain_name: string;
    created: number;
};

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
    stripe_account?: string;
    apple_pay_domains: ApplePayDomain[];
};

interface Layout<P> extends React.FC<P> {
    layout: (ReactNode) => ReactNode;
}

const ApplePayDomains: Layout<Props> = (props: Props) => {
    const [showDomainDeleteModal, setShowDomainDeleteModal] = useState(false);
    const [showNewDomainModal, setShowNewDomainModal] = useState(false);
    const [domainDeleteModalData, setDomainDeleteModalData] =
        useState<ApplePayDomain | null>(null);

    const deletePaymentMethod = async () => {
        Inertia.delete(
            route("central.tenants.delete_apple_pay_domains", [
                props.id,
                domainDeleteModalData.id,
            ]),
            {
                only: ["apple_pay_domains", "flash"],
                preserveScroll: true,
                preserveState: true,
                onFinish: (page) => {
                    setShowDomainDeleteModal(false);
                },
            }
        );
    };

    return (
        <>
            <Head title={`Apple Pay Domains - ${props.name}`} />

            {!props.stripe_account && (
                <Alert title="Stripe not enabled" variant="error">
                    Stripe has not been set up for this tenant.
                </Alert>
            )}

            {props.stripe_account && (
                <>
                    <div className="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                        <Form
                            initialValues={{
                                domain: "",
                            }}
                            validationSchema={yup.object({
                                domain: yup
                                    .string()
                                    .required("You must enter a domain name"),
                            })}
                            hideDefaultButtons
                            submitTitle="Add Apple Pay Domain"
                            action={route(
                                "central.tenants.apple_pay_domains",
                                props.id
                            )}
                            hideErrors
                        >
                            <Card
                                title="Apple Pay Domains"
                                subtitle="Manage tenant Apple Pay Domains."
                                footer={<SubmissionButtons />}
                            >
                                <RenderServerErrors />
                                <FlashAlert className="mb-4" />

                                <div>
                                    {props.apple_pay_domains.length > 0 && (
                                        <BasicList
                                            items={props.apple_pay_domains.map(
                                                (item) => {
                                                    return {
                                                        id: item.id,
                                                        content: (
                                                            <>
                                                                <div
                                                                    className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-3 text-sm"
                                                                    key={
                                                                        item.id
                                                                    }
                                                                >
                                                                    <div className="">
                                                                        <div className="text-gray-900">
                                                                            {
                                                                                item.domain_name
                                                                            }
                                                                        </div>
                                                                        <div className="text-gray-500">
                                                                            {formatUnixTime(
                                                                                item.created
                                                                            )}
                                                                        </div>
                                                                    </div>
                                                                    <div className="block">
                                                                        <Button
                                                                            variant="danger"
                                                                            className="ml-3"
                                                                            onClick={() => {
                                                                                setShowDomainDeleteModal(
                                                                                    true
                                                                                );
                                                                                setDomainDeleteModalData(
                                                                                    item
                                                                                );
                                                                            }}
                                                                        >
                                                                            Delete
                                                                        </Button>
                                                                    </div>
                                                                </div>
                                                            </>
                                                        ),
                                                    };
                                                }
                                            )}
                                        />
                                    )}
                                </div>
                                <TextInput name="domain" label="Domain name" />
                            </Card>
                        </Form>
                    </div>

                    <Modal
                        show={showDomainDeleteModal}
                        onClose={() => setShowDomainDeleteModal(false)}
                        variant="danger"
                        title="Delete Apple Pay Domain"
                        buttons={
                            <Button
                                variant="danger"
                                onClick={deletePaymentMethod}
                            >
                                Delete
                            </Button>
                        }
                    >
                        {domainDeleteModalData && (
                            <p>
                                Are you sure you want to delete{" "}
                                {domainDeleteModalData.domain_name}?
                            </p>
                        )}
                    </Modal>
                </>
            )}
        </>
    );
};

ApplePayDomains.layout = (page) => (
    <CentralMainLayout
        title={page.props.name}
        subtitle={`Manage details for ${page.props.name}`}
    >
        <Layout>{page}</Layout>
    </CentralMainLayout>
);

export default ApplePayDomains;
