import React, { useState } from "react";
import { UserGroupIcon } from "@heroicons/react/24/outline";
import Form, { RenderServerErrors } from "@/Components/Form/Form";
import * as yup from "yup";
import { formatISO } from "date-fns";
import Combobox from "@/Components/Form/Combobox";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Checkbox from "@/Components/Form/Checkbox";
import Modal from "@/Components/Modal";
import startOfDay from "date-fns/startOfDay";
import { OldSquadSelect } from "@/Components/SquadMove/OldSquadSelect";
import ValidationErrors from "@/Components/ValidationErrors";
import FlashAlert from "@/Components/FlashAlert";

type Props = {
    show: boolean;
    onClose: () => void;
    member?: {
        id: number;
        name: string;
        first_name: string;
    };
    squadToLeave?: number;
};

export const NewSquadMoveDialog = (props: Props) => {
    const [minDate] = useState(
        formatISO(startOfDay(new Date()), { representation: "date" }),
    );

    return (
        <Modal
            onClose={props.onClose}
            title={
                props.member
                    ? `New squad move for ${props.member.name}`
                    : "New squad move"
            }
            show={props.show}
            variant="primary"
            Icon={UserGroupIcon}
        >
            <Form
                validationSchema={yup.object().shape({
                    date: yup
                        .date()
                        .required("A moving date is required.")
                        .min(minDate, "The move date must not be in the past."),
                    new_squad: yup
                        .number()
                        .integer()
                        .nullable()
                        .notOneOf(
                            [yup.ref("old_squad")],
                            "The new squad can not be the same as the old squad.",
                        ),
                    old_squad: yup
                        .number()
                        .integer()
                        .nullable()
                        .notOneOf(
                            [yup.ref("new_squad")],
                            "The old squad can not be the same as the new squad.",
                        ),
                    paying: yup.boolean(),
                })}
                initialValues={{
                    member: props.member?.id,
                    date: formatISO(new Date()),
                    new_squad: null,
                    old_squad: props.squadToLeave ?? null,
                    paying: true,
                }}
                enableReinitialize={false}
                formName="create-move"
                submitTitle="Save"
                method="post"
                action={route("squad-moves.create")}
                inertiaOptions={{
                    onSuccess: (page) => {
                        props.onClose();
                    },
                    preserveState: true,
                    preserveScroll: true,
                }}
            >
                <FlashAlert bag="create-move" />

                <RenderServerErrors />

                {!props.member && (
                    <Combobox
                        endpoint={route("members.combobox")}
                        name="member"
                        label="Member"
                        help="Start typing to find a member."
                    />
                )}

                <div className="mb-6">
                    <DateTimeInput
                        name="date"
                        label="Move date"
                        min={minDate}
                        mb="mb-0"
                    />
                </div>

                <OldSquadSelect />

                <Combobox
                    endpoint={route("squads.combobox")}
                    name="new_squad"
                    label="New squad"
                    help="Start typing to find a squad. Leave this field blank if the member is not joining a new squad."
                    nullable
                />

                <Checkbox
                    name="paying"
                    label="Paying"
                    help={
                        props.member
                            ? `Whether ${props.member.first_name} will pay fees for this squad.`
                            : "Whether the member will pay fees for this squad."
                    }
                />
            </Form>
        </Modal>
    );
};
