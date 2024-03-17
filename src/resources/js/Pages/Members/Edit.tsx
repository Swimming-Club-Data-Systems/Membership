import React from "react";
import MainLayout from "@/Layouts/MainLayout";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import * as yup from "yup";
import Form, { RenderServerErrors } from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import Card from "@/Components/Card";
import FlashAlert from "@/Components/FlashAlert";
import Select from "@/Components/Form/Select";
import DateNumeralInput from "@/Components/Form/DateNumeralInput";
import Checkbox from "@/Components/Form/Checkbox";
import RadioGroup from "@/Components/Form/RadioGroup";
import Radio from "@/Components/Form/Radio";
import TextArea from "@/Components/Form/TextArea";
import Link from "@/Components/Link";

type ClubMembershipClass = {
    value: string;
    name: string;
};

type Props = {
    id: number;
    name: string;
    first_name: string;
    club_membership_classes: ClubMembershipClass[];
    ngb_membership_classes: ClubMembershipClass[];
    countries_of_representation: {
        value: string;
        name: string;
    }[];
    is_admin: boolean;
    age: number;
    tenant: {
        id: number;
        name: string;
    };
    is_linked_user: boolean;
};

const New = (props: Props) => {
    const validationRules = {
        first_name: yup
            .string()
            .required("A first name is required.")
            .max(255, "First name can not exceed 255 characters."),
        last_name: yup
            .string()
            .required("A last name is required.")
            .max(255, "Last name can not exceed 255 characters."),
        date_of_birth: yup
            .date()
            .max(new Date(), "Date of birth can not be in the future.")
            .required("Date of birth is required.")
            .min("1900-01-01", "Date can not be before 1900.")
            .typeError("Date of birth is required."),
        gender: yup
            .string()
            .nullable()
            .max(256, "Gender identity can not exceed 256 characters"),
        pronouns: yup
            .string()
            .nullable()
            .max(256, "Pronouns can not exceed 256 characters"),
        display_gender_identity: yup.boolean(),
        country: yup.string(),
        sex: yup
            .string()
            .required("A competition sex is required.")
            .oneOf(["Male", "Female"]),
        other_notes: yup
            .string()
            .nullable()
            .max(8192, "Other notes can not exceed 8192 characters"),
    };

    const adminValidationRules = {
        ngb_reg: yup
            .string()
            .max(36, "Governing body ID can not exceed 36 characters."),
        ngb_category: yup.string().required("A category is required."),
        club_pays_ngb_fees: yup.boolean(),
        club_category: yup.string().required("A category is required."),
        club_pays_club_membership_fees: yup.boolean(),
    };

    const photoValidationRules = {
        photo_website: yup.boolean(),
        photo_social: yup.boolean(),
        photo_noticeboard: yup.boolean(),
        photo_professional_photographer: yup.boolean(),
        photo_film_training: yup.boolean(),
    };

    return (
        <>
            <Head
                title={`Edit ${props.name}`}
                subtitle="Member"
                breadcrumbs={[
                    { name: "Members", route: "members.index" },
                    {
                        name: props.name,
                        route: "members.show",
                        routeParams: {
                            member: props.id,
                        },
                    },
                    {
                        name: "Edit",
                        route: "members.edit",
                        routeParams: {
                            member: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader title={`Edit ${props.name}`} subtitle="Member" />

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
                        ...validationRules,
                        ...(props.is_admin ? adminValidationRules : {}),
                        ...(props.is_linked_user && props.age < 18
                            ? photoValidationRules
                            : {}),
                    })}
                    submitTitle="Save"
                    action={route("members.edit", props.id)}
                    method="put"
                    removeDefaultInputMargin
                    hideErrors
                >
                    <RenderServerErrors />
                    <FlashAlert className="mb-3" />

                    <div className="grid gap-4">
                        <Card title="Basic information">
                            <div className="grid grid-cols-6 gap-4">
                                <div className="col-span-3 md:col-span-2">
                                    <TextInput
                                        name="first_name"
                                        label="First name"
                                    />
                                </div>

                                <div className="col-span-3 md:col-span-2">
                                    <TextInput
                                        name="last_name"
                                        label="Last name"
                                    />
                                </div>

                                <div className="col-span-6 md:col-span-2 md:col-start-1">
                                    <DateNumeralInput
                                        name="date_of_birth"
                                        label="Date of birth"
                                    />
                                </div>

                                <div className="col-span-6 md:col-span-2 md:col-start-1">
                                    <RadioGroup
                                        label="Competition category"
                                        name="sex"
                                    >
                                        <Radio
                                            value="Male"
                                            label="Open (formerly Male)"
                                        />
                                        <Radio value="Female" label="Female" />
                                    </RadioGroup>
                                </div>

                                {!props.is_admin && (
                                    <div className="col-span-6 md:col-span-2 md:col-start-1">
                                        <Select
                                            name="country"
                                            label="Country of international representation"
                                            items={
                                                props.countries_of_representation
                                            }
                                        />
                                    </div>
                                )}
                            </div>
                        </Card>

                        <Card title="Gender identity">
                            <div className="grid grid-cols-6 gap-4">
                                <div className="col-span-6 md:col-span-2 md:col-start-1">
                                    <div className="mb-4">
                                        <RadioGroup
                                            label="Gender"
                                            name="gender"
                                        >
                                            <Radio value="Male" label="Male" />
                                            <Radio
                                                value="Female"
                                                label="Female"
                                            />
                                            <Radio
                                                value="Non binary"
                                                label="Non binary"
                                            />
                                        </RadioGroup>
                                    </div>
                                    <TextInput
                                        name="gender"
                                        label="Other gender"
                                    />
                                </div>
                                <div className="col-span-6 md:col-span-2">
                                    <div className="mb-4">
                                        <RadioGroup
                                            label="Pronouns"
                                            name="pronouns"
                                        >
                                            <Radio
                                                value="He/Him/His"
                                                label="He/Him/His"
                                            />
                                            <Radio
                                                value="She/Her/Hers"
                                                label="She/Her/Hers"
                                            />
                                            <Radio
                                                value="They/Them/Theirs"
                                                label="They/Them/Theirs"
                                            />
                                        </RadioGroup>
                                    </div>
                                    <TextInput
                                        name="pronouns"
                                        label="Other pronouns"
                                        help="If entering custom pronouns, please consider using a similar format to those above."
                                    />
                                </div>
                                <div className="col-span-6">
                                    <Checkbox
                                        name="display_gender_identity"
                                        label="Show my gender and pronouns to club staff throughout the membership system"
                                    />
                                </div>
                            </div>
                        </Card>

                        <Card title="Medical details">
                            <div className="prose prose-sm">
                                <p>
                                    <Link
                                        href={route(
                                            "members.edit_medical",
                                            props.id,
                                        )}
                                        external
                                    >
                                        Edit medical details
                                    </Link>{" "}
                                    (opens in new tab).
                                </p>
                            </div>
                        </Card>

                        {props.is_admin && (
                            <Card title="Membership information">
                                <div className="grid grid-cols-6 gap-4">
                                    <div className="col-span-6 md:col-span-2 md:col-start-1">
                                        <TextInput
                                            name="ngb_reg"
                                            label="Swim England registration number"
                                        />
                                    </div>

                                    <div className="col-span-6 md:col-span-2">
                                        <Select
                                            name="ngb_category"
                                            label="Swim England membership category"
                                            items={props.ngb_membership_classes}
                                        />
                                    </div>

                                    <div className="col-span-6 md:col-span-2 md:col-start-1">
                                        <Select
                                            name="country"
                                            label="Country of international representation"
                                            items={
                                                props.countries_of_representation
                                            }
                                        />
                                    </div>

                                    <div className="col-span-6 md:col-span-2 md:col-start-1">
                                        <Checkbox
                                            name="club_pays_ngb_fees"
                                            label="Club pays Swim England fees"
                                        />
                                    </div>

                                    <div className="col-span-6 md:col-span-2 md:col-start-1">
                                        <Select
                                            name="club_category"
                                            label="Club membership category"
                                            items={
                                                props.club_membership_classes
                                            }
                                        />
                                    </div>
                                    <div className="col-span-6 md:col-span-2 md:col-start-1">
                                        <Checkbox
                                            name="club_pays_club_membership_fees"
                                            label="Club pays club membership fees"
                                        />
                                    </div>
                                </div>
                            </Card>
                        )}

                        {props.is_linked_user && props.age < 18 && (
                            <Card title="Photography permissions">
                                <div className="prose prose-sm">
                                    <p>
                                        You should complete this form after{" "}
                                        <Link
                                            href="https://www.swimming.org/swimengland/wavepower-child-safeguarding-for-clubs/"
                                            external
                                        >
                                            reading the Swim England Photography
                                            and Filming guidance contained in
                                            Wavepower (opens in new tab)
                                        </Link>{" "}
                                        and the{" "}
                                        <Link href="/privacy">
                                            {props.tenant.name}
                                            Privacy Policy
                                        </Link>
                                        .
                                    </p>

                                    <p>
                                        {props.tenant.name} may wish to take
                                        photographs or film individual or groups
                                        of members under the age of 18 that may
                                        include {props.name} during their
                                        membership of {props.tenant.name}. All
                                        photographs and filming and all use of
                                        such images will be in accordance with
                                        the Swim England Photography and Filming
                                        Guidance and {props.tenant.name}'s
                                        Privacy Policy.
                                    </p>

                                    <p>
                                        {props.tenant.name} will take all
                                        reasonable steps to ensure images and
                                        any footage is being used solely for
                                        their intended purpose and not kept for
                                        any longer than is necessary for that
                                        purpose. If you have any concerns or
                                        questions about how they are being used
                                        please contact the Welfare Officer to
                                        discuss this further.
                                    </p>

                                    <p>
                                        As a parent/guardian please complete the
                                        below in respect of {props.name}. We
                                        encourage all parents/guardians to
                                        discuss and explain their choices with
                                        their child/ren. Please note that either
                                        you or your child can withdraw consent
                                        or object to a particular type of use by
                                        notifying the Welfare Officer at any
                                        time and changing the consents given
                                        from within your club account.
                                    </p>

                                    <p>
                                        As the parent/guardian of {props.name} I
                                        am happy for:
                                    </p>

                                    <Checkbox
                                        name="photo_website"
                                        label={`${props.first_name}'s photograph to be used on the ${props.tenant.name} website.`}
                                    />
                                    <Checkbox
                                        name="photo_social"
                                        label={`${props.first_name}'s photograph to be used on ${props.tenant.name} social media platform/s.`}
                                    />
                                    <Checkbox
                                        name="photo_noticeboard"
                                        label={`${props.first_name}'s photograph to be used within other printed publications such as newspaper articles about ${props.tenant.name}.`}
                                    />
                                    <Checkbox
                                        name="photo_professional_photographer"
                                        label={`${props.first_name}'s photograph to be taken by a professional photographer employed by ${props.tenant.name} as the official photographer at competitions, galas and other organisational events.`}
                                    />
                                    <Checkbox
                                        name="photo_film_training"
                                        label={`${props.first_name} to be filmed by ${props.tenant.name} for training purposes.`}
                                    />
                                </div>
                            </Card>
                        )}

                        <Card title="Other details">
                            <TextArea name="other_notes" label="Other notes" />
                        </Card>
                    </div>
                </Form>
            </Container>
        </>
    );
};

New.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default New;
