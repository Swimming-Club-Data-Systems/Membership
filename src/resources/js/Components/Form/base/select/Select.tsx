import React, {
    useEffect,
    useRef,
    useState,
    Fragment,
    ReactNode,
    useCallback,
    useMemo,
} from "react";
import { Listbox as HeadlessListbox, Transition } from "@headlessui/react";
import { CheckIcon, ChevronUpDownIcon } from "@heroicons/react/20/solid";
import { ExclamationCircleIcon } from "@heroicons/react/24/solid";

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

export interface Props {
    keyField?: string;
    value?: string | number;
    label: string;
    id?: string;
    disabled?: boolean;
    readOnly?: boolean;
    name: string;
    onBlur: (ev: any) => void;
    onChange: (value: any) => void;
    multiple?: boolean;
    className?: string;
    isInvalid?: string;
    help?: string;
    nullable?: boolean;
    items: {
        value: string | number;
        name: ReactNode;
        disabled?: boolean;
    }[];
}

export const Select: React.FC<Props> = ({
    name,
    id,
    keyField = "value",
    className = "",
    value,
    items: rawItems,
    onChange: onChangeProps,
    onBlur: onBlurProps,
    nullable,
    ...props
}) => {
    const [selectedItem, setSelectedItem] = useState(null);
    const inputRef = useRef(null);

    const items = useMemo(() => {
        if (nullable) {
            return [{ value: null, name: "N/A" }, ...rawItems];
        }
        return rawItems;
    }, [rawItems, nullable]);

    /**
     * Get the initial value if id provided
     */
    useEffect(() => {
        if (value) {
            // Find value in items
            const selected = items.find(
                (element) => element[keyField] === value
            );

            setSelectedItem(selected);
        } else {
            setSelectedItem(null);
        }
    }, [items, keyField, value]);

    const onChange = useCallback(
        (value) => {
            if (onChangeProps) {
                onChangeProps(value);
            }
            setSelectedItem(value);
        },
        [onChangeProps]
    );

    const onBlur = useCallback(
        (e) => {
            if (onBlurProps) {
                onBlurProps({
                    target: {
                        name: name,
                        id: id,
                        outerHTML: e?.target?.outerHTML,
                    },
                });
            }
        },
        [onBlurProps, name, id]
    );

    const compareItems = useCallback(
        (a, b) => {
            if (a && b) {
                return a[keyField] === b[keyField];
            }
            return false;
        },
        [keyField]
    );

    return (
        <div ref={inputRef}>
            <HeadlessListbox
                as="div"
                value={selectedItem}
                onChange={onChange}
                by={compareItems}
                disabled={props.disabled || props.readOnly}
                name={name}
                // nullable={props.nullable}
            >
                {({ open }) => (
                    <>
                        <HeadlessListbox.Label className="block text-sm font-medium text-gray-700">
                            {props.label}
                        </HeadlessListbox.Label>
                        <div className="relative mt-1">
                            <HeadlessListbox.Button
                                className={`relative w-full cursor-default rounded-md bg-white ${
                                    props.disabled && "bg-gray-100"
                                } ${
                                    props.readOnly && "bg-gray-100"
                                } py-2 pl-3 pr-10 text-left text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-600 text-sm`}
                                onBlur={onBlur}
                            >
                                <span className="block truncate">
                                    {selectedItem?.name ?? "Select an item"}
                                </span>
                                <span className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                    {props.isInvalid && (
                                        <ExclamationCircleIcon
                                            className="h-5 w-5 mr-1 text-red-500"
                                            aria-hidden="true"
                                        />
                                    )}
                                    <ChevronUpDownIcon
                                        className="h-5 w-5 text-gray-400"
                                        aria-hidden="true"
                                    />
                                </span>
                            </HeadlessListbox.Button>

                            <Transition
                                show={open}
                                as={Fragment}
                                leave="transition ease-in duration-100"
                                leaveFrom="opacity-100"
                                leaveTo="opacity-0"
                            >
                                <HeadlessListbox.Options className="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                    {items.map((person) => (
                                        <HeadlessListbox.Option
                                            key={person.value}
                                            className={({ active }) =>
                                                classNames(
                                                    person.disabled
                                                        ? "text-gray-300"
                                                        : active
                                                        ? "bg-indigo-600 text-white"
                                                        : "text-gray-900",
                                                    "relative cursor-default select-none py-2 pl-3 pr-9"
                                                )
                                            }
                                            value={person}
                                            disabled={person.disabled}
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
                                                        {person.name}
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
                                        </HeadlessListbox.Option>
                                    ))}
                                </HeadlessListbox.Options>
                            </Transition>
                        </div>

                        <div>
                            {props.isInvalid && (
                                <p className="mt-2 text-sm text-red-600">
                                    {props.isInvalid}
                                </p>
                            )}

                            {props.help && (
                                <p className="mt-2 text-sm text-gray-500">
                                    {props.help}
                                </p>
                            )}
                        </div>
                    </>
                )}
            </HeadlessListbox>
        </div>
    );
};

export default Select;
