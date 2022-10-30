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
            <Form
                initialValues={{
                    message: "",
                    squads: {},
                }}
                validationSchema={yup.object().shape({
                    message: yup
                        .string()
                        .required("Message content is required")
                        .max(160, "SMS messages may not exceed 160 characters"),
                    squads: yup.object().shape(squadValidation),
                })}
                hideDefaultButtons
                hideErrors
                action={route("notify.sms.new")}
                method="post"
                disabled={props.balance <= 0}
            >
                <Card
                    title="Compose message"
                    subtitle="Write your message."
                    footer={<SubmissionButtons />}
                >
                    <p className="text-sm mb-3">
                        Your club balance is {props.formatted_balance}. SMS
                        messages cost £0.05 per message segment.
                    </p>

                    {props.balance <= 0 && (
                        <Alert
                            title="Your balance is too low"
                            className="mb-4"
                            variant="error"
                        >
                            As your club balance is only{" "}
                            {props.formatted_balance}, you won&apos;t be able to
                            send any messages. You can top up your club account
                            in SCDS System Administration.
                        </Alert>
                    )}

                    <Alert title="Head's up" className="mb-4" variant="warning">
                        We currently only support sending an SMS to members of
                        squads. Support for a wider range of options will be
                        made available when Notify Emails are migrated over to
                        SCDS Next.
                    </Alert>
                    <RenderServerErrors />
                    <UnknownError />
                    <FlashAlert />

                    <Fieldset legend="Squads">
                        {props.squads.map((squad) => {
                            return (
                                <Checkbox
                                    name={`squads.${squad.id}`}
                                    label={squad.name}
                                    key={squad.id}
                                />
                            );
                        })}
                    </Fieldset>

                    <TextArea name="message" label="Message" maxLength={160} />
                </Card>
            </Form>
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
