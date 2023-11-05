import React, { useEffect, useState } from "react";
import { Head } from "@inertiajs/react";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import FlashAlert from "@/Components/FlashAlert";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { LaravelPaginatorProps } from "@/Components/Collection";
import { ArrowRightIcon } from "@heroicons/react/24/solid";
import { formatDate } from "@/Utils/date-utils";
import Link from "@/Components/Link";
import Button from "@/Components/Button";
import { UserGroupIcon } from "@heroicons/react/24/outline";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import Combobox from "@/Components/Form/Combobox";
import Modal from "@/Components/Modal";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Checkbox from "@/Components/Form/Checkbox";
import { formatISO } from "date-fns";
import { useField } from "formik";
import Select from "@/Components/Form/Select";
import axios from "@/Utils/axios";

type Squad = {
    id: number;
    name: string;
};

type Member = {
    id: number;
    name: string;
};

type Item = {
    id: number;
    date: string;
    old_squad?: Squad;
    new_squad?: Squad;
    member: Member;
    paying_in_new_squad: boolean;
};

interface Props extends LaravelPaginatorProps {
    moves: Item[];
}

type OldSquadSelectProps = {
    memberId?: number;
};

const OldSquadSelect = (props: OldSquadSelectProps) => {
    const [values, setValues] = useState([]);
    const [{ value: fieldMemberId }] = useField("member");
    const [{ value }, {}, { setValue }] = useField("old_squad");

    const memberId = props.memberId || fieldMemberId;

    useEffect(() => {
        // Get the member's squads
        if (memberId) {
            axios.get(route("members.squads", memberId)).then((result) => {
                setValues(result?.data ?? []);
            });
        }
    }, [memberId]);

    useEffect(() => {
        if (value) {
            // If the squad currently selected is not in the array, set the field value to null
            if (!values.find((element) => element.value === value)) {
                setValue(null);
            }
        }
    }, [setValue, value, values]);

    return (
        <Select name="old_squad" items={values} label="Old squad" nullable />
    );
};

const ItemContent = (props: Item) => {
    const [showEditModal, setShowEditModal] = useState(false);
    const [minDate] = useState(formatISO(new Date()));

    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="text-sm">
                            <Link href={route("members.show", props.member.id)}>
                                {props.member.name}
                            </Link>
                        </div>
                        <div className="text-sm text-gray-700 group-hover:text-gray-800">
                            {props.old_squad && (
                                <Link
                                    href={route(
                                        "squads.show",
                                        props.old_squad.id
                                    )}
                                >
                                    {props.old_squad.name}
                                </Link>
                            )}
                            {props.old_squad && props.new_squad && (
                                <>
                                    {" "}
                                    <ArrowRightIcon className="inline h-4" />{" "}
                                </>
                            )}
                            {props.new_squad && (
                                <Link
                                    href={route(
                                        "squads.show",
                                        props.new_squad.id
                                    )}
                                >
                                    {props.new_squad.name}
                                </Link>
                            )}{" "}
                            on {formatDate(props.date)}
                        </div>
                    </div>
                </div>
                <div className="ml-2 flex-none flex-shrink-0">
                    <Button
                        variant="primary"
                        onClick={() => setShowEditModal(true)}
                    >
                        Edit
                    </Button>
                </div>
            </div>

            <Modal
                onClose={() => setShowEditModal(false)}
                title="Edit Squad Move"
                show={showEditModal}
                variant="primary"
                Icon={UserGroupIcon}
            >
                <Form
                    formName="edit"
                    validationSchema={yup.object().shape({
                        date: yup
                            .date()
                            .required("A moving date is required.")
                            .min(
                                minDate,
                                "The move date must not be in the past."
                            ),
                        new_squad: yup
                            .number()
                            .integer()
                            .nullable()
                            .notOneOf(
                                [yup.ref("old_squad")],
                                "The new squad can not be the same as the old squad."
                            ),
                        old_squad: yup
                            .number()
                            .integer()
                            .nullable()
                            .notOneOf(
                                [yup.ref("new_squad")],
                                "The old squad can not be the same as the new squad."
                            ),
                        paying: yup.boolean(),
                    })}
                    initialValues={{
                        date: props.date,
                        new_squad: props.new_squad?.id,
                        old_squad: props.old_squad?.id,
                        paying: props.paying_in_new_squad,
                    }}
                    submitTitle="Save"
                    method="put"
                    action={route("squad-moves.update", props.id)}
                    inertiaOptions={{
                        onSuccess: (page) => {
                            setShowEditModal(false);
                        },
                        only: ["coaches", "flash"],
                        preserveState: true,
                        preserveScroll: true,
                    }}
                >
                    <div className="mb-6">
                        <DateTimeInput
                            name="date"
                            label="Move date"
                            min={minDate}
                        />
                    </div>

                    <OldSquadSelect memberId={props.member.id} />

                    <Combobox
                        endpoint={route("squads.combobox")}
                        name="new_squad"
                        label="New squad"
                        help="Start typing to find a squad."
                        nullable
                    />

                    <Checkbox
                        name="paying"
                        label="Paying"
                        help="Whether the member will pay fees for this squad."
                    />
                </Form>
            </Modal>
        </>
    );
};

const crumbs = [{ route: "squad-moves.index", name: "Squad Moves" }];

const Index = (props: Props) => {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [minDate] = useState(formatISO(new Date()));

    return (
        <>
            <Head title="Squad Moves" />

            <Container>
                <MainHeader
                    title="Squad Moves"
                    subtitle="All upcoming moves"
                    buttons={
                        <Button onClick={() => setShowCreateModal(true)}>
                            New
                        </Button>
                    }
                />

                <FlashAlert className="mb-4" />
            </Container>

            <Container noMargin>
                <PlainCollection {...props.moves} itemRenderer={ItemContent} />
            </Container>

            <Modal
                onClose={() => setShowCreateModal(false)}
                title="New Squad Move"
                show={showCreateModal}
                variant="primary"
                Icon={UserGroupIcon}
            >
                <Form
                    formName="edit"
                    validationSchema={yup.object().shape({
                        date: yup
                            .date()
                            .required("A moving date is required.")
                            .min(
                                new Date(),
                                "The move date must not be in the past."
                            ),
                        new_squad: yup
                            .number()
                            .integer()
                            .nullable()
                            .notOneOf(
                                [yup.ref("old_squad")],
                                "The new squad can not be the same as the old squad."
                            ),
                        old_squad: yup
                            .number()
                            .integer()
                            .nullable()
                            .notOneOf(
                                [yup.ref("new_squad")],
                                "The old squad can not be the same as the new squad."
                            ),
                        paying: yup.boolean(),
                    })}
                    initialValues={{
                        date: formatISO(new Date()),
                        new_squad: null,
                        old_squad: null,
                        paying: true,
                    }}
                    submitTitle="Save"
                    method="post"
                    action={route("squad-moves.create")}
                    inertiaOptions={{
                        onSuccess: (page) => {
                            setShowCreateModal(false);
                        },
                        only: ["coaches", "flash"],
                        preserveState: true,
                        preserveScroll: true,
                    }}
                >
                    <Combobox
                        endpoint={route("members.combobox")}
                        name="member"
                        label="Member"
                        help="Start typing to find a member."
                    />

                    <div className="mb-6">
                        <DateTimeInput
                            name="date"
                            label="Move date"
                            min={minDate}
                        />
                    </div>

                    <OldSquadSelect />

                    <Combobox
                        endpoint={route("squads.combobox")}
                        name="new_squad"
                        label="New squad"
                        help="Start typing to find a squad."
                        nullable
                    />

                    <Checkbox
                        name="paying"
                        label="Paying"
                        help="Whether the member will pay fees for this squad."
                    />
                </Form>
            </Modal>
        </>
    );
};

Index.layout = (page) => (
    <MainLayout hideHeader breadcrumbs={crumbs}>
        {page}
    </MainLayout>
);

export default Index;
