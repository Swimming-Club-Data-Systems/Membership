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

type Props = {
    id: number;
    name: string;
    first_name: string;
    last_name: string;
    date_of_birth: string;
    age: number;
    country: string;
    governing_body_registration_number: string;
    sex: string;
    gender?: string;
    pronouns?: string;
    display_gender_identity: boolean;
    medical?: {
        conditions: string;
        allergies: string;
        medication: string;
        gp_name: string;
        gp_phone: string;
        gp_address: string[];
        consent_withheld: boolean;
    };
    emergency_contacts: {
        id: number;
        name: string;
        relation: string;
        contact_number_url: string;
        contact_number_display: string;
    }[];
    photography_permissions: {
        website: boolean;
        social: boolean;
        noticeboard: boolean;
        film_training: boolean;
        professional_photographer: boolean;
    };
    squads: {
        id: number;
        name: string;
        fee: number;
        formatted_fee: string;
        pays: boolean;
    }[];
    squad_moves: {
        id: number;
        old_squad?: {
            id: number;
            name: string;
        };
        new_squad?: {
            id: number;
            name: string;
        };
        paying: boolean;
        date: string;
    }[];
    extra_fees: {
        id: number;
        name: string;
        fee: number;
        formatted_fee: string;
        type: "Payment" | "Refund";
    }[];
    club_membership_class: {
        name: string;
    };
    club_pays_club_membership_fee: boolean;
    governing_body_membership_class: {
        name: string;
    };
    club_pays_governing_body_membership_fee: boolean;
    other_notes: string;
    editable: boolean;
    deletable: boolean;
};

const Show = (props: Props) => {
    const [showDeleteModal, setShowDeleteModal] = useState<boolean>(false);
    const [showNewSquadMoveModal, setShowNewSquadMoveModal] =
        useState<boolean>(false);
    const [showRemoveModal, setShowRemoveModal] = useState<number>(null);
    const [showEditModal, setShowEditModal] = useState<number>(null);
    const [showCancelModal, setShowCancelModal] = useState<number>(null);

    const deleteSquad = async () => {
        // router.delete(route("squads.delete", [props.id]), {
        //     onFinish: (page) => {
        //         setShowDeleteModal(false);
        //     },
        // });
    };

    return (
        <>
            <Head
                title={props.name}
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
                ]}
            />

            <Container>
                <MainHeader
                    title={props.name}
                    subtitle="Member"
                    buttons={
                        <>
                            {props.deletable && (
                                <Button
                                    variant="danger"
                                    onClick={() => {
                                        setShowDeleteModal(true);
                                    }}
                                >
                                    Delete
                                </Button>
                            )}
                            {props.editable && (
                                <ButtonLink
                                    href={route("members.edit", props.id)}
                                >
                                    Edit
                                </ButtonLink>
                            )}
                        </>
                    }
                />

                <FlashAlert className="mb-3" />
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    <Card title={`About ${props.name}`}>
                        <DefinitionList
                            items={[
                                {
                                    key: "date_of_birth",
                                    term: "Date of birth",
                                    definition: formatDate(props.date_of_birth),
                                },
                                {
                                    key: "age",
                                    term: "Age",
                                    definition: props.age,
                                    truncate: true,
                                },
                                {
                                    key: "membership_number",
                                    term: "Swim England membership number",
                                    definition:
                                        props.governing_body_registration_number,
                                },
                                {
                                    key: "category",
                                    term: "Swim England membership category",
                                    definition:
                                        props.governing_body_membership_class
                                            .name,
                                },
                                {
                                    key: "country",
                                    term: "Country of representation",
                                    definition: props.country,
                                },
                                {
                                    key: "sex",
                                    term: "Sex (for the purposes of competition)",
                                    definition: props.sex,
                                },
                                {
                                    key: "gender_identity",
                                    term: "Gender identity",
                                    definition: props.gender || "Not set",
                                },
                                {
                                    key: "pronouns",
                                    term: "Pronouns",
                                    definition: props.pronouns || "Not set",
                                },
                                {
                                    key: "other_notes",
                                    term: "Other notes",
                                    definition: props.other_notes || "None",
                                    unsafe: true,
                                },
                            ]}
                        />
                    </Card>

                    <Tabs>
                        <Tab name="Medical">
                            <div className="grid gap-4">
                                <Card title="Medical notes">
                                    <DefinitionList
                                        items={[
                                            {
                                                key: "conditions",
                                                term: "Conditions",
                                                definition:
                                                    props.medical.conditions ||
                                                    "N/A",
                                                unsafe: true,
                                            },
                                            {
                                                key: "allergies",
                                                term: "Allergies",
                                                definition:
                                                    props.medical.allergies ||
                                                    "N/A",
                                                unsafe: true,
                                            },
                                            {
                                                key: "medication",
                                                term: "Medication",
                                                definition:
                                                    props.medical.medication ||
                                                    "N/A",
                                                unsafe: true,
                                            },
                                        ]}
                                    />
                                </Card>

                                <Card title="GP details">
                                    <DefinitionList
                                        items={[
                                            {
                                                key: "name",
                                                term: "Name",
                                                definition:
                                                    props.medical.gp_name ||
                                                    "Unknown",
                                            },
                                            {
                                                key: "phone",
                                                term: "Phone",
                                                definition:
                                                    props.medical.gp_phone ||
                                                    "Unknown",
                                            },
                                            {
                                                key: "address",
                                                term: "Address",
                                                definition:
                                                    props.medical.gp_address.join(
                                                        ", ",
                                                    ) || "Unknown",
                                            },
                                        ]}
                                    />
                                </Card>
                            </div>
                        </Tab>
                        <Tab name="Emergency Contacts">
                            <Card title="Emergency contacts">
                                {props.emergency_contacts.length === 0 && (
                                    <Alert
                                        variant="warning"
                                        title="No emergency contacts"
                                    >
                                        {props.name} does not have any emergency
                                        contact details on file. {props.name}{" "}
                                        should not be allowed to train until
                                        details have been provided.
                                    </Alert>
                                )}

                                {props.emergency_contacts.length > 0 && (
                                    <>
                                        <p className="text-sm">
                                            In an emergency, dial one of the
                                            contact numbers shown below.
                                        </p>

                                        <BasicListTwo>
                                            {props.emergency_contacts.map(
                                                (contact) => {
                                                    return (
                                                        <BasicListTwoItem
                                                            key={contact.id}
                                                        >
                                                            <div className="text-sm flex justify-between gap-4 items-center">
                                                                <div>
                                                                    <h3 className="font-medium">
                                                                        {
                                                                            contact.name
                                                                        }
                                                                    </h3>
                                                                    <p className="text-gray-500">
                                                                        {
                                                                            contact.relation
                                                                        }
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <ButtonLink
                                                                        href={
                                                                            contact.contact_number_url
                                                                        }
                                                                        external
                                                                    >
                                                                        {
                                                                            contact.contact_number_display
                                                                        }
                                                                    </ButtonLink>
                                                                </div>
                                                            </div>
                                                        </BasicListTwoItem>
                                                    );
                                                },
                                            )}
                                        </BasicListTwo>
                                    </>
                                )}
                            </Card>
                        </Tab>
                        <Tab name="Photography">
                            <Card title="Photography permissions">
                                {props.age >= 18 && (
                                    <Alert
                                        variant="success"
                                        title="No restrictions"
                                    >
                                        {props.name} is {props.age} years old.
                                        There are no photography restrictions in
                                        place.
                                    </Alert>
                                )}
                                {props.age < 18 && (
                                    <>
                                        <div className="prose prose-sm">
                                            <p>
                                                Club staff are required to
                                                follow Swim England's Wavepower
                                                as well as relevant club
                                                guidance when taking photos or
                                                videos.
                                            </p>

                                            <p>
                                                The parent/guardian has allowed:
                                            </p>
                                        </div>

                                        <DefinitionList
                                            items={[
                                                {
                                                    key: "website",
                                                    term: "Take photos for the club website",
                                                    definition: props
                                                        .photography_permissions
                                                        .website
                                                        ? "Yes"
                                                        : "No",
                                                },
                                                {
                                                    key: "social",
                                                    term: "Take photos for social media",
                                                    definition: props
                                                        .photography_permissions
                                                        .social
                                                        ? "Yes"
                                                        : "No",
                                                },
                                                {
                                                    key: "noticeboard",
                                                    term: "Take photos to display on the club noticeboard",
                                                    definition: props
                                                        .photography_permissions
                                                        .noticeboard
                                                        ? "Yes"
                                                        : "No",
                                                },
                                                {
                                                    key: "film_training",
                                                    term: "Film for training purposes (video replay)",
                                                    definition: props
                                                        .photography_permissions
                                                        .film_training
                                                        ? "Yes"
                                                        : "No",
                                                },
                                                {
                                                    key: "professional_photographer",
                                                    term: "Have photographs taken by a professional photographer employed by the club",
                                                    definition: props
                                                        .photography_permissions
                                                        .professional_photographer
                                                        ? "Yes"
                                                        : "No",
                                                },
                                            ]}
                                        />
                                    </>
                                )}
                            </Card>
                        </Tab>
                        <Tab name="Squads">
                            <div className="grid gap-4">
                                <Card
                                    title="Squads"
                                    footer={
                                        <Button
                                            onClick={() =>
                                                setShowNewSquadMoveModal(true)
                                            }
                                        >
                                            Add
                                        </Button>
                                    }
                                >
                                    {props.squads.length === 0 && (
                                        <Alert
                                            variant="warning"
                                            title="No squads"
                                        >
                                            {props.name} is not assigned to any
                                            squads.
                                        </Alert>
                                    )}

                                    {props.squads.length > 0 && (
                                        <BasicListTwo>
                                            {props.squads.map((squad) => {
                                                return (
                                                    <BasicListTwoItem
                                                        key={squad.id}
                                                    >
                                                        <div className="text-sm flex flex-col sm:flex-row sm:justify-between gap-4 sm:items-center">
                                                            <div>
                                                                <h3 className="font-medium">
                                                                    <Link
                                                                        href={route(
                                                                            "squads.show",
                                                                            squad.id,
                                                                        )}
                                                                    >
                                                                        {
                                                                            squad.name
                                                                        }
                                                                    </Link>
                                                                </h3>
                                                                <p className="text-gray-500">
                                                                    {
                                                                        squad.formatted_fee
                                                                    }
                                                                    /month
                                                                    {!squad.pays &&
                                                                        " (does not pay)"}
                                                                </p>
                                                            </div>
                                                            <div className="flex gap-2">
                                                                {/*<Button variant="secondary">*/}
                                                                {/*    Edit*/}
                                                                {/*</Button>*/}
                                                                <Button
                                                                    variant="danger"
                                                                    onClick={() => {
                                                                        setShowRemoveModal(
                                                                            squad.id,
                                                                        );
                                                                    }}
                                                                >
                                                                    Remove
                                                                </Button>
                                                            </div>
                                                        </div>

                                                        <NewSquadMoveDialog
                                                            show={
                                                                showRemoveModal ===
                                                                squad.id
                                                            }
                                                            onClose={() =>
                                                                setShowRemoveModal(
                                                                    null,
                                                                )
                                                            }
                                                            squadToLeave={
                                                                squad.id
                                                            }
                                                            member={{
                                                                name: props.name,
                                                                id: props.id,
                                                                first_name:
                                                                    props.first_name,
                                                            }}
                                                        />
                                                    </BasicListTwoItem>
                                                );
                                            })}
                                        </BasicListTwo>
                                    )}
                                </Card>

                                {props.squad_moves.length > 0 && (
                                    <Card title="Pending squad moves">
                                        <BasicListTwo>
                                            {props.squad_moves.map((move) => {
                                                return (
                                                    <BasicListTwoItem
                                                        key={move.id}
                                                    >
                                                        <div className="text-sm flex flex-col sm:flex-row sm:justify-between gap-4 sm:items-center">
                                                            <div>
                                                                {move.old_squad && (
                                                                    <>
                                                                        {!move.new_squad && (
                                                                            <>
                                                                                Leaving{" "}
                                                                            </>
                                                                        )}
                                                                        <Link
                                                                            href={route(
                                                                                "squads.show",
                                                                                move
                                                                                    .old_squad
                                                                                    .id,
                                                                            )}
                                                                        >
                                                                            {
                                                                                move
                                                                                    .old_squad
                                                                                    .name
                                                                            }
                                                                        </Link>
                                                                    </>
                                                                )}
                                                                {move.old_squad &&
                                                                    move.new_squad && (
                                                                        <>
                                                                            {" "}
                                                                            <ArrowRightIcon className="inline h-4" />{" "}
                                                                        </>
                                                                    )}
                                                                {move.new_squad && (
                                                                    <>
                                                                        {!move.old_squad && (
                                                                            <>
                                                                                Joining{" "}
                                                                            </>
                                                                        )}
                                                                        <Link
                                                                            href={route(
                                                                                "squads.show",
                                                                                move
                                                                                    .new_squad
                                                                                    .id,
                                                                            )}
                                                                        >
                                                                            {
                                                                                move
                                                                                    .new_squad
                                                                                    .name
                                                                            }
                                                                        </Link>
                                                                    </>
                                                                )}{" "}
                                                                on{" "}
                                                                {formatDate(
                                                                    move.date,
                                                                )}
                                                            </div>
                                                            <div className="flex gap-2">
                                                                <Button
                                                                    variant="secondary"
                                                                    onClick={() =>
                                                                        setShowEditModal(
                                                                            move.id,
                                                                        )
                                                                    }
                                                                >
                                                                    Edit
                                                                </Button>
                                                                <Button
                                                                    variant="danger"
                                                                    onClick={() =>
                                                                        setShowCancelModal(
                                                                            move.id,
                                                                        )
                                                                    }
                                                                >
                                                                    Cancel
                                                                </Button>
                                                            </div>

                                                            <EditSquadMoveDialog
                                                                moveId={move.id}
                                                                paying_in_new_squad={
                                                                    move.paying
                                                                }
                                                                new_squad={
                                                                    move.new_squad
                                                                }
                                                                old_squad={
                                                                    move.old_squad
                                                                }
                                                                show={
                                                                    showEditModal ===
                                                                    move.id
                                                                }
                                                                onClose={() => {
                                                                    setShowEditModal(
                                                                        null,
                                                                    );
                                                                }}
                                                                date={move.date}
                                                                member={{
                                                                    id: props.id,
                                                                    name: props.name,
                                                                }}
                                                            />

                                                            <CancelSquadMoveDialog
                                                                show={
                                                                    showCancelModal ===
                                                                    move.id
                                                                }
                                                                onClose={() =>
                                                                    setShowCancelModal(
                                                                        null,
                                                                    )
                                                                }
                                                                member={{
                                                                    id: props.id,
                                                                    name: props.name,
                                                                }}
                                                                moveId={move.id}
                                                            />
                                                        </div>
                                                    </BasicListTwoItem>
                                                );
                                            })}
                                        </BasicListTwo>
                                    </Card>
                                )}
                            </div>

                            <NewSquadMoveDialog
                                show={showNewSquadMoveModal}
                                onClose={() => setShowNewSquadMoveModal(false)}
                                member={{
                                    id: props.id,
                                    name: props.name,
                                    first_name: props.first_name,
                                }}
                            />
                        </Tab>
                        <Tab name="Extra fees">
                            <Card title="Extra fees">
                                {props.extra_fees.length === 0 && (
                                    <Alert
                                        variant="warning"
                                        title="No extra fees"
                                    >
                                        {props.name} has no extra fees assigned.
                                    </Alert>
                                )}
                                {props.extra_fees.length > 0 && (
                                    <BasicListTwo>
                                        {props.extra_fees.map((extraFee) => {
                                            return (
                                                <BasicListTwoItem
                                                    key={extraFee.id}
                                                >
                                                    <div className="text-sm flex flex-col sm:flex-row sm:justify-between gap-4 sm:items-center">
                                                        <div>
                                                            <h3 className="font-medium">
                                                                {extraFee.name}
                                                            </h3>
                                                            <p className="text-gray-500">
                                                                {
                                                                    extraFee.formatted_fee
                                                                }
                                                                /month (
                                                                {extraFee.type})
                                                            </p>
                                                        </div>
                                                    </div>
                                                </BasicListTwoItem>
                                            );
                                        })}
                                    </BasicListTwo>
                                )}
                            </Card>
                        </Tab>
                        <Tab name="Membership">
                            <div className="grid gap-4">
                                <Card title="Club membership">
                                    <DefinitionList
                                        items={[
                                            {
                                                key: "category",
                                                term: "Club membership category",
                                                definition:
                                                    props.club_membership_class
                                                        .name,
                                            },
                                            {
                                                key: "club_pays",
                                                term: "Club pays club membership fees ",
                                                definition:
                                                    props.club_pays_club_membership_fee
                                                        ? "Yes"
                                                        : "No",
                                            },
                                        ]}
                                    />
                                </Card>

                                <Card title="Swim England membership">
                                    <DefinitionList
                                        items={[
                                            {
                                                key: "category",
                                                term: "Swim England membership category",
                                                definition:
                                                    props
                                                        .governing_body_membership_class
                                                        .name,
                                            },
                                            {
                                                key: "membership_number",
                                                term: "Swim England membership number",
                                                definition:
                                                    props.governing_body_registration_number,
                                            },
                                            {
                                                key: "club_pays",
                                                term: "Club pays club membership fees ",
                                                definition:
                                                    props.club_pays_governing_body_membership_fee
                                                        ? "Yes"
                                                        : "No",
                                            },
                                        ]}
                                    />
                                </Card>
                            </div>
                        </Tab>
                    </Tabs>
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
