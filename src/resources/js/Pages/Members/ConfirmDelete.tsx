import React, { useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import Card from "@/Components/Card";
import Link from "@/Components/Link";
import { DefinitionList } from "@/Components/DefinitionList";
import ButtonLink from "@/Components/ButtonLink";
import Button from "@/Components/Button";
import Modal from "@/Components/Modal";
import { TrashIcon } from "@heroicons/react/24/outline";
import FlashAlert from "@/Components/FlashAlert";
import { formatDate } from "@/Utils/date-utils";
import { Tab, Tabs } from "@/Components/Tabs";
import { BasicListTwo, BasicListTwoItem } from "@/Components/BasicListTwo";
import Alert from "@/Components/Alert";
import { NewSquadMoveDialog } from "@/Components/SquadMove/NewSquadMoveDialog";
import { ArrowRightIcon } from "@heroicons/react/24/solid";
import { EditSquadMoveDialog } from "@/Components/SquadMove/EditSquadMoveDialog";
import { CancelSquadMoveDialog } from "@/Components/SquadMove/CancelSquadMoveDialog";
import Form from "@/Components/Form/Form";
import Checkbox from "@/Components/Form/Checkbox";
import * as yup from "yup";
import { router } from "@inertiajs/react";

type Props = {
    id: number;
    name: string;
    first_name: string;
    last_name: string;
};

const ConfirmDelete = (props: Props) => {
    const deleteMember = async () => {
        router.delete(route("members.delete", [props.id]), {});
    };

    return (
        <>
            <Head
                title={`Confirm deletion of ${props.name}`}
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
                        name: "Delete",
                        route: "members.confirm_delete",
                        routeParams: {
                            member: props.id,
                        },
                    },
                ]}
            />

            <Container>
                <MainHeader
                    title={props.name}
                    subtitle="Confirm you want to delete this member"
                />

                <FlashAlert className="mb-3" />
            </Container>

            <Container noMargin>
                <div className="grid gap-4 grid-cols-1 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <Card
                            title={`What happens when I delete ${props.first_name}?`}
                        >
                            <div className="prose prose-sm">
                                <p>
                                    By deleting {props.first_name}, we will
                                    remove all personally identifiable
                                    information. Some information will be
                                    retained for the completeness of your club's
                                    records.
                                </p>

                                <p>
                                    Deleting {props.first_name} will have no
                                    impact on any linked user records.
                                </p>

                                <p>
                                    When you press confirm, {props.first_name}{" "}
                                    will be queued for deletion. This process
                                    may take some time - you will receive an
                                    email when the process completes telling you
                                    if the member was deleted successfully or
                                    not. The member will still be visible in
                                    systems until this time.
                                </p>

                                <p>
                                    Deletion is permanent and cannot be undone
                                    once the process completes successfully.
                                </p>
                            </div>
                        </Card>
                    </div>
                    <div className="col-start-1">
                        <Button variant="danger" onClick={deleteMember}>
                            Confirm delete
                        </Button>
                    </div>
                </div>
            </Container>
        </>
    );
};

ConfirmDelete.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default ConfirmDelete;
