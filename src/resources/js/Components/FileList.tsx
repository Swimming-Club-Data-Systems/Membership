import React, { useState } from "react";
import { PaperClipIcon } from "@heroicons/react/24/outline";
import Button from "@/Components/Button";
import Modal from "@/Components/Modal";
import Form from "@/Components/Form/Form";
import TextInput from "@/Components/Form/TextInput";
import * as yup from "yup";
import { DocumentIcon } from "@heroicons/react/24/outline";
import { router } from "@inertiajs/react";

export type FileProps = {
    id?: string;
    name: string;
    url: string;
    mime_type: string;
};

export type FileListProps = {
    items: FileProps[];
    editable?: boolean;
    updateRoute?: (id: string | number) => string;
    deleteRoute?: (id: string | number) => string;
};

const Link = (props) => <a {...props}>Download</a>;

export const FileList: React.FC<FileListProps> = ({
    items,
    updateRoute,
    deleteRoute,
    editable,
}) => {
    const [open, setOpen] = useState<boolean>(false);
    const [confirmDeleteOpen, setConfirmDeleteOpen] = useState<boolean>(false);
    const [file, setFile] = useState<FileProps>(null);

    if (items.length === 0) {
        return;
    }

    return (
        <>
            <ul className="divide-y divide-gray-200 rounded-md border border-gray-200">
                {items.map((item) => {
                    let isDownload = true;
                    switch (item.mime_type) {
                        case "application/pdf":
                            isDownload = false;
                            break;
                    }

                    // Wildcard test for videos and images
                    if (
                        item.mime_type.includes("image") ||
                        item.mime_type.includes("video")
                    ) {
                        isDownload = false;
                    }

                    return (
                        <li
                            key={item.url}
                            className="flex items-center justify-between py-3 pl-3 pr-4 text-sm"
                        >
                            <div className="flex w-0 flex-1 items-center">
                                <PaperClipIcon
                                    className="h-5 w-5 flex-shrink-0 text-gray-400"
                                    aria-hidden="true"
                                />
                                <span className="ml-2 w-0 flex-1 truncate">
                                    {item.name}
                                </span>
                            </div>
                            <div className="ml-4 flex-shrink-0">
                                {editable && (
                                    <Button
                                        variant="secondary"
                                        className="mr-3"
                                        onClick={() => {
                                            setOpen(true);
                                            setFile(item);
                                        }}
                                    >
                                        Edit
                                    </Button>
                                )}
                                <Link
                                    target="_blank"
                                    href={item.url}
                                    className="font-medium text-indigo-600 hover:text-indigo-500"
                                    download={isDownload}
                                    referrerPolicy="origin"
                                >
                                    Download
                                </Link>
                            </div>
                        </li>
                    );
                })}
            </ul>
            <Modal
                show={open}
                title="Edit file"
                onClose={() => setOpen(false)}
                Icon={DocumentIcon}
            >
                {file && (
                    <>
                        <Form
                            formName="file-edit"
                            method="put"
                            action={updateRoute(file.id)}
                            initialValues={{
                                name: file.name,
                            }}
                            validationSchema={yup.object().shape({
                                name: yup.string().required().max(255),
                            })}
                            submitTitle="Save"
                            onSuccess={() => {
                                setOpen(false);
                            }}
                            inertiaOptions={{
                                preserveScroll: true,
                            }}
                        >
                            <TextInput name="name" label="File name" />

                            <Button
                                variant="danger"
                                onClick={() => setConfirmDeleteOpen(true)}
                            >
                                Delete file
                            </Button>
                        </Form>

                        <Modal
                            show={confirmDeleteOpen}
                            variant="danger"
                            title="Delete file"
                            onClose={() => setConfirmDeleteOpen(false)}
                            Icon={DocumentIcon}
                            buttons={
                                <>
                                    <Button
                                        variant="danger"
                                        onClick={() => {
                                            router.delete(
                                                deleteRoute(file.id),
                                                {
                                                    preserveScroll: true,
                                                    onFinish: (page) => {
                                                        setConfirmDeleteOpen(
                                                            false,
                                                        );
                                                        setOpen(false);
                                                    },
                                                },
                                            );
                                        }}
                                    >
                                        Confirm
                                    </Button>
                                    <Button
                                        variant="secondary"
                                        onClick={() =>
                                            setConfirmDeleteOpen(false)
                                        }
                                    >
                                        Cancel
                                    </Button>
                                </>
                            }
                        >
                            <p>Are you sure you want to delete {file.name}?</p>
                        </Modal>
                    </>
                )}
            </Modal>
        </>
    );
};

export default FileList;
