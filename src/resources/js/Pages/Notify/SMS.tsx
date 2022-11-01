import React, { ReactNode } from "react";
import MainLayout from "@/Layouts/MainLayout";
import Container from "@/Components/Container";
import Card from "@/Components/Card";
import Form, {
    RenderServerErrors,
    SubmissionButtons,
    UnknownError,
} from "@/Components/Form/Form";
import * as yup from "yup";
import TextArea from "@/Components/Form/TextArea";
import Checkbox from "@/Components/Form/Checkbox";
import Fieldset from "@/Components/Form/Fieldset";
import Alert from "@/Components/Alert";
import FlashAlert from "@/Components/FlashAlert";
import { ExclamationTriangleIcon } from "@heroicons/react/24/outline";
import Link from "@/Components/Link";

type Squad = {
    id: number;
    name: string;
};

type Props = {
    squads: Squad[];
    balance: number;
    formatted_balance: string;
};

interface Layout<P> extends React.FC<P> {
    layout: (ReactNode) => ReactNode;
}

const SMS: Layout<Props> = (props: Props) => {
    const squadValidation = {};
    props.squads.forEach((squad) => {
        squadValidation[squad.id] = yup
            .bool()
            .oneOf([true, false], "Must either be true or false")
            .test(
                "at-least-one",
                "At least one squad must be selected",
                (value, testContext) => {
                    if (value) return true;

                    return (
                        Object.values(testContext.parent).find(
                            (value) => value === true
                        ) !== undefined
                    );
                }
            );
    });

    return (
        <Container noMargin>
            {props.balance <= 0 && (
                <>
                    <div className="overflow-hidden bg-white px-4 pt-5 pb-4 shadow sm:p-6 sm:pb-4 lg:rounded-lg">
                        <div className="sm:flex sm:items-start">
                            <div className="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <ExclamationTriangleIcon
                                    className="h-6 w-6 text-red-600"
                                    aria-hidden="true"
                                />
                            </div>
                            <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 className="text-lg font-medium leading-6 text-gray-900">
                                    Your balance is too low
                                </h3>
                                <div className="mt-2">
                                    <p className="text-sm mb-3 text-gray-500">
                                        As your club balance is only{" "}
                                        {props.formatted_balance}, you
                                        won&apos;t be able to send any messages.
                                        You can top up your club account in SCDS
                                        System Administration.
                                    </p>

                                    <p className="text-sm text-gray-500">
                                        <Link
                                            href="https://docs.myswimmingclub.uk/docs/notify/sms"
                                            external
                                        >
                                            Find out more about Notify SMS
                                        </Link>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}

            {props.balance > 0 && (
                <Form
                    initialValues={{
                        message: "",
                        squads: {},
                    }}
                    validationSchema={yup.object().shape({
                        message: yup
                            .string()
                            .required("Message content is required")
                            .max(
                                160,
                                "SMS messages may not exceed 160 characters"
                            ),
                        squads: yup.object().shape(squadValidation),
                    })}
                    hideDefaultButtons
                    hideErrors
                    action={route("notify.sms.new")}
                    method="post"
                >
                    <Card
                        title="Compose message"
                        subtitle="Write your message."
                        footer={<SubmissionButtons />}
                    >
                        <p className="text-sm mb-3">
                            Your club balance is {props.formatted_balance}. SMS
                            messages cost £0.05 per message segment.{" "}
                            <Link
                                href="https://docs.myswimmingclub.uk/docs/notify/sms"
                                external
                            >
                                Find out more about Notify SMS
                            </Link>
                            .
                        </p>

                        <Alert
                            title="Head's up"
                            className="mb-4"
                            variant="warning"
                        >
                            We currently only support sending an SMS to members
                            of squads. Support for a wider range of options will
                            be made available when Notify Emails are migrated
                            over to SCDS Next.
                        </Alert>
                        <RenderServerErrors />
                        <UnknownError />
                        <FlashAlert className="mb-4" />

                        <Fieldset legend="Squads">
                            <div className="grid gap-2 grid-cols-2 md:grid-cols-4">
                                {props.squads.map((squad) => {
                                    return (
                                        <Checkbox
                                            name={`squads.${squad.id}`}
                                            label={squad.name}
                                            key={squad.id}
                                        />
                                    );
                                })}
                            </div>
                        </Fieldset>

                        <TextArea
                            name="message"
                            label="Message"
                            maxLength={160}
                        />
                    </Card>
                </Form>
            )}
        </Container>
    );
};

SMS.layout = (page) => (
    <MainLayout
        title={"Notify SMS"}
        subtitle={`Send an urgent notification to members`}
    >
        {page}
    </MainLayout>
);

export default SMS;
