import React from "react";
import { UserGroupIcon } from "@heroicons/react/24/outline";
import Modal from "@/Components/Modal";
import Button from "@/Components/Button";
import { router } from "@inertiajs/react";

type Props = {
    show: boolean;
    onClose: () => void;
    member: {
        id: number;
        name: string;
    };
    moveId: number;
};

export const CancelSquadMoveDialog = (props: Props) => {
    const deleteMove = async () => {
        router.delete(route("squad-moves.delete", props.moveId), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                props.onClose();
            },
        });
    };

    return (
        <Modal
            onClose={props.onClose}
            title="Delete squad move"
            show={props.show}
            variant="danger"
            Icon={UserGroupIcon}
            buttons={
                <>
                    <Button variant="danger" onClick={deleteMove}>
                        Confirm
                    </Button>
                    <Button variant="secondary" onClick={props.onClose}>
                        Cancel
                    </Button>
                </>
            }
        >
            <p className="text-sm">
                Are you sure you want to delete {props.member.name}'s squad
                move?
            </p>
        </Modal>
    );
};
