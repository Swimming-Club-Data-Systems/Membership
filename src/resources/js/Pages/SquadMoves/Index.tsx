import React, { useState } from "react";
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
import { formatISO } from "date-fns";
import startOfDay from "date-fns/startOfDay";
import { NewSquadMoveDialog } from "@/Components/SquadMove/NewSquadMoveDialog";
import { EditSquadMoveDialog } from "@/Components/SquadMove/EditSquadMoveDialog";
import { CancelSquadMoveDialog } from "@/Components/SquadMove/CancelSquadMoveDialog";

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

interface Props {
    moves: LaravelPaginatorProps<Item>;
}

const ItemContent = (props: Item) => {
    const [showEditModal, setShowEditModal] = useState<boolean>(false);
    const [showCancelModal, setShowCancelModal] = useState<boolean>(false);

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
                                <>
                                    {!props.new_squad && <>Leaving </>}
                                    <Link
                                        href={route(
                                            "squads.show",
                                            props.old_squad.id,
                                        )}
                                    >
                                        {props.old_squad.name}
                                    </Link>
                                </>
                            )}
                            {props.old_squad && props.new_squad && (
                                <>
                                    {" "}
                                    <ArrowRightIcon className="inline h-4" />{" "}
                                </>
                            )}
                            {props.new_squad && (
                                <>
                                    {!props.old_squad && <>Joining </>}
                                    <Link
                                        href={route(
                                            "squads.show",
                                            props.new_squad.id,
                                        )}
                                    >
                                        {props.new_squad.name}
                                    </Link>
                                </>
                            )}{" "}
                            on {formatDate(props.date)}
                        </div>
                    </div>
                </div>
                <div className="ml-2 flex gap-2 flex-shrink-0">
                    <Button
                        variant="primary"
                        onClick={() => setShowEditModal(true)}
                    >
                        Edit
                    </Button>

                    <Button
                        variant="danger"
                        onClick={() => setShowCancelModal(true)}
                    >
                        Delete
                    </Button>
                </div>
            </div>

            <EditSquadMoveDialog
                moveId={props.id}
                paying_in_new_squad={props.paying_in_new_squad}
                new_squad={props.new_squad}
                old_squad={props.old_squad}
                show={showEditModal}
                onClose={() => {
                    setShowEditModal(false);
                }}
                date={props.date}
                member={props.member}
            />

            <CancelSquadMoveDialog
                moveId={props.id}
                show={showCancelModal}
                onClose={() => {
                    setShowCancelModal(false);
                }}
                member={props.member}
            />
        </>
    );
};

const crumbs = [{ route: "squad-moves.index", name: "Squad Moves" }];

const Index = (props: Props) => {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [minDate] = useState(
        formatISO(startOfDay(new Date()), { representation: "date" }),
    );

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

            <NewSquadMoveDialog
                show={showCreateModal}
                onClose={() => setShowCreateModal(false)}
            />
        </>
    );
};

Index.layout = (page) => (
    <MainLayout hideHeader breadcrumbs={crumbs}>
        {page}
    </MainLayout>
);

export default Index;
