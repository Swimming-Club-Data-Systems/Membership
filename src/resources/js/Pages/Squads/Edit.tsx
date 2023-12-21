import React, { ReactNode, useState } from "react";
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
import Button from "@/Components/Button";
import BasicList from "@/Components/BasicList";
import Link from "@/Components/Link";
import { TrashIcon, UserPlusIcon } from "@heroicons/react/24/outline";
import Modal from "@/Components/Modal";
import { router } from "@inertiajs/react";
import Combobox from "@/Components/Form/Combobox";

type Coach = {
    id: number;
    name: string;
    type: string;
};

type Props = {
    id: number;
    name: string;
    codes_of_conduct: {
        value: number;
        name: ReactNode;
    }[];
    coaches: Coach[];
};

const CoachTypeSelect = [
    {
        value: "LEAD_COACH",
        name: "Lead Coach",
    },
    {
        value: "COACH",
        name: "Coach",
    },
    {
        value: "ASSISTANT_COACH",
        name: "Assistant Coach",
    },
    {
        value: "TEACHER",
        name: "Teacher",
    },
    {
        value: "HELPER",
        name: "Helper",
    },
    {
        value: "ADMINISTRATOR",
        name: "Squad Administrator",
    },
];

const Edit = (props: Props) => {
    const [showAddCoachModal, setShowAddCoachModal] = useState<boolean>(false);
    const [showDeleteCoachModal, setShowDeleteCoachModal] =
        useState<boolean>(false);
    const [deleteModalDetails, setDeleteModalDetails] = useState<Coach>(null);

    const deleteCoach = async () => {
        router.delete(
            route("squads.delete-coach", {
                squad: props.id,
                user: deleteModalDetails.id,
            }),
            {
                only: ["coaches", "flash"],
                preserveState: true,
                preserveScroll: true,
                onFinish: (page) => {
                    setShowDeleteCoachModal(false);
                },
            },
        );
    };

    return (
        <>
            <Head
                title={props.name}
                breadcrumbs={[
                    { name: "Squads", route: "squads.index" },
                    {
                        name: props.name,
                        route: "squads.show",
                        routeParams: {
                            squad: props.id,
                        },
                    },
                    {
                        name: "Edit",
                        route: "squads.edit",
                        routeParams: {
                            squad: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader title={`Edit ${props.name}`} subtitle="Squad" />
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    <Form
                        initialValues={{
                            name: "",
                            monthly_fee: 0,
                            timetable: "",
                            code_of_conduct: null,
                        }}
                        validationSchema={yup.object().shape({
                            name: yup
                                .string()
                                .required("A squad name is required.")
                                .max(
                                    255,
                                    "Squad name can not exceed 100 characters.",
                                ),
                            monthly_fee: yup
                                .number()
                                .typeError("Monthly fee must be a number.")
                                .required("Monthly fee is required.")
                                .min(
                                    0,
                                    "Monthly fee can not be less than zero.",
                                ),
                            timetable: yup
                                .string()
                                .max(
                                    100,
                                    "Timetable url must not exceed 100 characters.",
                                )
                                .url("Timetable must be a valid URL."),
                            code_of_conduct: yup.number().nullable(),
                        })}
                        submitTitle="Save"
                        action={route("squads.show", props.id)}
                        method="put"
                        hideDefaultButtons
                    >
                        <Card footer={<SubmissionButtons />}>
                            <RenderServerErrors />
                            <FlashAlert className="mb-3" />

                            <TextInput name="name" label="Name" />
                            <DecimalInput
                                name="monthly_fee"
                                label="Monthly fee (£)"
                                precision={2}
                            />
                            <TextInput
                                name="timetable"
                                label="Timetable URL"
                                help="You can link to a timetable on your website. If you don't provide a link, we'll show a link to a timetable generated from the sessions in your registers."
                            />
                            <Select
                                nullable
                                name="code_of_conduct"
                                label="Code of conduct"
                                items={props.codes_of_conduct}
                            />
                        </Card>
                    </Form>

                    <Card
                        title="Coaches"
                        footer={
                            <Button
                                onClick={() => {
                                    setShowAddCoachModal(true);
                                }}
                            >
                                Add Coach
                            </Button>
                        }
                    >
                        <FlashAlert className="mb-3" bag="coaches" />

                        <BasicList
                            items={props.coaches.map((coach) => {
                                return {
                                    id: coach.id,
                                    content: (
                                        <>
                                            <div
                                                className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-2 text-sm"
                                                key={coach.id}
                                            >
                                                <div className="">
                                                    <div className="text-gray-900">
                                                        <Link
                                                            href={route(
                                                                "users.show",
                                                                coach.id,
                                                            )}
                                                        >
                                                            {coach.name}
                                                        </Link>
                                                    </div>
                                                    <div className="text-gray-500">
                                                        {coach.type}
                                                    </div>
                                                </div>
                                                <div className="block">
                                                    <div className="flex gap-1">
                                                        <Button
                                                            variant="danger"
                                                            onClick={() => {
                                                                setDeleteModalDetails(
                                                                    coach,
                                                                );
                                                                setShowDeleteCoachModal(
                                                                    true,
                                                                );
                                                            }}
                                                        >
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </>
                                    ),
                                };
                            })}
                        />
                    </Card>
                </div>
            </Container>

            <Modal
                onClose={() => setShowDeleteCoachModal(false)}
                title="Delete coach"
                show={showDeleteCoachModal}
                variant="danger"
                Icon={TrashIcon}
                buttons={
                    <>
                        <Button variant="danger" onClick={deleteCoach}>
                            Confirm delete
                        </Button>
                        <Button
                            variant="secondary"
                            onClick={() => setShowDeleteCoachModal(false)}
                        >
                            Cancel
                        </Button>
                    </>
                }
            >
                {deleteModalDetails && (
                    <p>
                        Are you sure you want to remove{" "}
                        {deleteModalDetails.name} from the list of {props.name}{" "}
                        coaches?
                    </p>
                )}
            </Modal>

            <Modal
                onClose={() => setShowAddCoachModal(false)}
                title="Add coach"
                show={showAddCoachModal}
                variant="primary"
                Icon={UserPlusIcon}
            >
                <Form
                    formName="coaches"
                    validationSchema={yup.object().shape({})}
                    initialValues={{
                        user_select: null,
                        type: "",
                    }}
                    submitTitle="Add coach"
                    method="post"
                    action={route("squads.add-coach", { squad: props.id })}
                    inertiaOptions={{
                        onSuccess: (page) => {
                            setShowAddCoachModal(false);
                        },
                        only: ["coaches", "flash"],
                        preserveState: true,
                        preserveScroll: true,
                    }}
                >
                    <p className="mb-3">
                        Please choose a user and role. Roles help indicate to
                        members the seniority of this person with respect to the
                        squad.
                    </p>

                    <p className="mb-3">
                        The user will automatically be granted the Coach access
                        permission if they do not already have it.
                    </p>

                    <Combobox
                        endpoint={route("users.combobox")}
                        name="user_select"
                        label="User"
                        help="Start typing to find a user"
                    />
                    <Select name="type" label="Type" items={CoachTypeSelect} />
                </Form>
            </Modal>
        </>
    );
};

Edit.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Edit;
