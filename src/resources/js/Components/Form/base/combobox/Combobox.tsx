import React, {
    useCallback,
    useEffect,
    useRef,
    useState,
    useDeferredValue,
} from "react";
import axios from "@/Utils/axios";
import { Combobox as HeadlessCombobox } from "@headlessui/react";
import { CheckIcon, ChevronUpDownIcon } from "@heroicons/react/20/solid";
import { selectTextOnFocus } from "@/Components/Form/base/Input";
import { ExclamationCircleIcon } from "@heroicons/react/24/solid";

function classNames(...classes) {
    return classes.filter(Boolean).join(" ");
}

export interface Props {
    keyField?: string;
    endpoint: string;
    value?: string | number;
    label: string;
    id?: string;
    disabled?: boolean;
    name: string;
    onBlur: (ev: any) => void;
    onChange: (value: any) => void;
    multiple?: boolean;
    className?: string;
    isInvalid?: string;
    help?: string;
    nullable?: boolean;
}

export const Combobox: React.FC<Props> = ({
    keyField = "id",
    className = "",
    name,
    id,
    endpoint,
    onBlur: onBlurProps,
    ...props
}) => {
    const [query, setQuery] = useState("");
    const deferredQuery = useDeferredValue(query);
    const [items, setItems] = useState([]);
    const [selectedItem, setSelectedItem] = useState(null);
    const inputRef = useRef(null);

    const compareItems = (a, b) => {
        if (a && b) {
            return a[keyField] === b[keyField];
        }
        return false;
    };

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

    /**
     * Get the initial value if id provided
     */
    useEffect(() => {
        if (props.value) {
            axios
                .get(endpoint, {
                    params: {
                        id: props.value,
                    },
                })
                .then((value) => {
                    setItems(value?.data?.data);
                    if (value?.data?.data?.length > 0) {
                        setSelectedItem(value.data.data[0]);
                    }
                });
        } else {
            setSelectedItem(null);
        }
    }, [props.value, endpoint]);

    /**
     * Get updated search results as the user types
     */
    useEffect(() => {
        axios
            .get(endpoint, {
                params: {
                    query: deferredQuery,
                },
            })
            .then((value) => {
                if (value?.data?.data) {
                    setItems(value.data.data);
                }
            });
    }, [deferredQuery, endpoint]);

    return (
        <HeadlessCombobox
            as="div"
            value={selectedItem}
            onChange={(value) => {
                if (props.onChange) {
                    props.onChange(value);
                }
                setSelectedItem(value);
            }}
            by={compareItems}
            disabled={props.disabled}
            onBlur={onBlur}
            name={name}
            nullable={props.nullable}
        >
            <HeadlessCombobox.Label className="block text-sm font-medium text-gray-700">
                {props.label}
            </HeadlessCombobox.Label>
            <div className="relative mt-1">
                <HeadlessCombobox.Input
                    // as={Input}
                    ref={inputRef}
                    className={`w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm ${className}`}
                    onChange={(event) => setQuery(event.target.value)}
                    onFocus={() => {
                        selectTextOnFocus(inputRef);
                    }}
                    displayValue={(person) => person?.name}
                />
                <HeadlessCombobox.Button className="absolute inset-y-0 right-0 flex items-center rounded-r-md px-2 focus:outline-none">
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
                </HeadlessCombobox.Button>

                {items.length > 0 && (
                    <HeadlessCombobox.Options className="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                        {items.map((item) => (
                            <HeadlessCombobox.Option
                                key={item.id}
                                value={item}
                                className={({ active }) =>
                                    classNames(
                                        "relative cursor-default select-none py-2 pl-3 pr-9",
                                        active
                                            ? "bg-indigo-600 text-white"
                                            : "text-gray-900"
                                    )
                                }
                            >
                                {({ active, selected }) => (
                                    <>
                                        <div className="flex items-center">
                                            {item.image_url && (
                                                <img
                                                    src={item.image_url}
                                                    alt=""
                                                    className="h-6 w-6 flex-shrink-0 rounded-full"
                                                />
                                            )}
                                            <span
                                                className={classNames(
                                                    "ml-3 truncate",
                                                    selected && "font-semibold"
                                                )}
                                            >
                                                {item.name}
                                            </span>
                                        </div>

                                        {selected && (
                                            <span
                                                className={classNames(
                                                    "absolute inset-y-0 right-0 flex items-center pr-4",
                                                    active
                                                        ? "text-white"
                                                        : "text-indigo-600"
                                                )}
                                            >
                                                <CheckIcon
                                                    className="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        )}
                                    </>
                                )}
                            </HeadlessCombobox.Option>
                        ))}
                    </HeadlessCombobox.Options>
                )}
            </div>
            <div>
                {props.isInvalid && (
                    <p className="mt-2 text-sm text-red-600">
                        {props.isInvalid}
                    </p>
                )}

                {props.help && (
                    <p className="mt-2 text-sm text-gray-500">{props.help}</p>
                )}
            </div>
        </HeadlessCombobox>
    );
};

export default Combobox;
