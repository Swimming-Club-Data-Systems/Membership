import React, { useState } from "react";
import MainLayout from "@/Layouts/MainLayout";
import Head from "@/Components/Head";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import BasicList from "@/Components/BasicList";
import Card from "@/Components/Card";
import Badge from "@/Components/Badge";
import Link from "@/Components/Link";
import { DefinitionList } from "@/Components/DefinitionList";
import ButtonLink from "@/Components/ButtonLink";
import Button from "@/Components/Button";
import Modal from "@/Components/Modal";
import { TrashIcon } from "@heroicons/react/24/outline";
import { router } from "@inertiajs/react";
import Alert from "@/Components/Alert";
import FlashAlert from "@/Components/FlashAlert";

type Props = {
    id: number;
    name: string;
    monthly_fee_formatted: string;
    timetable_url: string;
    timetable_url_display: string;
    members: {
        id: number;
        name: string;
        date_of_birth: string;
        age: number;
        pronouns?: string;
        medical_consent_withheld: boolean;
        medical_conditions_recently_updated: boolean;
    }[];
    coaches: {
        id: number;
        name: string;
        type: string;
    }[];
    editable: boolean;
};

const Show = (props: Props) => {
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    const deleteSquad = async () => {
        router.delete(route("squads.delete", [props.id]), {
            onFinish: (page) => {
                setShowDeleteModal(false);
            },
        });
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
                ]}
            />

            <Container>
                <MainHeader
                    title={props.name}
                    subtitle="Squad"
                    buttons={
                        <>
                            {props.editable && (
                                <>
                                    <Button
                                        variant="danger"
                                        onClick={() => {
                                            setShowDeleteModal(true);
                                        }}
                                    >
                                        Delete
                                    </Button>
                                    <ButtonLink
                                        href={route("squads.edit", props.id)}
                                    >
                                        Edit
                                    </ButtonLink>
                                </>
                            )}
                        </>
                    }
                />

                <FlashAlert className="mb-3" />
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    <Card title="About this squad">
                        <DefinitionList
                            items={[
                                {
                                    key: "monthly_fee",
                                    term: "Monthly fee",
                                    definition: props.monthly_fee_formatted,
                                },
                                {
                                    key: "coaches",
                                    term: "Coaches",
                                    definition: (
                                        <ul className="list-none">
                                            {props.coaches.map((coach) => (
                                                <li key={coach.id}>
                                                    <span className="font-bold">
                                                        {coach.name}
                                                    </span>{" "}
                                                    ({coach.type})
                                                </li>
                                            ))}
                                        </ul>
                                    ),
                                },
                                {
                                    key: "timetable",
                                    term: "Timetable",
                                    definition: (
                                        <Link
                                            href={props.timetable_url}
                                            external
                                        >
                                            {props.timetable_url_display}
                                        </Link>
                                    ),
                                    truncate: true,
                                },
                            ]}
                        />
                    </Card>

                    <Card title="Members">
                        {props.members.length > 0 && (
                            <BasicList
                                items={props.members.map((member) => {
                                    return {
                                        id: member.id,
                                        content: (
                                            <>
                                                <div
                                                    className="flex flex-col md:flex-row md:items-center md:justify-between gap-y-2 text-sm"
                                                    key={member.id}
                                                >
                                                    <div className="">
                                                        <div className="text-gray-900">
                                                            <Link
                                                                href={route(
                                                                    "members.show",
                                                                    member.id
                                                                )}
                                                            >
                                                                {member.name}
                                                            </Link>
                                                        </div>
                                                        {member.pronouns && (
                                                            <div className="text-gray-500">
                                                                {
                                                                    member.pronouns
                                                                }
                                                            </div>
                                                        )}
                                                    </div>
                                                    <div className="block">
                                                        <div className="flex gap-1">
                                                            {member.medical_consent_withheld && (
                                                                <Badge colour="red">
                                                                    Medical
                                                                    Consent
                                                                    Withheld
                                                                </Badge>
                                                            )}
                                                            {member.medical_conditions_recently_updated && (
                                                                <Badge colour="yellow">
                                                                    Medical
                                                                    Updated
                                                                </Badge>
                                                            )}
                                                            <Badge colour="indigo">
                                                                Age {member.age}
                                                            </Badge>
                                                        </div>
                                                    </div>
                                                </div>
                                            </>
                                        ),
                                    };
                                })}
                            />
                        )}
                        {props.members.length === 0 && (
                            <Alert
                                title="No members to display"
                                variant="warning"
                            >
                                This squad currently has no members.
                            </Alert>
                        )}
                    </Card>
                </div>
            </Container>

            <Modal
                onClose={() => setShowDeleteModal(false)}
                title="Delete squad"
                show={showDeleteModal}
                variant="danger"
                Icon={TrashIcon}
                buttons={
                    <>
                        <Button variant="danger" onClick={deleteSquad}>
                            Confirm delete
                        </Button>
                        <Button
                            variant="secondary"
                            onClick={() => setShowDeleteModal(false)}
                        >
                            Cancel
                        </Button>
                    </>
                }
            >
                <p>Are you sure you want to delete {props.name}?</p>
            </Modal>
        </>
    );
};

Show.layout = (page) => <MainLayout hideHeader>{page}</MainLayout>;

export default Show;
