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

type Props = {
    id: number;
    name: string;
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
        gp_address: string;
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
};

const Show = (props: Props) => {
    const [showDeleteModal, setShowDeleteModal] = useState(false);

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
                            ]}
                        />
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
