import React, { useState } from "react";
import { UserGroupIcon } from "@heroicons/react/24/outline";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import { formatISO } from "date-fns";
import Combobox from "@/Components/Form/Combobox";
import DateTimeInput from "@/Components/Form/DateTimeInput";
import Checkbox from "@/Components/Form/Checkbox";
import Modal from "@/Components/Modal";
import startOfDay from "date-fns/startOfDay";
import { OldSquadSelect } from "@/Components/SquadMove/OldSquadSelect";

type Props = {
    show: boolean;
    onClose: () => void;
    member: {
        id: number;
        name: string;
    };
    date: string;
    old_squad?: {
        id: number;
    };
    new_squad?: {
        id: number;
    };
    moveId: number;
    paying_in_new_squad: boolean;
};

export const EditSquadMoveDialog = (props: Props) => {
    const [minDate] = useState(
        formatISO(startOfDay(new Date()), { representation: "date" }),
    );

    return (
        <Modal
            onClose={props.onClose}
            title="Edit squad move"
            show={props.show}
            variant="primary"
            Icon={UserGroupIcon}
        >
            <Form
                formName="edit"
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
                    date: props.date,
                    new_squad: props.new_squad?.id,
                    old_squad: props.old_squad?.id,
                    paying: props.paying_in_new_squad,
                }}
                submitTitle="Save"
                method="put"
                action={route("squad-moves.update", props.moveId)}
                inertiaOptions={{
                    onSuccess: (page) => {
                        props.onClose();
                    },
                    preserveState: true,
                    preserveScroll: true,
                }}
            >
                <div className="mb-6">
                    <DateTimeInput
                        name="date"
                        label="Move date"
                        min={minDate}
                        mb="mb-0"
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
    );
};
