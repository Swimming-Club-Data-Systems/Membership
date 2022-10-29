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

type Props = {};

interface Layout<P> extends React.FC<P> {
    layout: (ReactNode) => ReactNode;
}

const SMS: Layout<Props> = (props: Props) => {
    return (
        <Container noMargin>
            <Form
                initialValues={{
                    message: "",
                }}
                validationSchema={yup.object().shape({
                    message: yup
                        .string()
                        .required("Message content is required")
                        .max(160, "SMS messages may not exceed 160 characters"),
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
                    <RenderServerErrors />
                    <UnknownError />

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
