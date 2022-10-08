import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import TextArea from "@/Components/Form/TextArea";
import * as yup from "yup";
import Checkbox from "@/Components/Form/Checkbox";

const ReportAnErrorForm = (props) => {
    return (
        <Form
            initialValues={{
                user: {
                    name: "",
                    email: "",
                },
                url: "",
                description: "",
                user_agent: navigator.userAgent,
                user_agent_brands: JSON.stringify(
                    navigator.userAgentData?.brands
                ),
                user_agent_platform: navigator.userAgentData?.platform,
                user_agent_mobile: navigator.userAgentData?.mobile,
                data_sharing_agreement: false,
            }}
            validationSchema={yup.object({
                user: yup.object({
                    name: yup.string().required("Please tell us your name"),
                    email: yup
                        .string()
                        .email()
                        .required("Please tell us your email address"),
                }),
                url: yup
                    .string()
                    .url()
                    .required(
                        "Please tell us the URL of the page you were on when you encountered an error"
                    ),
                description: yup
                    .string()
                    .required("Please include a description of your issue"),
                data_sharing_agreement: yup
                    .bool()
                    .oneOf(
                        [true],
                        "You must consent to data sharing to be able to report this issue"
                    ),
            })}
            submitTitle="Report error"
            action={route("report_an_error")}
        >
            <TextInput name="user.name" label="Name" />
            <TextInput
                name="user.email"
                label="Email address"
                help="Please tell us your email so that we can contact you about this issue."
            />
            <TextInput
                name="url"
                label="Page address (URL)"
                help="The URL of the page you were on when you encountered an error."
            />
            <TextArea
                name="description"
                label="Description"
                help="Please describe what you were doing before you encountered the error. This helps us recreate the issue."
            />

            <div className="prose prose-sm mb-4">
                <p>
                    When you send an error report, we will also send the
                    following information about your system to SCDS:
                </p>

                <ul>
                    <li>User agent string</li>
                    <li>Browser brand list</li>
                    <li>Operating system</li>
                    <li>Whether you&apos;re on mobile or desktop</li>
                </ul>

                <p>
                    By proceeding you consent to sharing the above data with
                    SCDS. SCDS may also share this data with your club in the
                    course of supporting your issue.
                </p>

                <p>
                    This information is very useful in helping us replicate your
                    issue as some bugs may only exist in specific browsers.
                </p>
            </div>

            <Checkbox
                name="data_sharing_agreement"
                label="I consent to sharing my data with SCDS to be used as described above"
            />
        </Form>
    );
};

export default ReportAnErrorForm;
