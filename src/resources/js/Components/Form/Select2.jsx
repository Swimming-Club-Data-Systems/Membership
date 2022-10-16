import React, { Fragment } from "react";
import { useField, useFormikContext } from "formik";
import { Listbox, Transition } from "@headlessui/react";
import {
    CheckIcon,
    ChevronDownIcon,
    ChevronUpIcon,
} from "@heroicons/react/solid";
import BaseInput from "./BaseInput";

/**
 * WORK IN PROGRESS
 */

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

const Select = ({
    options,
    disabled,
    type,
    leftText,
    // rightText,
    className = "",
    ...props
}) => {
    const [field, meta, helper] = useField({ ...props, type: "select" });
    const { isSubmitting } = useFormikContext();
    // const isValid = props.showValid && meta.touched && !meta.error;
    const isInvalid = meta.touched && meta.error;
    const controlId = props.id || props.name;

    if (!type) {
        type = "text";
    }

    let errorClasses = "";
    if (isInvalid) {
        errorClasses =
            "pr-10 border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500";
    }

    if (!leftText) {
        className += " rounded-l-md ";
    }

    const onChange = (ev) => {
        // field.onChange
        helper.setValue(ev.key);
        helper.setTouched(true);
    };

    const selected = options.find((option) => option.key === field.value);

    return (
        <Listbox value={selected} onChange={onChange} name={field.name}>
            {({ open }) => {
                const Icon = open ? ChevronUpIcon : ChevronDownIcon;
                return (
                    <>
                        <Listbox.Label className="block text-sm font-medium text-gray-700">
                            {props.label}
                        </Listbox.Label>
                        <div className="relative mt-1">
                            <Listbox.Button className="relative w-full cursor-default rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                <span className="block truncate">
                                    {selected.name}
                                </span>
                                <span className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                    <Icon
                                        className="h-5 w-5 text-gray-400"
                                        aria-hidden="true"
                                    />
                                </span>
                            </Listbox.Button>

                            <Transition
                                show={open}
                                as={Fragment}
                                leave="transition ease-in duration-100"
                                leaveFrom="opacity-100"
                                leaveTo="opacity-0"
                            >
                                <Listbox.Options className="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                    {options.map((option) => (
                                        <Listbox.Option
                                            key={option.key}
                                            className={({ active }) =>
                                                classNames(
                                                    active
                                                        ? "text-white bg-indigo-600"
                                                        : "text-gray-900",
                                                    "relative cursor-default select-none py-2 pl-3 pr-9"
                                                )
                                            }
                                            value={option}
                                        >
                                            {({ selected, active }) => (
                                                <>
                                                    <span
                                                        className={classNames(
                                                            selected
                                                                ? "font-semibold"
                                                                : "font-normal",
                                                            "block truncate"
                                                        )}
                                                    >
                                                        {option.name}
                                                    </span>

                                                    {selected ? (
                                                        <span
                                                            className={classNames(
                                                                active
                                                                    ? "text-white"
                                                                    : "text-indigo-600",
                                                                "absolute inset-y-0 right-0 flex items-center pr-4"
                                                            )}
                                                        >
                                                            <CheckIcon
                                                                className="h-5 w-5"
                                                                aria-hidden="true"
                                                            />
                                                        </span>
                                                    ) : null}
                                                </>
                                            )}
                                        </Listbox.Option>
                                    ))}
                                </Listbox.Options>
                            </Transition>
                        </div>
                    </>
                );
            }}
        </Listbox>
    );

    return (
        <BaseInput
            input={
                <>
                    <select
                        disabled={isSubmitting || disabled}
                        name="location"
                        className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 ${className} ${errorClasses}`}
                        // className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        id={controlId}
                        type={type}
                        {...field}
                        {...props}
                    >
                        {options.map((option) => {
                            return (
                                <option key={option.key} value={option.key}>
                                    {option.name}
                                </option>
                            );
                        })}
                    </select>
                </>
            }
            {...props}
        />
    );
};

export default Select;
