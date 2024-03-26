import React, { useState } from "react";
import { Head, router } from "@inertiajs/react";
import Container from "@/Components/Container";
import MainHeader from "@/Layouts/Components/MainHeader";
import FlashAlert from "@/Components/FlashAlert";
import MainLayout from "@/Layouts/MainLayout";
import PlainCollection from "@/Components/PlainCollection";
import { LaravelPaginatorProps } from "@/Components/Collection";
import { PhoneIcon } from "@heroicons/react/24/outline";
import Link from "@/Components/Link";
import Button from "@/Components/Button";
import Modal from "@/Components/Modal";
import Form from "@/Components/Form/Form";
import * as yup from "yup";
import "yup-phone";
import TextInput from "@/Components/Form/TextInput";
import Card from "@/Components/Card";

type Contact = {
    id: number;
    name: string;
    relation: string;
    phone_plain: string;
    phone: string;
    phone_url: string;
};

interface Props {
    contacts: LaravelPaginatorProps<Contact>;
}

const contactValidationSchema = yup.object().shape({
    name: yup
        .string()
        .required("A name is required.")
        .max(255, "Name must not exceed 255 characters."),
    relation: yup
        .string()
        .required("A relation is required.")
        .max(255, "Relation must not exceed 255 characters."),
    phone: yup
        .string()
        .required("A phone number is required.")
        .phone("GB", true, "Please provide a valid phone number.")
        .max(255, "Phone number must not exceed 255 characters."),
});

const ItemContent = (props: Contact) => {
    const [showEditModal, setShowEditModal] = useState<boolean>(false);
    const [showDeleteModal, setShowDeleteModal] = useState<boolean>(false);

    const deleteContact = async () => {
        router.delete(route("emergency-contacts.delete", [props.id]), {
            onFinish: (page) => {
                setShowDeleteModal(false);
            },
        });
    };

    return (
        <>
            <div className="flex items-center justify-between">
                <div className="flex items-center min-w-0">
                    <div className="min-w-0 truncate overflow-ellipsis flex-shrink">
                        <div className="text-sm">{props.name}</div>
                        <div className="text-sm text-gray-700 group-hover:text-gray-800">
                            {props.relation} -{" "}
                            <Link href={props.phone_url} external>
                                {props.phone}
                            </Link>
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
                        onClick={() => setShowDeleteModal(true)}
                    >
                        Delete
                    </Button>
                </div>
            </div>

            <Modal
                Icon={PhoneIcon}
                title="Edit emergency contact"
                show={showEditModal}
                onClose={() => setShowEditModal(false)}
            >
                <Form
                    initialValues={{
                        name: props.name,
                        relation: props.relation,
                        phone: props.phone_plain,
                    }}
                    validationSchema={contactValidationSchema}
                    submitTitle="Save"
                    action={route("emergency-contacts.update", props.id)}
                    method="put"
                    onSuccess={() => setShowEditModal(false)}
                >
                    <TextInput name="name" label="Name of contact" />
                    <TextInput name="relation" label="Relation to you" />
                    <TextInput name="phone" label="Phone number" />
                </Form>
            </Modal>

            <Modal
                variant="danger"
                Icon={PhoneIcon}
                title="Delete emergency contact"
                show={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
                buttons={
                    <>
                        <Button variant="danger" onClick={deleteContact}>
                            Confirm
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
                <p className="text-sm">
                    Are you sure you want to delete {props.name} from your
                    emergency contacts?
                </p>
            </Modal>
        </>
    );
};

const crumbs = [
    { route: "emergency-contacts.index", name: "Emergency contacts" },
];

const Index = (props: Props) => {
    const [showCreateModal, setShowCreateModal] = useState(false);

    return (
        <>
            <Head title="Emergency contacts" />

            <Container>
                <MainHeader
                    title="Emergency contacts"
                    subtitle="Add, edit or remove your emergency contacts"
                    buttons={
                        <Button onClick={() => setShowCreateModal(true)}>
                            New
                        </Button>
                    }
                />

                <FlashAlert className="mb-4" />
            </Container>

            <Container noMargin>
                <div className="grid gap-4">
                    <Card title="About emergency contacts">
                        <div className="prose prose-sm">
                            <p>
                                We'll use these emergency contacts for all
                                members connected to your account if we can't
                                reach you on your phone number. You can change
                                your own phone number in{" "}
                                <Link href={route("my_account.index")}>
                                    My Account
                                </Link>
                                .
                            </p>
                            <p>
                                Please let people know if you have assigned them
                                as your emergency contacts.
                            </p>
                        </div>
                    </Card>

                    <PlainCollection
                        {...props.contacts}
                        itemRenderer={(item) => <ItemContent {...item} />}
                    />
                </div>
            </Container>

            <Modal
                Icon={PhoneIcon}
                title="Create emergency contact"
                show={showCreateModal}
                onClose={() => setShowCreateModal(false)}
            >
                <Form
                    initialValues={{
                        name: "",
                        relation: "",
                        phone: "",
                    }}
                    validationSchema={contactValidationSchema}
                    submitTitle="Create"
                    action={route("emergency-contacts.index")}
                    method="post"
                    onSuccess={() => setShowCreateModal(false)}
                >
                    <TextInput name="name" label="Name of contact" />
                    <TextInput name="relation" label="Relation to you" />
                    <TextInput name="phone" label="Phone number" />
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
