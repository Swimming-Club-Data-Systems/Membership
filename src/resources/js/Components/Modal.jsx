/* This example requires Tailwind CSS v2.0+ */
import React, { Fragment } from "react";
import { Dialog, Transition } from "@headlessui/react";
import { ExclamationIcon, XIcon } from "@heroicons/react/outline";

const Modal = ({
    variant,
    Icon = ExclamationIcon,
    show,
    onClose,
    ...props
}) => {
    let variantBg, variantText;

    switch (variant) {
        case "success":
            variantBg = "bg-green-100";
            variantText = "text-green-600";
            break;
        case "warning":
            variantBg = "bg-yellow-100";
            variantText = "text-yellow-600";
            break;
        case "danger":
            variantBg = "bg-red-100";
            variantText = "text-red-600";
            break;
        default:
            variantBg = "bg-indigo-100";
            variantText = "text-indigo-600";
            break;
    }

    return (
        <Transition.Root show={show} as={Fragment}>
            <Dialog as="div" className="relative z-10" onClose={onClose}>
                <Transition.Child
                    as={Fragment}
                    enter="ease-out duration-300"
                    enterFrom="opacity-0"
                    enterTo="opacity-100"
                    leave="ease-in duration-200"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                >
                    <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                </Transition.Child>

                <div className="fixed z-10 inset-0 overflow-y-auto">
                    <div className="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
                        <Transition.Child
                            as={Fragment}
                            enter="ease-out duration-300"
                            enterFrom="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enterTo="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leaveFrom="opacity-100 translate-y-0 sm:scale-100"
                            leaveTo="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <Dialog.Panel className="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full sm:p-6">
                                <div className="hidden sm:block absolute top-0 right-0 pt-4 pr-4">
                                    <button
                                        type="button"
                                        className="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        onClick={onClose}
                                    >
                                        <span className="sr-only">Close</span>
                                        <XIcon
                                            className="h-6 w-6"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                                <div className="sm:flex sm:items-start">
                                    <div
                                        className={`mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full ${variantBg} sm:mx-0 sm:h-10 sm:w-10`}
                                    >
                                        {/*<ExclamationIcon className={`h-6 w-6 ${variantText}`} aria-hidden="true"/>*/}
                                        <Icon
                                            className={`h-6 w-6 ${variantText}`}
                                            aria-hidden="true"
                                        />
                                    </div>
                                    <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <Dialog.Title
                                            as="h3"
                                            className="text-lg leading-6 font-medium text-gray-900"
                                        >
                                            {props.title}
                                        </Dialog.Title>
                                        <div className="mt-2 text-sm text-gray-500">
                                            {props.children}
                                        </div>
                                    </div>
                                </div>
                                {props.buttons && (
                                    <div className="mt-5 sm:mt-4 flex flex-row-reverse justify-center sm:justify-start gap-4">
                                        {props.buttons}
                                    </div>
                                )}
                            </Dialog.Panel>
                        </Transition.Child>
                    </div>
                </div>
            </Dialog>
        </Transition.Root>
    );
};

export default Modal;
